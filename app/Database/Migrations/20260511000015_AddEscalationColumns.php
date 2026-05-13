<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEscalationColumns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tickets', [
            'is_escalated' => [
                'type'    => 'BOOLEAN',
                'default' => false,
                'after'   => 'archived_at',
            ],
            'escalated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'is_escalated',
            ],
            'escalated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'escalated_at',
            ],
            'escalation_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'escalated_by',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tickets', 'is_escalated');
        $this->forge->dropColumn('tickets', 'escalated_at');
        $this->forge->dropColumn('tickets', 'escalated_by');
        $this->forge->dropColumn('tickets', 'escalation_reason');
    }
}
