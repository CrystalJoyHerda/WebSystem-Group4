<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsActiveToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => false,
            ],
        ];

        $this->forge->addColumn('users', $fields);

        $db = \Config\Database::connect();
        $db->table('users')->set(['is_active' => 1])->update();
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'is_active');
    }
}
