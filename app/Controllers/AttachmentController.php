<?php

namespace App\Controllers;

use App\Models\AttachmentModel;

/**
 * AttachmentController — serves uploaded files securely.
 *
 * Files are stored outside webroot in writable/uploads/tickets/.
 * Access is role-gated: students can only download their own tickets' files.
 *
 * Route: GET /attachment/download/(:num)
 */
class AttachmentController extends BaseController
{
    public function download(int $id)
    {
        $model      = new AttachmentModel();
        $userId     = (int) session()->get('user_id');
        $role       = session()->get('user_role');

        if (!$model->userCanAccess($id, $userId, $role)) {
            return $this->response->setStatusCode(403)->setBody('Access denied.');
        }

        $attachment = $model->find($id);
        if (!$attachment) {
            return $this->response->setStatusCode(404)->setBody('File not found.');
        }

        $uploadPath = WRITEPATH . 'uploads/tickets/';
        $filePath   = $uploadPath . $attachment['stored_name'];

        if (!file_exists($filePath)) {
            return $this->response->setStatusCode(404)->setBody('File no longer exists on disk.');
        }

        // Inline display for images/PDFs, attachment download for everything else
        $inline = in_array($attachment['mime_type'], [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf',
        ]);

        return $this->response
            ->setHeader('Content-Type', $attachment['mime_type'])
            ->setHeader('Content-Disposition', ($inline ? 'inline' : 'attachment') . '; filename="' . $attachment['original_name'] . '"')
            ->setHeader('Content-Length', (string) $attachment['file_size'])
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($filePath));
    }

    public function delete(int $id)
    {
        $model  = new AttachmentModel();
        $userId = (int) session()->get('user_id');
        $role   = session()->get('user_role');

        if (!$model->userCanAccess($id, $userId, $role)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied.'])->setStatusCode(403);
        }

        $attachment = $model->find($id);
        if (!$attachment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not found.'])->setStatusCode(404);
        }

        // Only uploader or agent/admin can delete
        if ($role === 'student' && $attachment['uploader_id'] !== $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied.'])->setStatusCode(403);
        }

        $filePath = WRITEPATH . 'uploads/tickets/' . $attachment['stored_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $model->delete($id);

        return $this->response->setJSON(['success' => true]);
    }
}
