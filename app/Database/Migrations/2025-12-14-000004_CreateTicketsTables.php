<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketsTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'priority' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'normal',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'open',
            ],
            'requester_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'assignee_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['status', 'priority']);
        $this->forge->addKey('warehouse_id');
        $this->forge->addKey('requester_id');
        $this->forge->addKey('assignee_id');
        $this->forge->createTable('tickets', true);

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'ticket_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'comment' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('ticket_id');
        $this->forge->addKey('user_id');
        $this->forge->createTable('ticket_comments', true);

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'ticket_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'actor_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'before_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'after_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('ticket_id');
        $this->forge->addKey('actor_user_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('ticket_history', true);
    }

    public function down()
    {
        $this->forge->dropTable('ticket_history', true);
        $this->forge->dropTable('ticket_comments', true);
        $this->forge->dropTable('tickets', true);
    }
}
