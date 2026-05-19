<?php

namespace App\Controllers;

use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use App\Models\TicketAssigneeModel;
use App\Models\TicketFeedbackModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Models\AttachmentModel;
use App\Libraries\Mailer;
use App\Libraries\RealtimePublisher;
use CodeIgniter\Controller;


class AgentController extends BaseController
{
    protected $helpers = ['url', 'form', 'session', 'reply'];
    protected TicketModel $ticketModel;
    protected TicketReplyModel $replyModel;
    protected TicketAssigneeModel $ticketAssigneeModel;
    protected TicketFeedbackModel $feedbackModel;
    protected UserModel $userModel;
    protected NotificationModel $notificationModel;
    protected AttachmentModel $attachmentModel;
    protected Mailer $mailer;


    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->ticketModel          = new TicketModel();
        $this->replyModel           = new TicketReplyModel();
        $this->ticketAssigneeModel  = new TicketAssigneeModel();
        $this->feedbackModel        = new TicketFeedbackModel();
        $this->userModel            = new UserModel();
        $this->notificationModel    = new NotificationModel();
        $this->attachmentModel      = new AttachmentModel();
        $this->mailer               = new Mailer();
    }

    /** Save uploaded files attached to a reply */
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
            if ($file->getSize() > 5 * 1024 * 1024) continue;
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
        $departmentId = session()->get('department_id');
        $query        = trim((string) $this->request->getGet('q'));

        // Fix #8: Elevated users previously triggered N+1 queries (1 per ticket).
        // Now uses a single joined query for both elevated and department-scoped users.
        if ($query !== '') {
            // Search mode — no pagination needed, results are bounded by query
            if ($this->isElevated() && ! $departmentId) {
                $tickets = $this->ticketModel->getAllActiveWithRelations();
                // Filter in PHP since searchForDepartment needs a dept ID
                $tickets = array_filter($tickets, function ($t) use ($query) {
                    $q = strtolower($query);
                    return str_contains(strtolower($t->subject), $q)
                        || str_contains(strtolower($t->description), $q)
                        || str_contains(strtolower($t->requester_name ?? ''), $q)
                        || str_contains(strtolower($t->status), $q);
                });
                $tickets = array_values($tickets);
            } else {
                $tickets = $this->ticketModel->searchForDepartment((int) $departmentId, $query);
            }
            $pager = null;
        } else {
            // Normal paginated mode
            if ($this->isElevated() && ! $departmentId) {
                $tickets = $this->ticketModel->getAllActiveWithRelations();
                $pager   = null; // All-dept admins get full list (could paginate later)
            } else {
                $tickets = $this->ticketModel
                    ->select('tickets.*, requester.name AS requester_name, resolver.name AS resolver_name, departments.name AS department_name')
                    ->join('users AS requester', 'requester.id = tickets.requester_id')
                    ->join('users AS resolver', 'resolver.id = tickets.resolver_id', 'left')
                    ->join('departments', 'departments.id = tickets.department_id')
                    ->where('tickets.department_id', $departmentId)
                    ->where('tickets.archived_at IS NULL')
                    ->orderBy('tickets.created_at', 'DESC')
                    ->paginate(15, 'tickets');
                $pager = $this->ticketModel->pager;
            }
        }

        return view('agent/dashboard', [
            'tickets' => $tickets,
            'pager'   => $pager ?? null,
            'query'   => $query,
        ]);
    }

    public function archived()
    {
        $departmentId = session()->get('department_id');

        // Fix #8: Same N+1 fix applied to archived view.
        if ($this->isElevated() && ! $departmentId) {
            $tickets = $this->ticketModel->getAllArchivedWithRelations();
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

        if ($this->isElevated()) {
            $agents = $this->userModel->where('role', 'agent')->findAll();
        } else {
            $agents = $this->userModel->getAgentsForDepartment(session()->get('department_id'));
        }

        $assignees = $this->ticketModel->getAssigneesForTicket($ticket->id);

        return view('agent/ticket_view', [
            'ticket'          => $ticket,
            'agents'          => $agents,
            'assignees'       => $assignees,
            'realtimeConfig' => $this->getRealtimeConfigForTicket((int) $ticket->id),
            ...$this->getThreadViewData($ticket),
        ]);
    }

    public function thread($id)
    {
        $ticket = $this->ticketModel->getTicketWithRelations((int) $id);

        if (! $ticket || ! $this->canAccess($ticket->department_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Concern not found or access denied.',
            ])->setStatusCode(403);
        }

        $viewData = $this->getThreadViewData($ticket);

        return $this->response->setJSON([
            'success'      => true,
            'repliesHtml'  => view('agent/partials/reply_thread', [
                'ticket'  => $ticket,
                'replies' => $viewData['replies'],
            ]),
            'timelineHtml' => view('partials/timeline', [
                'timeline' => $viewData['timeline'],
            ]),
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
        $newResolver = $this->userModel->find($resolverId);

        if (! $newResolver || $newResolver->role !== 'agent') {
            return redirect()->back()->with('error', 'Selected resolver is not a valid agent.');
        }

        if ((int) $newResolver->department_id !== (int) $ticket->department_id) {
            return redirect()->back()->with('error', 'Selected agent does not belong to this concern\'s department.');
        }

        if ($ticket->resolver_id == $resolverId) {
            return redirect()->to(site_url('agent/view/' . $ticket->id))->with('error', 'This agent is already assigned to this concern.');
        }

        if ($resolverId !== $userId && ! $this->isElevated()) {
            if ((int) $newResolver->department_id !== (int) $departmentId) {
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

        // Email escalation alert to all SAO/admin
        $reason = $this->request->getPost('reason') ?: 'Escalated by agent';
        $this->mailer->sendEscalationAlert($ticket, $saos, $reason);

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

        $newStatus  = $this->request->getPost('status');
        $oldStatus  = $ticket->status;
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

        // Email the student about the status change
        $student = $this->userModel->find($ticket->requester_id);
        if ($student) {
            $this->mailer->sendStatusChanged($ticket, $student, $oldStatus, $newStatus);
        }

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

        $isInternal = (bool) $this->request->getPost('is_internal');

        $this->replyModel->insert([
            'ticket_id'   => $ticket->id,
            'user_id'     => session()->get('user_id'),
            'message'     => $this->request->getPost('message'),
            'reply_to'    => $replyTo,
            'is_internal' => $isInternal ? 1 : 0,
        ]);

        $replyId = $this->replyModel->getInsertID();

        // Handle file uploads attached to this reply
        $this->handleUploads($ticket->id, $replyId, (int) session()->get('user_id'));

        if (! $ticket->first_response_at) {
            $this->ticketModel->update($ticket->id, [
                'first_response_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // In-app notification
        if (! $isInternal) {
            $this->notificationModel->insert([
                'user_id'   => $ticket->requester_id,
                'ticket_id' => $ticket->id,
                'message'   => 'New reply added to your concern "' . $ticket->subject . '"',
                'is_read'   => false,
            ]);

            // Email the student (only for public replies)
            $student = $this->userModel->find($ticket->requester_id);
            $replyObj = $this->replyModel->find($replyId);
            $agentName = session()->get('user_name') ?? 'Agent';
            if ($student && $replyObj) {
                $this->mailer->sendNewReply($ticket, $student, $replyObj, $agentName);
            }
        }

        (new RealtimePublisher())->publishReplyCreated(
            (int) $ticket->id,
            (int) session()->get('user_id'),
            $isInternal ? 'staff' : 'all'
        );

        return redirect()->back()->with('success', $isInternal ? 'Internal note added.' : 'Reply added successfully.');
    }

}
