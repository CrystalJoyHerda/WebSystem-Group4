<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePutAwayTaskTable extends Migration
{
    public function up()
    {
          $this->forge->addField([
            'task_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'item_details' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high'],
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],

        ]);
        $this->forge->addKey('task_id', true); // Primary key

        $this->forge->addForeignKey('id', 'inventory', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('put_away_tasks');
    }

    public function down()
    {
        $this->forge->dropTable('put_away_tasks', true);
    }
}
