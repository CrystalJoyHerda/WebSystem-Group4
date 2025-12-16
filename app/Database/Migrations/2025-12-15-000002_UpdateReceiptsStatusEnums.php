<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateReceiptsStatusEnums extends Migration
{
    public function up()
    {
        // Update inbound_receipts status enum to include new statuses
        $this->db->query("ALTER TABLE inbound_receipts MODIFY COLUMN status ENUM('Pending', 'PACKING', 'DELIVERING', 'DELIVERED', 'Approved', 'Rejected') DEFAULT 'Pending'");
        
        // Update outbound_receipts status enum to include new statuses
        $this->db->query("ALTER TABLE outbound_receipts MODIFY COLUMN status ENUM('Pending', 'Approved', 'SCANNED', 'DELIVERING', 'DELIVERED', 'Rejected') DEFAULT 'Pending'");
    }

    public function down()
    {
        // Revert to original status enums
        $this->db->query("ALTER TABLE inbound_receipts MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'");
        
        $this->db->query("ALTER TABLE outbound_receipts MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'");
    }
}
