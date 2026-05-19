<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('App\Database\Seeds\OfficeSeeder');
        $this->call('App\Database\Seeds\UserSeeder');
        $this->call('App\Database\Seeds\TicketSeeder');
    }
}
