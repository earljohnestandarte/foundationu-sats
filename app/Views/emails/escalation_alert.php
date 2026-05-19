<?php
$ref = '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
ob_start();
?>
<p>Hi <strong><?= esc($recipient->name) ?></strong>,</p>
<p>🚨 A ticket has been <strong>escalated</strong> and requires immediate attention.</p>
<div class="ticket-box">
  <div class="ref"><?= esc($ref) ?></div>
  <div class="subject"><?= esc($ticket->subject) ?></div>
  <div class="meta" style="margin-top:8px;">
    <span><strong>Priority:</strong> <?= esc($ticket->priority) ?></span>
    <span><strong>Status:</strong> <?= esc($ticket->status) ?></span>
    <span><strong>Escalated:</strong> <?= date('M j, Y g:i A') ?></span>
  </div>
</div>
<?php if (!empty($reason)): ?>
<p><strong>Reason for escalation:</strong></p>
<div class="reply-box"><?= nl2br(esc($reason)) ?></div>
<?php endif; ?>
<p>Please review this concern immediately and take appropriate action.</p>
<a href="<?= $baseUrl ?>/agent/view/<?= $ticket->id ?>" class="btn">View Escalated Ticket →</a>
<?php $emailContent = ob_get_clean(); ?>
<?php echo view('emails/layout', compact('emailContent', 'baseUrl', 'subject', 'appName')); ?>
