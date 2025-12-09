<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    protected $db;
    protected $builder;

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
                    'rules'  => 'required|min_length[6]|max_length[255]|regex_match[/^(?!.*[\*"]).+$/]', // Password must be valid and not contain certain symbols
                    'errors' => [
                        'regex_match' => 'The {field} must not contain the symbols * or ".'
                    ]
                ],
                'password_confirm' => [
                    'label'  => 'Confirm Password',
                    'rules'  => 'required|min_length[6]|matches[password]|max_length[255]|regex_match[/^(?!.*[\*"]).+$/]', // Password must be valid and not contain certain symbols
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

         if(session()->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));

        }

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

                if ($user && password_verify($password, $user['password'])) {
                    session()->set([
                        'userID'     => $user['id'],
                        'name'       => $user['name'],
                        'email'      => $user['email'],
                        'role'       => $user['role'],
                        'isLoggedIn' => true
                    ]);
                    // Determine warehouse assignment for the user in a safe order:
                    // 1) Prefer `warehouse_id` column on the `users` row (if present)
                    // 2) Fall back to `user_warehouses` mapping table (if present)
                    // 3) Default to warehouse 1 (Main Warehouse)
                    try {
                        if (isset($user['warehouse_id']) && !empty($user['warehouse_id'])) {
                            // Use explicit warehouse assigned on the users table
                            session()->set('warehouse_id', (int)$user['warehouse_id']);
                        } else {
                            $db = \Config\Database::connect();
                            if ($db->tableExists('user_warehouses')) {
                                $uw = $db->table('user_warehouses')->where('user_id', $user['id'])->get()->getRowArray();
                                if ($uw && isset($uw['warehouse_id'])) {
                                    session()->set('warehouse_id', (int)$uw['warehouse_id']);
                                } else {
                                    session()->set('warehouse_id', 1);
                                }
                            } else {
                                // Mapping table not present; default to Main Warehouse
                                session()->set('warehouse_id', 1);
                            }
                        }
                    } catch (\Throwable $e) {
                        // If anything fails, set a safe default and continue
                        session()->set('warehouse_id', 1);
                        if (function_exists('log_message')) {
                            log_message('error', '[Auth::login] warehouse lookup failed: ' . $e->getMessage());
                        }
                    }

                    session()->setFlashdata('success', value: 'Welcome back, ' . $user['name'] . '!');
                    // Redirect based on role
                    if ($user['role'] === 'manager') {
                        return redirect()->to(base_url('dashboard/manager'));
                    } elseif ($user['role'] === 'staff') {
                        return redirect()->to(base_url('dashboard/staff'));
                    }

                    return redirect()->to(base_url('dashboard'));
                } else {
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
