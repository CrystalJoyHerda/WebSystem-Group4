<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockMovementTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'movement_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'transaction_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'items_in_progress' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'order_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
           'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
           'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'movement_type' => [
                'type'       => 'ENUM',
                'constraint' => ['in', 'out'],
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
        ]);
        $this->forge->addKey('movement_id', true); // Primary key

        $this->forge->addForeignKey('id', 'inventory', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('stock_movements');
    }

    public function down()
    {
         $this->forge->dropTable('stock_movements', true);
    }
}
