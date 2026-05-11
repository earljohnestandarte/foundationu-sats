<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReplyToToTicketRepliesTable extends Migration
{
    public function up()
    {
        $fields = [
            'reply_to' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
        ];

        $this->forge->addColumn('ticket_replies', $fields);
        $this->forge->addForeignKey('reply_to', 'ticket_replies', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('ticket_replies', 'reply_to');
        $this->forge->dropColumn('ticket_replies', 'reply_to');
    }
}
