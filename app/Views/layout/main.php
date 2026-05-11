<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $this->renderSection('title') ?: 'Foundation University SATS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet" />
    <?= $this->renderSection('css') ?>
</head>

<body>
    <?php $session = session(); ?>

    <?php if ($session->get('isLoggedIn')) : ?>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="sidebar" id="sidebar">
            <div class="px-2 py-2">
                <div class="d-flex align-items-center gap-2 mb-3 px-2 py-2">
                    <div class="w-10 h-10 rounded d-flex align-items-center justify-content-center">
                        <img src="<?= base_url('assets/logos/osl_logo.png') ?>" alt="OSL Logo" style="width: 128px; object-cover;">
                    </div>
                    <div>
                        <h1 class="fw-bold mb-0" style="color: var(--fu-primary); font-size: 18px;">Student Affairs</h1>
                        <p class="mb-0" style="color: var(--fu-on-surface-variant); font-size: 12px; font-weight: 600;">Foundation University</p>
                    </div>
                </div>

                <nav class="pt-3">
                    <?php if ($session->get('user_role') === 'student') : ?>
                        <a class="nav-item-sidebar <?= $this->renderSection('activeNav') === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('student/dashboard') ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span style="font-size: 14px; font-weight: 600;">Dashboard</span>
                        </a>
                        <a class="nav-item-sidebar <?= $this->renderSection('activeNav') === 'tickets' ? 'active' : '' ?>" href="<?= site_url('student/tickets') ?>">
                            <i class="fas fa-ticket-alt"></i>
                            <span style="font-size: 14px; font-weight: 600;">My Tickets</span>
                        </a>
                        <a class="nav-item-sidebar <?= $this->renderSection('activeNav') === 'create' ? 'active' : '' ?>" href="<?= site_url('student/tickets/create') ?>">
                            <i class="fas fa-plus-square"></i>
                            <span style="font-size: 14px; font-weight: 600;">Submit Concern</span>
                        </a>
                    <?php elseif ($session->get('user_role') === 'agent') : ?>
                        <a class="nav-item-sidebar <?= $this->renderSection('activeNav') === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('agent/dashboard') ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span style="font-size: 14px; font-weight: 600;">Dashboard</span>
                        </a>
                    <?php endif; ?>

                    <a class="nav-item-sidebar" href="#">
                        <i class="fas fa-book"></i>
                        <span style="font-size: 14px; font-weight: 600;">Knowledge Base</span>
                    </a>
                    <a class="nav-item-sidebar" href="#">
                        <i class="fas fa-university"></i>
                        <span style="font-size: 14px; font-weight: 600;">Offices</span>
                    </a>
                </nav>
            </div>

            <div class="mt-auto px-2 py-3" style="border-top: 1px solid var(--fu-outline-variant);">
                <?php if ($session->get('user_role') === 'student') : ?>
                    <a href="<?= site_url('student/tickets/create') ?>" class="btn btn-fu-primary w-100 mb-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-plus"></i> New Ticket
                    </a>
                <?php endif; ?>

                <nav>
                    <a class="nav-item-sidebar" href="#">
                        <i class="fas fa-cog"></i>
                        <span style="font-size: 14px; font-weight: 600;">Settings</span>
                    </a>
                    <a class="nav-item-sidebar" href="#">
                        <i class="fas fa-headset"></i>
                        <span style="font-size: 14px; font-weight: 600;">Support</span>
                    </a>
                </nav>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-header">
                <div class="d-flex justify-content-between align-items-center px-4 py-2" style="max-width: 1280px; margin: 0 auto;">
                    <div class="d-flex align-items-center gap-2">
                        <button class="menu-toggle" id="menuToggle">
                            <i class="fas fa-bars fa-lg"></i>
                        </button>
                        <h2 class="fw-bold mb-0" style="color: #ffffff; font-size: 20px;">SATS</h2>
                        <div class="mx-2 d-none d-md-block" style="width: 1px; height: 24px; background-color: rgba(255,255,255,0.3);"></div>
                        <p class="mb-0 d-none d-md-block" style="color: #ffffff;"><?= $session->get('user_role') === 'student' ? 'Student Dashboard' : 'Agent Dashboard' ?></p>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative d-none d-lg-block">
                            <i class="fas fa-search position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.85);"></i>
                            <input type="text" class="search-input" placeholder="Search tickets...">
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <?php
                            $notificationModel = new \App\Models\NotificationModel();
                            $unreadCount = $notificationModel->getUnreadCountForUser($session->get('user_id'));
                            ?>
                            <button class="icon-btn" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php if ($unreadCount > 0) : ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background-color: var(--fu-error);"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                <li>
                                    <h6 class="dropdown-header">Notifications</h6>
                                </li>
                                <?php
                                $notifications = $notificationModel->getUnreadNotificationsForUser($session->get('user_id'));
                                if (empty($notifications)) :
                                ?>
                                    <li><a class="dropdown-item text-muted" href="#">No new notifications</a></li>
                                <?php else : ?>
                                    <?php foreach ($notifications as $notification) : ?>
                                        <li>
                                            <a class="dropdown-item notification-item" href="#" data-id="<?= $notification->id ?>">
                                                <div class="small mb-1 text-truncate" style="max-width: 300px;"><?= esc((string)$notification->message) ?></div>
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

                            <button class="icon-btn d-none d-md-block">
                                <i class="fas fa-question-circle"></i>
                            </button>

                            <div class="d-flex align-items-center gap-2 ms-1">
                                <div class="w-8 h-8 rounded-circle overflow-hidden d-none d-md-block" style="background-color: rgba(255, 255, 255, 0.2);">
                                    <i class="fas fa-user d-flex align-items-center justify-content-center w-100 h-100" style="color: #ffffff;"></i>
                                </div>
                                <a href="<?= site_url('logout') ?>" class="text-decoration-none d-none d-md-block" style="color: #ffffff; font-size: 14px; font-weight: 600;">Sign Out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <?php if (session()->getFlashdata('success')) : ?>
                    <div id="successToast" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: var(--fu-primary);">
                        <div class="d-flex">
                            <div class="toast-body px-4 py-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= session()->getFlashdata('success') ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')) : ?>
                    <div id="errorToast" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: var(--fu-error);">
                        <div class="d-flex">
                            <div class="toast-body px-4 py-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?= $this->renderSection('content') ?>
        </main>
    <?php else : ?>
        <main>
            <?= $this->renderSection('content') ?>
        </main>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function siteUrl(uri = '') {
            return '<?= base_url() ?>' + (uri ? '/' + uri : '');
        }

        <?php if (session()->getFlashdata('success')) : ?>
            var successToast = new bootstrap.Toast(document.getElementById('successToast'), {
                delay: 5000
            });
            successToast.show();
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')) : ?>
            var errorToast = new bootstrap.Toast(document.getElementById('errorToast'), {
                delay: 5000
            });
            errorToast.show();
        <?php endif; ?>
    </script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>