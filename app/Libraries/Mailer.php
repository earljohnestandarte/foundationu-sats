<?php

namespace App\Libraries;

use CodeIgniter\Config\Services;

/**
 * Mailer — thin wrapper around CI4's Email service.
 *
 * All transactional emails for FU-SATS are sent through here.
 * In development the emails are caught by Mailpit (port 1025).
 *
 * Usage:
 *   $mailer = new \App\Libraries\Mailer();
 *   $mailer->sendTicketConfirmation($ticket, $student);
 */
class Mailer
{
    private \CodeIgniter\Email\Email $email;
    private string $baseUrl;

    public function __construct()
    {
        $this->email   = Services::email();
        $this->baseUrl = rtrim((string) base_url(), '/');
    }

    /* ── Internal: send a rendered view as email ──────────── */
    private function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $viewPath,
        array  $data = []
    ): bool {
        $data['baseUrl']   = $this->baseUrl;
        $data['appName']   = 'FU-SATS';
        $data['subject']   = $subject;

        $body = view($viewPath, $data);

        $this->email->clear();
        $this->email->setTo($toEmail, $toName);
        $this->email->setSubject('[FU-SATS] ' . $subject);
        $this->email->setMessage($body);

        try {
            return $this->email->send(false);
        } catch (\Exception $e) {
            log_message('error', 'Mailer::send failed — ' . $e->getMessage());
            return false;
        }
    }

    /* ── 1. New ticket confirmation to student ─────────────── */
    public function sendTicketConfirmation(object $ticket, object $student): bool
    {
        return $this->send(
            $student->email,
            $student->name,
            'Your concern has been received (#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT) . ')',
            'emails/ticket_confirmation',
            ['ticket' => $ticket, 'student' => $student]
        );
    }

    /* ── 2. New reply notification ─────────────────────────── */
    public function sendNewReply(object $ticket, object $recipient, object $reply, string $replierName): bool
    {
        $ref = '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
        return $this->send(
            $recipient->email,
            $recipient->name,
            'New reply on your concern ' . $ref,
            'emails/new_reply',
            ['ticket' => $ticket, 'recipient' => $recipient, 'reply' => $reply, 'replierName' => $replierName]
        );
    }

    /* ── 3. Status change notification to student ──────────── */
    public function sendStatusChanged(object $ticket, object $student, string $oldStatus, string $newStatus): bool
    {
        $ref = '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
        return $this->send(
            $student->email,
            $student->name,
            'Status update on ' . $ref . ': ' . $oldStatus . ' → ' . $newStatus,
            'emails/status_changed',
            ['ticket' => $ticket, 'student' => $student, 'oldStatus' => $oldStatus, 'newStatus' => $newStatus]
        );
    }

    /* ── 4. Escalation alert to agents / SAO ──────────────── */
    public function sendEscalationAlert(object $ticket, array $recipients, string $reason): bool
    {
        $ref     = '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
        $subject = 'Escalation alert: ' . $ref;
        $ok      = true;
        foreach ($recipients as $recipient) {
            $sent = $this->send(
                $recipient->email,
                $recipient->name,
                $subject,
                'emails/escalation_alert',
                ['ticket' => $ticket, 'recipient' => $recipient, 'reason' => $reason]
            );
            if (!$sent) $ok = false;
        }
        return $ok;
    }

    /* ── 5. SLA breach warning to assigned agent ───────────── */
    public function sendSlaBreachWarning(object $ticket, object $agent): bool
    {
        $ref = '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
        return $this->send(
            $agent->email,
            $agent->name,
            'SLA warning: ' . $ref . ' is approaching its deadline',
            'emails/sla_warning',
            ['ticket' => $ticket, 'agent' => $agent]
        );
    }
}
