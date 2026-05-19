<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AttachmentModel — manages ticket file attachments.
 */
class AttachmentModel extends Model
{
    protected $table         = 'ticket_attachments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false; // uses created_at manually
    protected $allowedFields = [
        'ticket_id', 'reply_id', 'uploader_id',
        'original_name', 'stored_name', 'mime_type', 'file_size', 'created_at',
    ];

    /** Get all attachments for a ticket (ticket-level + all reply-level) */
    public function getForTicket(int $ticketId): array
    {
        return $this->select('ticket_attachments.*, users.name AS uploader_name')
            ->join('users', 'users.id = ticket_attachments.uploader_id')
            ->where('ticket_id', $ticketId)
            ->whereIn('reply_id', $this->db->table('ticket_replies')->select('id')->where('ticket_id', $ticketId), false)
            ->orWhere('(ticket_id = ' . $ticketId . ' AND reply_id IS NULL)')
            ->orderBy('ticket_attachments.created_at', 'ASC')
            ->findAll();
    }

    /** Get ticket-level attachments (submitted with the ticket, no reply) */
    public function getForTicketLevel(int $ticketId): array
    {
        return $this->select('ticket_attachments.*, users.name AS uploader_name')
            ->join('users', 'users.id = ticket_attachments.uploader_id')
            ->where('ticket_id', $ticketId)
            ->where('reply_id IS NULL')
            ->orderBy('ticket_attachments.created_at', 'ASC')
            ->findAll();
    }

    /** Get attachments for a specific reply */
    public function getForReply(int $replyId): array
    {
        return $this->select('ticket_attachments.*, users.name AS uploader_name')
            ->join('users', 'users.id = ticket_attachments.uploader_id')
            ->where('reply_id', $replyId)
            ->orderBy('ticket_attachments.created_at', 'ASC')
            ->findAll();
    }

    /** Check if user has access to this attachment (via ticket membership) */
    public function userCanAccess(int $attachmentId, int $userId, string $role): bool
    {
        $attachment = $this->find($attachmentId);
        if (! $attachment) {
            return false;
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')
            ->select('department_id')
            ->where('id', $userId)
            ->get()
            ->getRow();
        $ticket = $db->table('tickets')
            ->select('id, requester_id, department_id')
            ->where('tickets.id', $attachment['ticket_id'])
            ->get()
            ->getRow();

        if (! $ticket) {
            return false;
        }

        if ($role === 'student') {
            return (int) $ticket->requester_id === $userId;
        }

        if ($role === 'agent') {
            return $user !== null
                && $user->department_id !== null
                && (int) $ticket->department_id === (int) $user->department_id;
        }

        if (in_array($role, ['sao', 'admin'], true)) {
            return $user === null
                || $user->department_id === null
                || (int) $ticket->department_id === (int) $user->department_id;
        }

        return false;
    }

    /** Detect if the file is an image (for inline preview) */
    public static function isImage(string $mimeType): bool
    {
        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /** Format file size for display */
    public static function formatSize(int $bytes): string
    {
        if ($bytes < 1024)       return $bytes . ' B';
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /** Get appropriate icon class based on mime type */
    public static function getIcon(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/')                => 'fa-file-image',
            $mimeType === 'application/pdf'                     => 'fa-file-pdf',
            in_array($mimeType, [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])                                                  => 'fa-file-word',
            str_starts_with($mimeType, 'text/')                 => 'fa-file-alt',
            default                                             => 'fa-file',
        };
    }
}
