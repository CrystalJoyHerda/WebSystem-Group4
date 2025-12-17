<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateARinvoicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ar_invoice_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'customer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'invoice_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'amount_due' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'due_date' => [
                'type'       => 'DATE',
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['paid', 'unpaid', 'overdue'],
            ],
            'customers_notes' => [
                'type'       => 'TEXT',
                'null'       => true,
            ]
        ]);

        $this->forge->addKey('ar_invoice_id', true); // Primary key
        $this->forge->addForeignKey('customer_id', 'customers', 'customer_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ar_invoices', true);
    }

    public function down()
    {
         $this->forge->dropTable('ar_invoices', true);
    }
}
