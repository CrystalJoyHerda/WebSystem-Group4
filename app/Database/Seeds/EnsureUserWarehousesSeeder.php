<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EnsureUserWarehousesSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if (! $db->tableExists('user_warehouses')) {
            $forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'warehouse_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('user_id');
            $forge->addKey('warehouse_id');
            $forge->createTable('user_warehouses', true);
        }

        // Ensure mappings for manager2@whs.com and staff2@whs.com
        $emails = ['manager2@whs.com', 'staff2@whs.com'];
        $userBuilder = $db->table('users');
        $uwBuilder = $db->table('user_warehouses');

        foreach ($emails as $email) {
            $user = $userBuilder->select('id')->where('email', $email)->get()->getRowArray();
            if (! $user) continue;

            $exists = $uwBuilder->where('user_id', $user['id'])->get()->getRowArray();
            if ($exists) {
                $uwBuilder->where('user_id', $user['id'])->update(['warehouse_id' => 2, 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                $uwBuilder->insert(['user_id' => $user['id'], 'warehouse_id' => 2, 'created_at' => date('Y-m-d H:i:s')]);
            }
        }
    }
}
