<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOutboundShipmentsTables extends Migration
{
    public function up()
    {
        // Create outbound_receipts table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'reference_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],
            'customer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Pending', 'Approved', 'Rejected'],
                'default' => 'Pending',
            ],
            'total_items' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('reference_no');
        $this->forge->addKey('status');
        $this->forge->createTable('outbound_receipts');

        // Create outbound_receipt_items table
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('receipt_id');
        $this->forge->createTable('outbound_receipt_items');
    }

    public function down()
    {
        $this->forge->dropTable('outbound_receipt_items');
        $this->forge->dropTable('outbound_receipts');
    }
}
