<?= $this->extend('layout/main') ?>
<?php $this->section('title') ?>SAO Dashboard - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>dashboard<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;"><?= $isAdmin ? 'Admin' : 'SAO' ?> Dashboard</h3>
    <p style="color: var(--fu-on-surface-variant);">Overview of all departments and concerns.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="stat-card"><p class="text-uppercase fw-semibold mb-2" style="font-size:12px;color:var(--fu-on-surface-variant);">Total</p><span class="fw-bold" style="color:var(--fu-primary);font-size:28px;"><?= $totalTickets ?></span></div></div>
        <div class="col-6 col-md"><div class="stat-card"><p class="text-uppercase fw-semibold mb-2" style="font-size:12px;color:var(--fu-on-surface-variant);">Open</p><span class="fw-bold" style="color:var(--fu-info);font-size:28px;"><?= $openCount ?></span></div></div>
        <div class="col-6 col-md"><div class="stat-card"><p class="text-uppercase fw-semibold mb-2" style="font-size:12px;color:var(--fu-on-surface-variant);">Resolved</p><span class="fw-bold" style="color:var(--fu-success);font-size:28px;"><?= $resolvedCount ?></span></div></div>
        <div class="col-6 col-md"><div class="stat-card"><p class="text-uppercase fw-semibold mb-2" style="font-size:12px;color:var(--fu-on-surface-variant);">SLA Overdue</p><span class="fw-bold" style="color:var(--fu-error);font-size:28px;"><?= $overdueCount ?></span></div></div>
        <div class="col-6 col-md"><div class="stat-card" style="border: 2px solid var(--fu-error);"><p class="text-uppercase fw-semibold mb-2" style="font-size:12px;color:var(--fu-on-surface-variant);">Escalated</p><span class="fw-bold" style="color:var(--fu-error);font-size:28px;"><?= $escalatedCount ?></span></div></div>
    </div>

    <?php if ($avgRating): ?>
    <div class="mb-4"><span class="fw-semibold">Average Rating:</span><span class="star-display ms-2"><?php for ($i=1;$i<=5;$i++): ?><i class="fas fa-star <?= $i <= round($avgRating) ? 'star-filled' : 'star-empty' ?>"></i><?php endfor; ?></span><small style="color:var(--fu-on-surface-variant);">(<?= $avgRating ?>)</small></div>
    <?php endif; ?>

    <?php if (! empty($escalatedTickets)): ?>
    <div class="card-fu mb-4" style="border: 2px solid var(--fu-error);">
        <div class="px-4 py-3" style="background-color: #fef2f2; border-bottom: 1px solid var(--fu-error);">
            <h6 class="fw-semibold mb-0" style="color: var(--fu-error);"><i class="fas fa-flag me-2"></i>Escalated Concerns</h6>
        </div>
        <div class="p-0">
            <?php foreach ($escalatedTickets as $t): ?>
            <div class="ticket-item" onclick="window.location='<?= site_url('agent/view/'.$t->id) ?>'" style="border-left: 3px solid var(--fu-error);">
                <div class="d-flex justify-content-between">
                    <div>
                        <span class="fw-semibold" style="color:var(--fu-primary);">#FAU-<?= str_pad(esc((string)$t->id),4,'0',STR_PAD_LEFT) ?></span>
                        <?= esc($t->subject) ?>
                        <small class="d-block" style="color:var(--fu-on-surface-variant);"><?= esc($t->requester_name) ?> — <?= esc($t->department_name ?? '—') ?></small>
                    </div>
                    <div class="d-flex gap-3 align-items-center">
                        <?php $sbc='open';if($t->status==='In Progress')$sbc='in-progress';elseif($t->status==='Pending')$sbc='pending';elseif($t->status==='Resolved')$sbc='resolved'; ?>
                        <span class="badge-fu <?= $sbc ?>"><?= esc($t->status) ?></span>
                        <i class="fas fa-flag" style="color:var(--fu-error);"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card-fu mb-4">
        <div class="px-4 py-3 card-header-section"><h6 class="fw-semibold mb-0">Department Overview</h6></div>
        <div class="p-0"><table class="table mb-0"><thead class="table-light"><tr><th>Department</th><th class="text-center">Total</th><th class="text-center">Open</th><th class="text-center">Resolved</th><th class="text-center">Overdue</th><th class="text-center">Rating</th></tr></thead><tbody>
            <?php foreach ($deptMetrics as $dm): ?>
            <tr><td class="fw-semibold"><?= esc($dm['department']->name) ?></td><td class="text-center"><?= $dm['total'] ?></td><td class="text-center" style="color:var(--fu-info);"><?= $dm['open'] ?></td><td class="text-center" style="color:var(--fu-success);"><?= $dm['resolved'] ?></td><td class="text-center" style="color:var(--fu-error);"><?= $dm['overdue'] ?></td><td class="text-center"><?php if ($dm['avg_rating']): ?><span class="star-display"><?php for($i=1;$i<=5;$i++):?><i class="fas fa-star <?= $i<=round($dm['avg_rating'])?'star-filled':'star-empty' ?>"></i><?php endfor;?></span><?php else:?><span style="color:var(--fu-on-surface-variant);">—</span><?php endif;?></td></tr>
            <?php endforeach; ?>
        </tbody></table></div>
    </div>

    <?php if(!empty($recentTickets)): ?>
    <div class="card-fu"><div class="px-4 py-3 card-header-section"><h6 class="fw-semibold mb-0">Recent Concerns</h6></div><div class="p-0">
        <?php foreach($recentTickets as $t): ?>
        <div class="ticket-item" onclick="window.location='<?= site_url('agent/view/'.$t->id) ?>'"><div class="d-flex justify-content-between"><div><span class="fw-semibold" style="color:var(--fu-primary);">#FAU-<?= str_pad(esc((string)$t->id),4,'0',STR_PAD_LEFT) ?></span> <?= esc($t->subject) ?></div><div class="d-flex gap-3"><small style="color:var(--fu-on-surface-variant);"><?= $t->department_name ?? '—' ?></small><?php $sbc='open';if($t->status==='In Progress')$sbc='in-progress';elseif($t->status==='Pending')$sbc='pending';elseif($t->status==='Resolved')$sbc='resolved';?><span class="badge-fu <?= $sbc ?>"><?= esc($t->status) ?></span></div></div></div>
        <?php endforeach; ?>
    </div></div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
