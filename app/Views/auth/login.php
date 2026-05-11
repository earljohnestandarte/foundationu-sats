<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Login - SATS<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Sign In</h4>
            </div>
            <div class="card-body">
                <?php if (isset($loginError)): ?>
                    <div class="alert alert-danger"><?= esc($loginError) ?></div>
                <?php endif ?>

                <?= form_open('login') ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <?= form_input('email', set_value('email'), ['class' => 'form-control', 'id' => 'email', 'type' => 'email', 'placeholder' => 'name@foundationu.edu']) ?>
                    <div class="form-text text-danger"><?= isset($validation) ? $validation->getError('email') : '' ?></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <?= form_password('password', '', ['class' => 'form-control', 'id' => 'password', 'placeholder' => 'Password']) ?>
                    <div class="form-text text-danger"><?= isset($validation) ? $validation->getError('password') : '' ?></div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>