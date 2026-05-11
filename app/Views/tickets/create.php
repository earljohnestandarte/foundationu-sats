<?= $this->extend('layout/main') ?>
<?php $this->section('title') ?>Submit a Ticket<?php $this->endSection() ?>
<?php $this->section('content') ?>
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mb-0">Submit a New Ticket</h4>
    </div>
    <div class="card-body">
        <?= form_open('student/tickets/store') ?>

        <div class="mb-3">
            <label for="office_id" class="form-label">Office</label>
            <?php $officeOptions = $officeOptions ?? []; ?>
            <?= form_dropdown('office_id', $officeOptions, set_value('office_id'), ['class' => 'form-select', 'id' => 'office_id']) ?>
            <div class="form-text text-danger"><?= isset($validation) ? $validation->getError('office_id') : '' ?></div>
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <?= form_input('subject', set_value('subject'), ['class' => 'form-control', 'id' => 'subject']) ?>
            <div class="form-text text-danger"><?= isset($validation) ? $validation->getError('subject') : '' ?></div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <?= form_textarea('description', set_value('description'), ['class' => 'form-control', 'id' => 'description', 'rows' => 6]) ?>
            <div class="form-text text-danger"><?= isset($validation) ? $validation->getError('description') : '' ?></div>
        </div>

        <div class="mb-3">
            <label for="priority" class="form-label">Priority</label>
            <?= form_dropdown('priority', ['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High', 'Urgent' => 'Urgent'], set_value('priority', 'Medium'), ['class' => 'form-select', 'id' => 'priority']) ?>
            <div class="form-text text-danger"><?= isset($validation) ? $validation->getError('priority') : '' ?></div>
        </div>

        <button type="submit" class="btn btn-primary">Submit Ticket</button>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>