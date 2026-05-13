<?php

namespace App\Controllers;

use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Models\TicketAssigneeModel;
use App\Models\TicketFeedbackModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use CodeIgniter\Controller;

class AgentController extends BaseController
{
    protected $helpers = ['url', 'form', 'session'];
    protected TicketModel $ticketModel;
    protected TicketReplyModel $replyModel;
    protected TicketAssigneeModel $ticketAssigneeModel;
    protected TicketFeedbackModel $feedbackModel;
    protected UserModel $userModel;
    protected NotificationModel $notificationModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->ticketModel = new TicketModel();
        $this->replyModel = new TicketReplyModel();
        $this->ticketAssigneeModel = new TicketAssigneeModel();
        $this->feedbackModel = new TicketFeedbackModel();
        $this->userModel = new UserModel();
        $this->notificationModel = new NotificationModel();
    }

    private function isElevated(): bool
    {
        return in_array(session()->get('user_role'), ['sao', 'admin']);
    }

    private function canAccess(int $ticketDepartmentId): bool
    {
        if ($this->isElevated()) return true;
        $userDept = session()->get('department_id');
        return $userDept !== null && $ticketDepartmentId === (int) $userDept;
    }

    public function dashboard()
    {
        $departmentId = session()->get('department_id');

        if ($this->isElevated() && ! $departmentId) {
            $tickets = $this->ticketModel->where('archived_at IS NULL')->orderBy('created_at', 'DESC')->findAll();
            $models = $this->ticketModel;
            $tickets = array_map(function ($t) use ($models) {
                return $models->getTicketWithRelations($t->id);
            }, $tickets);
        } else {
            $tickets = $this->ticketModel->getTicketsForDepartment($departmentId);
        }

        return view('agent/dashboard', [
            'tickets' => $tickets,
        ]);
    }

    public function archived()
    {
        $departmentId = session()->get('department_id');

        if ($this->isElevated() && ! $departmentId) {
            $tickets = $this->ticketModel->where('archived_at IS NOT NULL')->orderBy('archived_at', 'DESC')->findAll();
            $models = $this->ticketModel;
            $tickets = array_map(function ($t) use ($models) {
                return $models->getTicketWithRelations($t->id);
            }, $tickets);
        } else {
            $tickets = $this->ticketModel->getArchivedTicketsForDepartment($departmentId);
        }

        return view('agent/archived', [
            'tickets' => $tickets,
        ]);
    }

    public function view($id)
    {
        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);

        if (! $ticket || ! $this->canAccess($ticket->department_id)) {
            return redirect()->to(site_url('agent/dashboard'))->with('error', 'Concern not found or access denied.');
        }

        $replies = $this->replyModel
            ->select('ticket_replies.*, users.name AS author_name, users.role AS author_role')
            ->join('users', 'users.id = ticket_replies.user_id')
            ->where('ticket_replies.ticket_id', $ticket->id)
            ->orderBy('ticket_replies.created_at', 'ASC')
            ->findAll();

        $replyTree = $this->buildReplyTree($replies);

        if ($this->isElevated()) {
            $agents = $this->userModel->where('role', 'agent')->findAll();
        } else {
            $agents = $this->userModel->getAgentsForDepartment(session()->get('department_id'));
        }

        $assignees = $this->ticketModel->getAssigneesForTicket($ticket->id);
        $timeline = $this->ticketModel->getTimeline($ticket->id);
        $feedback = $this->feedbackModel->getFeedbackForTicket($ticket->id);

        return view('agent/ticket_view', [
            'ticket'    => $ticket,
            'replies'   => $replyTree,
            'agents'    => $agents,
            'assignees' => $assignees,
            'timeline'  => $timeline,
            'feedback'  => $feedback,
        ]);
    }

    public function assign($id)
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->back();
        }

        $ticket = $this->ticketModel->find((int) $id);
        $departmentId = session()->get('department_id');
        $userId = session()->get('user_id');

        if (! $userId) {
            return redirect()->to(site_url('agent/view/' . $id))->with('error', 'User not logged in.');
        }

        if (! $ticket || ! $this->canAccess($ticket->department_id)) {
            return redirect()->to(site_url('agent/view/' . $id))->with('error', 'Concern not found or access denied.');
        }

        $resolverId = (int) $this->request->getPost('resolver_id');

        if ($ticket->resolver_id == $resolverId) {
            return redirect()->to(site_url('agent/view/' . $ticket->id))->with('error', 'This agent is already assigned to this concern.');
        }

        if ($resolverId !== $userId && ! $this->isElevated()) {
            $newResolver = $this->userModel->find($resolverId);
            if (! $newResolver || $newResolver->role !== 'agent' || $newResolver->department_id !== $departmentId) {
                return redirect()->back()->with('error', 'Selected agent is not valid or belongs to another department.');
            }
        }

        $this->ticketAssigneeModel->assignUserToTicket($ticket->id, $resolverId, $userId);

        $this->ticketModel->update($ticket->id, [
            'resolver_id' => $resolverId,
            'status'      => 'In Progress',
        ]);

        $this->notificationModel->insert([
            'user_id'   => $ticket->requester_id,
            'ticket_id' => $ticket->id,
            'message'   => 'Your concern "' . $ticket->subject . '" has been assigned to an agent.',
            'is_read'   => false,
        ]);

        if ($resolverId !== $userId) {
            $this->notificationModel->insert([
                'user_id'   => $resolverId,
                'ticket_id' => $ticket->id,
                'message'   => 'Concern "' . $ticket->subject . '" has been assigned to you.',
                'is_read'   => false,
            ]);
        }

        return redirect()->to(site_url('agent/view/' . $ticket->id))->with('success', 'Concern assigned successfully.');
    }

    public function escalate($id)
    {
        $ticket = $this->ticketModel->find((int) $id);

        if (! $ticket || ! $this->canAccess($ticket->department_id)) {
            return redirect()->back()->with('error', 'Concern not available for your department.');
        }

        if ($ticket->is_escalated) {
            return redirect()->back()->with('error', 'This concern has already been escalated.');
        }

        $this->ticketModel->update($ticket->id, [
            'is_escalated'      => true,
            'escalated_at'      => date('Y-m-d H:i:s'),
            'escalated_by'      => session()->get('user_id'),
            'escalation_reason' => $this->request->getPost('reason') ?: 'Escalated by agent',
        ]);

        $saos = $this->userModel->whereIn('role', ['sao', 'admin'])->findAll();
        foreach ($saos as $sao) {
            $this->notificationModel->insert([
                'user_id'   => $sao->id,
                'ticket_id' => $ticket->id,
                'message'   => 'Concern "' . $ticket->subject . '" has been escalated by an agent.',
                'is_read'   => false,
            ]);
        }

        return redirect()->back()->with('success', 'Concern escalated to administration.');
    }

    public function updateStatus($id)
    {
        $rules = [
            'status' => 'required|in_list[Open,In Progress,Pending,Resolved,Closed]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Invalid status selected.');
        }

        $ticket = $this->ticketModel->find((int) $id);

        if (! $ticket || ! $this->canAccess($ticket->department_id)) {
            return redirect()->back()->with('error', 'Concern not available for your department.');
        }

        $newStatus = $this->request->getPost('status');
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'Resolved' && ! $ticket->resolved_at) {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }

        $this->ticketModel->update($ticket->id, $updateData);

        $this->notificationModel->insert([
            'user_id'   => $ticket->requester_id,
            'ticket_id' => $ticket->id,
            'message'   => 'Your concern "' . $ticket->subject . '" status has been updated to: ' . $newStatus,
            'is_read'   => false,
        ]);

        return redirect()->back()->with('success', 'Concern status updated successfully.');
    }

    public function addReply($id)
    {
        $ticket = $this->ticketModel->find((int) $id);

        if (! $ticket || ! $this->canAccess($ticket->department_id)) {
            return redirect()->back()->with('error', 'Concern not available for your department.');
        }

        $rules = [
            'message'  => 'required|min_length[3]',
            'reply_to' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid reply message.');
        }

        $replyTo = $this->request->getPost('reply_to');
        $replyTo = $replyTo ? (int) $replyTo : null;

        if ($replyTo) {
            $parentReply = $this->replyModel->findParentReply($replyTo, $ticket->id);
            if (! $parentReply) {
                return redirect()->back()->withInput()->with('error', 'Invalid reply target.');
            }
        }

        $this->replyModel->insert([
            'ticket_id' => $ticket->id,
            'user_id'   => session()->get('user_id'),
            'message'   => $this->request->getPost('message'),
            'reply_to'  => $replyTo,
        ]);

        if (! $ticket->first_response_at) {
            $this->ticketModel->update($ticket->id, [
                'first_response_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->notificationModel->insert([
            'user_id'   => $ticket->requester_id,
            'ticket_id' => $ticket->id,
            'message'   => 'New reply added to your concern "' . $ticket->subject . '"',
            'is_read'   => false,
        ]);

        return redirect()->back()->with('success', 'Reply added successfully.');
    }

    private function buildReplyTree(array $replies): array
    {
        $replyMap = [];
        foreach ($replies as $reply) {
            $reply->children = [];
            $replyMap[$reply->id] = $reply;
        }

        $tree = [];
        foreach ($replyMap as $reply) {
            if ($reply->reply_to && isset($replyMap[$reply->reply_to])) {
                $replyMap[$reply->reply_to]->children[] = $reply;
            } else {
                $tree[] = $reply;
            }
        }

        return $tree;
    }
}
