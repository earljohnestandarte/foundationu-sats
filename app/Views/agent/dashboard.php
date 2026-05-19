<?= $this->extend('layout/main') ?>
<?php /** @var object[] $tickets */ ?>
<?php $this->section('title') ?>Agent Dashboard - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>agent<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">Agent Dashboard</h3>
        <p style="color: var(--fu-on-surface-variant);">Review concerns for your department and take action with a streamlined view.</p>
    </div>

    {{-- Search Bar (#10) --}}
    <form method="get" action="<?= site_url('agent/dashboard') ?>" class="mb-3">
        <div class="input-group" style="max-width: 480px;">
            <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Search by subject, student name, or status…"
                value="<?= esc($query ?? '') ?>"
                autocomplete="off"
            >
            <button class="btn btn-outline-secondary" type="submit">
                <i class="fas fa-search"></i>
            </button>
            <?php if (!empty($query)): ?>
                <a href="<?= site_url('agent/dashboard') ?>" class="btn btn-outline-danger" title="Clear search">
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
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom: 1px solid var(--fu-outline-variant); background-color: var(--fu-surface-container-low);">
            <h4 class="fw-semibold mb-0" style="color: var(--fu-primary); font-size: 20px;">Department Concerns</h4>
            <span class="badge bg-secondary"><?= count($tickets) ?> ticket<?= count($tickets) !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="p-5 text-center">
                <i class="fas fa-check-circle fa-3x mb-3" style="color: var(--fu-on-surface-variant);"></i>
                <?php if (!empty($query)): ?>
                    <h5 class="text-muted mb-2">No results found</h5>
                    <p class="text-muted mb-4">No concerns match "<strong><?= esc($query) ?></strong>".</p>
                    <a href="<?= site_url('agent/dashboard') ?>" class="btn btn-fu-primary">Clear search</a>
                <?php else: ?>
                    <h5 class="text-muted mb-2">All caught up!</h5>
                    <p class="text-muted">No concerns are currently assigned to your department.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-item" onclick="window.location='<?= site_url('agent/view/' . $ticket->id) ?>'">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fw-semibold" style="color: var(--fu-primary);">#FAU-<?= str_pad(esc((string)$ticket->id), 4, '0', STR_PAD_LEFT) ?></span>
                                <h5 class="fw-semibold mb-0" style="color: var(--fu-on-surface); font-size: 18px;"><?= esc((string)$ticket->subject) ?></h5>
                                <?php if ($ticket->is_escalated ?? false): ?>
                                    <span class="badge bg-danger ms-1"><i class="fas fa-flag me-1"></i>Escalated</span>
                                <?php endif; ?>
                            </div>
                            <p class="mb-0" style="color: var(--fu-on-surface-variant); font-size: 14px;">From: <?= esc((string)$ticket->requester_name) ?></p>
                        </div>
                        <div class="col-md-5">
                            <div class="d-flex flex-wrap align-items-center gap-4 justify-content-end">
                                <div class="d-none d-sm-block">
                                    <p class="mb-1 text-uppercase" style="color: var(--fu-on-surface-variant); font-size: 12px;">Department</p>
                                    <p class="mb-0 fw-semibold" style="color: var(--fu-on-surface);"><?= esc((string)$ticket->department_name) ?></p>
                                </div>
                                <div class="d-none d-sm-block">
                                    <p class="mb-1 text-uppercase" style="color: var(--fu-on-surface-variant); font-size: 12px;">Assigned</p>
                                    <p class="mb-0" style="color: var(--fu-on-surface);"><?= $ticket->resolver_name ? esc((string)$ticket->resolver_name) : '<span class="text-muted">Unassigned</span>' ?></p>
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
