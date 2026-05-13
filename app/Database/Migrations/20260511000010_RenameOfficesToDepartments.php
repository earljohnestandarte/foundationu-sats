<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameOfficesToDepartments extends Migration
{
    public function up()
    {
        $prefix = $this->db->getPrefix();
        $dbName = $this->db->getDatabase();

        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        $this->db->query("ALTER TABLE `{$prefix}users` DROP FOREIGN KEY `{$prefix}users_office_id_foreign`");
        $this->db->query("ALTER TABLE `{$prefix}tickets` DROP FOREIGN KEY `{$prefix}tickets_office_id_foreign`");

        $this->db->query("RENAME TABLE `{$prefix}offices` TO `{$prefix}departments`");

        $this->db->query("ALTER TABLE `{$prefix}users` CHANGE `office_id` `department_id` INT(11) UNSIGNED NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `{$prefix}tickets` CHANGE `office_id` `department_id` INT(11) UNSIGNED NOT NULL");

        $this->db->query("ALTER TABLE `{$prefix}users` ADD CONSTRAINT `{$prefix}users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `{$prefix}departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        $this->db->query("ALTER TABLE `{$prefix}tickets` ADD CONSTRAINT `{$prefix}tickets_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `{$prefix}departments`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

        $this->db->query("ALTER TABLE `{$prefix}tickets` MODIFY COLUMN `status` ENUM('Open','In Progress','Pending','Resolved','Closed') NOT NULL DEFAULT 'Open'");

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        $prefix = $this->db->getPrefix();

        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        $this->db->query("ALTER TABLE `{$prefix}tickets` MODIFY COLUMN `status` ENUM('Open','In Progress','Waiting on Student','Resolved','Closed') NOT NULL DEFAULT 'Open'");

        $this->db->query("ALTER TABLE `{$prefix}users` DROP FOREIGN KEY `{$prefix}users_department_id_foreign`");
        $this->db->query("ALTER TABLE `{$prefix}tickets` DROP FOREIGN KEY `{$prefix}tickets_department_id_foreign`");

        $this->db->query("ALTER TABLE `{$prefix}users` CHANGE `department_id` `office_id` INT(11) UNSIGNED NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `{$prefix}tickets` CHANGE `department_id` `office_id` INT(11) UNSIGNED NOT NULL");

        $this->db->query("RENAME TABLE `{$prefix}departments` TO `{$prefix}offices`");

        $this->db->query("ALTER TABLE `{$prefix}users` ADD CONSTRAINT `{$prefix}users_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `{$prefix}offices`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        $this->db->query("ALTER TABLE `{$prefix}tickets` ADD CONSTRAINT `{$prefix}tickets_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `{$prefix}offices`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
