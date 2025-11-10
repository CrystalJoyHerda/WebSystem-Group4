<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Use passwords meeting the application's minimum length (>=8).
        // This seeder will insert or update the demo users so running it multiple
        // times won't create duplicate entries due to unique email constraint.
        $users = [
            [
                'email' => 'manager@whs.com',
                'name' => 'Tally',
                // password: manager123
                'password' => password_hash('manager123', PASSWORD_DEFAULT),
                'role' => 'manager',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'staff@whs.com',
                'name' => 'Amanie',
                // password: staff1234
                'password' => password_hash('staff1234', PASSWORD_DEFAULT),
                'role' => 'staff',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $builder = $this->db->table('users');
        foreach ($users as $u) {
            $exists = $builder->where('email', $u['email'])->get()->getRowArray();
            if ($exists) {
                // update password and name/role if needed
                $builder->where('email', $u['email'])->update([
                    'password' => $u['password'],
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $builder->insert($u);
            }
        }
    }
}
