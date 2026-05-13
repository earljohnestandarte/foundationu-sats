<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSlaColumnsToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tickets', [
            'sla_due_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'priority',
            ],
            'first_response_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'sla_due_at',
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'first_response_at',
            ],
            'archived_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'resolved_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tickets', 'sla_due_at');
        $this->forge->dropColumn('tickets', 'first_response_at');
        $this->forge->dropColumn('tickets', 'resolved_at');
        $this->forge->dropColumn('tickets', 'archived_at');
    }
}
