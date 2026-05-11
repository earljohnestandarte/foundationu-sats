<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = [
        'requester_id',
        'resolver_id',
        'office_id',
        'subject',
        'description',
        'status',
        'priority',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getTicketsForRequester(int $requesterId)
    {
        return $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, offices.name AS office_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('offices', 'offices.id = tickets.office_id')
            ->where('tickets.requester_id', $requesterId)
            ->orderBy('tickets.created_at', 'DESC')
            ->findAll();
    }

    public function getTicketsForOffice(int $officeId)
    {
        return $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, offices.name AS office_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('offices', 'offices.id = tickets.office_id')
            ->where('tickets.office_id', $officeId)
            ->orderBy('tickets.created_at', 'DESC')
            ->findAll();
    }

    public function getTicketWithRelations(int $ticketId)
    {
        return $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, offices.name AS office_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('offices', 'offices.id = tickets.office_id')
            ->where('tickets.id', $ticketId)
            ->first();
    }

    public function getAssigneesForTicket(int $ticketId)
    {
        return $this->db->table('ticket_assignees')
            ->select('ticket_assignees.*, users.name AS assignee_name')
            ->join('users', 'users.id = ticket_assignees.user_id')
            ->where('ticket_assignees.ticket_id', $ticketId)
            ->orderBy('ticket_assignees.assigned_at', 'ASC')
            ->get()
            ->getResult();
    }
}
