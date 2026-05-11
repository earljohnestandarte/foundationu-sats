<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketReplyModel extends Model
{
    protected $table = 'ticket_replies';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = [
        'ticket_id',
        'user_id',
        'message',
        'reply_to',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Find a parent reply for validation
     */
    public function findParentReply(int $replyId, int $ticketId): ?object
    {
        $reply = $this->find($replyId);
        if ($reply && $reply->ticket_id === $ticketId) {
            return $reply;
        }
        return null;
    }
}
