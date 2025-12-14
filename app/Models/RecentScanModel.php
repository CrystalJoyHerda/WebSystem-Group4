<?php

namespace App\Models;

use CodeIgniter\Model;

class RecentScanModel extends Model
{
    protected $table = 'recent_scans';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id','item_id','item_sku','item_name','warehouse_id','quantity','movement_type','status','created_at','updated_at'
    ];
    protected $useTimestamps = true;

    /**
     * Upsert a scan for a user and SKU: increment quantity if existing, else insert
     */
    public function upsertScan($userId, $warehouseId, $sku, $name, $movementType, $qty = 1, $itemId = null)
    {
        // Check for existing pending scan with same criteria
        $existing = $this->where('user_id', $userId)
                         ->where('item_sku', $sku)
                         ->where('movement_type', $movementType)
                         ->where('warehouse_id', $warehouseId)
                         ->where('status', 'Pending')
                         ->first();

        if ($existing) {
            $newQty = (int)$existing['quantity'] + (int)$qty;
            $this->update($existing['id'], [
                'quantity' => $newQty, 
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return $this->find($existing['id']);
        }

        $data = [
            'user_id' => $userId,
            'item_id' => $itemId,
            'item_sku' => $sku,
            'item_name' => $name,
            'warehouse_id' => $warehouseId,
            'quantity' => $qty,
            'movement_type' => strtoupper($movementType),
            'status' => 'Pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->insert($data);
        return $this->find($this->getInsertID());
    }

    public function listForUser($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('status', 'Pending')
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    public function remove($id)
    {
        return $this->delete($id);
    }

    public function clearForUser($userId)
    {
        return $this->where('user_id', $userId)->delete();
    }
}
