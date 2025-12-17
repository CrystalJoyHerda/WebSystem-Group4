<?php

namespace App\Filters;

use App\Services\AuthorizationService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ItAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = Services::session();

        if (! $session->get('isLoggedIn')) {
            if ($this->isApiRequest($request)) {
                return Services::response()->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
            }
            return redirect()->to('/login');
        }

        $auth = new AuthorizationService();
        if (! $auth->isItAdminRole($session->get('role'))) {
            if ($this->isApiRequest($request)) {
                return Services::response()->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }
            return redirect()->to('/login');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }

    private function isApiRequest(RequestInterface $request): bool
    {
        $path = trim((string) $request->getUri()->getPath(), '/');
        return strpos($path, 'api/') === 0;
    }
}
