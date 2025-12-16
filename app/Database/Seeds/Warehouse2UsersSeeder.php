<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Warehouse2UsersSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('users')) {
            // Users table missing; nothing to do
            return;
        }

        $users = [
            [
                'email' => 'manager2@whs.com',
                'name' => 'Manager Two',
                // password: manager2123
                'password' => password_hash('manager2123', PASSWORD_DEFAULT),
                'role' => 'manager',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'staff2@whs.com',
                'name' => 'Staff Two',
                // password: staff21234
                'password' => password_hash('staff21234', PASSWORD_DEFAULT),
                'role' => 'staff',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $userBuilder = $db->table('users');
        foreach ($users as $u) {
            $exists = $userBuilder->where('email', $u['email'])->get()->getRowArray();
            if ($exists) {
                $userBuilder->where('email', $u['email'])->update([
                    'password' => $u['password'],
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $userId = $exists['id'];
            } else {
                $userBuilder->insert($u);
                $userId = $db->insertID();
            }

            // Ensure user_warehouses mapping exists and points to warehouse 2
            if ($db->tableExists('user_warehouses')) {
                $uw = $db->table('user_warehouses')->where('user_id', $userId)->get()->getRowArray();
                if ($uw) {
                    $db->table('user_warehouses')->where('user_id', $userId)->update([
                        'warehouse_id' => 2,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $db->table('user_warehouses')->insert([
                        'user_id' => $userId,
                        'warehouse_id' => 2,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }
}
