<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsActiveIndexToProducts extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE products ADD INDEX idx_products_active_sort (is_active, sort_order)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE products DROP INDEX idx_products_active_sort');
    }
}
