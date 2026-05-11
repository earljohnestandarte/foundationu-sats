<?= $this->extend('layout/main') ?>
<?php /** @var object $ticket */ ?>
<?php /** @var object[] $replies */ ?>
<?php $this->section('title') ?>Ticket #<?= esc((string) $ticket->id) ?><?php $this->endSection() ?>
<?php $this->section('content') ?>
<?php
$statusClasses = [
    'Open' => 'bg-success',
    'In Progress' => 'bg-warning text-dark',
    'Waiting on Student' => 'bg-info text-dark',
    'Resolved' => 'bg-secondary',
    'Closed' => 'bg-dark',
];
$priorityClasses = [
    'Low' => 'bg-info text-dark',
    'Medium' => 'bg-warning text-dark',
    'High' => 'bg-danger',
];
$statusClass = $statusClasses[$ticket->status] ?? 'bg-primary';
$priorityClass = $priorityClasses[$ticket->priority] ?? 'bg-secondary text-light';
?>
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0">Ticket #<?= esc((string) $ticket->id) ?>: <?= esc((string) $ticket->subject) ?></h4>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <p class="mb-2"><strong>Office:</strong> <?= esc((string) $ticket->office_name) ?></p>
                <p class="mb-2"><strong>Created:</strong> <?= esc((string) $ticket->created_at) ?></p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong>Status:</strong> <span class="badge rounded-pill <?= $statusClass ?>"><?= esc((string) $ticket->status) ?></span></p>
                <p class="mb-0"><strong>Priority:</strong> <span class="badge rounded-pill <?= $priorityClass ?>"><?= esc((string) $ticket->priority) ?></span></p>
            </div>
        </div>
        <hr />
        <div class="mb-3">
            <h5>Description</h5>
            <p><?= nl2br(esc((string) $ticket->description)) ?></p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Conversation Thread</h5>
    </div>
    <div class="card-body">
        <?php if (empty($replies)): ?>
            <div class="alert alert-secondary">No replies yet. Please wait for an agent response.</div>
        <?php else: ?>
            <?php function renderReplies(array $replies, int $depth = 0, $ticketId)
            {
                foreach ($replies as $reply): ?>
                    <div class="reply-bubble<?= $depth ? ' ms-4' : '' ?> <?= $reply->user_id === session()->get('user_id') ? 'bg-light' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong><?= esc((string) $reply->author_name) ?></strong>
                            <small class="text-muted"><?= esc((string) $reply->created_at) ?></small>
                        </div>
                        <p class="mb-3"><?= nl2br(esc((string) $reply->message)) ?></p>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none reply-toggle" data-target="reply-form-<?= $reply->id ?>">Reply</button>
                        <div class="reply-form reply-form-<?= $reply->id ?>" style="display:none;">
                            <?= form_open('student/tickets/' . $ticketId . '/reply') ?>
                            <?= csrf_field() ?>
                            <?= form_hidden('reply_to', $reply->id) ?>
                            <div class="mb-2">
                                <?= form_textarea('message', '', ['class' => 'form-control form-control-sm', 'rows' => 3, 'placeholder' => 'Write a reply...']) ?>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Post Reply</button>
                            <?= form_close() ?>
                        </div>
                        <?php if (! empty($reply->children)): ?>
                            <?php renderReplies($reply->children, $depth + 1, $ticketId); ?>
                        <?php endif; ?>
                    </div>
            <?php endforeach;
            }
            renderReplies($replies, 0, $ticket->id); ?>
        <?php endif ?>

        <hr />
        <div>
            <h6 class="mb-3">Add a new reply</h6>
            <?= form_open('student/tickets/' . $ticket->id . '/reply') ?>
            <?= csrf_field() ?>
            <div class="mb-3">
                <?= form_textarea('message', old('message', ''), ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Write your message...']) ?>
            </div>
            <button type="submit" class="btn btn-primary">Send Reply</button>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?php $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.reply-toggle').on('click', function() {
            var target = $(this).data('target');
            $('.reply-form').not('.' + target).hide();
            $('.' + target).toggle();
        });
    });
</script>
<?= $this->endSection() ?>