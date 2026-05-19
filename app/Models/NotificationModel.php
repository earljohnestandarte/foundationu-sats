<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = [
        'user_id',
        'ticket_id',
        'message',
        'is_read',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    public function getUnreadNotificationsForUser(int $userId)
    {
        return $this->where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getUnreadCountForUser(int $userId)
    {
        return $this->where('user_id', $userId)
            ->where('is_read', false)
            ->countAllResults();
    }

    public function getNotificationsForUser(int $userId)
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
