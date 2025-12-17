<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    private function normalizeRole($role): string
    {
        $role = (string) ($role ?? '');
        $role = strtolower(trim($role));
        $role = preg_replace('/\s+/', '', $role);
        $role = str_replace('_', '', $role);
        return $role;
    }

    private function isItAdminRole($role): bool
    {
        $normalized = $this->normalizeRole($role);
        return in_array($normalized, ['itadministrator', 'itadminstrator', 'itadministsrator'], true);
    }

    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('info', 'Please log in to access the dashboard.');
            return redirect()->to('/login');
        }

        $role = session()->get('role') ?? session()->get('role');
        if ($this->isItAdminRole($role)) {
            return redirect()->to('/admin');
        }

        if ($this->normalizeRole($role) === 'topmanagement') {
            return redirect()->to('/top-management');
        }

        if ($role === 'manager') {
            return redirect()->to('/dashboard/manager');
        }

        if ($role === 'staff') {
            return redirect()->to('/dashboard/staff');
        }

        if ($role === 'viewer') {
            return redirect()->to('/dashboard/viewer');
        }

        // Default fallback
        return view('dashboard/manager/manager');
    }

    public function manager()
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('info', 'Please log in to access the dashboard.');
            return redirect()->to('/login');
        }
        if (session('role') !== 'manager') {
            return redirect()->to('/login');
        }
        
        // Fetch warehouse data
        $warehouseModel = new \App\Models\WarehouseModel();
        $inventoryModel = new \App\Models\InventoryModel();
        $db = \Config\Database::connect();
        
        $warehouses = $warehouseModel->findAll();
        
        // Get pending approvals count (Pending inbound + Pending outbound receipts)
        $pendingInbound = $db->table('inbound_receipts')
            ->where('status', 'Pending')
            ->countAllResults();
        
        $pendingOutbound = $db->table('outbound_receipts')
            ->where('status', 'Pending')
            ->countAllResults();
        
        $pendingApprovals = $pendingInbound + $pendingOutbound;
        
        // Get alert stocks count (low stock items)
        $alertStocks = $inventoryModel->getLowStockItems();
        $alertCount = count($alertStocks);
        
        // Enrich each warehouse with real data
        foreach ($warehouses as &$warehouse) {
            // Get item count and total quantity for this warehouse
            $items = $inventoryModel->where('warehouse_id', $warehouse['id'])->findAll();
            $warehouse['item_count'] = count($items);
            $warehouse['total_quantity'] = array_sum(array_column($items, 'quantity'));
            
            // Calculate capacity percentage
            $warehouse['capacity_percent'] = $warehouse['capacity'] > 0 
                ? min(($warehouse['total_quantity'] / $warehouse['capacity']) * 100, 100) 
                : 0;
            
            // Get staff count for this warehouse
            $staffCount = $db->table('users')
                ->where('role', 'staff')
                ->countAllResults();
            $warehouse['staff_count'] = ceil($staffCount / count($warehouses)); // Distribute evenly for now
            
            // Get recent activity
            $recentInbound = $db->table('inbound_receipts')
                ->where('warehouse_id', $warehouse['id'])
                ->where('status', 'Approved')
                ->orderBy('approved_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            
            $recentOutbound = $db->table('outbound_receipts')
                ->where('warehouse_id', $warehouse['id'])
                ->where('status', 'Approved')
                ->orderBy('approved_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            
            // Determine status message
            if ($recentInbound && $recentOutbound) {
                $inboundTime = strtotime($recentInbound['approved_at'] ?? $recentInbound['created_at']);
                $outboundTime = strtotime($recentOutbound['approved_at'] ?? $recentOutbound['created_at']);
                
                if ($inboundTime > $outboundTime) {
                    $hoursAgo = floor((time() - $inboundTime) / 3600);
                    $warehouse['status_message'] = "Inbound shipment completed {$hoursAgo}h ago";
                    $warehouse['status_class'] = 'text-success';
                } else {
                    $warehouse['status_message'] = 'Stock transfer in progress';
                    $warehouse['status_class'] = 'text-info';
                }
            } elseif ($recentInbound) {
                $hoursAgo = floor((time() - strtotime($recentInbound['approved_at'] ?? $recentInbound['created_at'])) / 3600);
                $warehouse['status_message'] = "Inbound shipment completed {$hoursAgo}h ago";
                $warehouse['status_class'] = 'text-success';
            } elseif ($recentOutbound) {
                $warehouse['status_message'] = 'Stock transfer in progress';
                $warehouse['status_class'] = 'text-info';
            } else {
                $warehouse['status_message'] = 'No recent activity';
                $warehouse['status_class'] = 'text-muted';
            }
        }
        
        echo view('dashboard/manager/manager', [
            'warehouses' => $warehouses,
            'pendingApprovals' => $pendingApprovals,
            'alertCount' => $alertCount
        ]);
    }

    public function warehouseInventory($warehouseId = null)
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('info', 'Please log in to access the dashboard.');
            return redirect()->to('/login');
        }
        if (session('role') !== 'manager') {
            return redirect()->to('/login');
        }

        if (!$warehouseId) {
            return redirect()->to('/dashboard/manager');
        }

        $warehouseModel = new \App\Models\WarehouseModel();
        $inventoryModel = new \App\Models\InventoryModel();
        
        // Get warehouse details
        $warehouse = $warehouseModel->find($warehouseId);
        
        if (!$warehouse) {
            session()->setFlashdata('error', 'Warehouse not found.');
            return redirect()->to('/dashboard/manager');
        }

        // Get all items in this warehouse
        $items = $inventoryModel->where('warehouse_id', $warehouseId)
                               ->orderBy('name', 'ASC')
                               ->findAll();

        // Calculate total value and other metrics
        $totalItems = count($items);
        $totalQuantity = 0;
        $totalValue = 0;
        $lowStockCount = 0;

        foreach ($items as &$item) {
            $totalQuantity += $item['quantity'];
            $totalValue += ($item['quantity'] * ($item['unit_price'] ?? 0));
            
            // Check if item is low stock
            if ($item['quantity'] <= ($item['reorder_level'] ?? 0)) {
                $lowStockCount++;
                $item['is_low_stock'] = true;
            } else {
                $item['is_low_stock'] = false;
            }
        }

        echo view('dashboard/manager/warehouse_inventory', [
            'warehouse' => $warehouse,
            'items' => $items,
            'totalItems' => $totalItems,
            'totalQuantity' => $totalQuantity,
            'totalValue' => $totalValue,
            'lowStockCount' => $lowStockCount
        ]);
    }

    public function staff()
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('info', 'Please log in to access the dashboard.');
            return redirect()->to('/login');
        }
        if (session('role') !== 'staff') {
            return redirect()->to('/login');
        }
        echo view('dashboard/staff/staff');
    }

    public function viewer()
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('info', 'Please log in to access the dashboard.');
            return redirect()->to('/login');
        }
        if (session('role') !== 'viewer') {
            return redirect()->to('/login');
        }
        echo view('dashboard/viewer/viewer');
    }

     public function admin()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('login');
        }
        if (! $this->isItAdminRole(session('role'))) {
            return redirect()->to('/login');
        }
        echo view('dashboard/IT Adminstrator/dashboard');
    }
}