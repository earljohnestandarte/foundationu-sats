<?= $this->extend('layout/main') ?>
<?php $this->section('title') ?>Reports - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>reports<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">Reports</h3>
    <p style="color: var(--fu-on-surface-variant);">View response times and resolution metrics.</p>

    <div class="card-fu mb-4">
        <div class="p-4">
            <form method="get" action="<?= site_url('sao/reports') ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">From</label>
                    <input type="date" name="from" class="form-control" value="<?= esc($from) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">To</label>
                    <input type="date" name="to" class="form-control" value="<?= esc($to) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d->id ?>" <?= $departmentFilter == $d->id ? 'selected' : '' ?>><?= esc($d->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-fu-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="stat-card"><p class="text-uppercase fw-semibold" style="font-size:12px;color:var(--fu-on-surface-variant);">Total Concerns</p><span class="fw-bold" style="font-size:28px;color:var(--fu-primary);"><?= $total ?></span></div></div>
        <div class="col-md-4"><div class="stat-card"><p class="text-uppercase fw-semibold" style="font-size:12px;color:var(--fu-on-surface-variant);">Resolved</p><span class="fw-bold" style="font-size:28px;color:var(--fu-success);"><?= $resolved ?></span></div></div>
        <div class="col-md-4"><div class="stat-card"><p class="text-uppercase fw-semibold" style="font-size:12px;color:var(--fu-on-surface-variant);">SLA Overdue</p><span class="fw-bold" style="font-size:28px;color:var(--fu-error);"><?= $overdue ?></span></div></div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-fu">
                <div class="p-4 text-center">
                    <h6 class="fw-semibold mb-3">Average Response Time</h6>
                    <?php if ($avgResponseMinutes): ?>
                        <span class="fw-bold" style="font-size:36px;color:var(--fu-primary);"><?= $avgResponseMinutes >= 60 ? round($avgResponseMinutes/60,1).'h' : $avgResponseMinutes.'m' ?></span>
                        <p class="mb-0" style="color:var(--fu-on-surface-variant);"><?= $avgResponseMinutes ?> minutes</p>
                    <?php else: ?>
                        <p style="color:var(--fu-on-surface-variant);">No data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-fu">
                <div class="p-4 text-center">
                    <h6 class="fw-semibold mb-3">Average Resolution Time</h6>
                    <?php if ($avgResolutionMinutes): ?>
                        <span class="fw-bold" style="font-size:36px;color:var(--fu-primary);"><?= $avgResolutionMinutes >= 60 ? round($avgResolutionMinutes/60,1).'h' : $avgResolutionMinutes.'m' ?></span>
                        <p class="mb-0" style="color:var(--fu-on-surface-variant);"><?= $avgResolutionMinutes ?> minutes</p>
                    <?php else: ?>
                        <p style="color:var(--fu-on-surface-variant);">No data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
