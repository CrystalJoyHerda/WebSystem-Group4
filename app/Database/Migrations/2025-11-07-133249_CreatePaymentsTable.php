<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
       $this->forge->addField([
           'payment_id' => [
               'type'           => 'INT',
               'constraint'     => 11,
               'unsigned'       => true,
               'auto_increment' => true,
           ],
           'invoice_id' => [
               'type'       => 'INT',
               'constraint' => 11,
               'unsigned'   => true,
           ],
           'amount' => [
               'type'       => 'DECIMAL',
               'constraint' => '10,2',
           ],
           'payment_date' => [
               'type'       => 'DATE',
               'null'       => false,
           ],
           'payment_status' => [
               'type'       => 'ENUM',
               'constraint' => ['fully_paid', 'partially_paid', 'unpaid'],
           ],
       ]);

       $this->forge->addKey('payment_id', true); // Primary key
         $this->forge->createTable('payments');
    }

    public function down()
    {
        $this->forge->dropTable('payments', true);
    }
}
