<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBrochureLeads extends Migration
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
            'mobile' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'source' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'default'    => 'footer',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->createTable('brochure_leads');
    }

    public function down()
    {
        $this->forge->dropTable('brochure_leads', true);
    }
}
