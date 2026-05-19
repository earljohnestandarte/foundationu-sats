<?php

namespace App\Controllers;

use App\Models\TicketModel;

/**
 * SearchController — live AJAX search endpoint for the header search capsule.
 *
 * Returns JSON results filtered by the current user's role so agents
 * see their department's tickets and students see only their own.
 *
 * Route: GET /search?q=<query>
 */
class SearchController extends BaseController
{
    protected $helpers = ['url'];

    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $query = trim((string) $this->request->getGet('q'));

        if (strlen($query) < 2) {
            return $this->response->setJSON(['results' => [], 'query' => $query]);
        }

        $role         = session()->get('user_role');
        $userId       = (int) session()->get('user_id');
        $departmentId = (int) session()->get('department_id');
        $ticketModel  = new TicketModel();

        $results = match ($role) {
            'student' => $this->searchAsStudent($ticketModel, $userId, $query),
            'agent'   => $this->searchAsAgent($ticketModel, $departmentId, $query),
            'sao', 'admin' => $this->searchAsElevated($ticketModel, $query),
            default   => [],
        };

        // Map to a lean JSON shape
        $mapped = array_map(function ($ticket) use ($role) {
            $url = $role === 'student'
                ? site_url('student/tickets/' . $ticket->id)
                : site_url('agent/view/' . $ticket->id);

            return [
                'id'          => $ticket->id,
                'ref'         => '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT),
                'subject'     => $ticket->subject,
                'status'      => $ticket->status,
                'priority'    => $ticket->priority,
                'department'  => $ticket->department_name ?? '—',
                'requester'   => $ticket->requester_name  ?? '—',
                'updated_at'  => $ticket->updated_at ?? $ticket->created_at,
                'url'         => $url,
            ];
        }, $results);

        return $this->response->setJSON([
            'results' => array_slice($mapped, 0, 8), // Cap at 8 preview results
            'query'   => $query,
            'total'   => count($results),
            'seeAllUrl' => $role === 'student'
                ? site_url('student/tickets?q=' . urlencode($query))
                : site_url('agent/dashboard?q=' . urlencode($query)),
        ]);
    }

    /* ── Role-specific search queries ───────────────────────── */

    private function searchAsStudent(TicketModel $model, int $userId, string $query): array
    {
        return $model->searchForRequester($userId, $query);
    }

    private function searchAsAgent(TicketModel $model, int $departmentId, string $query): array
    {
        if (!$departmentId) {
            return $this->searchAsElevated($model, $query);
        }
        return $model->searchForDepartment($departmentId, $query);
    }

    private function searchAsElevated(TicketModel $model, string $query): array
    {
        // Admins / SAO search all active tickets
        return $model
            ->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
            ->join('users AS requester', 'requester.id = tickets.requester_id')
            ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
            ->join('departments', 'departments.id = tickets.department_id')
            ->where('tickets.archived_at IS NULL')
            ->groupStart()
                ->like('tickets.subject', $query)
                ->orLike('tickets.description', $query)
                ->orLike('tickets.status', $query)
                ->orLike('requester.name', $query)
                ->orLike('departments.name', $query)
            ->groupEnd()
            ->orderBy('tickets.updated_at', 'DESC')
            ->findAll(20); // Limit to 20 for elevated search
    }
}
