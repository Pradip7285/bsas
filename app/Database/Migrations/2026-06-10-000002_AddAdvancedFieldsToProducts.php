<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdvancedFieldsToProducts extends Migration
{
    public function up(): void
    {
        $fields = [
            'stock_status' => [
                'type'       => 'ENUM',
                'constraint' => ['in_stock', 'made_to_order', 'out_of_stock'],
                'default'    => 'in_stock',
                'null'       => false,
                'after'      => 'price_label',
            ],
            'lead_time' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'default'    => null,
                'after'      => 'stock_status',
            ],
            'min_order_qty' => [
                'type'    => 'INT',
                'default' => 1,
                'null'    => false,
                'after'   => 'lead_time',
            ],
            'weight' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'default'    => null,
                'after'      => 'min_order_qty',
            ],
            'dimensions' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'weight',
            ],
            'material' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'dimensions',
            ],
            'compatibility' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'material',
            ],
            'datasheet_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'default'    => null,
                'after'      => 'compatibility',
            ],
            'specifications' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'datasheet_url',
            ],
            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'specifications',
            ],
        ];

        $this->forge->addColumn('products', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('products', [
            'stock_status',
            'lead_time',
            'min_order_qty',
            'weight',
            'dimensions',
            'material',
            'compatibility',
            'datasheet_url',
            'specifications',
            'is_featured',
        ]);
    }
}
