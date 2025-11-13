<?php
namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','sku','category','location','quantity','status','expiry','warehouse_id','version','created_at','updated_at'];
    protected $useTimestamps = false;

    /**
     * Find item by SKU and warehouse (for barcode scanning)
     */
    public function findBySkuAndWarehouse($sku, $warehouseId = null)
    {
        $builder = $this->builder();
        $builder->where('sku', $sku);
        
        if ($warehouseId !== null) {
            $builder->where('warehouse_id', $warehouseId);
        }
        
        return $builder->get()->getRowArray();
    }

    /**
     * Get all items in a specific warehouse
     */
    public function getByWarehouse($warehouseId)
    {
        return $this->where('warehouse_id', $warehouseId)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get items with warehouse information joined
     */
    public function getItemsWithWarehouse()
    {
        $builder = $this->db->table('inventory i')
            ->select('i.*, w.name as warehouse_name, w.location as warehouse_location')
            ->join('warehouses w', 'w.id = i.warehouse_id', 'left')
            ->orderBy('i.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get low stock items (quantity <= 10)
     */
    public function getLowStockItems($warehouseId = null)
    {
        $builder = $this->where('quantity <=', 10);
        
        if ($warehouseId !== null) {
            $builder->where('warehouse_id', $warehouseId);
        }
        
        return $builder->findAll();
    }

    /**
     * Get stock summary by warehouse
     */
    public function getStockSummaryByWarehouse()
    {
        $builder = $this->db->table('inventory i')
            ->select('i.warehouse_id, w.name as warehouse_name, 
                     COUNT(i.id) as total_items, 
                     SUM(i.quantity) as total_quantity,
                     SUM(CASE WHEN i.quantity <= 10 THEN 1 ELSE 0 END) as low_stock_count')
            ->join('warehouses w', 'w.id = i.warehouse_id', 'left')
            ->groupBy('i.warehouse_id, w.name')
            ->orderBy('w.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Update stock quantity (for transfers and stock movements)
     */
    public function updateStock($itemId, $newQuantity, $warehouseId = null)
    {
        $data = ['quantity' => $newQuantity];
        
        if ($warehouseId !== null) {
            $data['warehouse_id'] = $warehouseId;
        }
        
        // Determine status based on quantity
        if ($newQuantity <= 0) {
            $data['status'] = 'out';
        } elseif ($newQuantity <= 10) {
            $data['status'] = 'low';
        } else {
            $data['status'] = 'in';
        }
        
        return $this->update($itemId, $data);
    }
}
