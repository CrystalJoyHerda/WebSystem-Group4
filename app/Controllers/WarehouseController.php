<?php
namespace App\Controllers;

use App\Models\WarehouseModel;

class WarehouseController extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) return redirect()->to('/login');
        if (session('role') !== 'manager') return redirect()->to('/login');

        $model = new WarehouseModel();
        $warehouses = $model->orderBy('id','ASC')->findAll();
        return view('dashboard/manager/warehouses', ['warehouses' => $warehouses]);
    }

    // JSON endpoint to list warehouses (manager/staff)
    public function list()
    {
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        $model = new WarehouseModel();
        return $this->response->setJSON($model->orderBy('id','ASC')->findAll());
    }

    public function create()
    {
        if (! session()->get('isLoggedIn') || session('role') !== 'manager') return $this->response->setStatusCode(403);
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        if (empty($data['name'])) return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing name']);
        $model = new WarehouseModel();
        $id = $model->insert(['name' => $data['name'], 'location' => $data['location'] ?? null, 'created_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // Analytics endpoint for warehouse usage
    public function getAnalytics()
    {
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        
        $model = new WarehouseModel();
        try {
            $analytics = $model->getAllWithCounts();
            return $this->response->setJSON($analytics);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Seed test data for 3 warehouses to verify warehouse list and information display
     * Access via: /warehouses/seed-test-data
     */
    public function seedWarehousesTestData()
    {
        if (! session()->get('isLoggedIn') || session('role') !== 'manager') {
            return redirect()->to('/login');
        }

        $model = new WarehouseModel();
        
        // Define the 3 test warehouses
        $testWarehouses = [
            [
                'name' => 'Main Warehouse',
                'location' => 'Tagum City',
                'capacity' => 10000,
                'current_usage' => 3000,
                'status' => 'Active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Warehouse A',
                'location' => 'Panabo City',
                'capacity' => 8000,
                'current_usage' => 2500,
                'status' => 'Active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Warehouse B',
                'location' => 'Davao City',
                'capacity' => 5000,
                'current_usage' => 1000,
                'status' => 'Active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $insertedCount = 0;
        $updatedCount = 0;

        foreach ($testWarehouses as $warehouse) {
            // Check if warehouse already exists by name
            $existing = $model->where('name', $warehouse['name'])->first();
            
            if (!$existing) {
                // Insert new warehouse
                $model->insert($warehouse);
                $insertedCount++;
            } else {
                // Update existing warehouse with new fields
                $model->update($existing['id'], [
                    'capacity' => $warehouse['capacity'],
                    'current_usage' => $warehouse['current_usage'],
                    'status' => $warehouse['status'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $updatedCount++;
            }
        }

        // Set flash message
        if ($insertedCount > 0 || $updatedCount > 0) {
            $message = "Warehouse test data seeded successfully! ";
            if ($insertedCount > 0) {
                $message .= "Inserted: {$insertedCount} warehouses. ";
            }
            if ($updatedCount > 0) {
                $message .= "Updated: {$updatedCount} warehouses.";
            }
            session()->setFlashdata('success', $message);
        } else {
            session()->setFlashdata('info', 'All test warehouses already exist with current data.');
        }

        // Redirect to warehouse list to display the data
        return redirect()->to('/dashboard/manager/warehouses');
    }
}
