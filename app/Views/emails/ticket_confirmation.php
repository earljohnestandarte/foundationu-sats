<?php
$ref = '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
ob_start();
?>
<p>Hi <strong><?= esc($student->name) ?></strong>,</p>
<p>We've received your concern and it's now in our queue. Our team will review it shortly.</p>
<div class="ticket-box">
  <div class="ref"><?= esc($ref) ?></div>
  <div class="subject"><?= esc($ticket->subject) ?></div>
  <div class="meta">
    <span><strong>Department:</strong> <?= esc($ticket->department_name ?? '—') ?></span>
    <span><strong>Priority:</strong> <?= esc($ticket->priority) ?></span>
    <span><strong>Submitted:</strong> <?= date('M j, Y g:i A', strtotime($ticket->created_at)) ?></span>
  </div>
</div>
<p>You can track the status of your concern anytime by clicking the button below.</p>
<a href="<?= $baseUrl ?>/student/tickets/<?= $ticket->id ?>" class="btn">View My Concern →</a>
<p style="font-size:13px;color:#5a413d;margin-top:24px;">
  We aim to respond within the SLA window based on your priority level.<br>
  You will receive an email whenever your concern is updated.
</p>
<?php $emailContent = ob_get_clean(); ?>
<?php echo view('emails/layout', compact('emailContent', 'baseUrl', 'subject', 'appName')); ?>
