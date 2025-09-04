<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
         $managerData = [
            [
                'email' => 'manager@whs.com',
                'name' => 'Tally',
                'password' => password_hash('112311', PASSWORD_DEFAULT),
                'role' => 'manager',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $staffData = [
            [
                'email' => 'staff@whs.com',
                'name' => 'Amanie',
                'password' => password_hash('111111', PASSWORD_DEFAULT),
                'role' => 'staff',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
         $this->db->table('users')->insertBatch($managerData);
        $this->db->table('users')->insertBatch($staffData);
    }
}
