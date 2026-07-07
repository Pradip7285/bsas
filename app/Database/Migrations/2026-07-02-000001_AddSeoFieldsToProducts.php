<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeoFieldsToProducts extends Migration
{
    public function up(): void
    {
        $fields = [
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
                'default'    => null,
                'after'      => 'is_featured',
            ],
            'meta_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => true,
                'default'    => null,
                'after'      => 'meta_title',
            ],
            'meta_keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'meta_description',
            ],
            'focus_keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
                'default'    => null,
                'after'      => 'meta_keyword',
            ],
            'image_alt_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
                'default'    => null,
                'after'      => 'focus_keyword',
            ],
            'canonical_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'default'    => null,
                'after'      => 'image_alt_text',
            ],
            'og_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'canonical_url',
            ],
            'structured_data_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'default'    => 'Product',
                'after'      => 'og_image',
            ],
            'robots_meta' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'default'    => 'index, follow',
                'after'      => 'structured_data_type',
            ],
        ];

        $this->forge->addColumn('products', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('products', [
            'meta_title',
            'meta_description',
            'meta_keyword',
            'focus_keyword',
            'image_alt_text',
            'canonical_url',
            'og_image',
            'structured_data_type',
            'robots_meta',
        ]);
    }
}
