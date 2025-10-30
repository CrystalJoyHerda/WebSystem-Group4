<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('info', 'Please log in to access the dashboard.');
            return redirect()->to('/login');
        }

        $role = session()->get('role') ?? session()->get('role');
        if ($role === 'manager') {
            return redirect()->to('/dashboard/manager');
        }

        if ($role === 'staff') {
            return redirect()->to('/dashboard/staff');
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
        echo view('dashboard/manager/manager');
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
}
