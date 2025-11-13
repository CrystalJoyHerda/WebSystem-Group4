<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddViewerRole extends Migration
{
    public function up()
    {
        // Add 'viewer' to the existing role ENUM
        $this->forge->modifyColumn('users', [
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['manager','staff','viewer','inventory auditor',' procurement officer','accounts payable','accounts receivable','IT administrator','topmanagement'],
                'default' => 'staff',
            ]
        ]);
    }

    public function down()
    {
        // Remove 'viewer' from the role ENUM
        $this->forge->modifyColumn('users', [
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['manager','staff','inventory auditor',' procurement officer','accounts payable','accounts receivable','IT administrator','topmanagement'],
                'default' => 'staff',
            ]
        ]);
    }
}