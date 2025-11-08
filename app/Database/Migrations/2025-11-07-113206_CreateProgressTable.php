<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProgressTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'progress_id' => [
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
           
        ]);

        $this->forge->addKey('progress_id', true); // Primary key

        $this->forge->addForeignKey('id', 'inventory', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('progress');
    }

    public function down()
    {
        $this->forge->dropTable('progress', true);
    }
}
