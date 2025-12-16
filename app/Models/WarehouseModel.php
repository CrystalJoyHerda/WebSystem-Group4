<?php
namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','location','capacity','current_usage','status','created_at','updated_at'];
    protected $useTimestamps = false;

    private function lowStockThreshold(): int
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('system_settings')) {
            return 10;
        }

        $row = $db->table('system_settings')
            ->select('setting_value')
            ->where('setting_key', 'inventory.low_stock_threshold')
            ->get()
            ->getRowArray();

        $val = $row['setting_value'] ?? null;
        if ($val === null || $val === '' || ! is_numeric($val)) {
            return 10;
        }
        $n = (int) $val;
        return $n >= 0 ? $n : 10;
    }

    public function getAllWithCounts()
    {
        $threshold = $this->lowStockThreshold();
        // join with inventory to get counts per warehouse
        $builder = $this->db->table('warehouses w')
            ->select('w.*, 
                     COALESCE(COUNT(i.id),0) as total_items,
                     COALESCE(SUM(i.quantity),0) as total_quantity,
                     COALESCE(SUM(CASE WHEN i.quantity <= ' . (int) $threshold . ' THEN 1 ELSE 0 END),0) as low_stock_count')
            ->join('inventory i', 'i.warehouse_id = w.id', 'left')
            ->groupBy('w.id, w.name, w.location, w.created_at, w.updated_at');

        return $builder->get()->getResultArray();
    }
}
