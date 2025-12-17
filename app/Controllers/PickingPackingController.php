<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\OutboundReceiptModel;
use App\Models\InventoryModel;

class PickingPackingController extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Display picking and packing page
     */
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('info', 'Please log in to access this page.');
            return redirect()->to('/login');
        }
        if (session('role') !== 'staff') {
            return redirect()->to('/login');
        }
        
        return view('dashboard/staff/picking_packing');
    }

    /**
     * Get picking tasks for logged-in staff
     */
    public function getPickingTasks()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $staffId = session()->get('userID');
        $warehouseId = session()->get('warehouse_id');

        // Get approved outbound receipts that haven't been picked yet
        $tasks = $this->db->table('outbound_receipts or')
            ->select('or.*, ori.id as item_id, ori.item_id as inventory_item_id, ori.quantity as required_quantity, 
                     i.name as item_name, i.sku as item_sku, i.location as storage_location, i.quantity as available_stock,
                     COALESCE(pp.status, "Pending") as picking_status, pp.id as picking_id, pp.picked_quantity')
            ->join('outbound_receipt_items ori', 'ori.receipt_id = or.id', 'left')
            ->join('inventory i', 'i.id = ori.item_id', 'left')
            ->join('picking_packing_tasks pp', 'pp.receipt_id = or.id AND pp.item_id = ori.item_id AND pp.task_type = "PICKING"', 'left')
            ->where('or.status', 'Approved')
            ->where('or.warehouse_id', $warehouseId)
            ->whereIn('COALESCE(pp.status, "Pending")', ['Pending', 'In Progress'])
            ->orderBy('or.created_at', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['tasks' => $tasks]);
    }

    /**
     * Start picking a task
     */
    public function startPicking()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $receiptId = $this->request->getPost('receipt_id');
        $itemId = $this->request->getPost('item_id');
        $staffId = session()->get('userID');

        // Check if picking task already exists
        $existing = $this->db->table('picking_packing_tasks')
            ->where('receipt_id', $receiptId)
            ->where('item_id', $itemId)
            ->where('task_type', 'PICKING')
            ->get()
            ->getRowArray();

        if ($existing) {
            // Update to In Progress
            $this->db->table('picking_packing_tasks')
                ->where('id', $existing['id'])
                ->update([
                    'status' => 'In Progress',
                    'assigned_to' => $staffId,
                    'started_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            return $this->response->setJSON(['success' => true, 'task_id' => $existing['id']]);
        }

        // Create new picking task
        $taskId = $this->db->table('picking_packing_tasks')->insert([
            'receipt_id' => $receiptId,
            'item_id' => $itemId,
            'task_type' => 'PICKING',
            'status' => 'In Progress',
            'assigned_to' => $staffId,
            'started_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['success' => true, 'task_id' => $this->db->insertID()]);
    }

    /**
     * Complete picking and create stock movement
     */
    public function completePicking()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $taskId = $this->request->getPost('task_id');
        $receiptId = $this->request->getPost('receipt_id');
        $itemId = $this->request->getPost('item_id');
        $pickedQuantity = (int)$this->request->getPost('picked_quantity');
        $staffId = session()->get('userID');
        $warehouseId = session()->get('warehouse_id');

        if ($pickedQuantity <= 0) {
            return $this->response->setJSON(['error' => 'Invalid quantity'])->setStatusCode(400);
        }

        // Get or create task
        if ($taskId) {
            $task = $this->db->table('picking_packing_tasks')->where('id', $taskId)->get()->getRowArray();
        } else {
            // Create task first
            $this->db->table('picking_packing_tasks')->insert([
                'receipt_id' => $receiptId,
                'item_id' => $itemId,
                'task_type' => 'PICKING',
                'status' => 'In Progress',
                'assigned_to' => $staffId,
                'started_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $taskId = $this->db->insertID();
            $task = $this->db->table('picking_packing_tasks')->where('id', $taskId)->get()->getRowArray();
        }

        if (!$task) {
            return $this->response->setJSON(['error' => 'Task not found'])->setStatusCode(404);
        }

        // Get required quantity and reference number
        $receiptItem = $this->db->table('outbound_receipt_items ori')
            ->select('ori.*, or.reference_no')
            ->join('outbound_receipts or', 'or.id = ori.receipt_id')
            ->where('ori.receipt_id', $task['receipt_id'])
            ->where('ori.item_id', $task['item_id'])
            ->get()
            ->getRowArray();

        if (!$receiptItem) {
            return $this->response->setJSON(['error' => 'Receipt item not found'])->setStatusCode(404);
        }

        // Validate picked quantity
        if ($pickedQuantity > $receiptItem['quantity']) {
            return $this->response->setJSON(['error' => 'Picked quantity exceeds required quantity'])->setStatusCode(400);
        }

        // Check available stock
        $inventory = $this->db->table('inventory')
            ->where('id', $task['item_id'])
            ->get()
            ->getRowArray();

        if (!$inventory || $inventory['quantity'] < $pickedQuantity) {
            return $this->response->setJSON(['error' => 'Insufficient stock available'])->setStatusCode(400);
        }

        $this->db->transStart();

        try {
            // Update picking task
            $this->db->table('picking_packing_tasks')
                ->where('id', $taskId)
                ->update([
                    'picked_quantity' => $pickedQuantity,
                    'status' => 'Picked',
                    'completed_by' => $staffId,
                    'completed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Deduct stock (reserve for packing)
            $this->db->table('inventory')
                ->where('id', $task['item_id'])
                ->set('quantity', "quantity - {$pickedQuantity}", false)
                ->update();

            // Create stock movement record
            $this->db->table('stock_movements')->insert([
                'warehouse_id' => $warehouseId,
                'item_id' => $task['item_id'],
                'quantity' => $pickedQuantity,
                'movement_type' => 'OUTBOUND',
                'reason' => 'Order Picking - ' . $receiptItem['reference_no'],
                'reference_no' => $receiptItem['reference_no'],
                'created_by' => $staffId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Auto-generate packing task
            $packingExists = $this->db->table('picking_packing_tasks')
                ->where('receipt_id', $task['receipt_id'])
                ->where('item_id', $task['item_id'])
                ->where('task_type', 'PACKING')
                ->get()
                ->getRowArray();

            if (!$packingExists) {
                $this->db->table('picking_packing_tasks')->insert([
                    'receipt_id' => $task['receipt_id'],
                    'item_id' => $task['item_id'],
                    'task_type' => 'PACKING',
                    'status' => 'Pending',
                    'picked_quantity' => $pickedQuantity,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['error' => 'Transaction failed'])->setStatusCode(500);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Picking completed successfully. Packing task created.']);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Picking completion failed: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to complete picking'])->setStatusCode(500);
        }
    }

    /**
     * Get packing tasks for logged-in staff
     */
    public function getPackingTasks()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $warehouseId = session()->get('warehouse_id');

        // Get packing tasks (only show if picking is completed)
        $tasks = $this->db->table('picking_packing_tasks pp')
            ->select('pp.*, or.reference_no, or.customer_name, i.name as item_name, i.sku as item_sku')
            ->join('outbound_receipts or', 'or.id = pp.receipt_id', 'left')
            ->join('inventory i', 'i.id = pp.item_id', 'left')
            ->where('pp.task_type', 'PACKING')
            ->where('pp.status', 'Pending')
            ->where('or.warehouse_id', $warehouseId)
            ->orderBy('pp.created_at', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['tasks' => $tasks]);
    }

    /**
     * Complete packing
     */
    public function completePacking()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $taskId = $this->request->getPost('task_id');
        $packedQuantity = (int)$this->request->getPost('packed_quantity');
        $boxCount = (int)($this->request->getPost('box_count') ?? 1);
        $staffId = session()->get('userID');

        if ($packedQuantity <= 0) {
            return $this->response->setJSON(['error' => 'Invalid quantity'])->setStatusCode(400);
        }

        // Get task details
        $task = $this->db->table('picking_packing_tasks')
            ->where('id', $taskId)
            ->where('task_type', 'PACKING')
            ->get()
            ->getRowArray();

        if (!$task) {
            return $this->response->setJSON(['error' => 'Task not found'])->setStatusCode(404);
        }

        // Validate packed quantity matches picked quantity
        if ($packedQuantity != $task['picked_quantity']) {
            return $this->response->setJSON(['error' => 'Packed quantity must match picked quantity'])->setStatusCode(400);
        }

        $this->db->transStart();

        try {
            // Update packing task
            $this->db->table('picking_packing_tasks')
                ->where('id', $taskId)
                ->update([
                    'packed_quantity' => $packedQuantity,
                    'box_count' => $boxCount,
                    'status' => 'Packed',
                    'completed_by' => $staffId,
                    'completed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Check if all items in the receipt are packed
            $allPacked = $this->db->table('picking_packing_tasks pp')
                ->join('outbound_receipt_items ori', 'ori.receipt_id = pp.receipt_id AND ori.item_id = pp.item_id', 'inner')
                ->where('pp.receipt_id', $task['receipt_id'])
                ->where('pp.task_type', 'PACKING')
                ->where('pp.status !=', 'Packed')
                ->countAllResults();

            // If all items packed, update order status
            if ($allPacked == 0) {
                $this->db->table('outbound_receipts')
                    ->where('id', $task['receipt_id'])
                    ->update([
                        'status' => 'SCANNED', // Ready for Shipment/Delivery
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['error' => 'Transaction failed'])->setStatusCode(500);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Packing completed successfully']);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Packing completion failed: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to complete packing'])->setStatusCode(500);
        }
    }
}
