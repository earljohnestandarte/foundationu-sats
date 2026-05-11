<?php

namespace App\Controllers;

use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Models\TicketAssigneeModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use CodeIgniter\Controller;

class AgentController extends BaseController
{
    protected $helpers = ['url', 'form', 'session'];
    protected TicketModel $ticketModel;
    protected TicketReplyModel $replyModel;
    protected TicketAssigneeModel $ticketAssigneeModel;
    protected UserModel $userModel;
    protected NotificationModel $notificationModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->ticketModel = new TicketModel();
        $this->replyModel = new TicketReplyModel();
        $this->ticketAssigneeModel = new TicketAssigneeModel();
        $this->userModel = new UserModel();
        $this->notificationModel = new NotificationModel();
    }

    public function dashboard()
    {
        $officeId = session()->get('office_id');
        $tickets = $this->ticketModel->getTicketsForOffice($officeId);

        return view('agent/dashboard', [
            'tickets' => $tickets,
        ]);
    }

    public function view($id)
    {
        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);
        $officeId = session()->get('office_id');

        if (! $ticket || $ticket->office_id !== $officeId) {
            return redirect()->to(site_url('agent/dashboard'))->with('error', 'Ticket not found or access denied.');
        }

        $replies = $this->replyModel
            ->select('ticket_replies.*, users.name AS author_name')
            ->join('users', 'users.id = ticket_replies.user_id')
            ->where('ticket_replies.ticket_id', $ticket->id)
            ->orderBy('ticket_replies.created_at', 'ASC')
            ->findAll();

        $replyTree = $this->buildReplyTree($replies);

        $agents = $this->userModel->getAgentsForOffice($officeId);

        $assignees = $this->ticketModel->getAssigneesForTicket($ticket->id);

        return view('agent/ticket_view', [
            'ticket' => $ticket,
            'replies' => $replyTree,
            'agents' => $agents,
            'assignees' => $assignees,
        ]);
    }

    public function assign($id)
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->back();
        }

        // Ensure TicketModel returns an object or this will fail
        $ticket = $this->ticketModel->find((int) $id);
        $officeId = session()->get('office_id');
        $userId = session()->get('user_id');

        if (! $userId) {
            return redirect()->to(site_url('agent/view/' . $id))->with('error', 'User not logged in.');
        }

        if (! $ticket || $ticket->office_id !== $officeId) {
            return redirect()->to(site_url('agent/view/' . $id))->with('error', 'Ticket not found or access denied.');
        }

        // 1. Check if the ticket is already assigned to THIS user
        if ($ticket->resolver_id == $userId) {
            return redirect()->to(site_url('agent/view/' . $ticket->id))->with('error', 'You are already assigned to this ticket.');
        }

        // 2. Log the assignment in your new ticket_assignees table
        // (We use insert ignore or simply insert since we want a history log)
        $this->ticketAssigneeModel->assignUserToTicket($ticket->id, $userId, $userId);

        // 3. Update the actual tickets table so the system knows who the active agent is
        $updateSuccess = $this->ticketModel->update($ticket->id, [
            'resolver_id' => $userId,
            'status'      => 'In Progress',
        ]);

        if (! $updateSuccess) {
            log_message('error', 'Ticket update failed. Check TicketModel allowedFields.');
            return redirect()->to(site_url('agent/view/' . $ticket->id))->with('error', 'Failed to update ticket status.');
        }

        // 4. Notify the student
        $this->notificationModel->insert([
            'user_id'   => $ticket->requester_id,
            'ticket_id' => $ticket->id,
            'message'   => 'Your ticket "' . $ticket->subject . '" has been assigned to an agent.',
            'is_read'   => false,
        ]);

        return redirect()->to(site_url('agent/view/' . $ticket->id))->with('success', 'Ticket assigned to you successfully.');
    }

    public function updateStatus($id)
    {
        $rules = [
            'status' => 'required|in_list[Open,In Progress,Waiting on Student,Resolved,Closed]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Invalid status selected.');
        }

        $ticket = $this->ticketModel->find((int) $id);
        $officeId = session()->get('office_id');

        if (! $ticket || $ticket->office_id !== $officeId) {
            return redirect()->back()->with('error', 'Ticket not available for your office.');
        }

        $this->ticketModel->update($ticket->id, [
            'status' => $this->request->getPost('status'),
        ]);

        // Notify the student about status change
        $this->notificationModel->insert([
            'user_id' => $ticket->requester_id,
            'ticket_id' => $ticket->id,
            'message' => 'Your ticket "' . $ticket->subject . '" status has been updated to: ' . $this->request->getPost('status'),
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Ticket status updated successfully.');
    }

    public function addReply($id)
    {
        $rules = [
            'message' => 'required|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid reply message.');
        }

        $ticket = $this->ticketModel->find((int) $id);
        $officeId = session()->get('office_id');

        if (! $ticket || $ticket->office_id !== $officeId) {
            return redirect()->back()->with('error', 'Ticket not available for your office.');
        }

        $rules = [
            'message' => 'required|min_length[3]',
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
            'user_id' => session()->get('user_id'),
            'message' => $this->request->getPost('message'),
            'reply_to' => $replyTo,
        ]);

        // Notify the student about the new reply
        $this->notificationModel->insert([
            'user_id' => $ticket->requester_id,
            'ticket_id' => $ticket->id,
            'message' => 'New reply added to your ticket "' . $ticket->subject . '"',
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Reply added successfully.');
    }

    public function reassign($id)
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->back();
        }

        $rules = [
            'resolver_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please select a valid agent.');
        }

        $ticket = $this->ticketModel->find((int) $id);
        $officeId = session()->get('office_id');
        $newResolverId = (int) $this->request->getPost('resolver_id');

        if (! $ticket || $ticket->office_id !== $officeId) {
            return redirect()->back()->with('error', 'Ticket not found or belongs to another office.');
        }

        // Check if the new resolver is an agent in the same office
        $newResolver = $this->userModel->find($newResolverId);
        if (! $newResolver || $newResolver->role !== 'agent' || $newResolver->office_id !== $officeId) {
            return redirect()->back()->with('error', 'Selected agent is not valid or belongs to another office.');
        }

        $this->ticketModel->update($ticket->id, [
            'resolver_id' => $newResolverId,
        ]);

        // Add to assignees if not already
        if (! $this->ticketAssigneeModel->isUserAssignedToTicket($ticket->id, $newResolverId)) {
            $this->ticketAssigneeModel->assignUserToTicket($ticket->id, $newResolverId, session()->get('user_id'));
        }

        // Notify the student about reassignment
        $this->notificationModel->insert([
            'user_id' => $ticket->requester_id,
            'ticket_id' => $ticket->id,
            'message' => 'Your ticket "' . $ticket->subject . '" has been reassigned to another agent.',
            'is_read' => false,
        ]);

        // Notify the new agent about assignment
        if ($newResolverId !== session()->get('user_id')) {
            $this->notificationModel->insert([
                'user_id' => $newResolverId,
                'ticket_id' => $ticket->id,
                'message' => 'Ticket "' . $ticket->subject . '" has been reassigned to you.',
                'is_read' => false,
            ]);
        }

        return redirect()->back()->with('success', 'Ticket reassigned successfully.');
    }

    /**
     * @param object[] $replies
     * @return object[]
     */
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
