<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConcernTypeToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tickets', [
            'concern_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'department_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tickets', 'concern_type');
    }
}
