<?php

namespace App\Controllers;

use App\Models\TicketModel;
use App\Models\TicketReplyModel;
use CodeIgniter\Controller;

class AiController extends BaseController
{
    protected $helpers = ['url', 'session'];

    public function suggest($ticketId)
    {
        $userId = session()->get('user_id');
        if (! $userId) {
            return $this->response->setJSON(['error' => 'Not authenticated'])->setStatusCode(401);
        }

        $userRole = session()->get('user_role') ?? 'student';
        $userName = session()->get('user_name') ?? 'Staff';

        $ticketModel = new TicketModel();
        $ticket = $ticketModel->getTicketWithRelations((int) $ticketId);

        if (! $ticket) {
            return $this->response->setJSON(['error' => 'Concern not found'])->setStatusCode(404);
        }

        $replyModel = new TicketReplyModel();
        $replies = $replyModel
            ->select('ticket_replies.message, users.name AS author_name, users.role AS author_role')
            ->join('users', 'users.id = ticket_replies.user_id')
            ->where('ticket_replies.ticket_id', $ticket->id)
            ->orderBy('ticket_replies.created_at', 'ASC')
            ->findAll();

        $currentText = $this->request->getGet('current_text') ?? '';

        $roleLabels = ['agent' => 'Agent', 'sao' => 'Administrator', 'admin' => 'Administrator', 'student' => 'Student'];
        $roleLabel = $roleLabels[$userRole] ?? 'Staff';
        $isStaff = in_array($userRole, ['agent', 'sao', 'admin']);

        if ($isStaff) {
            $context = "You are {$userName}, a {$roleLabel} at Foundation University's {$ticket->department_name} department. You are drafting a reply to a student concern.\n\n";
        } else {
            $context = "IMPORTANT: You are {$userName}, a STUDENT at Foundation University. This is YOUR OWN concern ticket. You are writing a follow-up message to university staff at the Office of Student Life (OSL) — a professional university organization. Speak in FIRST PERSON (I, me, my) but be RESPECTFUL and POLITE. Use proper English or the student's native language naturally. Do NOT offer help or assistance — you are the one ASKING for help.\n\n";
        }

        $context .= "Concern: {$ticket->subject}\n";
        $context .= "Description: {$ticket->description}\n";
        $context .= "Department: {$ticket->department_name}\n";
        $context .= "Type: {$ticket->concern_type}\n";
        $context .= "Priority: {$ticket->priority}\n";
        $context .= "Status: {$ticket->status}\n";

        if (! empty($replies)) {
            $context .= "\nConversation so far:\n";
            foreach ($replies as $r) {
                $rRole = ($r->author_role === 'agent' || $r->author_role === 'admin' || $r->author_role === 'sao') ? 'Staff' : 'Student';
                $context .= "{$rRole} ({$r->author_name}): {$r->message}\n";
            }
        }

        if ($currentText) {
            if ($isStaff) {
                $context .= "\nThe {$roleLabel} has already started typing this draft:\n\"{$currentText}\"\nContinue or improve this draft.\n";
            } else {
                $context .= "\nYou have already started typing this draft:\n\"{$currentText}\"\nContinue or improve this draft in first person.\n";
            }
        }

        if ($isStaff) {
            $context .= "\nWrite a SHORT reply (2-4 sentences max, chat-style, NOT an email). Do NOT include: subject lines, formal salutations like \"Dear...\", closings like \"Sincerely\", phone numbers, email addresses, or email signatures. Just the message body. Use **bold** for emphasis where needed. Address the student directly and state the next step clearly.";
        } else {
            $context .= "\nWrite a SHORT reply (2-4 sentences max, chat-style, NOT an email) spoken in FIRST PERSON as the student. Use a POLITE and RESPECTFUL tone suitable for communication with university staff. Do NOT start with casual greetings like \"Hey\" or \"Hi\" — use proper openings like \"Good day\" or be direct but courteous. Do NOT include: subject lines, salutations, closings, or signatures. Use **bold** for emphasis. Ask a question, request an update, or provide additional info about YOUR concern.";
        }

        $apiKey = getenv('AI_API_KEY') ?: getenv('NVIDIA_API_KEY') ?: '';
        if (empty($apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'AI suggestion is not configured. Set AI_API_KEY in your .env file.',
            ]);
        }

        $baseUrl = rtrim(getenv('AI_BASE_URL') ?: 'https://integrate.api.nvidia.com/v1', '/');
        $model = getenv('AI_MODEL') ?: 'openai/gpt-oss-120b';

        $ch = curl_init($baseUrl . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', "Authorization: Bearer {$apiKey}"],
            CURLOPT_POSTFIELDS     => json_encode([
                'model'       => $model,
                'messages'    => [['role' => 'user', 'content' => $context]],
                'max_tokens'  => 512,
                'temperature' => 0.7,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 45,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 403) {
            $body = json_decode($response, true);
            $detail = $body['detail'] ?? 'Authorization failed';
            log_message('error', 'AI API 403: ' . $detail);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'AI API authorization failed. Make sure your API key has access to the ' . esc($model) . ' model.',
            ]);
        }

        if ($httpCode !== 200 || ! $response) {
            log_message('error', 'AI API error HTTP ' . $httpCode . ': ' . ($response ?: ($curlError ?: 'No response')));
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unable to generate suggestion at this time. (HTTP ' . $httpCode . ')',
            ]);
        }

        $data = json_decode($response, true);
        $message = $data['choices'][0]['message'] ?? [];
        $suggestion = $message['content'] ?? $message['reasoning_content'] ?? '';

        return $this->response->setJSON([
            'success'    => true,
            'suggestion' => trim($suggestion),
        ]);
    }
}
