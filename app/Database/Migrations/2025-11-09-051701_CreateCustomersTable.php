<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'customer_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'customer_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'customer_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],    
            'taxt_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'payment_terms' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);

        $this->forge->addKey('customer_id', true); // Primary key
        $this->forge->createTable('customers');
    }

    public function down()
    {
        $this->forge->dropTable('customers', true);
    }
}
