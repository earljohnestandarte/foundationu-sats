<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\DepartmentUserModel;
use App\Models\ResponseTemplateModel;
use App\Models\TicketModel;
use App\Models\TicketFeedbackModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class ManagementController extends BaseController
{
    protected $helpers = ['url', 'form', 'session'];
    protected DepartmentModel $departmentModel;
    protected UserModel $userModel;
    protected TicketModel $ticketModel;
    protected TicketFeedbackModel $feedbackModel;
    protected ResponseTemplateModel $templateModel;
    protected DepartmentUserModel $departmentUserModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $this->departmentModel = new DepartmentModel();
        $this->userModel = new UserModel();
        $this->ticketModel = new TicketModel();
        $this->feedbackModel = new TicketFeedbackModel();
        $this->templateModel = new ResponseTemplateModel();
        $this->departmentUserModel = new DepartmentUserModel();
    }

    private function isAdmin(): bool
    {
        return session()->get('user_role') === 'admin';
    }

    private function allowedRoles(): array
    {
        return $this->isAdmin() ? ['student', 'agent', 'sao', 'admin'] : ['student', 'agent'];
    }

    private function guardManageUser(object $targetUser): void
    {
        if ($this->isAdmin()) return;
        if (! in_array($targetUser->role, ['student', 'agent'])) {
            throw new \RuntimeException('You cannot manage accounts with the ' . esc($targetUser->role) . ' role.');
        }
    }

    public function dashboard()
    {
        $departmentId = session()->get('department_id');
        $allTickets = $departmentId ? $this->ticketModel->getTicketsForDepartment($departmentId) : $this->ticketModel->findAll();
        $userRole = session()->get('user_role');

        $totalTickets = count($allTickets);
        $openCount = 0;
        $resolvedCount = 0;
        $overdueCount = 0;
        $now = time();

        foreach ($allTickets as $t) {
            if ($t->status === 'Open' || $t->status === 'In Progress' || $t->status === 'Pending') $openCount++;
            if ($t->status === 'Resolved' || $t->status === 'Closed') $resolvedCount++;
            if ($t->sla_due_at && ! $t->first_response_at && strtotime($t->sla_due_at) < $now) $overdueCount++;
        }

        $avgRating = $departmentId ? $this->feedbackModel->getAverageRatingForDepartment($departmentId) : null;
        $escalatedTickets = $this->ticketModel->getEscalatedTickets($departmentId);
        $escalatedCount = count($escalatedTickets);

        $departmentList = $this->departmentModel->findAll();
        $deptMetrics = [];

        foreach ($departmentList as $dept) {
            $deptTickets = $this->ticketModel->getTicketsForDepartment($dept->id);
            $openD = 0; $resolvedD = 0; $overdueD = 0;
            foreach ($deptTickets as $dt) {
                if ($dt->status === 'Open' || $dt->status === 'In Progress' || $dt->status === 'Pending') $openD++;
                if ($dt->status === 'Resolved' || $dt->status === 'Closed') $resolvedD++;
                if ($dt->sla_due_at && ! $dt->first_response_at && strtotime($dt->sla_due_at) < $now) $overdueD++;
            }
            $deptMetrics[] = [
                'department' => $dept,
                'total'      => count($deptTickets),
                'open'       => $openD,
                'resolved'   => $resolvedD,
                'overdue'    => $overdueD,
                'avg_rating' => $this->feedbackModel->getAverageRatingForDepartment($dept->id),
            ];
        }

        return view('management/dashboard', [
            'totalTickets'    => $totalTickets,
            'openCount'       => $openCount,
            'resolvedCount'   => $resolvedCount,
            'overdueCount'    => $overdueCount,
            'escalatedCount'  => $escalatedCount,
            'avgRating'       => $avgRating,
            'deptMetrics'     => $deptMetrics,
            'escalatedTickets' => $escalatedTickets,
            'recentTickets'   => array_slice($allTickets, 0, 10),
            'isAdmin'         => $this->isAdmin(),
        ]);
    }

    public function reports()
    {
        $from = $this->request->getGet('from') ?: date('Y-m-d', strtotime('-30 days'));
        $to = $this->request->getGet('to') ?: date('Y-m-d');
        $departmentFilter = $this->request->getGet('department_id') ? (int) $this->request->getGet('department_id') : null;
        $departmentId = session()->get('department_id');
        if (! $departmentFilter && $departmentId) $departmentFilter = $departmentId;

        $builder = $this->ticketModel->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59');

        if ($departmentFilter) {
            $builder->where('department_id', $departmentFilter);
        }

        $tickets = $builder->orderBy('created_at', 'DESC')->findAll();

        $total = count($tickets);
        $resolved = 0;
        $responseTimes = [];
        $resolutionTimes = [];
        $overdue = 0;

        foreach ($tickets as $t) {
            if (in_array($t->status, ['Resolved', 'Closed'])) $resolved++;
            if ($t->first_response_at && $t->created_at) {
                $responseTimes[] = strtotime($t->first_response_at) - strtotime($t->created_at);
            }
            if ($t->resolved_at && $t->created_at) {
                $resolutionTimes[] = strtotime($t->resolved_at) - strtotime($t->created_at);
            }
            if ($t->sla_due_at && ! $t->first_response_at && strtotime($t->sla_due_at) < time()) $overdue++;
        }

        $avgResponseMinutes = ! empty($responseTimes) ? round(array_sum($responseTimes) / count($responseTimes) / 60, 1) : null;
        $avgResolutionMinutes = ! empty($resolutionTimes) ? round(array_sum($resolutionTimes) / count($resolutionTimes) / 60, 1) : null;

        $departments = $this->departmentModel->findAll();

        return view('management/reports', [
            'from'            => $from,
            'to'              => $to,
            'departmentFilter' => $departmentFilter,
            'total'           => $total,
            'resolved'        => $resolved,
            'overdue'         => $overdue,
            'avgResponseMinutes'  => $avgResponseMinutes,
            'avgResolutionMinutes' => $avgResolutionMinutes,
            'departments'     => $departments,
        ]);
    }

    public function users()
    {
        $users = $this->userModel->getAllWithDepartments();
        $departments = $this->departmentModel->findAll();

        return view('management/users', [
            'users'       => $users,
            'departments' => $departments,
            'isAdmin'     => $this->isAdmin(),
        ]);
    }

    public function userCreate()
    {
        $allowedRoles = $this->allowedRoles();
        $role = $this->request->getPost('role');

        if (! in_array($role, $allowedRoles)) {
            return redirect()->back()->withInput()->with('error', 'You do not have permission to create users with the ' . esc($role) . ' role.');
        }

        $rules = [
            'name'     => 'required|min_length[2]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[' . implode(',', $allowedRoles) . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input.');
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $role,
            'department_id' => $this->request->getPost('department_id') ?: null,
        ];

        if ($this->request->getPost('student_id_number')) {
            $data['student_id_number'] = $this->request->getPost('student_id_number');
        }

        $this->userModel->insert($data);

        return redirect()->to(site_url('sao/users'))->with('success', 'User created successfully.');
    }

    public function userEdit($id)
    {
        $user = $this->userModel->find((int) $id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        try {
            $this->guardManageUser($user);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $allowedRoles = $this->allowedRoles();
        $newRole = $this->request->getPost('role');

        if (! in_array($newRole, $allowedRoles)) {
            return redirect()->back()->withInput()->with('error', 'You do not have permission to assign the ' . esc($newRole) . ' role.');
        }

        // SAO cannot change an admin/sao user's role to a lower level either
        if (! $this->isAdmin() && ! in_array($user->role, ['student', 'agent'])) {
            return redirect()->back()->with('error', 'You cannot modify ' . esc($user->role) . ' accounts.');
        }

        $rules = [
            'name'  => 'required|min_length[2]',
            'email' => 'required|valid_email',
            'role'  => 'required|in_list[' . implode(',', $allowedRoles) . ']',
        ];

        if ($this->request->getPost('email') !== $user->email) {
            $rules['email'] = 'required|valid_email|is_unique[users.email,id,' . $user->id . ']';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input.');
        }

        $update = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role'  => $newRole,
            'department_id' => $this->request->getPost('department_id') ?: null,
            'student_id_number' => $this->request->getPost('student_id_number') ?: null,
        ];

        if ($this->request->getPost('password')) {
            $update['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $this->userModel->update($user->id, $update);

        return redirect()->to(site_url('sao/users'))->with('success', 'User updated successfully.');
    }

    public function userDelete($id)
    {
        $user = $this->userModel->find((int) $id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        try {
            $this->guardManageUser($user);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        // Soft-disable instead of hard-delete (#14).
        // Hard deletion cascades and permanently destroys all associated tickets.
        $this->userModel->deactivate($user->id);
        return redirect()->to(site_url('sao/users'))->with('success', 'User deactivated. Their tickets are preserved.');
    }

    /**
     * Toggle a user account between active and deactivated (#14).
     */
    public function userToggleActive($id)
    {
        $user = $this->userModel->find((int) $id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        try {
            $this->guardManageUser($user);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        if ($user->is_active) {
            $this->userModel->deactivate($user->id);
            $msg = 'User account deactivated.';
        } else {
            $this->userModel->activate($user->id);
            $msg = 'User account reactivated.';
        }

        return redirect()->to(site_url('sao/users'))->with('success', $msg);
    }

    public function departments()
    {
        $departments = $this->departmentModel->findAll();

        return view('management/departments', [
            'departments' => $departments,
            'isAdmin'     => $this->isAdmin(),
        ]);
    }

    public function departmentCreate()
    {
        $rules = ['name' => 'required|min_length[2]'];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Department name is required.');
        }

        $this->departmentModel->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: null,
        ]);

        return redirect()->to(site_url('sao/departments'))->with('success', 'Department created.');
    }

    public function departmentEdit($id)
    {
        $dept = $this->departmentModel->find((int) $id);
        if (! $dept) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        $this->departmentModel->update($dept->id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: null,
        ]);

        return redirect()->to(site_url('sao/departments'))->with('success', 'Department updated.');
    }

    public function departmentDelete($id)
    {
        if (! $this->isAdmin()) {
            return redirect()->back()->with('error', 'Only administrators can delete departments.');
        }

        $dept = $this->departmentModel->find((int) $id);
        if (! $dept) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        $this->departmentModel->delete($dept->id);
        return redirect()->to(site_url('sao/departments'))->with('success', 'Department deleted.');
    }

    public function templates()
    {
        $departmentId = session()->get('department_id');
        $isAdmin = $this->isAdmin();
        $departments = $this->departmentModel->findAll();

        if ($isAdmin || ! $departmentId) {
            $templates = $this->templateModel->orderBy('title', 'ASC')->findAll();
        } else {
            $templates = $this->templateModel->getTemplatesForDepartment($departmentId);
        }

        return view('management/templates', [
            'templates'   => $templates,
            'departments' => $departments,
            'isAdmin'     => $isAdmin,
        ]);
    }

    public function templateCreate()
    {
        $rules = [
            'title'   => 'required|min_length[2]',
            'message' => 'required|min_length[5]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Title and message are required.');
        }

        $departmentId = session()->get('department_id');
        if ($this->isAdmin()) {
            $departmentId = $this->request->getPost('department_id') ?: null;
        }

        $this->templateModel->insert([
            'department_id' => $departmentId,
            'title'         => $this->request->getPost('title'),
            'message'       => $this->request->getPost('message'),
            'created_by'    => session()->get('user_id'),
        ]);

        return redirect()->to(site_url('sao/templates'))->with('success', 'Template created.');
    }

    public function templateDelete($id)
    {
        $template = $this->templateModel->find((int) $id);
        if (! $template) {
            return redirect()->back()->with('error', 'Template not found.');
        }

        if (! $this->isAdmin()) {
            $departmentId = session()->get('department_id');
            if ($departmentId === null || (int) $template->department_id !== (int) $departmentId) {
                return redirect()->back()->with('error', 'You do not have permission to delete this template.');
            }
        }

        $this->templateModel->delete($template->id);
        return redirect()->to(site_url('sao/templates'))->with('success', 'Template deleted.');
    }

    public function templatesJson()
    {
        $departmentId = session()->get('department_id');
        if ($this->isAdmin()) {
            $templates = $this->templateModel->orderBy('title', 'ASC')->findAll();
        } else {
            $templates = $this->templateModel->getTemplatesForDepartment($departmentId);
        }

        return $this->response->setJSON($templates);
    }
}
