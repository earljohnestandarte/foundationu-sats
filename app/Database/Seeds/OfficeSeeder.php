<?php

namespace App\Database\Seeds;

use App\Models\DepartmentModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run()
    {
        $departmentModel = new DepartmentModel();

        $departmentModel->insertBatch([
            [
                'name'        => 'OSL Counseling',
                'description' => 'Office of Student Life Counseling and support services.',
            ],
            [
                'name'        => 'OSL Student Records',
                'description' => 'Office responsible for student records and documentation.',
            ],
            [
                'name'        => 'OSL Wellness',
                'description' => 'Office for student wellness and campus engagement.',
            ],
        ]);
    }
}
