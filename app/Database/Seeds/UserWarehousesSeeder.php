<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserWarehousesSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('user_warehouses')) {
            // If migration isn't run yet, nothing to seed.
            return;
        }

        // Get users and their warehouse_id if present
        if (! $db->tableExists('users')) {
            return;
        }

        $users = $db->table('users')->select('id, warehouse_id')->get()->getResultArray();

        foreach ($users as $u) {
            $exists = $db->table('user_warehouses')->where('user_id', $u['id'])->countAllResults();
            if ($exists) {
                continue;
            }

            $warehouseId = 1;
            if (array_key_exists('warehouse_id', $u) && $u['warehouse_id']) {
                $warehouseId = (int) $u['warehouse_id'];
            }

            $db->table('user_warehouses')->insert([
                'user_id' => $u['id'],
                'warehouse_id' => $warehouseId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
