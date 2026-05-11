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
        'office_id',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all agents for a specific office
     */
    public function getAgentsForOffice(int $officeId): array
    {
        return $this->where('office_id', $officeId)
            ->where('role', 'agent')
            ->findAll();
    }
}
