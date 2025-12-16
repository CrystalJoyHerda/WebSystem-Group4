<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TopManagementFilter implements FilterInterface
{
    private function normalizeRole($role): string
    {
        $role = (string) ($role ?? '');
        $role = strtolower(trim($role));
        $role = preg_replace('/\s+/', '', $role);
        $role = str_replace('_', '', $role);
        return $role;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = session()->get('role');
        if ($this->normalizeRole($role) !== 'topmanagement') {
            return redirect()->to('/login');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
