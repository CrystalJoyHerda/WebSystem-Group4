<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'report_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vendor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'invoice_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'report_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'debit' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'credit' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
        ]);
        $this->forge->addKey('report_id', true); // Primary key
        $this->forge->addForeignKey('vendor_id', 'vendors', 'vendor_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('invoice_id', 'invoices', 'invoice_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reports');
    }

    public function down()
    {
        $this->forge->dropTable('reports', true);
    }
}
