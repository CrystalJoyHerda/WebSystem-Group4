<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseRequestModel extends Model
{
    protected $table = 'warehouse_requests';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'reference_no', 'requesting_warehouse_id', 'supplying_warehouse_id', 
        'requested_by', 'approved_by', 'approved_at', 'status', 
        'outbound_receipt_id', 'inbound_receipt_id', 'notes', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get request with items and warehouse names
     */
    public function getRequestWithDetails($requestId)
    {
        $request = $this->select('warehouse_requests.*, 
                                  w1.name as requesting_warehouse_name, 
                                  w2.name as supplying_warehouse_name,
                                  u.username as requested_by_name')
                        ->join('warehouses w1', 'w1.id = warehouse_requests.requesting_warehouse_id', 'left')
                        ->join('warehouses w2', 'w2.id = warehouse_requests.supplying_warehouse_id', 'left')
                        ->join('users u', 'u.id = warehouse_requests.requested_by', 'left')
                        ->find($requestId);

        if (!$request) return null;

        // Get request items
        $db = $this->db;
        $items = $db->table('warehouse_request_items wri')
                   ->select('wri.*, i.name as item_name, i.sku as item_sku')
                   ->join('inventory i', 'i.id = wri.item_id', 'left')
                   ->where('wri.request_id', $requestId)
                   ->get()
                   ->getResultArray();

        $request['items'] = $items;
        return $request;
    }

    /**
     * Get all pending requests for a warehouse
     */
    public function getPendingRequestsForWarehouse($warehouseId)
    {
        return $this->select('warehouse_requests.*, 
                             w1.name as requesting_warehouse_name, 
                             w2.name as supplying_warehouse_name,
                             u.username as requested_by_name')
                    ->join('warehouses w1', 'w1.id = warehouse_requests.requesting_warehouse_id', 'left')
                    ->join('warehouses w2', 'w2.id = warehouse_requests.supplying_warehouse_id', 'left')
                    ->join('users u', 'u.id = warehouse_requests.requested_by', 'left')
                    ->where('warehouse_requests.supplying_warehouse_id', $warehouseId)
                    ->where('warehouse_requests.status', 'PENDING')
                    ->orderBy('warehouse_requests.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get deliveries ready for transport (status = SCANNED or DELIVERING)
     */
    public function getDeliveriesForWarehouse($warehouseId)
    {
        return $this->select('warehouse_requests.*, 
                             w1.name as requesting_warehouse_name, 
                             w2.name as supplying_warehouse_name')
                    ->join('warehouses w1', 'w1.id = warehouse_requests.requesting_warehouse_id', 'left')
                    ->join('warehouses w2', 'w2.id = warehouse_requests.supplying_warehouse_id', 'left')
                    ->whereIn('warehouse_requests.status', ['SCANNED', 'DELIVERING'])
                    ->where('warehouse_requests.supplying_warehouse_id', $warehouseId)
                    ->orderBy('warehouse_requests.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Generate unique reference number
     */
    public function generateReferenceNo()
    {
        $prefix = 'WR-' . date('Ymd') . '-';
        $lastRequest = $this->like('reference_no', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->first();

        if ($lastRequest) {
            $lastNumber = (int)substr($lastRequest['reference_no'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a warehouse request with items
     */
    public function createRequest($data, $items)
    {
        $db = $this->db;
        $db->transStart();

        try {
            // Generate reference number
            $referenceNo = $this->generateReferenceNo();

            // Create request
            $requestData = [
                'reference_no' => $referenceNo,
                'requesting_warehouse_id' => $data['requesting_warehouse_id'],
                'supplying_warehouse_id' => $data['supplying_warehouse_id'],
                'requested_by' => $data['requested_by'],
                'notes' => $data['notes'] ?? null,
                'status' => 'PENDING',
            ];

            $requestId = $this->insert($requestData);

            // Create request items
            foreach ($items as $item) {
                $db->table('warehouse_request_items')->insert([
                    'request_id' => $requestId,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }


            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Failed to create warehouse request');
            }

            return $requestId;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error creating warehouse request: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve a warehouse request
     */
    public function approveRequest($requestId, $managerId)
    {
        $request = $this->find($requestId);
        if (!$request) {
            return false;
        }

        return $this->update($requestId, [
            'status' => 'APPROVED',
            'approved_by' => $managerId,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update request status
     */
    public function updateStatus($requestId, $status)
    {
        return $this->update($requestId, ['status' => $status]);
    }

    /**
     * Link outbound receipt to request
     */
    public function linkOutboundReceipt($requestId, $receiptId)
    {
        return $this->update($requestId, ['outbound_receipt_id' => $receiptId]);
    }

    /**
     * Link inbound receipt to request
     */
    public function linkInboundReceipt($requestId, $receiptId)
    {
        return $this->update($requestId, ['inbound_receipt_id' => $receiptId]);
    }
}
    