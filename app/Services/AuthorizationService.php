<?php

namespace App\Services;

class AuthorizationService
{
    private function normalizeRole($role): string
    {
        $role = (string) ($role ?? '');
        $role = strtolower(trim($role));
        $role = preg_replace('/\s+/', '', $role);
        $role = str_replace('_', '', $role);
        return $role;
    }

    public function permissionsForRole($role): array
    {
        $normalized = $this->normalizeRole($role);

		$db = \Config\Database::connect();
		if ($db->tableExists('roles') && $db->tableExists('permissions') && $db->tableExists('role_permissions')) {
			$roleRow = $db->table('roles')->select('id')->where('role_key', $normalized)->get()->getRowArray();
			if ($roleRow && ! empty($roleRow['id'])) {
				$rows = $db->table('role_permissions rp')
					->select('p.code')
					->join('permissions p', 'p.id = rp.permission_id', 'inner')
					->where('rp.role_id', (int) $roleRow['id'])
					->orderBy('p.code', 'ASC')
					->get()
					->getResultArray();

				$out = [];
				foreach ($rows as $r) {
					if (! empty($r['code'])) {
						$out[] = (string) $r['code'];
					}
				}
				$out = array_values(array_unique($out));
				return $out;
			}
		}

        if (in_array($normalized, ['itadministrator', 'itadminstrator', 'itadministsrator'], true)) {
            return [
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
            ];
        }

        if ($normalized === 'manager') {
            return [
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
            ];
        }

        if ($normalized === 'staff') {
            return [
                'inventory.view',
                'inventory.scan',
                'transfers.view',
                'transfers.create',
                'ticket.create',
                'ticket.comment',
                'ticket.view_own',
                'asset.view_own',
            ];
        }

        if ($normalized === 'viewer') {
            return [
                'ticket.view_own',
                'asset.view_own',
            ];
        }

        if ($normalized === 'topmanagement') {
            return [
                'top.dashboard.view',
                'inventory.view',
                'transfers.view',
                'transfers.approve',
                'po.view',
                'po.approve',
                'finance.view',
                'reports.view',
                'logs.view',
                'ticket.view_all',
            ];
        }

        return [];
    }

    public function hasPermission($role, string $permission): bool
    {
        return in_array($permission, $this->permissionsForRole($role), true);
    }

    public function isItAdminRole($role): bool
    {
        $normalized = $this->normalizeRole($role);
        return in_array($normalized, ['itadministrator', 'itadminstrator', 'itadministsrator'], true);
    }
}
