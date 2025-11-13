<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTransfersAddApprovalFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transfers', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'approved', 'completed', 'rejected'],
                'default' => 'completed', // Keep existing transfers as completed
                'after' => 'quantity'
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'status'
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by'
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'approved_at'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transfers', ['status', 'approved_by', 'approved_at', 'notes']);
    }
}