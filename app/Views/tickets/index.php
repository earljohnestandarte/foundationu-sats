<?= $this->extend('layout/main') ?>
<?php /** @var object[] $tickets */ ?>
<?php $this->section('title') ?>My Tickets - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>tickets<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">My Tickets</h3>
                <p style="color: var(--fu-on-surface-variant);">Track all your support requests.</p>
            </div>
            <a href="<?= site_url('student/tickets/create') ?>" class="btn btn-fu-primary d-flex align-items-center gap-2">
                <i class="fas fa-plus"></i> New Ticket
            </a>
        </div>
    </div>

    <div class="card-fu">
        <?php if (empty($tickets)): ?>
            <div class="p-5 text-center">
                <i class="fas fa-inbox fa-3x mb-3" style="color: var(--fu-on-surface-variant);"></i>
                <h5 class="text-muted mb-2">No tickets yet</h5>
                <p class="text-muted mb-4">You haven't submitted any support requests yet.</p>
                <a href="<?= site_url('student/tickets/create') ?>" class="btn btn-fu-primary">Submit your first ticket</a>
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-item" onclick="window.location='<?= site_url('student/tickets/' . $ticket->id) ?>'">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fw-semibold" style="color: var(--fu-primary);">#FAU-<?= str_pad(esc((string)$ticket->id), 4, '0', STR_PAD_LEFT) ?></span>
                                <h5 class="fw-semibold mb-0" style="color: var(--fu-on-surface); font-size: 18px;"><?= esc((string)$ticket->subject) ?></h5>
                            </div>
                            <p class="mb-0 text-truncate" style="color: var(--fu-on-surface-variant);"><?= esc((string)$ticket->description) ?></p>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-wrap align-items-center gap-4 justify-content-end">
                                <div class="text-end d-none d-sm-block">
                                    <p class="mb-1 text-uppercase" style="color: var(--fu-on-surface-variant); font-size: 12px;">Office</p>
                                    <p class="mb-0 fw-semibold" style="color: var(--fu-on-surface);"><?= esc((string)$ticket->office_name) ?></p>
                                </div>
                                <div class="text-end d-none d-sm-block">
                                    <p class="mb-1 text-uppercase" style="color: var(--fu-on-surface-variant); font-size: 12px;">Updated</p>
                                    <p class="mb-0 fw-semibold" style="color: var(--fu-on-surface);"><?= date('M j, Y', strtotime($ticket->updated_at ?? $ticket->created_at)) ?></p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php
                                    $statusBadgeClass = 'open';
                                    if ($ticket->status === 'In Progress') {
                                        $statusBadgeClass = 'in-progress';
                                    } elseif ($ticket->status === 'Resolved') {
                                        $statusBadgeClass = 'resolved';
                                    }
                                    ?>
                                    <span class="badge-fu <?= $statusBadgeClass ?>"><?= esc((string)$ticket->status) ?></span>
                                    <?php if ($ticket->priority === 'High' || $ticket->priority === 'Urgent'): ?>
                                        <i class="fas fa-exclamation-triangle" style="color: var(--fu-error);"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>