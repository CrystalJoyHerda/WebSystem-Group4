<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetAssignmentModel extends Model
{
    protected $table = 'asset_assignments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'asset_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'notes',
    ];

    protected $useTimestamps = false;
}
