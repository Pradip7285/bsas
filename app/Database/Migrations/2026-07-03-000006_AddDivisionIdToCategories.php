<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDivisionIdToCategories extends Migration
{
    public function up()
    {
        $this->forge->addColumn('categories', [
            'division_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'slug',
            ],
        ]);

        $this->forge->addKey('division_id');
        // Signature is (field, table, tableField, onUpdate, onDelete, ...) — CASCADE on
        // update, SET NULL on delete (detach rather than destroy categories when a
        // Division is removed, mirroring products.category_id's detach behavior).
        $this->forge->addForeignKey('division_id', 'divisions', 'id', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('categories');
    }

    public function down()
    {
        $this->forge->dropForeignKey('categories', 'categories_division_id_foreign');
        $this->forge->dropColumn('categories', 'division_id');
    }
}
