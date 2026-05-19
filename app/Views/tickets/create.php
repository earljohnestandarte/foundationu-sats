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
                <?= form_open('student/tickets/store', ['enctype' => 'multipart/form-data']) ?>

                <div class="p-4">

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

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Attachments <span style="font-weight:400;color:var(--fu-on-surface-variant);font-size:13px;">(optional — max 5 files, 5 MB each)</span></label>
                        <div class="fu-dropzone" id="dropzone">
                            <input type="file" name="attachments[]" id="attachments" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.txt">
                            <i class="fas fa-cloud-upload-alt fu-dz-icon"></i>
                            <p class="fu-dz-label"><strong>Click to upload</strong> or drag &amp; drop files here</p>
                            <p class="fu-dz-hint">JPG, PNG, PDF, DOC, TXT · Max 5 MB per file</p>
                        </div>
                        <div class="fu-file-preview" id="filePreview"></div>
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
<?php $this->section('scripts') ?>
<script>
(function () {
    const dropzone   = document.getElementById('dropzone');
    const fileInput  = document.getElementById('attachments');
    const preview    = document.getElementById('filePreview');
    let selectedFiles = [];

    function formatSize(bytes) {
        if (bytes < 1024)    return bytes + ' B';
        if (bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB';
        return (bytes/1048576).toFixed(1) + ' MB';
    }

    function getIcon(name) {
        const ext = name.split('.').pop().toLowerCase();
        if (['jpg','jpeg','png','gif','webp'].includes(ext)) return 'fa-file-image';
        if (ext === 'pdf') return 'fa-file-pdf';
        if (['doc','docx'].includes(ext)) return 'fa-file-word';
        return 'fa-file';
    }

    function renderChips() {
        preview.innerHTML = '';
        selectedFiles.forEach((file, i) => {
            const chip = document.createElement('div');
            chip.className = 'fu-file-chip';
            chip.innerHTML = `<i class="fas ${getIcon(file.name)}"></i>
                <span class="fu-chip-name">${file.name}</span>
                <span class="fu-chip-size">${formatSize(file.size)}</span>
                <button type="button" class="fu-chip-remove" data-idx="${i}" title="Remove"><i class="fas fa-times"></i></button>`;
            preview.appendChild(chip);
        });

        // Sync the actual input (rebuild DataTransfer)
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    preview.addEventListener('click', e => {
        const btn = e.target.closest('.fu-chip-remove');
        if (!btn) return;
        const idx = parseInt(btn.dataset.idx);
        selectedFiles.splice(idx, 1);
        renderChips();
    });

    fileInput.addEventListener('change', () => {
        const incoming = Array.from(fileInput.files);
        incoming.forEach(f => {
            if (selectedFiles.length < 5 && f.size <= 5 * 1024 * 1024) {
                selectedFiles.push(f);
            }
        });
        renderChips();
    });

    dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        const dropped = Array.from(e.dataTransfer.files);
        dropped.forEach(f => {
            if (selectedFiles.length < 5 && f.size <= 5 * 1024 * 1024) {
                selectedFiles.push(f);
            }
        });
        renderChips();
    });
})();
</script>
<?php $this->endSection() ?>
