<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'name'       => 'Hex Bolts M10',
                'sku'        => 'HB-M10-002',
                'category'   => 'Fasteners',
                'location'   => 'B-2-03',
                'quantity'   => 0,
                'status'     => 'out',
                'expiry'     => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Portland Cement 50kg',
                'sku'        => 'CEM-50-001',
                'category'   => 'Building Materials',
                'location'   => 'C-1-05',
                'quantity'   => 250,
                'status'     => 'in',
                'expiry'     => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Timber Plank 2x4',
                'sku'        => 'TMR-24-003',
                'category'   => 'Lumber',
                'location'   => 'D-3-02',
                'quantity'   => 120,
                'status'     => 'in',
                'expiry'     => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Exterior Paint 5L',
                'sku'        => 'PNT-5L-004',
                'category'   => 'Finishing',
                'location'   => 'E-1-01',
                'quantity'   => 15,
                'status'     => 'low',
                'expiry'     => '2027-06-30',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('inventory')->insertBatch($data);
    }
}
