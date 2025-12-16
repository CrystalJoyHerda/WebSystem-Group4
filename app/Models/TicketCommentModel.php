<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketCommentModel extends Model
{
    protected $table = 'ticket_comments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ticket_id',
        'user_id',
        'comment',
        'created_at',
    ];

    protected $useTimestamps = false;
}
