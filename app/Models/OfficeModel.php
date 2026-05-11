<?php

namespace App\Models;

use CodeIgniter\Model;

class OfficeModel extends Model
{
    protected $table = 'offices';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = ['name', 'description'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
