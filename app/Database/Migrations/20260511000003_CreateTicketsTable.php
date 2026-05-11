<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketsTable extends Migration
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
            'requester_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'resolver_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'office_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'subject' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type' => "ENUM('Open','In Progress','Waiting on Student','Resolved','Closed')",
                'default' => 'Open',
            ],
            'priority' => [
                'type' => "ENUM('Low','Medium','High','Urgent')",
                'default' => 'Medium',
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
        $this->forge->addForeignKey('requester_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('resolver_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('office_id', 'offices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tickets');
    }

    public function down()
    {
        $this->forge->dropTable('tickets');
    }
}
