<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRbacTables extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('roles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'role_key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false,
                ],
                'label' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('role_key');
            $this->forge->createTable('roles', true);
        }

        if (! $db->tableExists('permissions')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'code' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => false,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('code');
            $this->forge->createTable('permissions', true);
        }

        if (! $db->tableExists('role_permissions')) {
            $this->forge->addField([
                'role_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => false,
                ],
                'permission_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => false,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey(['role_id', 'permission_id'], true);
            $this->forge->addKey('permission_id');
            $this->forge->createTable('role_permissions', true);
        }

        $seedRoles = [
            'itadministrator' => 'IT Administrator',
            'manager' => 'Manager',
            'staff' => 'Staff',
            'viewer' => 'Viewer',
        ];

        $seedRolePerms = [
            'itadministrator' => [
                'admin.dashboard.view',
                'user.manage',
                'logs.view',
                'access.view',
                'backup.view',
                'config.view',
                'inventory.view',
                'inventory.scan',
                'inventory.approve',
                'transfers.view',
                'transfers.create',
                'transfers.approve',
                'inbound.approve',
                'outbound.approve',
                'ticket.view_all',
                'ticket.create',
                'ticket.comment',
                'ticket.assign',
                'ticket.manage',
                'asset.manage',
                'asset.assign',
                'asset.view_all',
                'jobs.manage',
            ],
            'manager' => [
                'inventory.view',
                'inventory.scan',
                'inventory.approve',
                'transfers.view',
                'transfers.create',
                'transfers.approve',
                'inbound.approve',
                'outbound.approve',
                'ticket.view_all',
                'ticket.create',
                'ticket.comment',
                'ticket.assign',
                'ticket.manage',
                'asset.view_all',
            ],
            'staff' => [
                'inventory.view',
                'inventory.scan',
                'transfers.view',
                'transfers.create',
                'ticket.create',
                'ticket.comment',
                'ticket.view_own',
                'asset.view_own',
            ],
            'viewer' => [
                'ticket.view_own',
                'asset.view_own',
            ],
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($seedRoles as $roleKey => $label) {
            $existing = $db->table('roles')->select('id')->where('role_key', $roleKey)->get()->getRowArray();
            if (! $existing) {
                $db->table('roles')->insert([
                    'role_key' => $roleKey,
                    'label' => $label,
                    'created_at' => $now,
                ]);
            }
        }

        $allPermissionCodes = [];
        foreach ($seedRolePerms as $codes) {
            foreach ($codes as $c) {
                $allPermissionCodes[$c] = true;
            }
        }

        foreach (array_keys($allPermissionCodes) as $code) {
            $p = $db->table('permissions')->select('id')->where('code', $code)->get()->getRowArray();
            if (! $p) {
                $db->table('permissions')->insert([
                    'code' => $code,
                    'description' => null,
                    'created_at' => $now,
                ]);
            }
        }

        foreach ($seedRolePerms as $roleKey => $codes) {
            $role = $db->table('roles')->select('id')->where('role_key', $roleKey)->get()->getRowArray();
            if (! $role) {
                continue;
            }

            $roleId = (int) $role['id'];

            foreach ($codes as $code) {
                $perm = $db->table('permissions')->select('id')->where('code', $code)->get()->getRowArray();
                if (! $perm) {
                    continue;
                }

                $permId = (int) $perm['id'];

                $rp = $db->table('role_permissions')->select('role_id')->where('role_id', $roleId)->where('permission_id', $permId)->get()->getRowArray();
                if (! $rp) {
                    $db->table('role_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permId,
                        'created_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
    }
}
