<?php
namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','location','created_at','updated_at'];
    protected $useTimestamps = false;

    public function getAllWithCounts()
    {
        // join with inventory to get counts per warehouse
        $builder = $this->db->table('warehouses w')
            ->select('w.*, COALESCE(SUM(i.quantity),0) as total_quantity')
            ->join('inventory i', 'i.warehouse_id = w.id', 'left')
            ->groupBy('w.id');

        return $builder->get()->getResultArray();
    }
}
