<?= $this->extend('layout/main') ?>
<?php $this->section('title') ?>Login - SATS<?php $this->endSection() ?>
<?php $this->section('css') ?>
<link href="<?= base_url('assets/css/login.css') ?>" rel="stylesheet" />
<?php $this->endSection() ?>
<?php $this->section('content') ?>
<div class="login-bg">
    <div class="decorative-blob-1"></div>
    <div class="decorative-blob-2"></div>

    <div class="login-card animate-fadeIn">
        <div class="login-icons">
            <div class="login-icon-item login-icon-fu">
                <img src="<?= base_url('fu.svg') ?>" alt="Foundation University">
            </div>
            <div class="login-icon-divider"></div>
            <div class="login-icon-item login-icon-osl">
                <img src="<?= base_url('osl.svg') ?>" alt="OSL">
            </div>
        </div>

        <h2 class="text-center text-white login-heading">
            Foundation University
        </h2>
        <p class="text-center login-subtitle">
            Student Affairs Ticketing System
        </p>

        <?php if (isset($loginError)): ?>
            <div class="error-box mb-4">
                Login failed: <?= esc($loginError) ?>
            </div>
        <?php endif ?>

        <?= form_open('login', ['id' => 'loginForm']) ?>
        <div class="mb-4">
            <label for="email" class="form-label d-block text-sm font-medium text-white mb-2">
                Email Address
            </label>
            <?= form_input('email', set_value('email'), [
                'class' => 'login-input',
                'id' => 'email',
                'type' => 'email',
                'placeholder' => 'you@example.com',
                'required' => 'required'
            ]) ?>
            <div class="form-text login-form-text">
                <?= isset($validation) ? $validation->getError('email') : '' ?>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label d-block text-sm font-medium text-white mb-2">
                Password
            </label>
            <div class="password-toggle-wrapper">
                <?= form_password('password', '', [
                    'class' => 'login-input',
                    'id' => 'password',
                    'placeholder' => 'Enter password',
                    'required' => 'required'
                ]) ?>
                <button type="button" class="password-toggle" id="passwordToggle" aria-label="Show password">
                    <i class="fas fa-eye" id="passwordIcon"></i>
                </button>
            </div>
            <div class="form-text login-form-text">
                <?= isset($validation) ? $validation->getError('password') : '' ?>
            </div>
        </div>

        <button type="submit" class="btn-login" id="loginBtn">
            <span id="loginText">Login</span>
        </button>
        <?= form_close() ?>

        <p class="mt-4 text-center text-xs login-footer-text">
            Login · <a href="#" class="login-footer-link">Privacy Policy</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>
<?php $this->section('scripts') ?>
<script src="<?= base_url('assets/js/login.js') ?>"></script>
<?php $this->endSection() ?>