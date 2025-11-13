<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStaffTasksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'movement_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true, // References stock_movements table
            ],
            'reference_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false, // PO-1234, SO-5678, etc.
            ],
            'warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'item_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'item_sku' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
            ],
            'movement_type' => [
                'type' => 'ENUM',
                'constraint' => ['IN', 'OUT'],
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Pending', 'Completed', 'Failed'],
                'default' => 'Pending',
            ],
            'assigned_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true, // Manager who approved the receipt
            ],
            'completed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true, // Staff who completed the task
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
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
        ]);

        $this->forge->addKey('id', true);
        
        // Add foreign key constraints
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'inventory', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('completed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('staff_tasks', true);
    }

    public function down()
    {
        $this->forge->dropTable('staff_tasks', true);
    }
}