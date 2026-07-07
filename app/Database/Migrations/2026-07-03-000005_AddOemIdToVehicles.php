<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOemIdToVehicles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('vehicles', [
            'oem_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'category_id',
            ],
        ]);

        $this->forge->addKey('oem_id');
        // Signature is (field, table, tableField, onUpdate, onDelete, ...) — CASCADE on
        // update, SET NULL on delete (detach rather than destroy vehicles when an OEM
        // is removed, mirroring the category_id FK on this same table).
        $this->forge->addForeignKey('oem_id', 'oems', 'id', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('vehicles');
    }

    public function down()
    {
        $this->forge->dropForeignKey('vehicles', 'vehicles_oem_id_foreign');
        $this->forge->dropColumn('vehicles', 'oem_id');
    }
}
