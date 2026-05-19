<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueEmailIndexToUsers extends Migration
{
    public function up()
    {
        // Add a true unique index on users.email so the database enforces
        // uniqueness at the storage level, not just at the validation layer (#19).
        $this->db->query('ALTER TABLE `users` ADD UNIQUE INDEX `users_email_unique` (`email`)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `users` DROP INDEX `users_email_unique`');
    }
}
