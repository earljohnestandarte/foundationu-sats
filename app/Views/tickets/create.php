<?= $this->extend('layout/main') ?>
<?php $this->section('title') ?>Submit a Ticket - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>create<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="<?= site_url('student/dashboard') ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">Submit a New Ticket</h3>
        <p style="color: var(--fu-on-surface-variant);">Describe your issue and we'll get back to you as soon as possible.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-fu">
                <div class="p-4">
                    <?= form_open('student/tickets/store') ?>

                    <div class="mb-4">
                        <label for="office_id" class="form-label fw-semibold">Office</label>
                        <?php $officeOptions = $officeOptions ?? []; ?>
                        <?= form_dropdown('office_id', $officeOptions, set_value('office_id'), ['class' => 'form-select', 'id' => 'office_id']) ?>
                        <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('office_id') : '' ?></div>
                    </div>

                    <div class="mb-4">
                        <label for="subject" class="form-label fw-semibold">Subject</label>
                        <?= form_input('subject', set_value('subject'), ['class' => 'form-control', 'id' => 'subject', 'placeholder' => 'Briefly describe your issue']) ?>
                        <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('subject') : '' ?></div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <?= form_textarea('description', set_value('description'), ['class' => 'form-control', 'id' => 'description', 'rows' => 6, 'placeholder' => 'Provide detailed information about your issue...']) ?>
                        <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('description') : '' ?></div>
                    </div>

                    <div class="mb-4">
                        <label for="priority" class="form-label fw-semibold">Priority</label>
                        <?= form_dropdown('priority', ['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High', 'Urgent' => 'Urgent'], set_value('priority', 'Medium'), ['class' => 'form-select', 'id' => 'priority']) ?>
                        <div class="form-text" style="color: var(--fu-error);"><?= isset($validation) ? $validation->getError('priority') : '' ?></div>
                    </div>

                    <button type="submit" class="btn btn-fu-primary d-flex align-items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Submit Ticket
                    </button>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>