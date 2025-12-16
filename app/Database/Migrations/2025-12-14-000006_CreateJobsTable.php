<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJobsTable extends Migration
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
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'payload_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending',
            ],
            'attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'available_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_error' => [
                'type' => 'TEXT',
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
        $this->forge->addKey(['status', 'available_at']);
        $this->forge->addKey('type');
        $this->forge->createTable('jobs', true);
    }

    public function down()
    {
        $this->forge->dropTable('jobs', true);
    }
}
