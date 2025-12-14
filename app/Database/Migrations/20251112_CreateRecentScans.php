<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecentScans extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'item_sku' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => false,
            ],
            'item_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ],
            'movement_type' => [
                'type' => 'ENUM',
                'constraint' => ['IN','OUT'],
                'default' => 'IN',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'default' => 'Pending',
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('recent_scans');
    }

    public function down()
    {
        $this->forge->dropTable('recent_scans');
    }
}
