<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserWarehousesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('user_warehouses')) {
            return;
        }

        $this->forge->addField([
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['user_id', 'warehouse_id'], true);
        $this->forge->addKey('warehouse_id');
        $this->forge->createTable('user_warehouses', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_warehouses', true);
    }
}
