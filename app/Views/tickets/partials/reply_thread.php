<?php /** @var object $ticket */ ?>
<?php /** @var object[] $replies */ ?>
<?php if (empty($replies)): ?>
    <div class="p-5 text-center">
        <i class="fas fa-comments fa-3x mb-3" style="color: var(--fu-on-surface-variant);"></i>
        <p class="mb-0" style="color: var(--fu-on-surface-variant);">No replies yet. Please wait for an agent response.</p>
    </div>
<?php else: ?>
    <?php
    $renderReplies = function (array $items, int $depth = 0) use (&$renderReplies, $ticket) {
        foreach ($items as $reply):
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
                <div class="reply-content"><?= $reply->message ?></div>
                <div class="signature-line mt-2 pt-2" style="border-top: 1px solid var(--fu-outline-variant);">
                    <small style="color: var(--fu-on-surface-variant);">
                        <i class="fas fa-building me-1"></i> Foundation University — Student Affairs Ticketing System
                    </small>
                </div>
                <button type="button" class="btn btn-sm btn-link text-decoration-none reply-toggle mt-2" data-target="reply-form-<?= $reply->id ?>">
                    <i class="fas fa-reply me-1"></i> Reply
                </button>
                <div class="reply-form reply-form-<?= $reply->id ?> mt-3">
                    <?= form_open('student/tickets/' . $ticket->id . '/reply') ?>
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
                    <?php $renderReplies($reply->children, $depth + 1); ?>
                <?php endif; ?>
            </div>
        <?php
        endforeach;
    };
    $renderReplies($replies);
    ?>
<?php endif; ?>
