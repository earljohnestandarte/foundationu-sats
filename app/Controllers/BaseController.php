<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use CodeIgniter\Controller;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /** Unread notification count, shared with all views via the layout. */
    protected int $unreadNotificationCount = 0;

    /** Recent unread notifications list passed to the layout. */
    protected array $unreadNotifications = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Pre-load notification data for authenticated users so views never
        // need to instantiate models themselves (fixes MVC violation #5).
        $userId = session()->get('user_id');
        if ($userId) {
            $notificationModel             = new NotificationModel();
            $this->unreadNotificationCount = (int) $notificationModel->getUnreadCountForUser((int) $userId);
            $this->unreadNotifications     = $notificationModel->getUnreadNotificationsForUser((int) $userId);
        }

        // Share data globally so all views (including layouts) receive them
        // as plain PHP variables without instantiating models in the view layer.
        $renderer = Services::renderer();
        $renderer->setVar('unreadNotificationCount', $this->unreadNotificationCount);
        $renderer->setVar('unreadNotifications',     $this->unreadNotifications);
    }
}
