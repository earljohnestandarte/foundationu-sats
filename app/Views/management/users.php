<?= $this->extend('layout/main') ?>
<?php /** @var bool $isAdmin */ ?>
<?php $this->section('title') ?>User Management - SATS<?php $this->endSection() ?>
<?php $this->section('activeNav') ?>users<?php $this->endSection() ?>
<?php $this->section('content') ?>
<section class="section-padding">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: var(--fu-primary); font-size: 28px;">User Management</h3>
            <p style="color: var(--fu-on-surface-variant);"><?= $isAdmin ? 'Manage all users.' : 'Manage students and agents.' ?></p>
        </div>
        <button class="btn btn-fu-primary" onclick="document.getElementById('createUserForm').classList.toggle('d-none')">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>

    <div class="card-fu mb-4 d-none" id="createUserForm">
        <div class="px-4 py-3 card-header-section"><h6 class="fw-semibold mb-0">New User</h6></div>
        <div class="p-4">
            <?= form_open('sao/users/create') ?>
            <div class="row g-3">
                <div class="col-md-4"><input name="name" class="form-control" placeholder="Full Name" required></div>
                <div class="col-md-4"><input name="email" type="email" class="form-control" placeholder="Email" required></div>
                <div class="col-md-2"><input name="password" type="password" class="form-control" placeholder="Password" required minlength="6"></div>
                <div class="col-md-2">
                    <select name="role" class="form-select" required>
                        <option value="student">Student</option>
                        <option value="agent">Agent</option>
                        <?php if ($isAdmin): ?>
                        <option value="sao">SAO</option>
                        <option value="admin">Admin</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4"><input name="student_id_number" class="form-control" placeholder="Student ID (optional)"></div>
                <div class="col-md-4">
                    <select name="department_id" class="form-select">
                        <option value="">No Department</option>
                        <?php foreach ($departments as $d): ?><option value="<?= $d->id ?>"><?= esc($d->name) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-fu-primary w-100">Create User</button>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </div>

    <div class="card-fu">
        <div class="p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Department</th><th style="width:180px;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user):
                        $canManage = $isAdmin || in_array($user->role, ['student', 'agent']);
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($user->name) ?></td>
                        <td><?= esc($user->email) ?></td>
                        <td><span class="badge-fu <?= $user->role==='admin'?'in-progress':($user->role==='sao'?'pending':($user->role==='agent'?'open':'resolved')) ?>"><?= ucfirst($user->role) ?></span></td>
                        <td><?= esc($user->department_name ?? '—') ?></td>
                        <td>
                            <?php if ($canManage): ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleEdit(<?= $user->id ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete user?')) document.getElementById('delForm<?= $user->id ?>').submit()"><i class="fas fa-trash"></i></button>
                            <form id="delForm<?= $user->id ?>" action="<?= site_url('sao/users/delete/'.$user->id) ?>" method="post" style="display:none;"><?= csrf_field() ?></form>
                            <?php else: ?>
                            <small style="color: var(--fu-on-surface-variant);">Admin only</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($canManage): ?>
                    <tr id="editRow<?= $user->id ?>" style="display:none;">
                        <td colspan="5">
                            <?= form_open('sao/users/edit/'.$user->id) ?>
                            <div class="row g-2 p-2">
                                <div class="col-md-2"><input name="name" class="form-control" value="<?= esc($user->name) ?>" required></div>
                                <div class="col-md-2"><input name="email" type="email" class="form-control" value="<?= esc($user->email) ?>"></div>
                                <div class="col-md-1"><input name="password" type="password" class="form-control" placeholder="New pw"></div>
                                <div class="col-md-1">
                                    <select name="role" class="form-select">
                                        <option value="student" <?= $user->role=='student'?'selected':'' ?>>Student</option>
                                        <option value="agent" <?= $user->role=='agent'?'selected':'' ?>>Agent</option>
                                        <?php if ($isAdmin): ?>
                                        <option value="sao" <?= $user->role=='sao'?'selected':'' ?>>SAO</option>
                                        <option value="admin" <?= $user->role=='admin'?'selected':'' ?>>Admin</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="department_id" class="form-select"><option value="">—</option>
                                        <?php foreach($departments as $d): ?><option value="<?= $d->id ?>" <?= $user->department_id==$d->id?'selected':'' ?>><?= esc($d->name) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2"><input name="student_id_number" class="form-control" value="<?= esc($user->student_id_number ?? '') ?>" placeholder="Student ID"></div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-fu-primary btn-sm w-100">Save</button>
                                </div>
                            </div>
                            <?= form_close() ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<script>function toggleEdit(id){document.getElementById('editRow'+id).style.display=document.getElementById('editRow'+id).style.display==='none'?'':'none';}</script>
<?= $this->endSection() ?>
