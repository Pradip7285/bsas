<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryIdToProducts extends Migration
{
    public function up()
    {
        // Add category_id FK — nullable so existing rows aren't broken.
        // The category VARCHAR column stays as a denormalized cache:
        //   written on every save, used for fast LIKE search and display.
        $this->forge->addColumn('products', [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'category',
            ],
        ]);

        $this->db->query('ALTER TABLE products ADD INDEX idx_products_category_id (category_id)');
    }

    public function down()
    {
        $this->forge->dropColumn('products', 'category_id');
    }
}
