<?php

namespace App\Models;

use CodeIgniter\Model;

class UserWarehouseModel extends Model
{
    protected $table = 'user_warehouses';
    protected $primaryKey = 'user_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'warehouse_id',
        'created_at',
    ];

    protected $useTimestamps = false;
}
