<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendorsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'vendor_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vendor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'vendor_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],    
        ]);

        $this->forge->addKey('vendor_id', true); // Primary key
        $this->forge->createTable('vendors');
    }

    public function down()
    {
        $this->forge->dropTable('vendors', true);
    }
}
