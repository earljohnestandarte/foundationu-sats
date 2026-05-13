<?= $this->extend('layout/main') ?>
<?php /** @var bool $isAdmin */ ?>
<?php $this->section('title') ?>Department Management - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>departments<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h3 class="fw-bold mb-1" style="color:var(--fu-primary);font-size:28px;">Department Management</h3>
        <p style="color:var(--fu-on-surface-variant);"><?= $isAdmin ? 'Manage all departments.' : 'View and edit departments.' ?></p></div>
        <button class="btn btn-fu-primary" onclick="document.getElementById('createForm').classList.toggle('d-none')"><i class="fas fa-plus"></i> Add Department</button>
    </div>
    <div class="card-fu mb-4 d-none" id="createForm">
        <div class="px-4 py-3 card-header-section"><h6 class="fw-semibold mb-0">New Department</h6></div>
        <div class="p-4"><?= form_open('sao/departments/create') ?>
            <div class="row g-3"><div class="col-md-5"><input name="name" class="form-control" placeholder="Department Name" required></div>
            <div class="col-md-5"><textarea name="description" class="form-control" rows="2" placeholder="Description (optional)"></textarea></div>
            <div class="col-md-2"><button type="submit" class="btn btn-fu-primary w-100">Create</button></div></div>
        <?= form_close() ?></div>
    </div>
    <div class="card-fu"><div class="p-0"><table class="table mb-0"><thead class="table-light"><tr><th>Name</th><th>Description</th><th style="width:180px;">Actions</th></tr></thead><tbody>
        <?php foreach($departments as $d): ?>
        <tr><td class="fw-semibold"><?= esc($d->name) ?></td><td><?= esc($d->description ?? '—') ?></td>
            <td>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleEdit(<?= $d->id ?>)"><i class="fas fa-edit"></i></button>
                <?php if ($isAdmin): ?>
                <form action="<?= site_url('sao/departments/delete/'.$d->id) ?>" method="post" style="display:inline;" onsubmit="return confirm('Delete department &quot;<?= esc($d->name) ?>&quot;?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                <?php else: ?>
                <small style="color: var(--fu-on-surface-variant);">Admin only</small>
                <?php endif; ?>
            </td></tr>
        <tr id="editDept<?= $d->id ?>" style="display:none;"><td colspan="3">
            <?= form_open('sao/departments/edit/'.$d->id) ?>
            <div class="row g-2 p-2"><div class="col-md-5"><input name="name" class="form-control" value="<?= esc($d->name) ?>" required></div>
            <div class="col-md-5"><textarea name="description" class="form-control" rows="2"><?= esc($d->description ?? '') ?></textarea></div>
            <div class="col-md-2"><button type="submit" class="btn btn-fu-primary w-100 btn-sm">Save</button></div></div>
            <?= form_close() ?>
        </td></tr>
        <?php endforeach; ?>
    </tbody></table></div></div>
</section>
<script>function toggleEdit(id){document.getElementById('editDept'+id).style.display=document.getElementById('editDept'+id).style.display==='none'?'':'none';}</script>
<?= $this->endSection() ?>
