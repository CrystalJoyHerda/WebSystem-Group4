<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    protected $db;
    protected $builder;

    private function getSystemSettingInt(string $key, int $default): int
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

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
    }

    public function register()
    {
        helper(['form']);
        $data = [];

        if(session()->get('isLoggedIn')) {
             session()->setFlashdata('info', 'You are already logged in.');
            return redirect()->to(base_url('dashboard'));
        }

        if ($this->request->is('post')) {
            $minLen = $this->getSystemSettingInt('auth.password_min_length', 6);
            if ($minLen < 6) {
                $minLen = 6;
            }
            $rules = [
               'name' => [
                    'label'  => 'Full Name',
                    'rules'  => 'required|min_length[3]|max_length[50]|regex_match[/^[A-Za-z\s]+$/]', // Name must be required and match regex
                    'errors' => [
                        'regex_match' => 'The {field} may only contain letters and spaces.' // Custom error message for regex match
                    ]
                ],
                'email' => [
                    'label'  => 'Email Address',
                    'rules'  => 'required|valid_email|is_unique[users.email]|regex_match[/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/]', // Email must be valid and unique
                    'errors' => [
                        'regex_match' => 'The {field} contains invalid characters.', // Custom error message for regex match
                        'is_unique'   => 'That email is already registered.' // Custom error message for unique check
                    ]
                ],
                'password' => [
                    'label'  => 'Password',
                    'rules'  => 'required|min_length[' . $minLen . ']|max_length[255]|regex_match[/^(?!.*[\*"]).+$/]', // Password must be valid and not contain certain symbols
                    'errors' => [
                        'regex_match' => 'The {field} must not contain the symbols * or ".'
                    ]
                ],
                'password_confirm' => [
                    'label'  => 'Confirm Password',
                    'rules'  => 'required|min_length[' . $minLen . ']|matches[password]|max_length[255]|regex_match[/^(?!.*[\*"]).+$/]', // Password must be valid and not contain certain symbols
                    'errors' => [
                        'regex_match' => 'The {field} must not contain the symbols * or ".'
                    ]
                ],
            ];

            if ($this->validate($rules)) {
                $role = $this->request->getPost('role') ?? 'user';

                $newData = [
                    'name'       => $this->request->getPost('name'),
                    'email'      => $this->request->getPost('email'),
                    'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'       => $role,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->builder->insert($newData)) {
                    session()->setFlashdata('success', 'Registration successful. You can now log in.');
                    return redirect()->to(base_url('login'));
                } else {
                    session()->setFlashdata('error', 'Registration failed. Please try again.');
                }
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view('auth/register', $data);
    }

    public function login()
    {
        helper(['form']);
        $data = [];

        //  if(session()->get('isLoggedIn')) {
        //     return redirect()->to(base_url('dashboard'));

        // }

        if ($this->request->is('post')) {
            $rules = [
               'email' => [
                    'label'  => 'Email Address',
                    'rules'  => 'required|valid_email|regex_match[/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/]', // Email must be valid and unique
                    'errors' => [
                        'regex_match' => 'The {field} contains invalid characters.', // Custom error message for regex match
                        'is_unique'   => 'That email is already registered.' // Custom error message for unique check
                    ]
                ],
               'password' => [
                    'label'  => 'Password',
                    'rules'  => 'required|min_length[6]|max_length[255]|regex_match[/^(?!.*[\*"]).+$/]', // Password must be valid and not contain certain symbols
                    'errors' => [
                        'regex_match' => 'The {field} must not contain the symbols * or ".'
                    ]
                ],
            ];

            if ($this->validate($rules)) {
                $email = $this->request->getPost('email');
                $password = $this->request->getPost('password');

                $user = $this->builder
                    ->where('email', $email)
                    ->get()
                    ->getRowArray();

                $lockoutAttempts = $this->getSystemSettingInt('auth.lockout_attempts', 5);
                if ($lockoutAttempts < 0) {
                    $lockoutAttempts = 0;
                }
                $lockoutMinutes = $this->getSystemSettingInt('auth.lockout_minutes', 15);
                if ($lockoutMinutes < 0) {
                    $lockoutMinutes = 0;
                }

                $db = \Config\Database::connect();
                $hasLockout = $db->fieldExists('failed_login_attempts', 'users') && $db->fieldExists('lockout_until', 'users');
                if ($hasLockout && $user && ! empty($user['lockout_until'])) {
                    $untilTs = strtotime((string) $user['lockout_until']);
                    if ($untilTs !== false && time() < $untilTs) {
                        session()->setFlashdata('error', 'Account temporarily locked. Please try again later.');
                        return view('auth/login', $data);
                    }
                }

                if ($user && array_key_exists('is_active', $user) && (int) $user['is_active'] === 0) {
                    session()->setFlashdata('error', 'Your account has been disabled. Please contact the administrator.');
                } elseif ($user && password_verify($password, $user['password'])) {
                    if ($hasLockout && isset($user['id'])) {
                        $this->db->table('users')->where('id', (int) $user['id'])->update([
                            'failed_login_attempts' => 0,
                            'lockout_until' => null,
                        ]);
                    }
                    session()->set([
                        'userID'     => $user['id'],
                        'name'       => $user['name'],
                        'email'      => $user['email'],
                        'role'       => $user['role'],
                        'isLoggedIn' => true
                    ]);

                    session()->setFlashdata('success', 'Welcome back, ' . $user['name'] . '!');

                    $db = \Config\Database::connect();
                    if ($db->tableExists('audit_logs')) {
                        $userAgent = $this->request->getUserAgent();
                        $ua = '';
                        if ($userAgent && method_exists($userAgent, 'getAgentString')) {
                            $ua = (string) $userAgent->getAgentString();
                        } elseif ($userAgent) {
                            $ua = (string) $userAgent;
                        }

                        $db->table('audit_logs')->insert([
                            'actor_user_id' => (int) $user['id'],
                            'action' => 'login',
                            'entity_type' => 'auth',
                            'entity_id' => (int) $user['id'],
                            'before_json' => null,
                            'after_json' => json_encode(['role' => $user['role'] ?? null]),
                            'ip_address' => $this->request->getIPAddress(),
                            'user_agent' => $ua,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    
                    // Debug: Log the user role to help troubleshoot
                    log_message('info', 'User role: ' . $user['role']);
                    
                    // Redirect based on role
                    if ($this->isItAdminRole($user['role'])) {
                        return redirect()->to('dashboard/admin');
                    } elseif ($user['role'] === 'manager') {
                        return redirect()->to('dashboard/manager');
                    } elseif ($user['role'] === 'staff') {
                        return redirect()->to('dashboard/staff');
                    } elseif ($user['role'] === 'viewer') {
                        return redirect()->to('dashboard/viewer');
                    } else {
                        // Log unexpected role for debugging
                        log_message('warning', 'Unexpected user role: ' . $user['role']);
                        return redirect()->to('dashboard/manager');
                    }
                } else {
                    if ($hasLockout && $user && isset($user['id']) && $lockoutAttempts > 0) {
                        $attempts = isset($user['failed_login_attempts']) ? (int) $user['failed_login_attempts'] : 0;
                        $attempts++;
                        $update = ['failed_login_attempts' => $attempts];
                        if ($attempts >= $lockoutAttempts && $lockoutMinutes > 0) {
                            $update['lockout_until'] = date('Y-m-d H:i:s', time() + ($lockoutMinutes * 60));
                            $update['failed_login_attempts'] = 0;
                        }
                        $this->db->table('users')->where('id', (int) $user['id'])->update($update);
                    }
                    session()->setFlashdata('error', 'Invalid email or password.');
                }
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view('auth/login', $data);
    }

    public function logout()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('audit_logs') && session()->get('userID')) {
            $userAgent = $this->request->getUserAgent();
            $ua = '';
            if ($userAgent && method_exists($userAgent, 'getAgentString')) {
                $ua = (string) $userAgent->getAgentString();
            } elseif ($userAgent) {
                $ua = (string) $userAgent;
            }

            $uid = (int) session()->get('userID');
            $db->table('audit_logs')->insert([
                'actor_user_id' => $uid,
                'action' => 'logout',
                'entity_type' => 'auth',
                'entity_id' => $uid,
                'before_json' => null,
                'after_json' => null,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $ua,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        session()->destroy();
        return redirect()->to(base_url('login'));
    }

    /**
     * Debug helper: fetch user row directly from database by email.
     * Usage: GET /auth/dbfetch?email=manager@whs.com
     * Returns JSON (including hashed password) so you can verify DB contents.
     */
    public function dbfetch()
    {
        $email = $this->request->getGet('email') ?? $this->request->getPost('email');
        if (! $email) {
            return $this->response->setStatusCode(400)->setBody('Missing email parameter');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $user = $builder->select('id, name, email, role, password, created_at, updated_at')
            ->where('email', $email)
            ->get()
            ->getRowArray();

        if (! $user) {
            return $this->response->setStatusCode(404)->setBody('User not found');
        }

        // Return JSON for quick inspection. Do NOT use in production.
        return $this->response->setContentType('application/json')
            ->setBody(json_encode($user));
    }
}
