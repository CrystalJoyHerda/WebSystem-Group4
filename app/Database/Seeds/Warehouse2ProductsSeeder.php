<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Warehouse2ProductsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('inventory')) {
            // Nothing to do if inventory table missing
            return;
        }

        // Define the construction products to insert for Warehouse 2
        $now = date('Y-m-d H:i:s');
        $products = [
            ['name' => 'Cement',      'sku' => 'CEM-001', 'description' => 'Portland cement 50kg', 'price' => 8.50,  'stock' => 200],
            ['name' => 'Sand',        'sku' => 'SND-001', 'description' => 'Construction sand (m3)',   'price' => 12.00, 'stock' => 50],
            ['name' => 'Gravel',      'sku' => 'GRV-001', 'description' => 'Gravel (m3)',             'price' => 14.00, 'stock' => 60],
            ['name' => 'Bricks',      'sku' => 'BRK-001', 'description' => 'Clay bricks (pcs)',       'price' => 0.35,  'stock' => 5000],
            ['name' => 'Steel rods',  'sku' => 'STL-001', 'description' => 'Steel reinforcement rods','price' => 3.25,  'stock' => 800],
            ['name' => 'Wood planks', 'sku' => 'WD-001',  'description' => 'Timber planks',          'price' => 6.00,  'stock' => 300],
            ['name' => 'Paint',       'sku' => 'PNT-001', 'description' => 'Exterior paint 5L',      'price' => 22.00, 'stock' => 40],
            ['name' => 'Tiles',       'sku' => 'TLS-001', 'description' => 'Ceramic floor tiles',    'price' => 1.75,  'stock' => 1200],
        ];

        // Discover which columns exist in `inventory` to avoid errors
        try {
            $fields = $db->getFieldNames('inventory');
        } catch (\Throwable $e) {
            $fields = [];
        }

        $builder = $db->table('inventory');

        foreach ($products as $p) {
            // Avoid duplicates within Warehouse 2 by SKU and warehouse_id
            $exists = $builder->where('sku', $p['sku'])->where('warehouse_id', 2)->get()->getRowArray();

            $quantity = isset($p['stock']) ? (int)$p['stock'] : 0;
            $status = 'in';
            if ($quantity <= 0) $status = 'out';
            elseif ($quantity <= 10) $status = 'low';

            // Build data only for fields that exist
            $data = [
                'name'       => $p['name'],
                'sku'        => $p['sku'],
                'category'   => 'Building Materials',
                'location'   => null,
                'quantity'   => $quantity,
                'status'     => $status,
                'expiry'     => null,
                'warehouse_id'=> 2,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Add optional columns if they exist
            if (in_array('description', $fields)) {
                $data['description'] = $p['description'] ?? null;
            }
            if (in_array('price', $fields)) {
                $data['price'] = $p['price'] ?? null;
            }

            if ($exists) {
                // Update existing row to ensure stock and basic details are set for warehouse 2
                $updateData = $data;
                // Do not overwrite created_at
                unset($updateData['created_at']);
                $builder->where('id', $exists['id'])->update($updateData);
            } else {
                $builder->insert($data);
            }
        }
    }
}
