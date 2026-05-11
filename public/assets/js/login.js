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
