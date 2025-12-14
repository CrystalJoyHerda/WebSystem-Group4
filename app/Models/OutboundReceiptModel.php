<?php

namespace App\Models;

use CodeIgniter\Model;

class OutboundReceiptModel extends Model
{
    protected $table = 'outbound_receipts';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'reference_no', 'customer_name', 'warehouse_id', 'status', 'total_items', 'created_at', 'updated_at', 'approved_by', 'approved_at'
    ];
    protected $useTimestamps = true;

    /**
     * Get pending outbound receipts (not approved yet)
     */
    public function getPendingReceipts()
    {
        return $this->where('status', 'Pending')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get receipt with items
     */
    public function getReceiptWithItems($receiptId)
    {
        $receipt = $this->find($receiptId);
        if (!$receipt) return null;

        $db = $this->db;
        $items = $db->table('outbound_receipt_items ori')
                   ->select('ori.*, i.name as item_name, i.sku as item_sku, w.name as warehouse_name')
                   ->join('inventory i', 'i.id = ori.item_id', 'left')
                   ->join('warehouses w', 'w.id = ori.warehouse_id', 'left')
                   ->where('ori.receipt_id', $receiptId)
                   ->get()
                   ->getResultArray();

        $receipt['items'] = $items;
        return $receipt;
    }

    /**
     * Approve receipt and update status
     */
    public function approveReceipt($receiptId, $managerId)
    {
        return $this->update($receiptId, [
            'status' => 'Approved',
            'approved_by' => $managerId,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Create a new receipt with items
     */
    public function createReceiptWithItems($receiptData, $itemsData)
    {
        $db = $this->db;
        $db->transStart();

        // Insert receipt
        $receiptId = $this->insert([
            'reference_no' => $receiptData['reference_no'],
            'customer_name' => $receiptData['customer_name'],
            'warehouse_id' => $receiptData['warehouse_id'],
            'status' => 'Pending',
            'total_items' => count($itemsData)
        ]);

        if (!$receiptId) {
            $db->transRollback();
            return false;
        }

        // Insert receipt items
        foreach ($itemsData as $item) {
            $db->table('outbound_receipt_items')->insert([
                'receipt_id' => $receiptId,
                'item_id' => $item['item_id'],
                'warehouse_id' => $item['warehouse_id'],
                'quantity' => $item['quantity']
            ]);
        }

        $db->transComplete();
        return $db->transStatus() ? $receiptId : false;
    }
}