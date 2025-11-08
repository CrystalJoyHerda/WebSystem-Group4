<?php
namespace App\Models;

use CodeIgniter\Model;

class TransferModel extends Model
{
    protected $table = 'transfers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['item_id','from_warehouse_id','to_warehouse_id','quantity','created_by','created_at'];
    protected $useTimestamps = false;
}
