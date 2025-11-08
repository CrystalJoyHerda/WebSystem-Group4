<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransfers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'item_id' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'from_warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'to_warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('transfers', true);
    }

    public function down()
    {
        $this->forge->dropTable('transfers', true);
    }
}
