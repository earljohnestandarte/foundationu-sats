<?php

namespace App\Database\Seeds;

use App\Models\OfficeModel;
use CodeIgniter\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run()
    {
        $officeModel = new OfficeModel();

        $officeModel->insertBatch([
            [
                'name' => 'OSL Counseling',
                'description' => 'Office of Student Life Counseling and support services.',
            ],
            [
                'name' => 'OSL Student Records',
                'description' => 'Office responsible for student records and documentation.',
            ],
            [
                'name' => 'OSL Wellness',
                'description' => 'Office for student wellness and campus engagement.',
            ],
        ]);
    }
}
