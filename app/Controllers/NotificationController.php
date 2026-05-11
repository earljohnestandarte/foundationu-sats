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

        return $this->response->setJSON(['success' => true]);
    }
}
