<?php
namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','location','capacity','current_usage','status','created_at','updated_at'];
    protected $useTimestamps = false;

    public function getAllWithCounts()
    {
        // join with inventory to get counts per warehouse
        $builder = $this->db->table('warehouses w')
            ->select('w.*, 
                     COALESCE(COUNT(i.id),0) as total_items,
                     COALESCE(SUM(i.quantity),0) as total_quantity,
                     COALESCE(SUM(CASE WHEN i.quantity <= 10 THEN 1 ELSE 0 END),0) as low_stock_count')
            ->join('inventory i', 'i.warehouse_id = w.id', 'left')
            ->groupBy('w.id, w.name, w.location, w.created_at, w.updated_at');

        return $builder->get()->getResultArray();
    }
}
