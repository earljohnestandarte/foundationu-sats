<?= $this->extend('layout/main') ?>
<?php /** @var object $ticket */ ?>
<?php /** @var object[] $replies */ ?>
<?php /** @var array $timeline */ ?>
<?php /** @var object|null $feedback */ ?>
<?php $this->section('title') ?>Concern #<?= esc((string) $ticket->id) ?><?php $this->endSection() ?>
<?php $this->section('css') ?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="<?= site_url('student/tickets') ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="d-flex align-items-start justify-content-between gap-3">
            <h3 class="fw-bold mb-0" style="color: var(--fu-primary); font-size: 24px;">#FAU-<?= str_pad(esc((string)$ticket->id), 4, '0', STR_PAD_LEFT) ?>: <?= esc((string) $ticket->subject) ?></h3>
            <?php if ($ticket->status === 'Resolved'): ?>
            <div class="d-flex gap-2 flex-shrink-0">
                <form action="<?= site_url('student/tickets/' . $ticket->id . '/confirm') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-fu-primary">Confirm & Close</button>
                </form>
                <form action="<?= site_url('student/tickets/' . $ticket->id . '/reopen') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-warning">Reopen</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <?php if ($ticket->sla_due_at): ?>
            <?php
                $slaDue = strtotime($ticket->sla_due_at);
                $now = time();
                $slaClass = $ticket->first_response_at ? 'sla-met' : ($now > $slaDue ? 'sla-breached' : 'sla-ok');
                $slaLabel = $ticket->first_response_at ? 'SLA Met' : ($now > $slaDue ? 'SLA Breached' : 'Within SLA');
            ?>
            <div class="card-fu mb-3">
                <div class="p-3 d-flex align-items-center gap-3">
                    <div class="sla-indicator <?= $slaClass ?>"></div>
                    <div>
                        <span class="fw-semibold"><?= $slaLabel ?></span>
                        <small class="d-block" style="color: var(--fu-on-surface-variant);">Response due by <?= date('M j, g:i A', $slaDue) ?></small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card-fu mb-4">
                <div class="p-3">
                    <div class="mb-3">
                        <h6 class="text-uppercase fw-semibold mb-3" style="color: var(--fu-on-surface-variant); font-size: 12px; letter-spacing: 0.05em;">Description</h6>
                        <p style="line-height: 1.7;"><?= nl2br(esc((string) $ticket->description)) ?></p>
                    </div>
                </div>
            </div>

            <div class="card-fu">
                <div class="d-flex justify-between items-center px-3 py-3" style="border-bottom: 1px solid var(--fu-outline-variant); background-color: var(--fu-surface-container-low);">
                    <h6 class="fw-semibold mb-0" style="color: var(--fu-primary);">Conversation Thread</h6>
                </div>
                <div style="padding: 0;">
                    <?php if (empty($replies)): ?>
                        <div class="p-5 text-center">
                            <i class="fas fa-comments fa-3x mb-3" style="color: var(--fu-on-surface-variant);"></i>
                            <p class="mb-0" style="color: var(--fu-on-surface-variant);">No replies yet. Please wait for an agent response.</p>
                        </div>
                    <?php else: ?>
                        <?php function renderReplies(array $replies, int $depth = 0, $ticketId)
                        {
                            foreach ($replies as $reply):
                                $roleLabel = ($reply->author_role ?? '') === 'agent' ? 'Agent' : (($reply->author_role ?? '') === 'admin' ? 'Admin' : 'Student');
                                $roleBadgeClass = ($reply->author_role ?? '') === 'agent' ? 'agent' : (($reply->author_role ?? '') === 'admin' ? 'admin' : 'student');
                            ?>
                                <div class="reply-bubble<?= $depth ? ' depth-1' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: var(--fu-surface-container-low);">
                                                <i class="fas fa-user" style="color: var(--fu-on-surface-variant);"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <strong style="color: var(--fu-on-surface);"><?= esc((string) $reply->author_name) ?></strong>
                                                    <span class="badge-fu signature-badge <?= $roleBadgeClass ?>"><?= $roleLabel ?></span>
                                                </div>
                                                <small style="color: var(--fu-on-surface-variant);"><?= date('M j, Y g:i A', strtotime($reply->created_at)) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="reply-content"><?= ($reply->message) ?></div>
                                    <div class="signature-line mt-2 pt-2" style="border-top: 1px solid var(--fu-outline-variant);">
                                        <small style="color: var(--fu-on-surface-variant);">
                                            <i class="fas fa-building me-1"></i> Foundation University — Student Affairs Ticketing System
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none reply-toggle mt-2" data-target="reply-form-<?= $reply->id ?>">
                                        <i class="fas fa-reply me-1"></i> Reply
                                    </button>
                                    <div class="reply-form reply-form-<?= $reply->id ?> mt-3">
                                        <?= form_open('student/tickets/' . $ticketId . '/reply') ?>
                                        <?= csrf_field() ?>
                                        <?= form_hidden('reply_to', $reply->id) ?>
                                        <div class="mb-2">
                                            <div class="quill-editor" id="quill-<?= $reply->id ?>"></div>
                                            <textarea name="message" class="quill-hidden" style="display:none;"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-fu-primary btn-sm quill-submit">Post Reply</button>
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
                <div class="p-3" style="border-top: 1px solid var(--fu-outline-variant);">
                    <h6 class="fw-semibold mb-3" style="color: var(--fu-on-surface);">Add a new reply</h6>
                    <?= form_open('student/tickets/' . $ticket->id . '/reply') ?>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <div class="quill-editor" id="quill-main"></div>
                        <textarea name="message" class="quill-hidden" style="display:none;"></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-2 ai-suggest-btn" data-ticket="<?= $ticket->id ?>">
                            <i class="fas fa-magic"></i> AI Suggest
                        </button>
                        <button type="submit" class="btn btn-fu-primary d-flex align-items-center gap-2 quill-submit">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <?php if (! $ticket->is_escalated): ?>
            <div class="card-fu mb-4">
                <div class="px-3 py-3" style="background-color:#fef2f2;border-bottom:1px solid var(--fu-error);">
                    <h6 class="fw-semibold mb-0" style="color:var(--fu-error);"><i class="fas fa-flag me-2"></i>Escalate Concern</h6>
                </div>
                <div class="p-3">
                    <form action="<?= site_url('student/tickets/'.$ticket->id.'/escalate') ?>" method="post">
                        <?= csrf_field() ?>
                        <textarea name="reason" class="form-control mb-2" rows="2" placeholder="Reason for escalation (optional)"></textarea>
                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm">Escalate to Administration</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <div class="card-fu mb-4">
                <div class="d-flex justify-between items-center px-3 py-3" style="border-bottom: 1px solid var(--fu-outline-variant); background-color: var(--fu-surface-container-low);">
                    <h6 class="fw-semibold mb-0" style="color: var(--fu-primary);">Concern Details</h6>
                </div>
                <div class="p-3">
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block" style="color: var(--fu-on-surface-variant);">Status</label>
                        <?php
                        $statusBadgeClass = 'open';
                        if ($ticket->status === 'In Progress') $statusBadgeClass = 'in-progress';
                        elseif ($ticket->status === 'Pending') $statusBadgeClass = 'pending';
                        elseif ($ticket->status === 'Resolved') $statusBadgeClass = 'resolved';
                        ?>
                        <span class="badge-fu <?= $statusBadgeClass ?>"><?= esc((string)$ticket->status) ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block" style="color: var(--fu-on-surface-variant);">Priority</label>
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
                        <label class="text-uppercase small fw-semibold mb-1 d-block" style="color: var(--fu-on-surface-variant);">Department</label>
                        <div style="color: var(--fu-on-surface);"><?= esc((string) $ticket->department_name) ?></div>
                    </div>
                    <?php if (! empty($ticket->concern_type)): ?>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block" style="color: var(--fu-on-surface-variant);">Type of Concern</label>
                        <div style="color: var(--fu-on-surface);"><?= esc((string) $ticket->concern_type) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block" style="color: var(--fu-on-surface-variant);">Created</label>
                        <div style="color: var(--fu-on-surface-variant);"><?= date('M j, Y g:i A', strtotime($ticket->created_at)) ?></div>
                    </div>
                </div>
            </div>

            <?php if (! empty($timeline)): ?>
            <div class="card-fu mb-4">
                <div class="d-flex justify-between items-center px-3 py-3" style="border-bottom: 1px solid var(--fu-outline-variant); background-color: var(--fu-surface-container-low);">
                    <h6 class="fw-semibold mb-0" style="color: var(--fu-primary);">Timeline</h6>
                </div>
                <div class="p-3">
                    <div class="timeline-list">
                        <?php foreach ($timeline as $i => $event): ?>
                        <div class="timeline-item <?= $event['type'] ?>">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <i class="fas <?= $event['icon'] ?> timeline-icon"></i>
                                    <span class="timeline-label"><?= $event['label'] ?></span>
                                </div>
                                <small style="color: var(--fu-on-surface-variant);"><?= date('M j, g:i A', strtotime($event['timestamp'])) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($feedback): ?>
            <div class="card-fu mb-4">
                <div class="d-flex justify-between items-center px-3 py-3" style="border-bottom: 1px solid var(--fu-outline-variant); background-color: var(--fu-surface-container-low);">
                    <h6 class="fw-semibold mb-0" style="color: var(--fu-primary);">Your Rating</h6>
                </div>
                <div class="p-3 text-center">
                    <div class="star-display mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $feedback->rating ? 'star-filled' : 'star-empty' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <?php if ($feedback->comment): ?>
                        <p class="mb-0" style="color: var(--fu-on-surface-variant); font-style: italic;">"<?= esc($feedback->comment) ?>"</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
<?php $this->section('scripts') ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="<?= base_url('assets/js/ticket-view.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.quill-editor').forEach(function (el) {
            var quill = new Quill(el, {
                theme: 'snow',
                modules: { toolbar: [['bold','italic','underline'], [{list:'ordered'},{list:'bullet'}], ['link']] },
                placeholder: 'Write your message...',
            });
            el.__quill = quill;
            var form = el.closest('form');
            var hidden = form.querySelector('.quill-hidden');
            form.addEventListener('submit', function () {
                hidden.value = quill.root.innerHTML;
            });
        });
        document.querySelectorAll('.quill-submit').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                var form = btn.closest('form');
                var quill = form.querySelector('.ql-editor');
                if (quill && quill.innerHTML.trim() === '<p><br></p>') {
                    e.preventDefault();
                    alert('Please enter a message.');
                }
            });
        });
    });
</script>
<?php $this->endSection() ?>
