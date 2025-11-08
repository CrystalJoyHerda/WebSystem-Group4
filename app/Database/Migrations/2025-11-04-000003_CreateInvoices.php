<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoices extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'payable' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'vendor_client' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'due_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'OPEN'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('invoices', true);
    }

    public function down()
    {
        $this->forge->dropTable('invoices', true);
    }
}
