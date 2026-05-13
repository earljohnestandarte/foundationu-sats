<?= $this->extend('layout/main') ?>
<?php $this->section('title') ?>Submit a Concern - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>create<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="<?= site_url('student/dashboard') ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">Submit a Concern</h3>
        <p style="color: var(--fu-on-surface-variant);">Describe your concern and we'll get back to you as soon as possible.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-fu">
                <?= form_open('student/tickets/store') ?>
                <div class="p-4">
                    <div class="d-flex justify-content-end mb-4">
                        <button type="submit" class="btn btn-fu-primary d-flex align-items-center gap-2">
                            <i class="fas fa-paper-plane"></i> Submit Concern
                        </button>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="department_id" class="form-label fw-semibold">Department</label>
                            <?php $departmentOptions = $departmentOptions ?? []; ?>
                            <?= form_dropdown('department_id', $departmentOptions, set_value('department_id'), ['class' => 'form-select', 'id' => 'department_id']) ?>
                            <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('department_id') : '' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label for="concern_type" class="form-label fw-semibold">Type of Concern</label>
                            <?php $concernTypes = $concernTypes ?? []; ?>
                            <?= form_dropdown('concern_type', $concernTypes, set_value('concern_type'), ['class' => 'form-select', 'id' => 'concern_type']) ?>
                            <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('concern_type') : '' ?></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="subject" class="form-label fw-semibold">Subject</label>
                        <?= form_input('subject', set_value('subject'), ['class' => 'form-control', 'id' => 'subject', 'placeholder' => 'Briefly describe your concern']) ?>
                        <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('subject') : '' ?></div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <?= form_textarea('description', set_value('description'), ['class' => 'form-control', 'id' => 'description', 'rows' => 6, 'placeholder' => 'Provide detailed information about your concern...']) ?>
                        <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('description') : '' ?></div>
                    </div>

                    <div class="mb-4">
                        <label for="priority" class="form-label fw-semibold">Priority</label>
                        <?= form_dropdown('priority', ['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High', 'Urgent' => 'Urgent'], set_value('priority', 'Medium'), ['class' => 'form-select', 'id' => 'priority']) ?>
                        <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('priority') : '' ?></div>
                    </div>

                    <button type="submit" class="btn btn-fu-primary d-flex align-items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Submit Concern
                    </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
