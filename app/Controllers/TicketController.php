<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Models\TicketFeedbackModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Models\AttachmentModel;
use App\Libraries\Mailer;
use App\Libraries\RealtimePublisher;
use CodeIgniter\Controller;


class TicketController extends BaseController
{
    protected $helpers = ['url', 'form', 'session', 'reply'];
    protected $departmentModel;
    protected $ticketModel;
    protected $replyModel;
    protected $feedbackModel;
    protected $notificationModel;
    protected $userModel;
    protected $attachmentModel;
    protected $mailer;


    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->departmentModel  = new DepartmentModel();
        $this->ticketModel      = new TicketModel();
        $this->replyModel       = new TicketReplyModel();
        $this->feedbackModel    = new TicketFeedbackModel();
        $this->notificationModel = new NotificationModel();
        $this->userModel        = new UserModel();
        $this->attachmentModel  = new AttachmentModel();
        $this->mailer           = new Mailer();
    }

    /** Save uploaded files for a ticket/reply and return stored attachment IDs. */
    private function handleUploads(int $ticketId, ?int $replyId, int $uploaderId): void
    {
        $files = $this->request->getFiles();
        if (empty($files['attachments'])) return;

        $uploadPath = WRITEPATH . 'uploads/tickets/';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

        $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp','application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'];

        foreach ($files['attachments'] as $file) {
            if (!$file->isValid() || $file->hasMoved()) continue;
            if ($file->getSize() > 5 * 1024 * 1024) continue; // 5 MB max
            if (!in_array($file->getMimeType(), $allowedMimes)) continue;

            $storedName = $file->getRandomName();
            $file->move($uploadPath, $storedName);

            $this->attachmentModel->insert([
                'ticket_id'     => $ticketId,
                'reply_id'      => $replyId,
                'uploader_id'   => $uploaderId,
                'original_name' => $file->getClientName(),
                'stored_name'   => $storedName,
                'mime_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function getRealtimeBrowserUrl(): string
    {
        $config = config('Realtime');
        if ($config->browserUrl !== '') {
            return $config->browserUrl;
        }

        $host = parse_url(base_url(), PHP_URL_HOST) ?: ($this->request->getServer('SERVER_NAME') ?: '127.0.0.1');
        $scheme = $this->request->isSecure() ? 'wss' : 'ws';

        return $scheme . '://' . $host . ':' . $config->websocketPort;
    }

    private function getRealtimeConfigForTicket(int $ticketId): array
    {
        $publisher = new RealtimePublisher();

        return $publisher->makeSubscriptionData(
            $ticketId,
            (int) session()->get('user_id'),
            (string) session()->get('user_role'),
            $this->getRealtimeBrowserUrl()
        );
    }

    private function getThreadViewData(object $ticket): array
    {
        $replies = $this->replyModel
            ->select('ticket_replies.*, users.name AS author_name, users.role AS author_role')
            ->join('users', 'users.id = ticket_replies.user_id')
            ->where('ticket_replies.ticket_id', $ticket->id)
            ->where('ticket_replies.is_internal', 0)
            ->orderBy('ticket_replies.created_at', 'ASC')
            ->findAll();

        $attachmentModel = $this->attachmentModel;
        foreach ($replies as &$reply) {
            $reply->attachments = $attachmentModel->getForReply((int) $reply->id);
        }
        unset($reply);

        return [
            'replies'           => buildReplyTree($replies),
            'ticketAttachments' => $this->attachmentModel->getForTicketLevel($ticket->id),
            'timeline'          => $this->ticketModel->getTimeline($ticket->id),
            'feedback'          => $this->feedbackModel->getFeedbackForTicket($ticket->id),
        ];
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
        $query  = trim((string) $this->request->getGet('q'));

        if ($query !== '') {
            $tickets   = $this->ticketModel->searchForRequester($userId, $query);
            $pager     = null;
        } else {
            $perPage = 10;
            $tickets = $this->ticketModel
                ->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
                ->join('users AS requester', 'requester.id = tickets.requester_id')
                ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
                ->join('departments', 'departments.id = tickets.department_id')
                ->where('tickets.requester_id', $userId)
                ->where('tickets.archived_at IS NULL')
                ->orderBy('tickets.created_at', 'DESC')
                ->paginate($perPage, 'tickets');
            $pager = $this->ticketModel->pager;
        }

        return view('tickets/index', [
            'tickets' => $tickets,
            'pager'   => $pager,
            'query'   => $query,
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
            'sla_due_at'    => $this->calculateSlaDueAt($this->request->getPost('priority')),
        ];

        $this->ticketModel->insert($data);
        $ticketId = $this->ticketModel->getInsertID();

        // Handle file uploads
        $this->handleUploads($ticketId, null, (int) session()->get('user_id'));

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

        // Send confirmation email to student (non-blocking)
        $ticket   = $this->ticketModel->getTicketWithRelations($ticketId);
        $student  = $this->userModel->find(session()->get('user_id'));
        if ($ticket && $student) {
            $this->mailer->sendTicketConfirmation($ticket, $student);
        }

        return redirect()->to(site_url('student/tickets'))->with('success', 'Your concern has been submitted successfully.');
    }


    public function view($id)
    {
        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return redirect()->to(site_url('student/tickets'))->with('error', 'Concern not found or access denied.');
        }

        return view('tickets/view', [
            'ticket'          => $ticket,
            'realtimeConfig' => $this->getRealtimeConfigForTicket((int) $ticket->id),
            ...$this->getThreadViewData($ticket),
        ]);
    }

    public function thread($id)
    {
        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);

        if (! $ticket || $ticket->requester_id !== session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Concern not found or access denied.',
            ])->setStatusCode(403);
        }

        $viewData = $this->getThreadViewData($ticket);

        return $this->response->setJSON([
            'success'      => true,
            'repliesHtml'  => view('tickets/partials/reply_thread', [
                'ticket'   => $ticket,
                'replies'  => $viewData['replies'],
            ]),
            'timelineHtml' => view('partials/timeline', [
                'timeline' => $viewData['timeline'],
            ]),
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

        (new RealtimePublisher())->publishReplyCreated(
            (int) $ticket->id,
            (int) session()->get('user_id'),
            'all'
        );

        return redirect()->back()->with('success', 'Reply added successfully.');
    }

    /**
     * Calculate SLA due date based on ticket priority (#12).
     *
     * | Priority | Response window |
     * |----------|-----------------|
     * | Urgent   | 1 hour          |
     * | High     | 4 hours         |
     * | Medium   | 8 hours         |
     * | Low      | 24 hours        |
     */
    private function calculateSlaDueAt(string $priority): string
    {
        $hoursMap = [
            'Urgent' => 1,
            'High'   => 4,
            'Medium' => 8,
            'Low'    => 24,
        ];
        $hours = $hoursMap[$priority] ?? 8;
        return date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
    }
}
