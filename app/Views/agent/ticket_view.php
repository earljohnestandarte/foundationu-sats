<?= $this->extend('layout/main') ?>
<?php /** @var object $ticket */ ?>
<?php /** @var object[] $replies */ ?>
<?php /** @var object[] $agents */ ?>
<?php /** @var array $timeline */ ?>
<?php /** @var object|null $feedback */ ?>
<?php $this->section('title') ?>Concern #<?= esc((string) $ticket->id) ?><?php $this->endSection() ?>
<?php $this->section('activeNav') ?>agent<?php $this->endSection() ?>
<?php $this->section('css') ?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="<?= site_url('agent/dashboard') ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <h3 class="fw-bold mb-0">#FAU-<?= str_pad(esc((string)$ticket->id), 4, '0', STR_PAD_LEFT) ?>: <?= esc((string) $ticket->subject) ?></h3>
            <form action="<?= site_url('agent/updateStatus/' . $ticket->id) ?>" method="post" class="d-flex gap-2 align-items-center">
                <?= csrf_field() ?>
                <select name="status" class="form-select min-width-180">
                    <option value="Open" <?= $ticket->status === 'Open' ? 'selected' : '' ?>>Open</option>
                    <option value="In Progress" <?= $ticket->status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Pending" <?= $ticket->status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Resolved" <?= $ticket->status === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="Closed" <?= $ticket->status === 'Closed' ? 'selected' : '' ?>>Closed</option>
                </select>
                <button type="submit" class="btn btn-fu-primary">Update</button>
            </form>
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
                        <small class="d-block" style="color: var(--fu-on-surface-variant);">
                            Response due by <?= date('M j, g:i A', $slaDue) ?>
                            <?php if ($ticket->first_response_at): ?>
                                | First response: <?= date('M j, g:i A', strtotime($ticket->first_response_at)) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card-fu mb-4">
                <div class="p-3">
                    <div class="mb-3">
                        <h6 class="text-uppercase fw-semibold mb-3">Description</h6>
                        <p style="line-height: 1.7;"><?= nl2br(esc((string) $ticket->description)) ?></p>
                        <?php if (!empty($ticketAttachments)): ?>
                        <div class="mt-3">
                            <h6 class="text-uppercase fw-semibold mb-2" style="font-size:12px;">Attachments</h6>
                            <div class="fu-attachments">
                                <?php foreach ($ticketAttachments as $att): ?>
                                <?php $isImg = in_array($att['mime_type'], ['image/jpeg','image/png','image/gif','image/webp']); ?>
                                <a href="<?= site_url('attachment/download/' . $att['id']) ?>" class="fu-attachment-item<?= $isImg ? ' is-image' : '' ?>" target="_blank">
                                    <?php if ($isImg): ?>
                                    <img src="<?= site_url('attachment/download/' . $att['id']) ?>" alt="<?= esc($att['original_name']) ?>">
                                    <?php else: ?>
                                    <i class="fas <?= \App\Models\AttachmentModel::getIcon($att['mime_type']) ?>"></i>
                                    <?php endif; ?>
                                    <span class="fu-att-name"><?= esc($att['original_name']) ?></span>
                                    <span class="fu-att-size"><?= \App\Models\AttachmentModel::formatSize((int)$att['file_size']) ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <div class="card-fu">
                <div class="d-flex justify-between items-center px-3 py-3 card-header-section">
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
                            foreach ($replies as $reply):
                                $roleLabel = ($reply->author_role ?? '') === 'agent' ? 'Agent' : (($reply->author_role ?? '') === 'admin' ? 'Admin' : 'Student');
                                $roleBadgeClass = ($reply->author_role ?? '') === 'agent' ? 'agent' : (($reply->author_role ?? '') === 'admin' ? 'admin' : 'student');
                                $isInternal = !empty($reply->is_internal);
                            ?>
                                <div class="reply-bubble<?= $depth ? ' depth-1' : '' ?><?= $isInternal ? ' internal' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: var(--fu-surface-container-low);">
                                                <i class="fas fa-user" style="color: var(--fu-on-surface-variant);"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <strong><?= esc((string) $reply->author_name) ?></strong>
                                                    <span class="badge-fu signature-badge <?= $roleBadgeClass ?>"><?= $roleLabel ?></span>
                                                    <?php if ($isInternal): ?>
                                                    <span class="internal-badge"><i class="fas fa-lock me-1"></i>Internal Note</span>
                                                    <?php endif; ?>
                                                </div>
                                                <small><?= date('M j, Y g:i A', strtotime($reply->created_at)) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="reply-content"><?= ($reply->message) ?></div>
                                    <?php if (!empty($reply->attachments)): ?>
                                    <div class="fu-attachments mt-2">
                                        <?php foreach ($reply->attachments as $att): ?>
                                        <?php $isImg = in_array($att['mime_type'], ['image/jpeg','image/png','image/gif','image/webp']); ?>
                                        <a href="<?= site_url('attachment/download/' . $att['id']) ?>" class="fu-attachment-item<?= $isImg ? ' is-image' : '' ?>" target="_blank">
                                            <?php if ($isImg): ?>
                                            <img src="<?= site_url('attachment/download/' . $att['id']) ?>" alt="<?= esc($att['original_name']) ?>">
                                            <?php else: ?>
                                            <i class="fas <?= \App\Models\AttachmentModel::getIcon($att['mime_type']) ?>"></i>
                                            <?php endif; ?>
                                            <span class="fu-att-name"><?= esc($att['original_name']) ?></span>
                                            <span class="fu-att-size"><?= \App\Models\AttachmentModel::formatSize((int)$att['file_size']) ?></span>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="signature-line mt-2 pt-2" style="border-top: 1px solid var(--fu-outline-variant);">
                                        <small style="color: var(--fu-on-surface-variant);">
                                            <i class="fas fa-building me-1"></i> Foundation University — Student Affairs Ticketing System
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none reply-toggle mt-2" data-target="reply-form-<?= $reply->id ?>">
                                        <i class="fas fa-reply me-1"></i> Reply
                                    </button>
                                    <div class="reply-form reply-form-<?= $reply->id ?> mt-3">
                                        <?= form_open('agent/addReply/' . $ticketId) ?>
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
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-semibold mb-0">Add a new reply</h6>
                        <button type="button" id="internalToggle" class="btn btn-outline-secondary internal-toggle-btn" title="Toggle internal note">
                            <i class="fas fa-lock me-1"></i> Internal Note
                        </button>
                    </div>
                    <?= form_open('agent/addReply/' . $ticket->id, ['enctype' => 'multipart/form-data', 'id' => 'main-reply-form']) ?>
                    <?= csrf_field() ?>
                    <input type="hidden" name="is_internal" id="isInternalInput" value="0">
                    <div class="mb-3">
                        <div class="quill-editor" id="quill-main"></div>
                        <textarea name="message" class="quill-hidden" style="display:none;"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="fu-dropzone" id="reply-dropzone">
                            <input type="file" name="attachments[]" id="reply-attachments" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.txt">
                            <i class="fas fa-paperclip fu-dz-icon" style="font-size:20px;"></i>
                            <p class="fu-dz-label" style="font-size:13px;"><strong>Attach files</strong> or drag &amp; drop</p>
                            <p class="fu-dz-hint">Max 5 files · 5 MB each</p>
                        </div>
                        <div class="fu-file-preview" id="replyFilePreview"></div>
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
                    <form action="<?= site_url('agent/escalate/'.$ticket->id) ?>" method="post">
                        <?= csrf_field() ?>
                        <textarea name="reason" class="form-control mb-2" rows="2" placeholder="Reason for escalation (optional)"></textarea>
                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm">Escalate to Administration</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <div class="card-fu mb-4">
                <div class="d-flex justify-between items-center px-3 py-3 card-header-section">
                    <h6 class="fw-semibold mb-0">Concern Details</h6>
                </div>
                <div class="p-3">
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Requester</label>
                        <div><?= esc((string) $ticket->requester_name) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Status</label>
                        <?php
                        $statusBadgeClass = 'open';
                        if ($ticket->status === 'In Progress') $statusBadgeClass = 'in-progress';
                        elseif ($ticket->status === 'Pending') $statusBadgeClass = 'pending';
                        elseif ($ticket->status === 'Resolved') $statusBadgeClass = 'resolved';
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
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Department</label>
                        <div><?= esc((string) $ticket->department_name) ?></div>
                    </div>
                    <?php if (! empty($ticket->concern_type)): ?>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Type of Concern</label>
                        <div><?= esc((string) $ticket->concern_type) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Assigned To</label>
                        <div><?= $ticket->resolver_name ? esc((string) $ticket->resolver_name) : '<span class="text-muted-unassigned">Unassigned</span>' ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Created</label>
                        <div><?= date('M j, Y g:i A', strtotime($ticket->created_at)) ?></div>
                    </div>
                    <?php if ($ticket->resolved_at): ?>
                    <div class="mb-3">
                        <label class="text-uppercase small fw-semibold mb-1 d-block">Resolved</label>
                        <div style="color: var(--fu-success);"><?= date('M j, Y g:i A', strtotime($ticket->resolved_at)) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-fu mb-4">
                <div class="d-flex justify-between items-center px-3 py-3 card-header-section">
                    <h6 class="fw-semibold mb-0">Assign Concern</h6>
                </div>
                <div class="p-3">
                    <form action="<?= site_url('agent/assign/' . $ticket->id) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="resolver_id" class="form-label fw-semibold">Assign to Agent</label>
                            <select name="resolver_id" id="resolver_id" class="form-select" required>
                                <option value="">Select an agent...</option>
                                <option value="<?= session()->get('user_id') ?>">Assign to me</option>
                                <?php foreach ($agents as $agent): ?>
                                    <?php if ($agent->id != session()->get('user_id')): ?>
                                        <option value="<?= $agent->id ?>" <?= $ticket->resolver_id == $agent->id ? 'selected' : '' ?>>
                                            <?= esc((string) $agent->name) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-fu-primary w-100">Assign</button>
                    </form>
                </div>
            </div>

            <?php if (! empty($timeline)): ?>
            <div class="card-fu mb-4">
                <div class="d-flex justify-between items-center px-3 py-3 card-header-section">
                    <h6 class="fw-semibold mb-0">Timeline</h6>
                </div>
                <div class="p-3">
                    <div class="timeline-list">
                        <?php foreach ($timeline as $event): ?>
                        <div class="timeline-item <?= $event['type'] ?>">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas <?= $event['icon'] ?> timeline-icon"></i>
                                    <span><?= $event['label'] ?></span>
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
                <div class="d-flex justify-between items-center px-3 py-3 card-header-section">
                    <h6 class="fw-semibold mb-0">Student Rating</h6>
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
<?php $this->endSection() ?>
<?php $this->section('scripts') ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="<?= base_url('assets/js/ticket-view.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Quill editors ──────────────────────────────────────
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

        // ── Internal note toggle ───────────────────────────────
        const toggleBtn    = document.getElementById('internalToggle');
        const internalInput = document.getElementById('isInternalInput');
        const replyForm    = document.getElementById('main-reply-form');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const active = toggleBtn.classList.toggle('active');
                internalInput.value = active ? '1' : '0';
                replyForm.classList.toggle('is-internal', active);
                toggleBtn.innerHTML = active
                    ? '<i class="fas fa-lock me-1"></i> Internal Note <i class="fas fa-check ms-1"></i>'
                    : '<i class="fas fa-lock me-1"></i> Internal Note';
            });
        }

        // ── Reply file drop-zone ───────────────────────────────
        const replyDropzone = document.getElementById('reply-dropzone');
        const replyFileInput = document.getElementById('reply-attachments');
        const replyPreview  = document.getElementById('replyFilePreview');
        let replyFiles = [];

        function formatSize(b) {
            if (b < 1024) return b + ' B';
            if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
            return (b/1048576).toFixed(1) + ' MB';
        }
        function getIcon(n) {
            const e = n.split('.').pop().toLowerCase();
            if (['jpg','jpeg','png','gif','webp'].includes(e)) return 'fa-file-image';
            if (e === 'pdf') return 'fa-file-pdf';
            if (['doc','docx'].includes(e)) return 'fa-file-word';
            return 'fa-file';
        }
        function renderReplyChips() {
            replyPreview.innerHTML = '';
            replyFiles.forEach((f, i) => {
                const chip = document.createElement('div');
                chip.className = 'fu-file-chip';
                chip.innerHTML = `<i class="fas ${getIcon(f.name)}"></i><span class="fu-chip-name">${f.name}</span><span class="fu-chip-size">${formatSize(f.size)}</span><button type="button" class="fu-chip-remove" data-idx="${i}"><i class="fas fa-times"></i></button>`;
                replyPreview.appendChild(chip);
            });
            const dt = new DataTransfer();
            replyFiles.forEach(f => dt.items.add(f));
            replyFileInput.files = dt.files;
        }
        if (replyPreview) {
            replyPreview.addEventListener('click', e => {
                const btn = e.target.closest('.fu-chip-remove');
                if (!btn) return;
                replyFiles.splice(parseInt(btn.dataset.idx), 1);
                renderReplyChips();
            });
        }
        if (replyFileInput) {
            replyFileInput.addEventListener('change', () => {
                Array.from(replyFileInput.files).forEach(f => {
                    if (replyFiles.length < 5 && f.size <= 5*1024*1024) replyFiles.push(f);
                });
                renderReplyChips();
            });
        }
        if (replyDropzone) {
            replyDropzone.addEventListener('dragover', e => { e.preventDefault(); replyDropzone.classList.add('dragover'); });
            replyDropzone.addEventListener('dragleave', () => replyDropzone.classList.remove('dragover'));
            replyDropzone.addEventListener('drop', e => {
                e.preventDefault();
                replyDropzone.classList.remove('dragover');
                Array.from(e.dataTransfer.files).forEach(f => {
                    if (replyFiles.length < 5 && f.size <= 5*1024*1024) replyFiles.push(f);
                });
                renderReplyChips();
            });
        }
    });
</script>
<?php $this->endSection() ?>

