<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketFeedbackModel extends Model
{
    protected $table = 'ticket_feedback';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = [
        'ticket_id',
        'user_id',
        'rating',
        'comment',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    public function getFeedbackForTicket(int $ticketId): ?object
    {
        return $this->where('ticket_id', $ticketId)->first();
    }

    public function getAverageRatingForDepartment(int $departmentId): ?float
    {
        $result = $this->select('AVG(rating) as avg_rating')
            ->join('tickets', 'tickets.id = ticket_feedback.ticket_id')
            ->where('tickets.department_id', $departmentId)
            ->first();

        return $result ? round((float) $result->avg_rating, 1) : null;
    }
}
