<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class AccountsPayableController extends Controller
{
    public function dashboard() {
        return view('dashboard/account_payable/dashboard');
    }

     public function invoices() {
        return view('dashboard/account_payable/invoices');
    }

    public function payments() {
        return view('dashboard/account_payable/payments');
    }

     public function vendors() {
        return view('dashboard/account_payable/vendors');
    }

     public function reports() {
        return view('dashboard/account_payable/reports');
    }
}