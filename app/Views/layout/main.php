<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $this->renderSection('title') ?: 'Foundation University SATS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        :root {
            --fu-maroon: #800000;
            --fu-maroon-hover: #5e0000;
            --fd-bg: #f4f6f8;
            --fd-border: #ebeff3;
            --fd-text-main: #12344d;
        }

        body {
            background-color: var(--fd-bg);
            color: var(--fd-text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .navbar-fu {
            background-color: var(--fu-maroon) !important;
        }

        .navbar-fu .navbar-brand,
        .navbar-fu .nav-link {
            color: #fff !important;
        }

        .navbar-fu .nav-link:hover {
            color: #f4f6f8 !important;
        }

        .card {
            border: 1px solid var(--fd-border) !important;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(18, 52, 77, 0.06);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid var(--fd-border);
        }

        .btn-primary {
            background-color: var(--fu-maroon);
            border-color: var(--fu-maroon);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--fu-maroon-hover);
            border-color: var(--fu-maroon-hover);
        }

        .table th,
        .table td {
            border-top: 0;
            padding: 12px 16px;
        }

        .table thead th {
            border-bottom: 1px solid var(--fd-border);
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .reply-bubble {
            padding: 1.5rem;
            border-bottom: 1px solid var(--fd-border);
            background-color: #fff;
            border-radius: 12px;
        }

        .reply-bubble:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-fu mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/') ?>">Foundation University SATS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php $session = session(); ?>
                    <?php if ($session->get('isLoggedIn')) : ?>
                        <?php
                        $notificationModel = new \App\Models\NotificationModel();
                        $unreadCount = $notificationModel->getUnreadCountForUser($session->get('user_id'));
                        ?>
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php if ($unreadCount > 0) : ?>
                                    <span class="badge bg-danger rounded-pill"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                <li>
                                    <h6 class="dropdown-header">Notifications</h6>
                                </li>
                                <?php
                                $notifications = $notificationModel->getUnreadNotificationsForUser($session->get('user_id'));
                                if (empty($notifications)) :
                                ?>
                                    <li><a class="dropdown-item" href="#">No new notifications</a></li>
                                <?php else : ?>
                                    <?php foreach ($notifications as $notification) : ?>
                                        <li>
                                            <a class="dropdown-item notification-item" href="#" data-id="<?= $notification->id ?>">
                                                <div class="small mb-1 text-truncate" style="max-width: 260px;"><?= esc((string)$notification->message) ?></div>
                                                <small class="text-muted"><?= date('M j, H:i', strtotime($notification->created_at)) ?></small>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">View All Notifications</a></li>
                            </ul>
                        </li>
                        <?php if ($session->get('user_role') === 'student') : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('student/tickets') ?>">My Tickets</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('student/tickets/create') ?>">Submit Ticket</a>
                            </li>
                        <?php elseif ($session->get('user_role') === 'agent') : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('agent/dashboard') ?>">Agent Dashboard</a>
                            </li>
                        <?php endif ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('logout') ?>">Logout</a>
                        </li>
                    <?php else : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('login') ?>">Login</a>
                        </li>
                    <?php endif ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        <!-- Toast Container for Snackbars -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <?php if (session()->getFlashdata('success')) : ?>
                <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= session()->getFlashdata('success') ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')) : ?>
                <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= session()->getFlashdata('error') ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?= $this->renderSection('content') ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.notification-item').on('click', function(e) {
                e.preventDefault();
                var notificationId = $(this).data('id');

                $.ajax({
                    url: '<?= site_url('notification/markAsRead') ?>/' + notificationId,
                    method: 'POST',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            });

            // Initialize and show toasts (snackbars)
            <?php if (session()->getFlashdata('success')) : ?>
                var successToast = new bootstrap.Toast(document.getElementById('successToast'));
                successToast.show();
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')) : ?>
                var errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
                errorToast.show();
            <?php endif; ?>
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>