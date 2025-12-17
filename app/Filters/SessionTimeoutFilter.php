<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class SessionTimeoutFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = Services::session();

        if (! $session->get('isLoggedIn')) {
            return null;
        }

        $timeoutMinutes = $this->getSettingInt('auth.session_timeout_minutes', 30);
        if ($timeoutMinutes <= 0) {
            $session->set('last_activity', time());
            return null;
        }

        $last = $session->get('last_activity');
        $now = time();
        if (is_numeric($last)) {
            $last = (int) $last;
            if (($now - $last) > ($timeoutMinutes * 60)) {
                $session->destroy();
                if ($this->isApiRequest($request)) {
                    return Services::response()->setStatusCode(401)->setJSON(['error' => 'Session expired']);
                }
                return redirect()->to('/login');
            }
        }

        $session->set('last_activity', $now);
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function isApiRequest(RequestInterface $request): bool
    {
        $path = trim((string) $request->getUri()->getPath(), '/');
        return strpos($path, 'api/') === 0;
    }

    private function getSettingInt(string $key, int $default): int
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('system_settings')) {
            return $default;
        }

        $row = $db->table('system_settings')->select('setting_value')->where('setting_key', $key)->get()->getRowArray();
        if (! $row) {
            return $default;
        }

        $val = $row['setting_value'] ?? null;
        if ($val === null) {
            return $default;
        }

        $val = trim((string) $val);
        if ($val === '' || ! is_numeric($val)) {
            return $default;
        }

        return (int) $val;
    }
}
