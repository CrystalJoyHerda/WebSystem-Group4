<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Warehouse2Dashboard extends Controller
{
    public function manager()
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('info', 'Please log in to access the dashboard.');
            return redirect()->to('/login');
        }
        if (session('role') !== 'manager') {
            return redirect()->to('/login');
        }

        // Ensure warehouse_id is set for warehouse-scoped queries
        session()->set('warehouse_id', 2);

        echo view('warehouse2/manager_dashboard/manager');
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

        // Ensure warehouse_id is set for warehouse-scoped queries
        session()->set('warehouse_id', 2);

        echo view('warehouse2/staff_dashboard/staff');
    }
}
