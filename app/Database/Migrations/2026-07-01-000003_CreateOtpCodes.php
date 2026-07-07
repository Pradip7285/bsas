<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOtpCodes extends Migration
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
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'code_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'purpose' => [
                'type'       => 'ENUM',
                'constraint' => ['login', 'verify_phone'],
            ],
            'attempts' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'consumed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['phone', 'purpose']);
        $this->forge->createTable('otp_codes');
    }

    public function down()
    {
        $this->forge->dropTable('otp_codes', true);
    }
}
