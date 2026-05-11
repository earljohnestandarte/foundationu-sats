<?= $this->extend('layout/main') ?>
<?php /** @var object $ticket */ ?>
<?php /** @var object[] $replies */ ?>
<?php /** @var object[] $agents */ ?>
<?php $this->section('title') ?>Ticket #<?= esc((string) $ticket->id) ?><?php $this->endSection() ?>
<?php $this->section('content') ?>
<div class="card mb-4">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Ticket #<?= esc((string) $ticket->id) ?>: <?= esc((string) $ticket->subject) ?></h4>
            <p class="text-muted mb-0">Manage ticket details, assignment, and the conversation thread.</p>
        </div>
        <form action="<?= site_url('agent/updateStatus/' . $ticket->id) ?>" method="post" class="d-flex gap-2 align-items-center">
            <?= csrf_field() ?>
            <select name="status" class="form-select form-select-sm">
                <option value="Open" <?= $ticket->status === 'Open' ? 'selected' : '' ?>>Open</option>
                <option value="In Progress" <?= $ticket->status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Waiting on Student" <?= $ticket->status === 'Waiting on Student' ? 'selected' : '' ?>>Waiting on Student</option>
                <option value="Resolved" <?= $ticket->status === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="Closed" <?= $ticket->status === 'Closed' ? 'selected' : '' ?>>Closed</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Update</button>
        </form>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <p class="mb-2"><strong>Requester:</strong> <?= esc((string) $ticket->requester_name) ?></p>
                <p class="mb-2"><strong>Office:</strong> <?= esc((string) $ticket->office_name) ?></p>
                <p class="mb-2"><strong>Status:</strong>
                    <?php if ($ticket->status === 'Open'): ?>
                        <span class="badge rounded-pill bg-primary">Open</span>
                    <?php elseif ($ticket->status === 'In Progress'): ?>
                        <span class="badge rounded-pill bg-warning text-dark">In Progress</span>
                    <?php elseif ($ticket->status === 'Waiting on Student'): ?>
                        <span class="badge rounded-pill bg-info text-dark">Waiting on Student</span>
                    <?php elseif ($ticket->status === 'Resolved'): ?>
                        <span class="badge rounded-pill bg-success">Resolved</span>
                    <?php else: ?>
                        <span class="badge rounded-pill bg-secondary">Closed</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong>Priority:</strong>
                    <?php if ($ticket->priority === 'Low'): ?>
                        <span class="badge rounded-pill bg-light text-success">Low</span>
                    <?php elseif ($ticket->priority === 'Medium'): ?>
                        <span class="badge rounded-pill bg-light text-primary">Medium</span>
                    <?php elseif ($ticket->priority === 'High'): ?>
                        <span class="badge rounded-pill bg-light text-warning">High</span>
                    <?php else: ?>
                        <span class="badge rounded-pill bg-light text-danger"><i class="fas fa-fire me-1"></i>Urgent</span>
                    <?php endif; ?>
                </p>
                <p class="mb-2"><strong>Assigned To:</strong> <?= $ticket->resolver_name ? esc((string) $ticket->resolver_name) : 'Unassigned' ?></p>
                <p class="mb-0"><strong>Created:</strong> <?= esc((string) $ticket->created_at) ?></p>
            </div>
        </div>

        <hr />
        <div>
            <h5 class="mb-3">Description</h5>
            <p class="mb-0" style="line-height: 1.7;"><?= nl2br(esc((string) $ticket->description)) ?></p>
        </div>
    </div>
</div>

<?php if (count($agents) > 1): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Reassign Ticket</h5>
        </div>
        <div class="card-body">
            <form action="<?= site_url('agent/reassign/' . $ticket->id) ?>" method="post">
                <?= csrf_field() ?>
                <div class="row gy-3">
                    <div class="col-md-6">
                        <label for="resolver_id" class="form-label">Assign to Agent</label>
                        <select name="resolver_id" id="resolver_id" class="form-select" required>
                            <option value="">Select an agent...</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent->id ?>" <?= $ticket->resolver_id == $agent->id ? 'selected' : '' ?>>
                                    <?= esc((string) $agent->name) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-secondary">Reassign Ticket</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif ?>

<?php
$assignees = $assignees ?? [];
$isAssigned = false;
foreach ($assignees as $assignee) {
    if ($assignee->user_id == session()->get('user_id')) {
        $isAssigned = true;
        break;
    }
}
?>
<?php if (! $isAssigned): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Assign Ticket</h5>
        </div>
        <div class="card-body">
            <p>Assign this ticket to yourself to start working on it.</p>
            <form action="<?= site_url('agent/assign/' . $ticket->id) ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Assign to Me</button>
            </form>
        </div>
    </div>
<?php endif ?>

<?php if (! empty($assignees)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Assigned Agents</h5>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <?php foreach ($assignees as $assignee): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= esc((string) $assignee->assignee_name) ?>
                        <small class="text-muted">Assigned on <?= esc((string) $assignee->assigned_at) ?></small>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
<?php endif ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Conversation Thread</h5>
    </div>
    <div class="card-body">
        <?php if (empty($replies)): ?>
            <div class="alert alert-secondary">No replies yet.</div>
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
                            <?= form_open('agent/addReply/' . $ticketId) ?>
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
            <?= form_open('agent/addReply/' . $ticket->id) ?>
            <?= csrf_field() ?>
            <div class="mb-3">
                <textarea name="message" class="form-control" rows="4" required></textarea>
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