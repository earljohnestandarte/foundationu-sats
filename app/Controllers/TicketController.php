<?php

namespace App\Controllers;

use App\Models\OfficeModel;
use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class TicketController extends BaseController
{
    protected $helpers = ['url', 'form', 'session'];
    protected $officeModel;
    protected $ticketModel;
    protected $replyModel;
    protected $notificationModel;
    protected $userModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->officeModel = new OfficeModel();
        $this->ticketModel = new TicketModel();
        $this->replyModel = new TicketReplyModel();
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
            if (in_array($ticket->status, ['Open', 'In Progress', 'Waiting on Student'])) {
                $activeCount++;
            } elseif ($ticket->status === 'Resolved') {
                $resolvedCount++;
            }
        }

        return view('tickets/dashboard', [
            'tickets' => $tickets,
            'activeCount' => $activeCount,
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

    public function create()
    {
        $offices = $this->officeModel->findAll();
        $officeOptions = [];

        foreach ($offices as $office) {
            $officeOptions[$office->id] = $office->name;
        }

        return view('tickets/create', [
            'officeOptions' => $officeOptions,
            'validation' => service('validation'),
        ]);
    }

    public function store()
    {
        $rules = [
            'office_id' => 'required|is_natural_no_zero',
            'subject' => 'required|min_length[5]|max_length[255]',
            'description' => 'required|min_length[10]',
            'priority' => 'required|in_list[Low,Medium,High,Urgent]',
        ];

        if (! $this->validate($rules)) {
            $offices = $this->officeModel->findAll();
            $officeOptions = [];
            foreach ($offices as $office) {
                $officeOptions[$office->id] = $office->name;
            }

            return view('tickets/create', [
                'officeOptions' => $officeOptions,
                'validation' => $this->validator,
            ]);
        }

        $data = [
            'requester_id' => session()->get('user_id'),
            'office_id' => $this->request->getPost('office_id'),
            'subject' => $this->request->getPost('subject'),
            'description' => $this->request->getPost('description'),
            'priority' => $this->request->getPost('priority'),
            'status' => 'Open',
        ];

        $this->ticketModel->insert($data);
        $ticketId = $this->ticketModel->getInsertID();

        // Create notifications for all agents in the office
        $agents = $this->userModel->where('office_id', $data['office_id'])
            ->where('role', 'agent')
            ->findAll();

        foreach ($agents as $agent) {
            $this->notificationModel->insert([
                'user_id' => $agent->id,
                'ticket_id' => $ticketId,
                'message' => 'New ticket created: ' . $data['subject'],
                'is_read' => false,
            ]);
        }

        return redirect()->to(site_url('student/tickets'))->with('success', 'Your ticket has been created successfully.');
    }

    public function view($id)
    {
        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->to(site_url('student/tickets'))->with('error', 'Ticket not found or access denied.');
        }

        $replies = $this->replyModel
            ->select('ticket_replies.*, users.name AS author_name')
            ->join('users', 'users.id = ticket_replies.user_id')
            ->where('ticket_replies.ticket_id', $ticket->id)
            ->orderBy('ticket_replies.created_at', 'ASC')
            ->findAll();

        $replyTree = $this->buildReplyTree($replies);

        return view('tickets/view', [
            'ticket' => $ticket,
            'replies' => $replyTree,
        ]);
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
            return redirect()->to(site_url('student/tickets'))->with('error', 'Ticket not found or access denied.');
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
            'user_id' => session()->get('user_id'),
            'message' => $this->request->getPost('message'),
            'reply_to' => $replyTo,
        ]);

        $notifyUsers = [];
        if ($ticket->resolver_id && $ticket->resolver_id !== session()->get('user_id')) {
            $notifyUsers[] = $ticket->resolver_id;
        }

        if (empty($notifyUsers)) {
            $agents = $this->userModel->where('office_id', $ticket->office_id)
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
                'user_id' => $userId,
                'ticket_id' => $ticket->id,
                'message' => 'New reply to ticket "' . $ticket->subject . '"',
                'is_read' => false,
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
