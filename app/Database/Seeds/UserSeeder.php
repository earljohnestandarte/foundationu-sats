<?php

namespace App\Database\Seeds;

use App\Models\DepartmentModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Safety guard: never seed test users in production
        if (ENVIRONMENT === 'production') {
            echo "UserSeeder skipped in production.\n";
            return;
        }

        $departmentModel = new DepartmentModel();
        $userModel = new UserModel();

        $counseling = $departmentModel->where('name', 'OSL Counseling')->first();
        $records = $departmentModel->where('name', 'OSL Student Records')->first();
        $wellness = $departmentModel->where('name', 'OSL Wellness')->first();

        $userModel->insertBatch([
            [
                'student_id_number' => '20220001',
                'name' => 'Liza Santos',
                'email' => 'liza.santos@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'student',
                'department_id' => null,
            ],
            [
                'student_id_number' => '20220002',
                'name' => 'Juan dela Cruz',
                'email' => 'juan.delacruz@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'student',
                'department_id' => null,
            ],
            [
                'student_id_number' => '20220003',
                'name' => 'Maria Reyes',
                'email' => 'maria.reyes@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'student',
                'department_id' => null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Mark Garcia',
                'email' => 'mark.garcia@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $counseling ? $counseling->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Elena Rodriguez',
                'email' => 'elena.rodriguez@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $counseling ? $counseling->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $counseling ? $counseling->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $records ? $records->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'David Chen',
                'email' => 'david.chen@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $records ? $records->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Lisa Wong',
                'email' => 'lisa.wong@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $records ? $records->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Anne dela Cruz',
                'email' => 'anne.delacruz@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $wellness ? $wellness->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Michael Torres',
                'email' => 'michael.torres@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $wellness ? $wellness->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Jennifer Kim',
                'email' => 'jennifer.kim@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'agent',
                'department_id' => $wellness ? $wellness->id : null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Robert Smith',
                'email' => 'robert.smith@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'admin',
                'department_id' => null,
            ],
            [
                'student_id_number' => null,
                'name' => 'Patricia Brown',
                'email' => 'patricia.brown@foundationu.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role' => 'admin',
                'department_id' => null,
            ],
        ]);
    }
}
