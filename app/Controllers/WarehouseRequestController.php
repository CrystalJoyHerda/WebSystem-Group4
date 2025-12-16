<?php

namespace App\Controllers;

use App\Models\WarehouseRequestModel;
use App\Models\OutboundReceiptModel;
use App\Models\InboundReceiptModel;
use App\Models\StaffTaskModel;
use App\Models\InventoryModel;

class WarehouseRequestController extends BaseController
{
    protected $warehouseRequestModel;
    protected $outboundReceiptModel;
    protected $inboundReceiptModel;
    protected $staffTaskModel;
    protected $inventoryModel;
    protected $db;

    public function __construct()
    {
        $this->warehouseRequestModel = new WarehouseRequestModel();
        $this->outboundReceiptModel = new OutboundReceiptModel();
        $this->inboundReceiptModel = new InboundReceiptModel();
        $this->staffTaskModel = new StaffTaskModel();
        $this->inventoryModel = new InventoryModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new warehouse request
     */
    public function create()
    {
        if (!session()->get('isLoggedIn') || session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $data = $this->request->getJSON(true);

        // Validate required fields
        if (empty($data['requesting_warehouse_id']) || empty($data['supplying_warehouse_id']) || empty($data['items'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing required fields']);
        }

        $requestData = [
            'requesting_warehouse_id' => $data['requesting_warehouse_id'],
            'supplying_warehouse_id' => $data['supplying_warehouse_id'],
            'requested_by' => session('user_id'),
            'notes' => $data['notes'] ?? null,
        ];

        $requestId = $this->warehouseRequestModel->createRequest($requestData, $data['items']);

        if ($requestId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Warehouse request created successfully',
                'request_id' => $requestId
            ]);
        }

        return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to create request']);
    }

    /**
     * Get pending requests for manager's warehouse
     */
    public function getPendingRequests()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $warehouseId = $this->request->getGet('warehouse_id');
        if (!$warehouseId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Warehouse ID required']);
        }

        $requests = $this->warehouseRequestModel->getPendingRequestsForWarehouse($warehouseId);

        // Get items for each request
        foreach ($requests as &$request) {
            $items = $this->db->table('warehouse_request_items wri')
                             ->select('wri.*, i.name as item_name, i.sku as item_sku')
                             ->join('inventory i', 'i.id = wri.item_id', 'left')
                             ->where('wri.request_id', $request['id'])
                             ->get()
                             ->getResultArray();
            $request['items'] = $items;
        }

        return $this->response->setJSON($requests);
    }

    /**
     * Approve a warehouse request
     */
    public function approve($requestId)
    {
        if (!session()->get('isLoggedIn') || session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $db = $this->db;
        $db->transStart();

        try {
            // Get request details
            $request = $this->warehouseRequestModel->getRequestWithDetails($requestId);
            if (!$request) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Request not found']);
            }

            // 1. Approve the request
            $this->warehouseRequestModel->approveRequest($requestId, session('user_id'));

            // 2. Create outbound receipt for Warehouse B (supplying warehouse)
            $outboundData = [
                'reference_no' => 'OUT-' . $request['reference_no'],
                'customer_name' => $request['requesting_warehouse_name'],
                'warehouse_id' => $request['supplying_warehouse_id'],
                'status' => 'Approved',
                'total_items' => count($request['items']),
                'approved_by' => session('user_id'),
                'approved_at' => date('Y-m-d H:i:s'),
            ];

            $outboundId = $this->outboundReceiptModel->insert($outboundData);

            // Create outbound receipt items
            foreach ($request['items'] as $item) {
                $db->table('outbound_receipt_items')->insert([
                    'receipt_id' => $outboundId,
                    'item_id' => $item['item_id'],
                    'warehouse_id' => $request['supplying_warehouse_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // Link outbound receipt to request
            $this->warehouseRequestModel->linkOutboundReceipt($requestId, $outboundId);

            // 3. Create staff tasks for outbound picking
            foreach ($request['items'] as $item) {
                $this->staffTaskModel->createTask([
                    'reference_no' => 'OUT-' . $request['reference_no'],
                    'warehouse_id' => $request['supplying_warehouse_id'],
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'item_sku' => $item['item_sku'],
                    'quantity' => $item['quantity'],
                    'movement_type' => 'OUTBOUND',
                    'status' => 'Pending',
                    'assigned_by' => session('user_id'),
                ]);
            }

            // 4. Create inbound receipt for Warehouse A (requesting warehouse) with status PACKING
            $inboundData = [
                'reference_no' => 'IN-' . $request['reference_no'],
                'supplier_name' => $request['supplying_warehouse_name'],
                'warehouse_id' => $request['requesting_warehouse_id'],
                'status' => 'PACKING',
                'total_items' => count($request['items']),
            ];

            $inboundId = $this->inboundReceiptModel->insert($inboundData);

            // Create inbound receipt items
            foreach ($request['items'] as $item) {
                $db->table('inbound_receipt_items')->insert([
                    'receipt_id' => $inboundId,
                    'item_id' => $item['item_id'],
                    'warehouse_id' => $request['requesting_warehouse_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => 0.00,
                ]);
            }

            // Link inbound receipt to request
            $this->warehouseRequestModel->linkInboundReceipt($requestId, $inboundId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Request approved successfully'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error approving warehouse request: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to approve request']);
        }
    }

    /**
     * Get deliveries for warehouse (SCANNED or DELIVERING status)
     */
    public function getDeliveries()
    {
        if (!session()->get('isLoggedIn') || session('role') !== 'staff') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $warehouseId = $this->request->getGet('warehouse_id');
        if (!$warehouseId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Warehouse ID required']);
        }

        $deliveries = $this->warehouseRequestModel->getDeliveriesForWarehouse($warehouseId);

        // Get items for each delivery
        foreach ($deliveries as &$delivery) {
            $items = $this->db->table('warehouse_request_items wri')
                             ->select('wri.*, i.name as item_name, i.sku as item_sku')
                             ->join('inventory i', 'i.id = wri.item_id', 'left')
                             ->where('wri.request_id', $delivery['id'])
                             ->get()
                             ->getResultArray();
            $delivery['items'] = $items;
        }

        return $this->response->setJSON($deliveries);
    }

    /**
     * Mark delivery as delivered
     */
    public function markDelivered($requestId)
    {
        if (!session()->get('isLoggedIn') || session('role') !== 'staff') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $db = $this->db;
        $db->transStart();

        try {
            // Get request details
            $request = $this->warehouseRequestModel->find($requestId);
            if (!$request) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Request not found']);
            }

            // 1. Update warehouse request status to DELIVERED
            $this->warehouseRequestModel->updateStatus($requestId, 'DELIVERED');

            // 2. Update outbound receipt status to DELIVERED
            if ($request['outbound_receipt_id']) {
                $this->outboundReceiptModel->update($request['outbound_receipt_id'], [
                    'status' => 'DELIVERED'
                ]);
            }

            // 3. Update inbound receipt status to DELIVERED
            if ($request['inbound_receipt_id']) {
                $this->inboundReceiptModel->update($request['inbound_receipt_id'], [
                    'status' => 'DELIVERED'
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Delivery marked as delivered'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error marking delivery as delivered: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to mark as delivered']);
        }
    }

    /**
     * Delivery page view (for staff)
     */
    public function deliveryPage()
    {
        if (!session()->get('isLoggedIn') || session('role') !== 'staff') {
            return redirect()->to('/login');
        }

        return view('dashboard/staff/deliveries');
    }
}
