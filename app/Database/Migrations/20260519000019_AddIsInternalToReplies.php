<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsInternalToReplies extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ticket_replies', [
            'is_internal' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'message',
                'comment'    => '1 = private staff note, not visible to student',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ticket_replies', 'is_internal');
    }
}
