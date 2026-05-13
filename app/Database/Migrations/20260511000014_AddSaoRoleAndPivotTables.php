<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSaoRoleAndPivotTables extends Migration
{
    public function up()
    {
        $prefix = $this->db->getPrefix();
        $this->db->query("ALTER TABLE `{$prefix}users` MODIFY COLUMN `role` ENUM('student','agent','sao','admin') NOT NULL DEFAULT 'student'");

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'department_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('department_user');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('response_templates');
    }

    public function down()
    {
        $prefix = $this->db->getPrefix();
        $this->forge->dropTable('department_user');
        $this->forge->dropTable('response_templates');
        $this->db->query("ALTER TABLE `{$prefix}users` MODIFY COLUMN `role` ENUM('student','agent','admin') NOT NULL DEFAULT 'student'");
    }
}
