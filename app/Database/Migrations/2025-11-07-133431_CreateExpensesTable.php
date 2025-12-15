<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        $this->forge->addField(fields: [
            'expense_id' => [
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
            'expense_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'budget' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'actual' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'variance' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'percent_budget' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'expense_date' => [
                'type'       => 'DATE',
                'null'       => false,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('expense_id', true); // Primary key
        $this->forge->addForeignKey('vendor_id', 'vendors', 'vendor_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('expenses');
    }

    public function down()
    {
        $this->forge->dropTable('expenses', true);
    }
}
