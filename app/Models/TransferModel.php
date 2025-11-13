<?php
namespace App\Models;

use CodeIgniter\Model;

class TransferModel extends Model
{
    protected $table = 'transfers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['item_id','from_warehouse_id','to_warehouse_id','quantity','status','approved_by','approved_at','notes','created_by','created_at'];
    protected $useTimestamps = false;

    /**
     * Get transfer history with warehouse and item information
     */
    public function getTransferHistory($limit = 50)
    {
        $builder = $this->db->table('transfers t')
            ->select('t.*, 
                     i.name as item_name, i.sku as item_sku,
                     w1.name as from_warehouse_name,
                     w2.name as to_warehouse_name')
            ->join('inventory i', 'i.id = t.item_id', 'left')
            ->join('warehouses w1', 'w1.id = t.from_warehouse_id', 'left')
            ->join('warehouses w2', 'w2.id = t.to_warehouse_id', 'left')
            ->orderBy('t.created_at', 'DESC')
            ->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Get pending transfers requiring approval
     */
    public function getPendingTransfers()
    {
        $builder = $this->db->table('transfers t')
            ->select('t.*, 
                     i.name as item_name, i.sku as item_sku,
                     w1.name as from_warehouse_name,
                     w2.name as to_warehouse_name')
            ->join('inventory i', 'i.id = t.item_id', 'left')
            ->join('warehouses w1', 'w1.id = t.from_warehouse_id', 'left')
            ->join('warehouses w2', 'w2.id = t.to_warehouse_id', 'left')
            ->where('t.status', 'pending')
            ->orderBy('t.created_at', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Update transfer status (approve/reject)
     */
    public function updateTransferStatus($transferId, $status, $approvedBy, $notes = '')
    {
        $data = [
            'status' => $status,
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'notes' => $notes
        ];

        return $this->update($transferId, $data);
    }

    /**
     * Get transfer statistics
     */
    public function getTransferStats($dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table('transfers t');
        
        if ($dateFrom) {
            $builder->where('t.created_at >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('t.created_at <=', $dateTo);
        }

        $stats = $builder->select('
            COUNT(*) as total_transfers,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_transfers,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_transfers,
            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_transfers,
            SUM(quantity) as total_quantity_transferred
        ')->get()->getRowArray();

        return $stats ?: [
            'total_transfers' => 0,
            'completed_transfers' => 0,
            'pending_transfers' => 0,
            'rejected_transfers' => 0,
            'total_quantity_transferred' => 0
        ];
    }
}
