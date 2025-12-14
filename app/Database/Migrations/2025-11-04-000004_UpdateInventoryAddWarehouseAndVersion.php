<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateInventoryAddWarehouseAndVersion extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('inventory')) return;

        $fields = [
            'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'location'],
            'version' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'warehouse_id'],
        ];

        $this->forge->addColumn('inventory', $fields);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('inventory')) return;
        $this->forge->dropColumn('inventory', 'warehouse_id');
        $this->forge->dropColumn('inventory', 'version');
    }
}
