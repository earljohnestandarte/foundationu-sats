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
        'department_id',
        'concern_type',
        'subject',
        'description',
        'status',
        'priority',
        'sla_due_at',
        'first_response_at',
        'resolved_at',
        'archived_at',
        'is_escalated',
        'escalated_at',
        'escalated_by',
        'escalation_reason',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getTicketsForRequester(int $requesterId)
    {
        return $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('departments', 'departments.id = tickets.department_id')
            ->where('tickets.requester_id', $requesterId)
            ->where('tickets.archived_at IS NULL')
            ->orderBy('tickets.created_at', 'DESC')
            ->findAll();
    }

    public function getTicketsForDepartment(int $departmentId)
    {
        return $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('departments', 'departments.id = tickets.department_id')
            ->where('tickets.department_id', $departmentId)
            ->where('tickets.archived_at IS NULL')
            ->orderBy('tickets.created_at', 'DESC')
            ->findAll();
    }

    public function getArchivedTicketsForRequester(int $requesterId)
    {
        return $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('departments', 'departments.id = tickets.department_id')
            ->where('tickets.requester_id', $requesterId)
            ->where('tickets.archived_at IS NOT NULL')
            ->orderBy('tickets.archived_at', 'DESC')
            ->findAll();
    }

    public function getArchivedTicketsForDepartment(int $departmentId)
    {
        return $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('departments', 'departments.id = tickets.department_id')
            ->where('tickets.department_id', $departmentId)
            ->where('tickets.archived_at IS NOT NULL')
            ->orderBy('tickets.archived_at', 'DESC')
            ->findAll();
    }

    public function getTicketWithRelations(int $ticketId)
    {
        return $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('departments', 'departments.id = tickets.department_id')
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

    public function getTimeline(int $ticketId): array
    {
        $events = [];

        $ticket = $this->find($ticketId);
        if ($ticket) {
            $events[] = [
                'type'      => 'created',
                'icon'      => 'fa-plus-circle',
                'label'     => 'Concern Submitted',
                'timestamp' => $ticket->created_at,
            ];
        }

        $assignees = $this->db->table('ticket_assignees')
            ->select('ticket_assignees.*, users.name AS assignee_name')
            ->join('users', 'users.id = ticket_assignees.user_id')
            ->where('ticket_id', $ticketId)
            ->orderBy('assigned_at', 'ASC')
            ->get()
            ->getResult();

        foreach ($assignees as $a) {
            $events[] = [
                'type'      => 'assigned',
                'icon'      => 'fa-user-check',
                'label'     => 'Assigned to ' . esc($a->assignee_name),
                'timestamp' => $a->assigned_at,
            ];
        }

        $replies = $this->db->table('ticket_replies')
            ->select('ticket_replies.*, users.name AS author_name, users.role AS author_role')
            ->join('users', 'users.id = ticket_replies.user_id')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResult();

        foreach ($replies as $r) {
            $roleLabel = ($r->author_role === 'agent' || $r->author_role === 'admin') ? 'Agent' : 'Student';
            $events[] = [
                'type'      => 'reply',
                'icon'      => 'fa-comment',
                'label'     => esc($roleLabel) . ' replied: ' . mb_substr(strip_tags($r->message), 0, 60) . (mb_strlen(strip_tags($r->message)) > 60 ? '...' : ''),
                'timestamp' => $r->created_at,
            ];
        }

        if ($ticket && $ticket->resolved_at) {
            $events[] = [
                'type'      => 'resolved',
                'icon'      => 'fa-check-circle',
                'label'     => 'Concern Resolved',
                'timestamp' => $ticket->resolved_at,
            ];
        }

        if ($ticket && $ticket->archived_at) {
            $events[] = [
                'type'      => 'archived',
                'icon'      => 'fa-archive',
                'label'     => 'Concern Closed & Archived',
                'timestamp' => $ticket->archived_at,
            ];
        }

        if ($ticket && $ticket->is_escalated) {
            $events[] = [
                'type'      => 'escalated',
                'icon'      => 'fa-flag',
                'label'     => 'Escalated' . ($ticket->escalation_reason ? ': ' . esc(mb_substr($ticket->escalation_reason, 0, 60)) : ''),
                'timestamp' => $ticket->escalated_at ?? $ticket->created_at,
            ];
        }

        usort($events, function ($a, $b) {
            return strtotime($a['timestamp']) <=> strtotime($b['timestamp']);
        });

        return $events;
    }

    public function getEscalatedTickets(?int $departmentId = null): array
    {
        $builder = $this->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('departments', 'departments.id = tickets.department_id')
            ->where('tickets.is_escalated', true)
            ->where('tickets.status !=', 'Closed')
            ->orderBy('tickets.escalated_at', 'DESC');

        if ($departmentId) {
            $builder->where('tickets.department_id', $departmentId);
        }

        return $builder->findAll();
    }
}
