<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTicketAttachmentsTable extends Migration
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
            'ticket_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'reply_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Null = attached at ticket creation',
            ],
            'uploader_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'stored_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'UUID-based filename on disk',
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'file_size' => [
                'type'     => 'INT',
                'unsigned' => true,
                'comment'  => 'Size in bytes',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('ticket_id',   'tickets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploader_id', 'users',   'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('ticket_attachments');
    }

    public function down()
    {
        $this->forge->dropTable('ticket_attachments', true);
    }
}
