<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketAssigneeModel extends Model
{
    protected $table            = 'ticket_assignees';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ticket_id', 'user_id', 'assigned_by', 'assigned_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Check if a user is already assigned to a ticket
     */
    public function isUserAssignedToTicket(int $ticketId, int $userId): bool
    {
        return $this->where('ticket_id', $ticketId)
            ->where('user_id', $userId)
            ->first() !== null;
    }

    /**
     * Assign a user to a ticket
     */
    public function assignUserToTicket(int $ticketId, int $userId, int $assignedBy): bool
    {
        $data = [
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'assigned_by' => $assignedBy,
            'assigned_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data) !== false;
    }
}
