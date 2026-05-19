<?php
$ref = '#FAU-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
$badgeClass = match($newStatus) {
    'In Progress' => 'badge-inprogress',
    'Pending'     => 'badge-pending',
    'Resolved'    => 'badge-resolved',
    'Closed'      => 'badge-closed',
    default       => 'badge-open',
};
ob_start();
?>
<p>Hi <strong><?= esc($student->name) ?></strong>,</p>
<p>The status of your concern <?= esc($ref) ?> has been updated.</p>
<div class="ticket-box">
  <div class="ref"><?= esc($ref) ?></div>
  <div class="subject"><?= esc($ticket->subject) ?></div>
  <div class="meta" style="margin-top:8px;">
    <span>Status changed from <strong><?= esc($oldStatus) ?></strong> to
      <span class="badge <?= $badgeClass ?>"><?= esc($newStatus) ?></span>
    </span>
  </div>
</div>
<?php if ($newStatus === 'Resolved'): ?>
<p>✅ Great news — your concern has been resolved! If you're satisfied with the resolution, no further action is needed.</p>
<p>If the issue persists, you can reopen the concern from FU-SATS within 7 days.</p>
<?php elseif ($newStatus === 'Closed'): ?>
<p>This concern has been closed and archived. If you have a new concern, please submit a fresh ticket.</p>
<?php else: ?>
<p>Our team is working on your concern. You'll receive another update when there's progress.</p>
<?php endif; ?>
<a href="<?= $baseUrl ?>/student/tickets/<?= $ticket->id ?>" class="btn">View Concern →</a>
<?php $emailContent = ob_get_clean(); ?>
<?php echo view('emails/layout', compact('emailContent', 'baseUrl', 'subject', 'appName')); ?>
