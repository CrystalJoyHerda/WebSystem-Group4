<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoginLockoutFieldsToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('users')) {
            return;
        }

        $fields = [];
        if (! $db->fieldExists('failed_login_attempts', 'users')) {
            $fields['failed_login_attempts'] = [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => false,
            ];
        }
        if (! $db->fieldExists('lockout_until', 'users')) {
            $fields['lockout_until'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('users')) {
            return;
        }

        if ($db->fieldExists('failed_login_attempts', 'users')) {
            $this->forge->dropColumn('users', 'failed_login_attempts');
        }
        if ($db->fieldExists('lockout_until', 'users')) {
            $this->forge->dropColumn('users', 'lockout_until');
        }
    }
}
