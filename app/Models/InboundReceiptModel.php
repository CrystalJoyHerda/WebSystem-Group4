<?php

namespace App\Models;

use CodeIgniter\Model;

class InboundReceiptModel extends Model
{
    protected $table = 'inbound_receipts';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'reference_no', 'supplier_name', 'warehouse_id', 'status', 'total_items', 'created_at', 'updated_at', 'approved_by', 'approved_at'
    ];
    protected $useTimestamps = true;

    private function applyWarehouseScope($builder, ?int $warehouseId, ?array $allowedWarehouseIds)
    {
        if ($warehouseId !== null && $warehouseId > 0) {
            $builder->where('warehouse_id', $warehouseId);
            return;
        }

        if ($allowedWarehouseIds !== null && $allowedWarehouseIds !== []) {
            $builder->whereIn('warehouse_id', $allowedWarehouseIds);
        }
    }

    /**
     * Get pending inbound receipts (not approved yet)
     */
    public function getPendingReceipts(?int $warehouseId = null, ?array $allowedWarehouseIds = null)
    {
        $builder = $this->builder();
        $builder->where('status', 'Pending')->orderBy('created_at', 'DESC');
        $this->applyWarehouseScope($builder, $warehouseId, $allowedWarehouseIds);
        return $builder->get()->getResultArray();
    }

    /**
     * Get receipt with items
     */
    public function getReceiptWithItems($receiptId)
    {
        $receipt = $this->find($receiptId);
        if (!$receipt) return null;

        $db = $this->db;
        $items = $db->table('inbound_receipt_items iri')
                   ->select('iri.*, i.name as item_name, i.sku as item_sku, w.name as warehouse_name')
                   ->join('inventory i', 'i.id = iri.item_id', 'left')
                   ->join('warehouses w', 'w.id = iri.warehouse_id', 'left')
                   ->where('iri.receipt_id', $receiptId)
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
            'supplier_name' => $receiptData['supplier_name'],
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
            $db->table('inbound_receipt_items')->insert([
                'receipt_id' => $receiptId,
                'item_id' => $item['item_id'],
                'warehouse_id' => $item['warehouse_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'] ?? 0
            ]);
        }

        $db->transComplete();
        return $db->transStatus() ? $receiptId : false;
    }
}