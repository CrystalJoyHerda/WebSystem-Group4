<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDecrepancyTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'decrepancy_id' => [
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
            'invoice_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'purchase_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'goods_received' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'vendor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'decrepancy_type' => [
                'type'       => 'ENUM',
                'constraint' => ['quantity mismatch', 'price decrepancy', 'missing items', 'damaged'],
            ],
            'reported_quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'actual_quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'report_date' => [
                'type'       => 'DATE',
                'null'       => false,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['under review', 'investigating', 'resolved', 'closed'],
            ],
            'resolution_date' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'notes' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('decrepancy_id', true); // Primary key
        $this->forge->addForeignKey('id', 'inventory', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('invoice_id', 'invoices', 'invoice_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('purchase_order_id', 'purchase_orders', 'purchase_order_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('vendor_id', 'vendors', 'vendor_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('decrepancy');
    }

    public function down()
    {
        $this->forge->dropTable('decrepancy', true);
    }
}
