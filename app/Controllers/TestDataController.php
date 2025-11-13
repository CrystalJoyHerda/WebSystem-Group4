<?php
namespace App\Controllers;

use App\Database\Seeds\UserSeeder;

class TestDataController extends BaseController
{
    /**
     * Seed comprehensive test data for the construction warehouse management system
     * Access via: /seed-test-data
     */
    public function seedTestData()
    {
        // Only allow in development or if user is logged in as manager
        if (ENVIRONMENT === 'production' && session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setBody('Access denied. Manager role required.');
        }

        try {
            $config = new \Config\Database();
            $seeder = new UserSeeder($config);
            
            // Run the existing user seeder first
            $seeder->run();
            
            // Then run our comprehensive test data seeder
            $seeder->seedTestData();
            
            return $this->response->setStatusCode(200)->setBody('
                <html>
                <head><title>Test Data Seeded</title></head>
                <body style="font-family: Arial; padding: 20px; background: #f5f5f5;">
                    <div style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h1 style="color: #28a745;">✅ Construction Warehouse Test Data Seeded Successfully!</h1>
                        
                        <h3>🏢 Warehouses Created:</h3>
                        <ul>
                            <li><strong>Main Warehouse</strong> - Tagum City</li>
                            <li><strong>Warehouse A</strong> - Panabo City</li>
                            <li><strong>Warehouse B</strong> - Davao City</li>
                        </ul>
                        
                        <h3>📦 Construction Materials & Equipment:</h3>
                        <ul>
                            <li><strong>Portland Cement</strong> (CMT001) - Distributed across warehouses</li>
                            <li><strong>Steel Reinforcement Bar</strong> (STL002) - Low stock in Warehouse B</li>
                            <li><strong>Construction Gravel</strong> (GRV003) - Out of stock in Warehouse B</li>
                            <li><strong>Exterior Paint</strong> (PNT004) - Available in Warehouse A & B</li>
                            <li><strong>Hydraulic Excavator</strong> (EQP005) - Heavy equipment in Warehouse A</li>
                            <li><strong>Electric Drill Machine</strong> (EQP006) - Power tools in Warehouse B</li>
                        </ul>
                        
                        <h3>👥 Test User Accounts:</h3>
                        <ul>
                            <li><strong>Manager:</strong> admin@construct.com / admin123</li>
                            <li><strong>Staff:</strong> staff@construct.com / staff123</li>
                            <li><strong>Viewer:</strong> viewer@construct.com / view123</li>
                        </ul>
                        
                        <h3>🧪 Testing Scenarios Ready:</h3>
                        <ul>
                            <li><strong>Barcode Scanning:</strong> All items have unique barcodes (CMT001, STL002, etc.)</li>
                            <li><strong>Low Stock Alerts:</strong> Steel bars in Warehouse B (8 units)</li>
                            <li><strong>Out of Stock:</strong> Gravel in Warehouse B (0 units)</li>
                            <li><strong>Transfer Approvals:</strong> Pending steel bar transfer awaiting approval</li>
                            <li><strong>Role-based Access:</strong> Different permissions for each user type</li>
                            <li><strong>Multi-warehouse:</strong> Items distributed across 3 locations</li>
                        </ul>
                        
                        <div style="background: #e7f3ff; border: 1px solid #b8daff; padding: 15px; border-radius: 5px; margin-top: 20px;">
                            <h4 style="margin: 0 0 10px 0; color: #004085;">🚀 Next Steps:</h4>
                            <ol>
                                <li>Log in with any test account</li>
                                <li>Navigate to <strong>Inventory</strong> to see distributed materials</li>
                                <li>Try <strong>Barcode Scanning</strong> with codes like CMT001, STL002</li>
                                <li>Check <strong>Stock Alerts</strong> in manager dashboard</li>
                                <li>Test <strong>Transfer Approvals</strong> in pending transfers</li>
                                <li>Switch between user roles to test permissions</li>
                            </ol>
                        </div>
                        
                        <p style="text-align: center; margin-top: 30px;">
                            <a href="' . site_url('login') . '" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Login</a>
                            <a href="' . site_url('dashboard') . '" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">Go to Dashboard</a>
                        </p>
                    </div>
                </body>
                </html>
            ');
            
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setBody('Error seeding test data: ' . $e->getMessage());
        }
    }

    /**
     * Seed staff tasks and stock movements for testing the integration workflow
     * Access via: /seed-staff-tasks-test-data
     */
    public function seedStaffTasksTestData()
    {
        if (!session()->get('isLoggedIn') || session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        try {
            $db = \Config\Database::connect();
            
            // Load required models
            $inventoryModel = new \App\Models\InventoryModel();
            $warehouseModel = new \App\Models\WarehouseModel();
            $staffTaskModel = new \App\Models\StaffTaskModel();
            $stockMovementModel = new \App\Models\StockMovementModel();

            // Get some inventory items and warehouses for testing
            $warehouses = $warehouseModel->findAll();
            $inventoryItems = $inventoryModel->findAll();

            if (empty($warehouses) || empty($inventoryItems)) {
                return $this->response->setJSON([
                    'error' => 'Need warehouses and inventory items first. Please run /seed-test-data first.'
                ]);
            }

            $warehouse = $warehouses[0]; // Use first warehouse
            $managerId = session('user_id');

            // Create sample stock movements and staff tasks
            $testData = [
                [
                    'reference_no' => 'PO-2024-001',
                    'movement_type' => 'IN',
                    'description' => 'Inbound cement delivery - scan to receive'
                ],
                [
                    'reference_no' => 'SO-2024-002', 
                    'movement_type' => 'OUT',
                    'description' => 'Outbound construction materials - scan to dispatch'
                ],
                [
                    'reference_no' => 'PO-2024-003',
                    'movement_type' => 'IN',
                    'description' => 'Steel bars delivery - scan to confirm receipt'
                ]
            ];

            $createdTasks = [];

            foreach ($testData as $i => $data) {
                if ($i >= count($inventoryItems)) break;
                
                $item = $inventoryItems[$i];
                $quantity = rand(10, 50);

                // Create stock movement
                $movementData = [
                    'transaction_number' => 'TXN-' . time() . '-' . ($i + 1),
                    'order_number' => $data['reference_no'],
                    'id' => $item['id'],
                    'quantity' => $quantity,
                    'movement_type' => strtolower($data['movement_type']),
                    'company_name' => 'Test Construction Co',
                    'location' => $warehouse['name'],
                    'status' => 'approved',
                    'items_in_progress' => 1
                ];

                $movementId = $stockMovementModel->createMovement($movementData);

                // Create staff task
                $taskData = [
                    'movement_id' => $movementId,
                    'reference_no' => $data['reference_no'],
                    'warehouse_id' => $warehouse['id'],
                    'item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'item_sku' => $item['sku'],
                    'quantity' => $quantity,
                    'movement_type' => $data['movement_type'],
                    'assigned_by' => $managerId,
                    'notes' => $data['description']
                ];

                $taskId = $staffTaskModel->createTask($taskData);

                $createdTasks[] = [
                    'task_id' => $taskId,
                    'movement_id' => $movementId,
                    'reference_no' => $data['reference_no'],
                    'item_name' => $item['name'],
                    'item_sku' => $item['sku'],
                    'movement_type' => $data['movement_type']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Staff task integration test data created successfully!',
                'created_tasks' => $createdTasks,
                'total_tasks' => count($createdTasks),
                'instructions' => [
                    '1. Go to Staff Barcode Scanning page',
                    '2. Select the warehouse: ' . $warehouse['name'],
                    '3. View the To-Do tasks list - you should see ' . count($createdTasks) . ' pending tasks',
                    '4. Click "Scan" on any task to start scanning',
                    '5. Scan the item SKU (or enter manually) to complete the task',
                    '6. Watch how stock quantities update automatically',
                    '7. Check the movement history in Manager Dashboard'
                ],
                'test_workflow' => 'Manager approves receipt → Staff task created → Staff scans item → Task completed → Stock updated'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Test data creation failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Failed to create test data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Clear staff tasks test data
     * Access via: /clear-staff-tasks-test-data
     */
    public function clearStaffTasksTestData()
    {
        if (!session()->get('isLoggedIn') || session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        try {
            $db = \Config\Database::connect();
            
            // Clear test data
            $db->query("DELETE FROM staff_tasks WHERE reference_no LIKE 'PO-2024-%' OR reference_no LIKE 'SO-2024-%'");
            $db->query("DELETE FROM stock_movements WHERE order_number LIKE 'PO-2024-%' OR order_number LIKE 'SO-2024-%'");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Staff tasks test data cleared successfully!'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Test data clearing failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Failed to clear test data: ' . $e->getMessage()
            ]);
        }
    }
}