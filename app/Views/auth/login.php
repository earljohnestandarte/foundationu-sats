<?= $this->extend('layout/main') ?>
<?php $this->section('title') ?>Login - SATS<?php $this->endSection() ?>
<?php $this->section('content') ?>
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse-slow {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.8s ease-out forwards;
    }

    .animate-pulse-slow {
        animation: pulse-slow 8s infinite ease-in-out;
    }

    .login-bg {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        position: relative;
        overflow: hidden;
        background-image: linear-gradient(rgba(15, 9, 28, 0.55), rgba(53, 16, 75, 0.55)),
            url('<?= base_url('assets/images/fu_social_garden.jpg') ?>');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .login-card {
        position: relative;
        z-index: 10;
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        max-width: 400px;
        width: 100%;
        padding: 32px;
    }

    @media (min-width: 768px) {
        .login-card {
            padding: 40px;
        }
    }

    .logo-circle {
        width: 96px;
        height: 96px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
    }

    .login-input {
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        transition: all 0.3s ease;
    }

    .login-input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .login-input:focus {
        border-color: #B22543;
        box-shadow: 0 0 0 1px #B22543;
        outline: none;
    }

    .btn-login {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
        border-radius: 12px;
        font-weight: bold;
        color: white;
        background: #7D1921;
        margin-top: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
    }

    .btn-login:hover {
        transform: translateY(-2px);
        background: #6b151b;
        color: white;
    }

    .btn-login:disabled {
        background: #6b7280;
        cursor: not-allowed;
        transform: none;
        opacity: 0.7;
    }

    .error-box {
        color: #fca5a5;
        background: rgba(127, 29, 29, 0.4);
        padding: 12px;
        border-radius: 8px;
        font-size: 14px;
        text-align: center;
        border: 1px solid rgba(239, 68, 68, 0.5);
    }

    .decorative-blob-1 {
        position: absolute;
        width: 288px;
        height: 288px;
        background: rgba(141, 37, 120, 0.3);
        border-radius: 50%;
        filter: blur(48px);
        bottom: -128px;
        left: -128px;
        animation: pulse-slow 8s infinite ease-in-out;
    }

    .decorative-blob-2 {
        position: absolute;
        width: 208px;
        height: 208px;
        background: rgba(121, 33, 33, 0.3);
        border-radius: 50%;
        filter: blur(48px);
        top: 0;
        right: 0;
        animation: pulse-slow 8s infinite ease-in-out;
        animation-delay: 1s;
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #a730a1;
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
    }

    .password-toggle:hover {
        color: #B22543;
    }
</style>
<div class="login-bg">
    <div class="decorative-blob-1"></div>
    <div class="decorative-blob-2"></div>

    <div class="login-card animate-fadeIn">
        <div class="d-flex justify-content-center align-items-center gap-4 mb-8" style="margin-top: -88px;">
            <div class="logo-circle animate-pulse-slow">
                <img src="<?= base_url('assets/logos/foundationu_logo.png') ?>" alt="Foundation University Logo" style="width: 128px; height: 128px; object-cover;">
            </div>
            <div class="logo-circle animate-pulse-slow" style="animation-delay: 0.5s;">
                <img src="<?= base_url('assets/logos/osl_logo.png') ?>" alt="OSL Logo" style="width: 129px; height: 128px; object-contain; padding: 8px; margin-left: 6.3px;">
            </div>
        </div>

        <h2 class="text-center text-white text-3xl font-extrabold mb-1">
            <?php
            $hour = date('H');
            echo $hour < 12 ? 'Good morning' : 'Welcome back';
            ?>
        </h2>
        <p class="text-center mb-8" style="color: #d9c3e5; font-weight: 300;">
            Sign in to continue to FU-SATS Web
        </p>

        <?php if (isset($loginError)): ?>
            <div class="error-box mb-6">
                Login failed: <?= esc($loginError) ?>
            </div>
        <?php endif ?>

        <?= form_open('login', ['id' => 'loginForm']) ?>
        <div class="mb-6">
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
            <div class="form-text" style="color: rgba(255,255,255,0.7);">
                <?= isset($validation) ? $validation->getError('email') : '' ?>
            </div>
        </div>

        <div class="mb-6">
            <label for="password" class="form-label d-block text-sm font-medium text-white mb-2">
                Password
            </label>
            <div class="position-relative">
                <?= form_password('password', '', [
                    'class' => 'login-input',
                    'id' => 'password',
                    'placeholder' => 'Enter password',
                    'required' => 'required',
                    'style' => 'padding-right: 48px;'
                ]) ?>
                <button type="button" class="password-toggle" id="passwordToggle" aria-label="Show password">
                    <i class="fas fa-eye" id="passwordIcon"></i>
                </button>
            </div>
            <div class="form-text" style="color: rgba(255,255,255,0.7);">
                <?= isset($validation) ? $validation->getError('password') : '' ?>
            </div>
        </div>

        <button type="submit" class="btn-login" id="loginBtn">
            <span id="loginText">Login</span>
        </button>
        <?= form_close() ?>

        <p class="mt-4 text-center text-xs" style="color: rgba(255,255,255,0.4);">
            Login · <a href="#" style="text-decoration: underline; color: rgba(255,255,255,0.4);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Privacy Policy</a>
        </p>
    </div>
</div>

<script>
    $(document).ready(function() {
        const passwordInput = $('#password');
        const passwordToggle = $('#passwordToggle');
        const passwordIcon = $('#passwordIcon');
        const loginForm = $('#loginForm');
        const loginBtn = $('#loginBtn');
        const loginText = $('#loginText');

        passwordToggle.on('click', function() {
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                passwordToggle.attr('aria-label', 'Hide password');
            } else {
                passwordInput.attr('type', 'password');
                passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                passwordToggle.attr('aria-label', 'Show password');
            }
        });

        loginForm.on('submit', function() {
            loginBtn.prop('disabled', true);
            loginText.html('<i class="fas fa-spinner fa-spin me-2"></i>Authenticating...');
        });
    });
</script>
<?= $this->endSection() ?>