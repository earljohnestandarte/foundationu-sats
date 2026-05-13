<?= $this->extend('layout/main') ?>
<?php $this->section('title') ?>Response Templates - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>templates<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h3 class="fw-bold mb-1" style="color:var(--fu-primary);font-size:28px;">Response Templates</h3>
        <p style="color:var(--fu-on-surface-variant);">Canned responses for quick replies.</p></div>
        <button class="btn btn-fu-primary" onclick="document.getElementById('createTemplateForm').classList.toggle('d-none')"><i class="fas fa-plus"></i> New Template</button>
    </div>
    <div class="card-fu mb-4 d-none" id="createTemplateForm">
        <div class="px-4 py-3 card-header-section"><h6 class="fw-semibold mb-0">New Template</h6></div>
        <div class="p-4"><?= form_open('sao/templates/create') ?>
            <div class="row g-3">
                <?php if($isAdmin): ?><div class="col-md-3"><select name="department_id" class="form-select"><option value="">All Departments</option><?php foreach($departments as $d): ?><option value="<?= $d->id ?>"><?= esc($d->name) ?></option><?php endforeach; ?></select></div><?php endif; ?>
                <div class="col-md-<?= $isAdmin?'3':'4' ?>"><input name="title" class="form-control" placeholder="Template Title" required></div>
                <div class="col-md-<?= $isAdmin?'4':'5' ?>"><textarea name="message" class="form-control" rows="2" placeholder="Response text..." required></textarea></div>
                <div class="col-md-2"><button type="submit" class="btn btn-fu-primary w-100">Create</button></div>
            </div>
        <?= form_close() ?></div>
    </div>
    <div class="card-fu"><div class="p-0"><table class="table mb-0"><thead class="table-light"><tr><th>Title</th><th>Message</th><th style="width:80px;"></th></tr></thead><tbody>
        <?php foreach($templates as $t): ?>
        <tr><td class="fw-semibold"><?= esc($t->title) ?></td><td style="max-width:500px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= esc(strip_tags($t->message)) ?></td>
            <td><form action="<?= site_url('sao/templates/delete/'.$t->id) ?>" method="post" onsubmit="return confirm('Delete template?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr>
        <?php endforeach; ?>
    </tbody></table></div></div>
</section>
<?= $this->endSection() ?>
