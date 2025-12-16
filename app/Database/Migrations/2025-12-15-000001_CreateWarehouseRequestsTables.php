<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWarehouseRequestsTables extends Migration
{
    public function up()
    {
        // Create warehouse_requests table
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
            'requesting_warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'supplying_warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'requested_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['PENDING', 'APPROVED', 'SCANNED', 'DELIVERING', 'DELIVERED', 'REJECTED'],
                'default' => 'PENDING',
            ],
            'outbound_receipt_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'inbound_receipt_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'notes' => [
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('reference_no');
        $this->forge->addKey('status');
        $this->forge->addKey('requesting_warehouse_id');
        $this->forge->addKey('supplying_warehouse_id');
        $this->forge->createTable('warehouse_requests');

        // Create warehouse_request_items table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'request_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('request_id');
        $this->forge->addKey('item_id');
        $this->forge->createTable('warehouse_request_items');
    }

    public function down()
    {
        $this->forge->dropTable('warehouse_request_items', true);
        $this->forge->dropTable('warehouse_requests', true);
    }
}
