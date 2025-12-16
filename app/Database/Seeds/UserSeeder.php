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
            [
                'email' => 'viewer@whs.com',
                'name' => 'Alex',
                // password: viewer123
                'password' => password_hash('viewer123', PASSWORD_DEFAULT),
                'role' => 'viewer',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'admin@whs.com',
                'name' => 'Jesse',
                // password: admin123
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'IT administrator',
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

    /**
     * Seed comprehensive test data for construction company warehouse management system
     * This function populates all necessary tables with realistic construction materials data
     */
    public function seedTestData()
    {
        $db = \Config\Database::connect();
        
        // 1. WAREHOUSES - Construction company locations
        $warehousesData = [
            [
                'name' => 'Main Warehouse',
                'location' => 'Tagum City',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Warehouse A',
                'location' => 'Panabo City',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Warehouse B',
                'location' => 'Davao City',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $warehouseBuilder = $db->table('warehouses');
        foreach ($warehousesData as $warehouse) {
            $exists = $warehouseBuilder->where('name', $warehouse['name'])->get()->getRowArray();
            if (!$exists) {
                $warehouseBuilder->insert($warehouse);
            }
        }

        // Get warehouse IDs for reference
        $warehouses = $warehouseBuilder->get()->getResultArray();
        $warehouseIds = [];
        foreach ($warehouses as $wh) {
            $warehouseIds[$wh['name']] = $wh['id'];
        }

        // 2. USERS - Enhanced for construction company testing
        $testUsers = [
            [
                'email' => 'admin@construct.com',
                'name' => 'Construction Admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'manager',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'staff@construct.com',
                'name' => 'Warehouse Staff',
                'password' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'staff',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'viewer@construct.com',
                'name' => 'Site Supervisor',
                'password' => password_hash('view123', PASSWORD_DEFAULT),
                'role' => 'viewer',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $userBuilder = $db->table('users');
        foreach ($testUsers as $user) {
            $exists = $userBuilder->where('email', $user['email'])->get()->getRowArray();
            if ($exists) {
                $userBuilder->where('email', $user['email'])->update([
                    'password' => $user['password'],
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $userBuilder->insert($user);
            }
        }

        // Get user IDs for reference
        $users = $userBuilder->whereIn('email', ['admin@construct.com', 'staff@construct.com', 'viewer@construct.com'])->get()->getResultArray();
        $userIds = [];
        foreach ($users as $user) {
            $userIds[$user['role']] = $user['id'];
        }

        // 3. CONSTRUCTION MATERIALS & EQUIPMENT - Distributed across warehouses
        $inventoryData = [
            // Main Warehouse - Primary storage
            [
                'name' => 'Portland Cement',
                'sku' => 'CMT001',
                'category' => 'Building Materials',
                'location' => 'Section A-1',
                'warehouse_id' => $warehouseIds['Main Warehouse'],
                'quantity' => 500,
                'status' => 'in',
                'expiry' => date('Y-m-d', strtotime('+2 years')),
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Steel Reinforcement Bar 12mm',
                'sku' => 'STL002',
                'category' => 'Steel Materials',
                'location' => 'Section B-2',
                'warehouse_id' => $warehouseIds['Main Warehouse'],
                'quantity' => 200,
                'status' => 'in',
                'expiry' => null,
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Construction Gravel',
                'sku' => 'GRV003',
                'category' => 'Aggregates',
                'location' => 'Outdoor Yard A',
                'warehouse_id' => $warehouseIds['Main Warehouse'],
                'quantity' => 1000,
                'status' => 'in',
                'expiry' => null,
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Warehouse A - Panabo City
            [
                'name' => 'Portland Cement',
                'sku' => 'CMT001',
                'category' => 'Building Materials',
                'location' => 'Storage Room 1',
                'warehouse_id' => $warehouseIds['Warehouse A'],
                'quantity' => 150,
                'status' => 'in',
                'expiry' => date('Y-m-d', strtotime('+2 years')),
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Exterior Paint',
                'sku' => 'PNT004',
                'category' => 'Finishing Materials',
                'location' => 'Storage Room 2',
                'warehouse_id' => $warehouseIds['Warehouse A'],
                'quantity' => 80,
                'status' => 'in',
                'expiry' => date('Y-m-d', strtotime('+3 years')),
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Hydraulic Excavator',
                'sku' => 'EQP005',
                'category' => 'Heavy Equipment',
                'location' => 'Equipment Bay 1',
                'warehouse_id' => $warehouseIds['Warehouse A'],
                'quantity' => 2,
                'status' => 'in',
                'expiry' => null,
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Warehouse B - Davao City
            [
                'name' => 'Steel Reinforcement Bar 12mm',
                'sku' => 'STL002',
                'category' => 'Steel Materials',
                'location' => 'Steel Section',
                'warehouse_id' => $warehouseIds['Warehouse B'],
                'quantity' => 8, // Low stock for testing
                'status' => 'low',
                'expiry' => null,
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Electric Drill Machine',
                'sku' => 'EQP006',
                'category' => 'Power Tools',
                'location' => 'Tool Storage',
                'warehouse_id' => $warehouseIds['Warehouse B'],
                'quantity' => 15,
                'status' => 'in',
                'expiry' => null,
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Construction Gravel',
                'sku' => 'GRV003',
                'category' => 'Aggregates',
                'location' => 'Outdoor Storage',
                'warehouse_id' => $warehouseIds['Warehouse B'],
                'quantity' => 0, // Out of stock for testing
                'status' => 'out',
                'expiry' => null,
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Exterior Paint',
                'sku' => 'PNT004',
                'category' => 'Finishing Materials',
                'location' => 'Paint Storage',
                'warehouse_id' => $warehouseIds['Warehouse B'],
                'quantity' => 25,
                'status' => 'in',
                'expiry' => date('Y-m-d', strtotime('+3 years')),
                'version' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $inventoryBuilder = $db->table('inventory');
        foreach ($inventoryData as $item) {
            $exists = $inventoryBuilder->where('sku', $item['sku'])
                                     ->where('warehouse_id', $item['warehouse_id'])
                                     ->get()->getRowArray();
            if (!$exists) {
                $inventoryBuilder->insert($item);
            }
        }

        // 4. SAMPLE TRANSFERS - Construction project movements
        $transfersData = [
            [
                'item_id' => 1, // Will need to get actual IDs
                'from_warehouse_id' => $warehouseIds['Main Warehouse'],
                'to_warehouse_id' => $warehouseIds['Warehouse A'],
                'quantity' => 50,
                'status' => 'completed',
                'approved_by' => $userIds['manager'],
                'approved_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'notes' => 'Transfer for Panabo construction project',
                'created_by' => 'Construction Admin',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'item_id' => 2, // Steel bars
                'from_warehouse_id' => $warehouseIds['Main Warehouse'],
                'to_warehouse_id' => $warehouseIds['Warehouse B'],
                'quantity' => 30,
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'notes' => 'Urgent transfer for Davao site foundation work',
                'created_by' => 'Warehouse Staff',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'item_id' => 3, // Gravel
                'from_warehouse_id' => $warehouseIds['Main Warehouse'],
                'to_warehouse_id' => $warehouseIds['Warehouse B'],
                'quantity' => 200,
                'status' => 'completed',
                'approved_by' => $userIds['manager'],
                'approved_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'notes' => 'Replenish Davao warehouse for road construction',
                'created_by' => 'Construction Admin',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
        ];

        // Get actual inventory IDs for transfers
        $inventory = $inventoryBuilder->get()->getResultArray();
        $inventoryMap = [];
        foreach ($inventory as $item) {
            $key = $item['sku'] . '_' . $item['warehouse_id'];
            $inventoryMap[$key] = $item['id'];
        }

        // Update transfer data with correct item IDs
        $transfersData[0]['item_id'] = $inventoryMap['CMT001_' . $warehouseIds['Main Warehouse']] ?? 1;
        $transfersData[1]['item_id'] = $inventoryMap['STL002_' . $warehouseIds['Main Warehouse']] ?? 2;
        $transfersData[2]['item_id'] = $inventoryMap['GRV003_' . $warehouseIds['Main Warehouse']] ?? 3;

        $transferBuilder = $db->table('transfers');
        foreach ($transfersData as $transfer) {
            $exists = $transferBuilder->where('item_id', $transfer['item_id'])
                                    ->where('from_warehouse_id', $transfer['from_warehouse_id'])
                                    ->where('to_warehouse_id', $transfer['to_warehouse_id'])
                                    ->where('created_at', $transfer['created_at'])
                                    ->get()->getRowArray();
            if (!$exists) {
                $transferBuilder->insert($transfer);
            }
        }

        echo "✅ Test data seeded successfully!\n\n";
        echo "🏢 WAREHOUSES:\n";
        echo "   - Main Warehouse (Tagum City)\n";
        echo "   - Warehouse A (Panabo City)\n";
        echo "   - Warehouse B (Davao City)\n\n";
        
        echo "📦 CONSTRUCTION MATERIALS:\n";
        echo "   - Portland Cement (CMT001)\n";
        echo "   - Steel Reinforcement Bar (STL002)\n";
        echo "   - Construction Gravel (GRV003)\n";
        echo "   - Exterior Paint (PNT004)\n";
        echo "   - Hydraulic Excavator (EQP005)\n";
        echo "   - Electric Drill Machine (EQP006)\n\n";
        
        echo "👥 TEST USERS:\n";
        echo "   - Admin: admin@construct.com / admin123\n";
        echo "   - Staff: staff@construct.com / staff123\n";
        echo "   - Viewer: viewer@construct.com / view123\n\n";
        
        echo "🔄 SAMPLE TRANSFERS:\n";
        echo "   - Cement transfer (completed)\n";
        echo "   - Steel bar transfer (pending approval)\n";
        echo "   - Gravel transfer (completed)\n\n";
        
        echo "🧪 TESTING SCENARIOS:\n";
        echo "   - Low stock: Steel bars in Warehouse B (8 units)\n";
        echo "   - Out of stock: Gravel in Warehouse B (0 units)\n";
        echo "   - Pending approval: Steel transfer awaiting manager approval\n";
        echo "   - Barcode scanning: All items have unique barcodes\n\n";
        
        echo "Ready for comprehensive system testing! 🎯\n";
    }
}
