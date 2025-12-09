<?php
namespace App\Models;

use CodeIgniter\Model;

class StockMovementModel extends Model
{
    protected $table = 'stock_movements';
    protected $primaryKey = 'movement_id';
    protected $allowedFields = [
        'transaction_number', 'items_in_progress', 'order_number', 'id', 
        'quantity', 'company_name', 'movement_type', 'location', 'status', 'warehouse_id'
    ];
    protected $useTimestamps = false;

    /**
     * Create a stock movement record
     * 
     * @param array $data Movement data
     * @return int|false Movement ID on success, false on failure
     */
    public function createMovement($data)
    {
        // Validate required fields
        $requiredFields = ['transaction_number', 'order_number', 'id', 'quantity', 'movement_type'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Set default values
        $movementData = [
            'transaction_number' => $data['transaction_number'],
            'items_in_progress' => $data['items_in_progress'] ?? 1,
            'order_number' => $data['order_number'], // PO-1234, SO-5678, etc.
            'id' => $data['id'], // inventory item id
            'quantity' => $data['quantity'],
            'company_name' => $data['company_name'] ?? 'WeBuild Construction',
            'movement_type' => $data['movement_type'], // 'in' or 'out'
            'location' => $data['location'] ?? 'Warehouse',
            'status' => $data['status'] ?? 'pending',
            'warehouse_id' => isset($data['warehouse_id']) ? (int)$data['warehouse_id'] : null,
        ];

        try {
            // If the underlying DB table doesn't have a warehouse_id column, remove it to avoid SQL errors
            try {
                $fields = $this->db->getFieldNames($this->table);
                if (!in_array('warehouse_id', $fields)) {
                    unset($movementData['warehouse_id']);
                }
            } catch (\Throwable $e) {
                // If inspection fails, silently continue without removing field — DB may still accept it
            }

            return $this->insert($movementData);
        } catch (\Exception $e) {
            log_message('error', 'Stock movement creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get movement history with item details
     * 
     * @param int $limit Number of records to return
     * @return array Movement history
     */
    public function getMovementHistory($limit = 50)
    {
        return $this->getMovementHistoryByWarehouse($limit, null);
    }

    /**
     * Get movement by reference number
     * 
     * @param string $referenceNo Order number (PO-1234, SO-5678, etc.)
     * @return array|null Movement data or null if not found
     */
    public function getByReferenceNumber($referenceNo)
    {
        return $this->db->table('stock_movements sm')
            ->select('sm.*, i.name as item_name, i.sku as item_sku, i.warehouse_id')
            ->join('inventory i', 'i.id = sm.id', 'left')
            ->where('sm.order_number', $referenceNo)
            ->get()
            ->getRowArray();
    }

    /**
     * Update movement status
     * 
     * @param int $movementId Movement ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateMovementStatus($movementId, $status)
    {
        return $this->update($movementId, ['status' => $status]);
    }

    /**
     * Get pending movements (for dashboard display)
     * 
     * @return array Pending movements
     */
    public function getPendingMovements()
    {
        return $this->getByTypeAndStatusAndWarehouse(null, 'pending', null);
    }

    /**
     * Get movements by type and status
     * 
     * @param string $type Movement type ('in' or 'out')
     * @param string $status Movement status
     * @return array Movements
     */
    public function getByTypeAndStatus($type, $status = 'pending')
    {
        return $this->getByTypeAndStatusAndWarehouse($type, $status, null);
    }

    /**
     * Get movement history with optional warehouse filter
     */
    public function getMovementHistoryByWarehouse($limit = 50, $warehouseId = null)
    {
        $builder = $this->db->table('stock_movements sm')
            ->select('sm.*, i.name as item_name, i.sku as item_sku, COALESCE(sm.warehouse_id, i.warehouse_id) as warehouse_id')
            ->join('inventory i', 'i.id = sm.id', 'left')
            ->orderBy('sm.movement_id', 'DESC')
            ->limit($limit);

        if ($warehouseId) {
            $builder->where('COALESCE(sm.warehouse_id, i.warehouse_id)', (int)$warehouseId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get movements by type and status, with optional warehouse filter
     */
    public function getByTypeAndStatusAndWarehouse($type = null, $status = 'pending', $warehouseId = null)
    {
        $builder = $this->db->table('stock_movements sm')
            ->select('sm.*, i.name as item_name, i.sku as item_sku, COALESCE(sm.warehouse_id, i.warehouse_id) as warehouse_id')
            ->join('inventory i', 'i.id = sm.id', 'left')
            ->orderBy('sm.movement_id', 'DESC');

        if ($type !== null) {
            $builder->where('sm.movement_type', $type);
        }

        if ($status !== null) {
            $builder->where('sm.status', $status);
        }

        if ($warehouseId) {
            $builder->where('COALESCE(sm.warehouse_id, i.warehouse_id)', (int)$warehouseId);
        }

        return $builder->get()->getResultArray();
    }
}