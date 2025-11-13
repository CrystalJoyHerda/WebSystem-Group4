<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FixWarehouseIds extends Seeder
{
    public function run()
    {
        // Update inventory items to have warehouse_id = 1 where it's NULL
        // This fixes the barcode scanning issue where items can't be found
        
        $this->db->query("UPDATE inventory SET warehouse_id = 1 WHERE warehouse_id IS NULL");
        
        echo "Updated inventory items to have warehouse_id = 1\n";
        
        // Show the updated count
        $result = $this->db->query("SELECT COUNT(*) as count FROM inventory WHERE warehouse_id = 1");
        $count = $result->getRow()->count;
        echo "Total items with warehouse_id = 1: $count\n";
    }
}