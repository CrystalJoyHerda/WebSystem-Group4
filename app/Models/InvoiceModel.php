<?php
namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $allowedFields = ['reference','amount','payable','vendor_client','due_date','status','created_at','updated_at'];
    protected $useTimestamps = false;
}
