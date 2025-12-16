<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuditFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'created_by');
        $this->forge->dropColumn('users', 'updated_by');
    }
}
