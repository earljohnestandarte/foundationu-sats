<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketAssigneesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'ticket_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'assigned_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'assigned_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        // $this->forge->addUniqueKey(['ticket_id', 'user_id']);
        // $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'CASCADE', 'CASCADE');
        // $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        // $this->forge->addForeignKey('assigned_by', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('ticket_assignees');
    }

    public function down()
    {
        $this->forge->dropTable('ticket_assignees');
    }
}
