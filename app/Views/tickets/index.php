<?= $this->extend('layout/main') ?>
<?php /** @var object[] $tickets */ ?>
<?php $this->section('title') ?>My Concerns - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>tickets<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">My Concerns</h3>
                <p style="color: var(--fu-on-surface-variant);">Track all your submitted concerns.</p>
            </div>
            <a href="<?= site_url('student/tickets/create') ?>" class="btn btn-fu-primary d-flex align-items-center gap-2">
                <i class="fas fa-plus"></i> Submit Concern
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <form method="get" action="<?= site_url('student/tickets') ?>" class="mb-3">
        <div class="input-group" style="max-width: 480px;">
            <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Search by subject, status, or department…"
                value="<?= esc($query ?? '') ?>"
                autocomplete="off"
            >
            <button class="btn btn-outline-secondary" type="submit">
                <i class="fas fa-search"></i>
            </button>
            <?php if (!empty($query)): ?>
                <a href="<?= site_url('student/tickets') ?>" class="btn btn-outline-danger" title="Clear search">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php if (!empty($query)): ?>
            <p class="mt-2 text-muted" style="font-size: 13px;">
                Showing results for <strong><?= esc($query) ?></strong> — <?= count($tickets) ?> found.
            </p>
        <?php endif; ?>
    </form>

    <div class="card-fu">
        <?php if (empty($tickets)): ?>
            <div class="p-5 text-center">
                <i class="fas fa-inbox fa-3x mb-3" style="color: var(--fu-on-surface-variant);"></i>
                <?php if (!empty($query)): ?>
                    <h5 class="text-muted mb-2">No results found</h5>
                    <p class="text-muted mb-4">No concerns match "<strong><?= esc($query) ?></strong>".</p>
                    <a href="<?= site_url('student/tickets') ?>" class="btn btn-fu-primary">Clear search</a>
                <?php else: ?>
                    <h5 class="text-muted mb-2">No concerns yet</h5>
                    <p class="text-muted mb-4">You haven't submitted any concerns yet.</p>
                    <a href="<?= site_url('student/tickets/create') ?>" class="btn btn-fu-primary">Submit your first concern</a>
                <?php endif; ?>
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
                                    <p class="mb-1 text-uppercase" style="color: var(--fu-on-surface-variant); font-size: 12px;">Department</p>
                                    <p class="mb-0 fw-semibold" style="color: var(--fu-on-surface);"><?= esc((string)$ticket->department_name) ?></p>
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
                                    } elseif ($ticket->status === 'Pending') {
                                        $statusBadgeClass = 'pending';
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

            <?php if (!empty($pager)): ?>
                <div class="px-4 py-3" style="border-top: 1px solid var(--fu-outline-variant);">
                    <?= $pager->links('tickets', 'bootstrap_pagination') ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
