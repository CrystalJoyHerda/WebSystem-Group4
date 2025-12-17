<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuthDefaultsToSystemSettings extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('system_settings')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $defaults = [
            'auth.password_min_length' => '6',
            'auth.lockout_attempts' => '5',
            'auth.lockout_minutes' => '15',
            'auth.session_timeout_minutes' => '30',
            'users.default_is_active' => '1',
        ];

        foreach ($defaults as $k => $v) {
            $row = $db->table('system_settings')->select('id')->where('setting_key', $k)->get()->getRowArray();
            if (! $row) {
                $insert = [
                    'setting_key' => $k,
                    'setting_value' => $v,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($db->fieldExists('updated_by', 'system_settings')) {
                    $insert['updated_by'] = null;
                }
                $db->table('system_settings')->insert($insert);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('system_settings')) {
            return;
        }

        $keys = [
            'auth.password_min_length',
            'auth.lockout_attempts',
            'auth.lockout_minutes',
            'auth.session_timeout_minutes',
            'users.default_is_active',
        ];

        $db->table('system_settings')->whereIn('setting_key', $keys)->delete();
    }
}
