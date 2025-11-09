<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'invoice_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'invoice_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'supplier' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'Date' => [
                'type'       => 'DATE',
                'null'       => false,
            ],
            'due_date' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'paid', 'overdue'],
            ],
        ]);

        $this->forge->addKey('invoice_id', true); // Primary key
        $this->forge->createTable('invoices');
    }

    public function down()
    {
        $this->forge->dropTable('invoices', true);
    }
}
