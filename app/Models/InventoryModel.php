<?php
namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','sku','category','location','quantity','status','expiry','created_at','updated_at'];
    protected $useTimestamps = false;
}
