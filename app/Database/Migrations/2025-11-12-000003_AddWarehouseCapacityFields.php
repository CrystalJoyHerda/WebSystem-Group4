<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarehouseCapacityFields extends Migration
{
    public function up()
    {
        $fields = [
            'contact_info' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'location'
            ],
            'capacity' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'after' => 'contact_info'
            ],
            'current_usage' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'after' => 'capacity'
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive', 'maintenance'],
                'default' => 'active',
                'after' => 'current_usage'
            ]
        ];

        $this->forge->addColumn('warehouses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('warehouses', ['contact_info', 'capacity', 'current_usage', 'status']);
    }
}