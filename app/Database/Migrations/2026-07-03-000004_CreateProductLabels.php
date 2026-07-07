<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductLabels extends Migration
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
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'label_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['product_id', 'label_id']);
        $this->forge->addKey('label_id');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('label_id', 'labels', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_labels');
    }

    public function down()
    {
        $this->forge->dropTable('product_labels', true);
    }
}
