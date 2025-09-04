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

        if ($this->request->is('post')) {
            $rules = [
                'name' => 'required|min_length[3]|max_length[100]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]|max_length[255]',
                'password_confirm' => 'matches[password]'
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

        if ($this->request->is('post')) {
            $rules = [
                'email' => 'required|valid_email',
                'password' => 'required|min_length[6]|max_length[255]'
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

                    session()->setFlashdata('success', 'Welcome back, ' . $user['name'] . '!');
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
