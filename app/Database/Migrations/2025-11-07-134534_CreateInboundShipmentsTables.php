<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInboundShipmentsTables extends Migration
{
    public function up()
    {
        // Create inbound_receipt_items table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'receipt_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'unit_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('receipt_id');
        $this->forge->createTable('inbound_receipt_items');
    }

    public function down()
    {
        $this->forge->dropTable('inbound_receipt_items');
        $this->forge->dropTable('inbound_receipts');
    }
}
