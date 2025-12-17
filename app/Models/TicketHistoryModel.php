<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketHistoryModel extends Model
{
    protected $table = 'ticket_history';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ticket_id',
        'actor_user_id',
        'action',
        'before_json',
        'after_json',
        'created_at',
    ];

    protected $useTimestamps = false;
}
