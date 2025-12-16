<?php namespace App\Controllers;

use App\Controllers\BaseController;

class DebugController extends BaseController
{
    public function userWarehouse($userId = null)
    {
        $db = \Config\Database::connect();
        $result = [];

        if ($db->tableExists('users') && $userId !== null) {
            $user = $db->table('users')
                ->select('id, email, warehouse_id')
                ->where('id', (int) $userId)
                ->get()
                ->getRowArray();

            $result['user'] = $user ?: null;
        } else {
            $result['user'] = null;
        }

        if ($db->tableExists('user_warehouses') && $userId !== null) {
            $mappings = $db->table('user_warehouses')
                ->where('user_id', (int) $userId)
                ->get()
                ->getResultArray();

            $result['user_warehouses'] = $mappings;
        } else {
            $result['user_warehouses'] = [];
        }

        return $this->response->setJSON($result);
    }

    public function sessionWarehouse()
    {
        $session = \Config\Services::session();
        $data = ['warehouse_id' => $session->get('warehouse_id')];
        return $this->response->setJSON($data);
    }
}
