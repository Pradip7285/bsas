<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPartNumberToProducts extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('products', [
            'part_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'sku',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('products', 'part_number');
    }
}
