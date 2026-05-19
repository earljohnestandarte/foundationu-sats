<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use CodeIgniter\Controller;

class NotificationController extends BaseController
{
    protected $helpers = ['url', 'form', 'session'];
    protected $notificationModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->notificationModel = new NotificationModel();
    }

    public function markAsRead($id)
    {
        $notification = $this->notificationModel->find((int) $id);

        if (! $notification || $notification->user_id !== session()->get('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Notification not found or access denied.']);
        }

        $this->notificationModel->update($notification->id, [
            'is_read' => true,
        ]);

        $role        = session()->get('user_role');
        $ticketId    = $notification->ticket_id;
        $redirectUrl = '';

        if ($ticketId) {
            if ($role === 'student') {
                $redirectUrl = site_url('student/tickets/' . $ticketId);
            } else {
                $redirectUrl = site_url('agent/view/' . $ticketId);
            }
        }

        return $this->response->setJSON([
            'success'     => true,
            'redirectUrl' => $redirectUrl,
        ]);
    }

    public function index()
    {
        $userId = (int) session()->get('user_id');

        return view('notifications/index', [
            'notifications' => $this->notificationModel->getNotificationsForUser($userId),
        ]);
    }
}
