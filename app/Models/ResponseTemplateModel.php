<?php

namespace App\Models;

use CodeIgniter\Model;

class ResponseTemplateModel extends Model
{
    protected $table = 'response_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = [
        'department_id',
        'title',
        'message',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getTemplatesForDepartment(int $departmentId): array
    {
        return $this->where('department_id', $departmentId)
            ->orderBy('title', 'ASC')
            ->findAll();
    }
}
