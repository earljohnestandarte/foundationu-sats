<?= $this->extend('layout/main') ?>
<?php /** @var object $ticket */ ?>
<?php /** @var object[] $replies */ ?>
<?php /** @var object[] $agents */ ?>
<?php $this->section('title') ?>Ticket #<?= esc((string) $ticket->id) ?><?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="<?= site_url('agent/dashboard') ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <h3 class="fw-bold mb-0">Ticket #FAU-<?= str_pad(esc((string)$ticket->id), 4, '0', STR_PAD_LEFT) ?>: <?= esc((string) $ticket->subject) ?></h3>
            <form action="<?= site_url('agent/updateStatus/' . $ticket->id) ?>" method="post" class="d-flex gap-2 align-items-center">
                <?= csrf_field() ?>
                <select name="status" class="form-select min-width-180">
                    <option value="Open" <?= $ticket->status === 'Open' ? 'selected' : '' ?>>Open</option>
                    <option value="In Progress" <?= $ticket->status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Waiting on Student" <?= $ticket->status === 'Waiting on Student' ? 'selected' : '' ?>>Waiting on Student</option>
                    <option value="Resolved" <?= $ticket->status === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="Closed" <?= $ticket->status === 'Closed' ? 'selected' : '' ?>>Closed</option>
                </select>
                <button type="submit" class="btn btn-fu-primary">Update</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card-fu mb-4">
                <div class="p-4">
                    <div class="mb-3">
                        <h6 class="text-uppercase fw-semibold mb-3">Description</h6>
                        <p style="line-height: 1.7;"><?= nl2br(esc((string) $ticket->description)) ?></p>
                    </div>
                </div>
            </div>

            <div class="card-fu">
                <div class="d-flex justify-between items-center px-4 py-3 card-header-section">
                    <h6 class="fw-semibold mb-0">Conversation Thread</h6>
                </div>
                <div style="padding: 0;">
                    <?php if (empty($replies)): ?>
                        <div class="p-5 text-center">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p class="mb-0">No replies yet. Start the conversation.</p>
                        </div>
                    <?php else: ?>
                        <?php function renderReplies(array $replies, int $depth = 0, $ticketId)
                        {
                            foreach ($replies as $reply): ?>
                                <div class="reply-bubble<?= $depth ? ' depth-1' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: var(--fu-surface-container-low);">
                                                <i class="fas fa-user" style="color: var(--fu-on-surface-variant);"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block"><?= esc((string) $reply->author_name) ?></strong>
                                                <small><?= date('M j, Y g:i A', strtotime($reply->created_at)) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="line-height: 1.7;"><?= nl2br(esc((string) $reply->message)) ?></div>
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none reply-toggle mt-2" data-target="reply-form-<?= $reply->id ?>">
                                        <i class="fas fa-reply me-1"></i> Reply
                                    </button>
                                    <div class="reply-form reply-form-<?= $reply->id ?> mt-3">
                                        <?= form_open('agent/addReply/' . $ticketId) ?>
                                        <?= csrf_field() ?>
                                        <?= form_hidden('reply_to', $reply->id) ?>
                                        <div class="mb-2">
                                            <textarea name="message" class="form-control" rows="3" placeholder="Write a reply..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-fu-primary btn-sm">Post Reply</button>
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
                </div>
                <div class="p-4" style="border-top: 1px solid var(--fu-outline-variant);">
                    <h6 class="fw-semibold mb-3">Add a new reply</h6>
                    <?= form_open('agent/addReply/' . $ticket->id) ?>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="4" placeholder="Write your message..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-fu-primary d-flex align-items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-fu mb-4">
                <div class="d-flex justify-between items-center px-4 py-3 card-header-section">
                    <h6 class="fw-semibold mb-0">Ticket Details</h6>
                </div>
                <div class="p-4">
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Requester</label>
                        <div><?= esc((string) $ticket->requester_name) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Status</label>
                        <?php
                        $statusBadgeClass = 'open';
                        if ($ticket->status === 'In Progress') {
                            $statusBadgeClass = 'in-progress';
                        } elseif ($ticket->status === 'Resolved') {
                            $statusBadgeClass = 'resolved';
                        }
                        ?>
                        <span class="badge-fu <?= $statusBadgeClass ?>"><?= esc((string)$ticket->status) ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Priority</label>
                        <?php if ($ticket->priority === 'Low'): ?>
                            <span class="fw-medium priority-low"><i class="fas fa-circle priority-dot"></i>Low</span>
                        <?php elseif ($ticket->priority === 'Medium'): ?>
                            <span class="fw-medium priority-medium"><i class="fas fa-circle priority-dot"></i>Medium</span>
                        <?php elseif ($ticket->priority === 'High'): ?>
                            <span class="fw-medium priority-high"><i class="fas fa-circle priority-dot"></i>High</span>
                        <?php else: ?>
                            <span class="fw-medium priority-urgent"><i class="fas fa-fire me-1"></i>Urgent</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Office</label>
                        <div><?= esc((string) $ticket->office_name) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Assigned To</label>
                        <div><?= $ticket->resolver_name ? esc((string) $ticket->resolver_name) : '<span class="text-muted-unassigned">Unassigned</span>' ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Created</label>
                        <div><?= date('M j, Y g:i A', strtotime($ticket->created_at)) ?></div>
                    </div>
                </div>
            </div>

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
                <div class="card-fu mb-4">
                    <div class="p-4">
                        <p class="mb-3 fw-medium">Assign this ticket to yourself to start working on it.</p>
                        <form action="<?= site_url('agent/assign/' . $ticket->id) ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-fu-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="fas fa-user-check"></i> Assign to Me
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif ?>

            <?php if (count($agents) > 1): ?>
                <div class="card-fu">
                    <div class="d-flex justify-between items-center px-4 py-3 card-header-section">
                        <h6 class="fw-semibold mb-0">Reassign Ticket</h6>
                    </div>
                    <div class="p-4">
                        <form action="<?= site_url('agent/reassign/' . $ticket->id) ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="resolver_id" class="form-label fw-semibold">Assign to Agent</label>
                                <select name="resolver_id" id="resolver_id" class="form-select" required>
                                    <option value="">Select an agent...</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?= $agent->id ?>" <?= $ticket->resolver_id == $agent->id ? 'selected' : '' ?>>
                                            <?= esc((string) $agent->name) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-reassign w-100">Reassign Ticket</button>
                        </form>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
<?php $this->section('scripts') ?>
<script src="<?= base_url('assets/js/ticket-view.js') ?>"></script>
<?php $this->endSection() ?>