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
            return redirect()->to('/dashboard/admin');
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
            session()->setFlashdata('info', 'Please log in to access the dashboard.');
            return redirect()->to('/login');
        }
        
        $role = session()->get('role');
        if (! $this->isItAdminRole($role)) {
            session()->setFlashdata('error', 'Access denied. Admin privileges required.');
            return redirect()->to('/login');
        }
        
        return redirect()->to('/admin');
    }
}