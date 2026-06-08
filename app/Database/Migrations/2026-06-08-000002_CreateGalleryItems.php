<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGalleryItems extends Migration
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
            'album_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'caption' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'badge_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'display_style' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'standard',
            ],
            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('album_id');
        $this->forge->addKey('is_active');
        $this->forge->addKey('sort_order');
        $this->forge->addForeignKey('album_id', 'gallery_albums', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('gallery_items');
    }

    public function down()
    {
        $this->forge->dropTable('gallery_items', true);
    }
}
