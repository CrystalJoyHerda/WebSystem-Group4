<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Debug extends Controller
{
    public function session()
    {
        helper('text');
        echo '<pre>' . print_r(session()->get(), true) . '</pre>';
    }
}
