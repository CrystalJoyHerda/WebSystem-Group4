<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'warehouse_id',
        'title',
        'description',
        'priority',
        'status',
        'requester_id',
        'assignee_id',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
