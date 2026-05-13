<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = [
        'student_id_number',
        'name',
        'email',
        'password',
        'role',
        'department_id',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAgentsForDepartment(int $departmentId): array
    {
        return $this->where('department_id', $departmentId)
            ->where('role', 'agent')
            ->findAll();
    }

    public function getAllWithDepartments(): array
    {
        return $this->select('users.*, departments.name AS department_name')
            ->join('departments', 'departments.id = users.department_id', 'left')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();
    }

    public function getByRole(string $role): array
    {
        return $this->where('role', $role)->findAll();
    }
}
