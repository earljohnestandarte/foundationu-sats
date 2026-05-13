<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Models\TicketFeedbackModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class TicketController extends BaseController
{
    protected $helpers = ['url', 'form', 'session'];
    protected $departmentModel;
    protected $ticketModel;
    protected $replyModel;
    protected $feedbackModel;
    protected $notificationModel;
    protected $userModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->departmentModel = new DepartmentModel();
        $this->ticketModel = new TicketModel();
        $this->replyModel = new TicketReplyModel();
        $this->feedbackModel = new TicketFeedbackModel();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
    }

    public function dashboard()
    {
        $userId = session()->get('user_id');
        $tickets = $this->ticketModel->getTicketsForRequester($userId);

        $activeCount = 0;
        $resolvedCount = 0;

        foreach ($tickets as $ticket) {
            if (in_array($ticket->status, ['Open', 'In Progress', 'Pending'])) {
                $activeCount++;
            } elseif ($ticket->status === 'Resolved') {
                $resolvedCount++;
            }
        }

        return view('tickets/dashboard', [
            'tickets'       => $tickets,
            'activeCount'   => $activeCount,
            'resolvedCount' => $resolvedCount,
        ]);
    }

    public function index()
    {
        $userId = session()->get('user_id');
        $tickets = $this->ticketModel->getTicketsForRequester($userId);

        return view('tickets/index', [
            'tickets' => $tickets,
        ]);
    }

    public function archived()
    {
        $userId = session()->get('user_id');
        $tickets = $this->ticketModel->getArchivedTicketsForRequester($userId);

        return view('tickets/archived', [
            'tickets' => $tickets,
        ]);
    }

    public function create()
    {
        $departments = $this->departmentModel->findAll();
        $departmentOptions = [];

        foreach ($departments as $department) {
            $departmentOptions[$department->id] = $department->name;
        }

        $concernTypes = [
            'Academic Concern'     => 'Academic Concern',
            'Financial Aid'        => 'Financial Aid',
            'Personal/Counseling'  => 'Personal/Counseling',
            'Student Records'      => 'Student Records',
            'Campus Life'          => 'Campus Life',
            'Grievance'            => 'Grievance',
            'Technical Support'    => 'Technical Support',
            'Health/Wellness'      => 'Health/Wellness',
        ];

        return view('tickets/create', [
            'departmentOptions' => $departmentOptions,
            'concernTypes'     => $concernTypes,
            'validation'       => service('validation'),
        ]);
    }

    public function store()
    {
        $rules = [
            'department_id' => 'required|is_natural_no_zero',
            'concern_type'  => 'required',
            'subject'       => 'required|min_length[5]|max_length[255]',
            'description'   => 'required|min_length[10]',
            'priority'      => 'required|in_list[Low,Medium,High,Urgent]',
        ];

        if (! $this->validate($rules)) {
            $departments = $this->departmentModel->findAll();
            $departmentOptions = [];
            foreach ($departments as $department) {
                $departmentOptions[$department->id] = $department->name;
            }

            $concernTypes = [
                'Academic Concern'     => 'Academic Concern',
                'Financial Aid'        => 'Financial Aid',
                'Personal/Counseling'  => 'Personal/Counseling',
                'Student Records'      => 'Student Records',
                'Campus Life'          => 'Campus Life',
                'Grievance'            => 'Grievance',
                'Technical Support'    => 'Technical Support',
                'Health/Wellness'      => 'Health/Wellness',
            ];

            return view('tickets/create', [
                'departmentOptions' => $departmentOptions,
                'concernTypes'     => $concernTypes,
                'validation'       => $this->validator,
            ]);
        }

        $data = [
            'requester_id'  => session()->get('user_id'),
            'department_id' => $this->request->getPost('department_id'),
            'concern_type'  => $this->request->getPost('concern_type'),
            'subject'       => $this->request->getPost('subject'),
            'description'   => $this->request->getPost('description'),
            'priority'      => $this->request->getPost('priority'),
            'status'        => 'Open',
            'sla_due_at'    => date('Y-m-d H:i:s', strtotime('+2 hours')),
        ];

        $this->ticketModel->insert($data);
        $ticketId = $this->ticketModel->getInsertID();

        $agents = $this->userModel->where('department_id', $data['department_id'])
            ->where('role', 'agent')
            ->findAll();

        foreach ($agents as $agent) {
            $this->notificationModel->insert([
                'user_id'   => $agent->id,
                'ticket_id' => $ticketId,
                'message'   => 'New concern submitted: ' . $data['subject'],
                'is_read'   => false,
            ]);
        }

        return redirect()->to(site_url('student/tickets'))->with('success', 'Your concern has been submitted successfully.');
    }

    public function view($id)
    {
        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->to(site_url('student/tickets'))->with('error', 'Concern not found or access denied.');
        }

        $replies = $this->replyModel
            ->select('ticket_replies.*, users.name AS author_name, users.role AS author_role')
            ->join('users', 'users.id = ticket_replies.user_id')
            ->where('ticket_replies.ticket_id', $ticket->id)
            ->orderBy('ticket_replies.created_at', 'ASC')
            ->findAll();

        $replyTree = $this->buildReplyTree($replies);
        $timeline = $this->ticketModel->getTimeline($ticket->id);
        $feedback = $this->feedbackModel->getFeedbackForTicket($ticket->id);

        return view('tickets/view', [
            'ticket'   => $ticket,
            'replies'  => $replyTree,
            'timeline' => $timeline,
            'feedback' => $feedback,
        ]);
    }

    public function confirm($id)
    {
        $ticket = $this->ticketModel->find((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->back()->with('error', 'Concern not found or access denied.');
        }

        if ($ticket->status !== 'Resolved') {
            return redirect()->back()->with('error', 'Only resolved concerns can be confirmed.');
        }

        $this->ticketModel->update($ticket->id, [
            'status'      => 'Closed',
            'archived_at' => date('Y-m-d H:i:s'),
        ]);

        $this->notificationModel->insert([
            'user_id'   => $ticket->resolver_id,
            'ticket_id' => $ticket->id,
            'message'   => 'Your resolution for "' . $ticket->subject . '" has been confirmed and the concern is now closed.',
            'is_read'   => false,
        ]);

        return redirect()->to(site_url('student/tickets/' . $ticket->id . '/rate'))->with('success', 'Concern closed. Please rate your experience.');
    }

    public function reopen($id)
    {
        $ticket = $this->ticketModel->find((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->back()->with('error', 'Concern not found or access denied.');
        }

        if ($ticket->status !== 'Resolved') {
            return redirect()->back()->with('error', 'Only resolved concerns can be reopened.');
        }

        $this->ticketModel->update($ticket->id, [
            'status'      => 'In Progress',
            'resolved_at' => null,
        ]);

        $notifyUsers = [];
        if ($ticket->resolver_id) {
            $notifyUsers[] = $ticket->resolver_id;
        }

        $agents = $this->userModel->where('department_id', $ticket->department_id)
            ->where('role', 'agent')
            ->findAll();

        foreach ($agents as $agent) {
            if (! in_array($agent->id, $notifyUsers)) {
                $notifyUsers[] = $agent->id;
            }
        }

        foreach ($notifyUsers as $userId) {
            $this->notificationModel->insert([
                'user_id'   => $userId,
                'ticket_id' => $ticket->id,
                'message'   => 'Concern "' . $ticket->subject . '" has been reopened by the student.',
                'is_read'   => false,
            ]);
        }

        return redirect()->back()->with('success', 'Concern reopened. An agent will follow up.');
    }

    public function escalate($id)
    {
        $ticket = $this->ticketModel->find((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->back()->with('error', 'Concern not found or access denied.');
        }

        if ($ticket->is_escalated) {
            return redirect()->back()->with('error', 'This concern has already been escalated.');
        }

        $this->ticketModel->update($ticket->id, [
            'is_escalated'     => true,
            'escalated_at'     => date('Y-m-d H:i:s'),
            'escalated_by'     => session()->get('user_id'),
            'escalation_reason' => $this->request->getPost('reason') ?: 'Escalated by student',
        ]);

        $saos = $this->userModel->whereIn('role', ['sao', 'admin'])->findAll();
        foreach ($saos as $sao) {
            $this->notificationModel->insert([
                'user_id'   => $sao->id,
                'ticket_id' => $ticket->id,
                'message'   => 'Concern "' . $ticket->subject . '" has been escalated.',
                'is_read'   => false,
            ]);
        }

        return redirect()->back()->with('success', 'Concern escalated. An administrator will review it.');
    }

    public function rate($id)
    {
        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->to(site_url('student/tickets'))->with('error', 'Concern not found or access denied.');
        }

        $existing = $this->feedbackModel->where('ticket_id', $ticket->id)
            ->where('user_id', session()->get('user_id'))
            ->first();

        return view('tickets/rate', [
            'ticket'   => $ticket,
            'feedback' => $existing,
        ]);
    }

    public function saveFeedback($id)
    {
        $ticket = $this->ticketModel->find((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->to(site_url('student/tickets'))->with('error', 'Concern not found or access denied.');
        }

        $rules = [
            'rating'  => 'required|greater_than[0]|less_than_equal_to[5]',
            'comment' => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please provide a valid rating.');
        }

        $existing = $this->feedbackModel->where('ticket_id', $ticket->id)
            ->where('user_id', session()->get('user_id'))
            ->first();

        $data = [
            'ticket_id' => $ticket->id,
            'user_id'   => session()->get('user_id'),
            'rating'    => (int) $this->request->getPost('rating'),
            'comment'   => $this->request->getPost('comment') ?: null,
        ];

        if ($existing) {
            $this->feedbackModel->update($existing->id, $data);
        } else {
            $this->feedbackModel->insert($data);
        }

        return redirect()->to(site_url('student/tickets'))->with('success', 'Thank you for your feedback!');
    }

    public function addReply($id)
    {
        $rules = [
            'message' => 'required|min_length[3]',
            'reply_to' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid reply message.');
        }

        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->to(site_url('student/tickets'))->with('error', 'Concern not found or access denied.');
        }

        $replyTo = $this->request->getPost('reply_to');
        $replyTo = $replyTo ? (int) $replyTo : null;

        if ($replyTo) {
            $parentReply = $this->replyModel->find($replyTo);
            if (! $parentReply || $parentReply->ticket_id !== $ticket->id) {
                return redirect()->back()->withInput()->with('error', 'Invalid reply target.');
            }
        }

        $this->replyModel->insert([
            'ticket_id' => $ticket->id,
            'user_id'   => session()->get('user_id'),
            'message'   => $this->request->getPost('message'),
            'reply_to'  => $replyTo,
        ]);

        $notifyUsers = [];
        if ($ticket->resolver_id && $ticket->resolver_id !== session()->get('user_id')) {
            $notifyUsers[] = $ticket->resolver_id;
        }

        if (empty($notifyUsers)) {
            $agents = $this->userModel->where('department_id', $ticket->department_id)
                ->where('role', 'agent')
                ->findAll();

            foreach ($agents as $agent) {
                if ($agent->id !== session()->get('user_id')) {
                    $notifyUsers[] = $agent->id;
                }
            }
        }

        foreach (array_unique($notifyUsers) as $userId) {
            $this->notificationModel->insert([
                'user_id'   => $userId,
                'ticket_id' => $ticket->id,
                'message'   => 'New reply to concern "' . $ticket->subject . '"',
                'is_read'   => false,
            ]);
        }

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
