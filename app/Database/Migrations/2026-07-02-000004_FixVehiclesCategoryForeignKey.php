<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The original CreateVehicles migration passed addForeignKey()'s onUpdate/onDelete
 * arguments in the wrong order (CI4 signature is field, table, tableField, onUpdate,
 * onDelete), which produced CASCADE on delete instead of SET NULL — meaning deleting
 * a category would destroy every vehicle under it instead of detaching them. This
 * migration drops and re-adds the constraint with the correct rules on databases
 * where the original migration already ran.
 */
class FixVehiclesCategoryForeignKey extends Migration
{
    public function up()
    {
        $this->forge->dropForeignKey('vehicles', 'vehicles_category_id_foreign');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('vehicles');
    }

    public function down()
    {
        $this->forge->dropForeignKey('vehicles', 'vehicles_category_id_foreign');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->processIndexes('vehicles');
    }
}
