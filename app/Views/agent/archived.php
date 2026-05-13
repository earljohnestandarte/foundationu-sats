<?= $this->extend('layout/main') ?>
<?php /** @var object[] $tickets */ ?>
<?php $this->section('title') ?>Archived Concerns - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>agent<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">Archived Concerns</h3>
                <p style="color: var(--fu-on-surface-variant);">Closed and archived concerns for your department.</p>
            </div>
            <a href="<?= site_url('agent/dashboard') ?>" class="btn btn-fu-primary d-flex align-items-center gap-2">
                <i class="fas fa-list"></i> Active Concerns
            </a>
        </div>
    </div>
    <div class="card-fu">
        <?php if (empty($tickets)): ?>
            <div class="p-5 text-center">
                <i class="fas fa-archive fa-3x mb-3" style="color: var(--fu-on-surface-variant);"></i>
                <h5 class="text-muted mb-2">No archived concerns</h5>
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-item" onclick="window.location='<?= site_url('agent/view/' . $ticket->id) ?>'">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fw-semibold" style="color: var(--fu-primary);">#FAU-<?= str_pad(esc((string)$ticket->id), 4, '0', STR_PAD_LEFT) ?></span>
                                <h5 class="fw-semibold mb-0" style="color: var(--fu-on-surface); font-size: 18px;"><?= esc((string)$ticket->subject) ?></h5>
                            </div>
                            <p class="mb-0" style="color: var(--fu-on-surface-variant); font-size: 14px;">From: <?= esc((string)$ticket->requester_name) ?></p>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-wrap align-items-center gap-4 justify-content-end">
                                <div class="text-end d-none d-sm-block">
                                    <p class="mb-1 text-uppercase" style="color: var(--fu-on-surface-variant); font-size: 12px;">Closed</p>
                                    <p class="mb-0 fw-semibold" style="color: var(--fu-on-surface);"><?= date('M j, Y', strtotime($ticket->archived_at)) ?></p>
                                </div>
                                <span class="badge-fu closed">Closed</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
