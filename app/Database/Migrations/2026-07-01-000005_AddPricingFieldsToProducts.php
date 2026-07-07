<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPricingFieldsToProducts extends Migration
{
    public function up()
    {
        // price = 0 means "not orderable" — the storefront keeps showing
        // price_label / quote-request flow for that product.
        $this->forge->addColumn('products', [
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
                'after'      => 'price_label',
            ],
            'compare_at_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'price',
            ],
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 3,
                'default'    => 'INR',
                'after'      => 'compare_at_price',
            ],
            'tax_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
                'after'      => 'currency',
            ],
            'stock_quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'stock_status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('products', ['price', 'compare_at_price', 'currency', 'tax_rate', 'stock_quantity']);
    }
}
