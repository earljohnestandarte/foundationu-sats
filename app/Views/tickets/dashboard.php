<?= $this->extend('layout/main') ?>
<?php /** @var object[] $tickets */ ?>
<?php /** @var int $activeCount */ ?>
<?php /** @var int $resolvedCount */ ?>
<?php $this->section('title') ?>Student Dashboard - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>dashboard<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <?php $session = session(); ?>
        <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">Welcome back, <?= esc((string)$session->get('user_name') ?? 'Student') ?></h3>
        <p style="color: var(--fu-on-surface-variant); font-size: 18px;">Here is a summary of your active support requests.</p>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="p-2 rounded" style="background-color: var(--fu-primary-container);">
                        <i class="fas fa-clock" style="color: var(--fu-on-primary-container); font-size: 24px;"></i>
                    </div>
                    <span class="fw-bold" style="color: var(--fu-primary); font-size: 24px;"><?= $activeCount ?></span>
                </div>
                <p class="mb-0 text-uppercase fw-semibold" style="color: var(--fu-on-surface-variant); font-size: 12px; letter-spacing: 0.05em;">Active Tickets</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="p-2 rounded" style="background-color: var(--fu-secondary);">
                        <i class="fas fa-check-double" style="color: var(--fu-on-secondary); font-size: 24px;"></i>
                    </div>
                    <span class="fw-bold" style="color: var(--fu-secondary); font-size: 24px;"><?= $resolvedCount ?></span>
                </div>
                <p class="mb-0 text-uppercase fw-semibold" style="color: var(--fu-on-surface-variant); font-size: 12px; letter-spacing: 0.05em;">Resolved This Semester</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="p-2 rounded" style="background-color: var(--fu-tertiary-container);">
                        <i class="fas fa-hourglass-half" style="color: var(--fu-on-tertiary-container); font-size: 24px;"></i>
                    </div>
                    <span class="fw-bold" style="color: var(--fu-primary); font-size: 24px;">4.2h</span>
                </div>
                <p class="mb-0 text-uppercase fw-semibold" style="color: var(--fu-on-surface-variant); font-size: 12px; letter-spacing: 0.05em;">Avg Response Time</p>
            </div>
        </div>
    </div>
    
    <div class="card-fu mb-4">
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom: 1px solid var(--fu-outline-variant); background-color: var(--fu-surface-container-low);">
            <h4 class="fw-semibold mb-0" style="color: var(--fu-primary); font-size: 20px;">Active Tickets</h4>
            <div class="d-flex gap-2">
                <button class="d-flex align-items-center gap-2 px-3 py-2 bg-white border rounded text-sm fw-semibold" style="border-color: var(--fu-outline-variant);">
                    <i class="fas fa-filter" style="font-size: 18px;"></i> Filter
                </button>
            </div>
        </div>
        
        <?php if (empty($tickets)): ?>
            <div class="p-5 text-center">
                <i class="fas fa-inbox fa-3x mb-3" style="color: var(--fu-on-surface-variant);"></i>
                <h5 class="text-muted mb-2">No active tickets</h5>
                <p class="text-muted">You don't have any active support requests at the moment.</p>
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
                                    <p class="mb-1 text-uppercase" style="color: var(--fu-on-surface-variant); font-size: 12px;">Last Updated</p>
                                    <p class="mb-0 fw-semibold" style="color: var(--fu-on-surface);"><?= date('M j, g:i A', strtotime($ticket->updated_at ?? $ticket->created_at)) ?></p>
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
        
        <div class="px-4 py-3 text-center" style="background-color: var(--fu-surface-container-low);">
            <a href="<?= site_url('student/tickets') ?>" class="text-decoration-none fw-semibold" style="color: var(--fu-primary);">View All Historical Tickets</a>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="position-relative rounded-lg overflow-hidden h-60" style="height: 240px;">
                <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=800" alt="Academic Building" class="w-100 h-100 object-cover">
                <div class="position-absolute inset-0" style="background: linear-gradient(to top, rgba(87, 0, 0, 0.9), transparent);">
                    <div class="position-absolute bottom-0 left-0 right-0 p-4">
                        <h5 class="text-white fw-semibold mb-2" style="font-size: 20px;">Need help with Financial Aid?</h5>
                        <p class="text-white-80 mb-3" style="color: rgba(255,255,255,0.85);">Our advisors are available for walk-in consultations every Tuesday and Thursday.</p>
                        <button class="px-4 py-2 bg-white rounded fw-semibold" style="color: var(--fu-primary);">Book Appointment</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="p-4 rounded-lg d-flex flex-column justify-content-center" style="background-color: var(--fu-primary); color: var(--fu-on-primary);">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-lightbulb" style="font-size: 36px;"></i>
                    <h5 class="fw-semibold mb-0" style="font-size: 20px;">Knowledge Base Tip</h5>
                </div>
                <p class="mb-4">Did you know you can track your graduation progress directly through the 'Offices' portal? Check the Registrar's section for your latest degree audit.</p>
                <a href="#" class="text-decoration-none d-flex align-items-center gap-2 fw-bold" style="color: var(--fu-primary-fixed);">
                    Explore Knowledge Base
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<footer class="py-4 px-4 d-flex flex-column md:flex-row justify-content-between align-items-center" style="max-width: 1280px; margin: 0 auto; border-top: 1px solid var(--fu-outline-variant); margin-top: 24px;">
    <div class="d-flex align-items-center gap-2 mb-3 mb-md-0">
        <span class="fw-bold" style="color: var(--fu-primary);">SATS</span>
        <p class="mb-0" style="color: var(--fu-on-surface-variant); font-size: 12px;">© 2024 Foundation University Student Affairs. All rights reserved.</p>
    </div>
    <div class="d-flex gap-4">
        <a href="#" class="text-decoration-none fw-semibold" style="color: var(--fu-on-surface-variant); font-size: 14px;">Privacy Policy</a>
        <a href="#" class="text-decoration-none fw-semibold" style="color: var(--fu-on-surface-variant); font-size: 14px;">Terms of Service</a>
        <a href="#" class="text-decoration-none fw-semibold" style="color: var(--fu-on-surface-variant); font-size: 14px;">Accessibility</a>
    </div>
</footer>
<?= $this->endSection() ?>