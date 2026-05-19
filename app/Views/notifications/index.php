<?= $this->extend('layout/main') ?>
<?php /** @var object[] $notifications */ ?>
<?php $this->section('title') ?>Notifications - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?><?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">Notifications</h3>
                <p style="color: var(--fu-on-surface-variant);">Review your recent updates and ticket activity.</p>
            </div>
        </div>
    </div>

    <div class="card-fu">
        <?php if (empty($notifications)): ?>
            <div class="p-5 text-center">
                <i class="fas fa-bell-slash fa-3x mb-3" style="color: var(--fu-on-surface-variant);"></i>
                <h5 class="text-muted mb-2">No notifications yet</h5>
                <p class="text-muted mb-0">You do not have any notifications at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <?php
                    $isStudent = session()->get('user_role') === 'student';
                    $targetUrl = $notification->ticket_id
                        ? site_url($isStudent ? 'student/tickets/' . $notification->ticket_id : 'agent/view/' . $notification->ticket_id)
                        : site_url('notification');
                ?>
                <a
                    class="ticket-item notification-item d-block text-decoration-none"
                    href="<?= $targetUrl ?>"
                    data-id="<?= $notification->id ?>"
                    data-ticket-id="<?= $notification->ticket_id ?>"
                    style="<?= ! $notification->is_read ? 'background-color: var(--fu-surface-container-low);' : '' ?>"
                >
                    <div class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-start gap-3">
                                <div class="mt-1">
                                    <i class="fas fa-bell" style="color: <?= ! $notification->is_read ? 'var(--fu-error)' : 'var(--fu-on-surface-variant)' ?>;"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-semibold mb-0" style="color: var(--fu-on-surface); font-size: 16px;"><?= esc((string) $notification->message) ?></h5>
                                        <?php if (! $notification->is_read): ?>
                                            <span class="badge rounded-pill" style="background-color: var(--fu-error);">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0" style="color: var(--fu-on-surface-variant); font-size: 13px;">
                                        <?= date('M j, Y g:i A', strtotime($notification->created_at)) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-md-end">
                                <?php if ($notification->ticket_id): ?>
                                    <span class="fw-semibold" style="color: var(--fu-primary);">Open ticket #<?= esc((string) $notification->ticket_id) ?></span>
                                <?php else: ?>
                                    <span style="color: var(--fu-on-surface-variant);">Notification</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?php $this->endSection() ?>
