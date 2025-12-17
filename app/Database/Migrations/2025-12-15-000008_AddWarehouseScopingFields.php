<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarehouseScopingFields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('tickets') && ! $db->fieldExists('warehouse_id', 'tickets')) {
            $this->forge->addColumn('tickets', [
                'warehouse_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'id',
                ],
            ]);
        }

        if ($db->tableExists('assets') && ! $db->fieldExists('warehouse_id', 'assets')) {
            $this->forge->addColumn('assets', [
                'warehouse_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'id',
                ],
            ]);
        }

        if ($db->tableExists('audit_logs') && ! $db->fieldExists('warehouse_id', 'audit_logs')) {
            $this->forge->addColumn('audit_logs', [
                'warehouse_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'id',
                ],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('tickets') && $db->fieldExists('warehouse_id', 'tickets')) {
            $this->forge->dropColumn('tickets', 'warehouse_id');
        }

        if ($db->tableExists('assets') && $db->fieldExists('warehouse_id', 'assets')) {
            $this->forge->dropColumn('assets', 'warehouse_id');
        }

        if ($db->tableExists('audit_logs') && $db->fieldExists('warehouse_id', 'audit_logs')) {
            $this->forge->dropColumn('audit_logs', 'warehouse_id');
        }
    }
}
