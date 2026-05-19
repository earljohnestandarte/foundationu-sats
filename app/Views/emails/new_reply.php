<?php
$ref = '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
$ticketUrl = $baseUrl . '/' . (session()->get('user_role') === 'student' ? 'student/tickets/' : 'agent/view/') . $ticket->id;
ob_start();
?>
<p>Hi <strong><?= esc($recipient->name) ?></strong>,</p>
<p><strong><?= esc($replierName) ?></strong> has left a new reply on concern <?= esc($ref) ?>.</p>
<div class="ticket-box">
  <div class="ref"><?= esc($ref) ?></div>
  <div class="subject"><?= esc($ticket->subject) ?></div>
</div>
<div class="reply-box">
  <?= nl2br(esc(strip_tags($reply->body))) ?>
</div>
<a href="<?= $baseUrl ?>/student/tickets/<?= $ticket->id ?>" class="btn">View Full Conversation →</a>
<?php $emailContent = ob_get_clean(); ?>
<?php echo view('emails/layout', compact('emailContent', 'baseUrl', 'subject', 'appName')); ?>
