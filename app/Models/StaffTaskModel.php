<?php
namespace App\Models;

use CodeIgniter\Model;

class StaffTaskModel extends Model
{
    protected $table = 'staff_tasks';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'movement_id', 'reference_no', 'warehouse_id', 'item_id', 'item_name', 
        'item_sku', 'quantity', 'movement_type', 'status', 'assigned_by', 
        'completed_by', 'completed_at', 'notes', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Create a staff task for barcode scanning
     * 
     * @param array $data Task data
     * @return int|false Task ID on success, false on failure
     */
    public function createTask($data)
    {
        // Validate required fields
        $requiredFields = ['reference_no', 'warehouse_id', 'item_id', 'item_name', 'quantity', 'movement_type'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Check if task already exists for this reference and item
        $existingTask = $this->where('reference_no', $data['reference_no'])
                            ->where('item_id', $data['item_id'])
                            ->where('status', 'Pending')
                            ->first();

        if ($existingTask) {
            log_message('info', "Task already exists for reference {$data['reference_no']} and item {$data['item_id']}");
            return $existingTask['id'];
        }

        // Set default values
        $taskData = [
            'movement_id' => $data['movement_id'] ?? null,
            'reference_no' => $data['reference_no'],
            'warehouse_id' => $data['warehouse_id'],
            'item_id' => $data['item_id'],
            'item_name' => $data['item_name'],
            'item_sku' => $data['item_sku'] ?? '',
            'quantity' => $data['quantity'],
            'movement_type' => strtoupper($data['movement_type']), // IN or OUT
            'status' => 'Pending',
            'assigned_by' => $data['assigned_by'] ?? session('user_id'),
            'notes' => $data['notes'] ?? null
        ];

        try {
            $taskId = $this->insert($taskData);
            log_message('info', "Staff task created: Task ID {$taskId} for reference {$data['reference_no']}");
            return $taskId;
        } catch (\Exception $e) {
            log_message('error', 'Staff task creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get pending tasks for staff dashboard
     * 
     * @param int|null $warehouseId Filter by warehouse
     * @return array Pending tasks
     */
    public function getPendingTasks($warehouseId = null)
    {
        $builder = $this->db->table('staff_tasks st')
            ->select('st.*, w.name as warehouse_name, i.sku as current_sku, i.quantity as current_stock')
            ->join('warehouses w', 'w.id = st.warehouse_id', 'left')
            ->join('inventory i', 'i.id = st.item_id', 'left')
            ->where('st.status', 'Pending')
            ->orderBy('st.created_at', 'ASC');

        if ($warehouseId) {
            $builder->where('st.warehouse_id', $warehouseId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Complete a staff task (when barcode is scanned)
     * 
     * @param int $taskId Task ID
     * @param int $staffId Staff user ID
     * @param string $notes Optional completion notes
     * @return bool Success status
     */
    public function completeTask($taskId, $staffId, $notes = null)
    {
        $updateData = [
            'status' => 'Completed',
            'completed_by' => $staffId,
            'completed_at' => date('Y-m-d H:i:s')
        ];

        if ($notes) {
            $updateData['notes'] = $notes;
        }

        try {
            $result = $this->update($taskId, $updateData);
            if ($result) {
                log_message('info', "Staff task completed: Task ID {$taskId} by staff {$staffId}");
            }
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Task completion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark task as failed
     * 
     * @param int $taskId Task ID
     * @param string $reason Failure reason
     * @return bool Success status
     */
    public function failTask($taskId, $reason = null)
    {
        $updateData = [
            'status' => 'Failed',
            'notes' => $reason
        ];

        try {
            return $this->update($taskId, $updateData);
        } catch (\Exception $e) {
            log_message('error', 'Task failure update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tasks by reference number
     * 
     * @param string $referenceNo Reference number (PO-1234, SO-5678, etc.)
     * @return array Tasks for the reference
     */
    public function getTasksByReference($referenceNo)
    {
        return $this->db->table('staff_tasks st')
            ->select('st.*, w.name as warehouse_name, i.sku as current_sku, i.quantity as current_stock')
            ->join('warehouses w', 'w.id = st.warehouse_id', 'left')
            ->join('inventory i', 'i.id = st.item_id', 'left')
            ->where('st.reference_no', $referenceNo)
            ->orderBy('st.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get task statistics for dashboard
     * 
     * @return array Task statistics
     */
    public function getTaskStats($warehouseId = null)
    {
        $builder = $this->where('1', '1');

        // Helper to apply warehouse filter when present
        $applyWarehouse = function($qb) use ($warehouseId) {
            if ($warehouseId) {
                $qb->where('warehouse_id', (int)$warehouseId);
            }
            return $qb;
        };

        $stats = [];

        // Total pending
        $pendingBuilder = $this->where('status', 'Pending');
        if ($warehouseId) $pendingBuilder->where('warehouse_id', (int)$warehouseId);
        $stats['total_pending'] = $pendingBuilder->countAllResults();

        // Total completed today
        $completedBuilder = $this->where('status', 'Completed');
        if ($warehouseId) $completedBuilder->where('warehouse_id', (int)$warehouseId);
        $stats['total_completed_today'] = $completedBuilder->where('DATE(completed_at)', date('Y-m-d'))->countAllResults();

        // Total failed
        $failedBuilder = $this->where('status', 'Failed');
        if ($warehouseId) $failedBuilder->where('warehouse_id', (int)$warehouseId);
        $stats['total_failed'] = $failedBuilder->countAllResults();

        // Pending by type
        $pendingInBuilder = $this->where('status', 'Pending')->where('movement_type', 'IN');
        if ($warehouseId) $pendingInBuilder->where('warehouse_id', (int)$warehouseId);
        $stats['pending_inbound'] = $pendingInBuilder->countAllResults();

        $pendingOutBuilder = $this->where('status', 'Pending')->where('movement_type', 'OUT');
        if ($warehouseId) $pendingOutBuilder->where('warehouse_id', (int)$warehouseId);
        $stats['pending_outbound'] = $pendingOutBuilder->countAllResults();

        return $stats;
    }

    /**
     * Get task history for a specific staff member
     * 
     * @param int $staffId Staff user ID
     * @param int $limit Number of records to return
     * @return array Task history
     */
    public function getStaffTaskHistory($staffId, $limit = 20)
    {
        return $this->db->table('staff_tasks st')
            ->select('st.*, w.name as warehouse_name')
            ->join('warehouses w', 'w.id = st.warehouse_id', 'left')
            ->where('st.completed_by', $staffId)
            ->where('st.status', 'Completed')
            ->orderBy('st.completed_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}