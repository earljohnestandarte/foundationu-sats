<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentUserModel extends Model
{
    protected $table = 'department_user';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = ['user_id', 'department_id'];
    protected $useTimestamps = false;

    public function getDepartmentsForUser(int $userId): array
    {
        return $this->select('department_user.*, departments.name AS department_name')
            ->join('departments', 'departments.id = department_user.department_id')
            ->where('user_id', $userId)
            ->findAll();
    }

    public function getUsersForDepartment(int $departmentId): array
    {
        return $this->select('department_user.*, users.name AS user_name, users.email AS user_email, users.role AS user_role')
            ->join('users', 'users.id = department_user.user_id')
            ->where('department_id', $departmentId)
            ->findAll();
    }

    public function setUserDepartments(int $userId, array $departmentIds): void
    {
        $this->where('user_id', $userId)->delete();
        foreach ($departmentIds as $departmentId) {
            $this->insert(['user_id' => $userId, 'department_id' => (int) $departmentId]);
        }
    }
}
