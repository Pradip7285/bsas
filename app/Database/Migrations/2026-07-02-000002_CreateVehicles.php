<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVehicles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            // Nullable + detach-on-delete (not cascade) — mirrors how products.category_id
            // is handled: AdminController::deleteCategory() detaches rather than blocks.
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('category_id');
        $this->forge->addKey('is_active');
        // Signature is (field, table, tableField, onUpdate, onDelete, ...) — CASCADE on
        // update (category id essentially never changes in place), SET NULL on delete
        // (detach rather than destroy vehicles, mirroring products.category_id below).
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('vehicles');
    }

    public function down()
    {
        $this->forge->dropTable('vehicles', true);
    }
}
