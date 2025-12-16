<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('system_settings')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'setting_key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => false,
                ],
                'setting_value' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('setting_key');
            $this->forge->createTable('system_settings', true);
        }

        $now = date('Y-m-d H:i:s');
        $defaults = [
            'inventory.low_stock_threshold' => '10',
            'backup.retention_days' => '30',
            'backup.max_backups' => '50',
        ];

        foreach ($defaults as $k => $v) {
            $row = $db->table('system_settings')->select('id')->where('setting_key', $k)->get()->getRowArray();
            if (! $row) {
                $db->table('system_settings')->insert([
                    'setting_key' => $k,
                    'setting_value' => $v,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'updated_by' => null,
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('system_settings', true);
    }
}
