<?php

require_once __DIR__ . '/../constants.inc.php';

class StumiStundenzettelTables extends Migration
{
    public function description()
    {
        return 'Add role and DB tables for Stumi Stundenzettelverwaltung';
    }

    public function up()
    {
        
        $role = new Role();
        $role->setRolename(\Stundenzettelverwaltung\STUNDENVERWALTUNG_ROLE);
        $role->setSystemtype(false);
        RolePersistence::saveRole($role);
        
        $db = DBManager::get();
        //add db-table for stumis
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_contracts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `inst_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `contract_hours` int(11) NOT NULL,
            `supervisor` varchar(32) COLLATE latin1_bin NULL,
            `contract_begin` int(11) NOT NULL ,
            `contract_end` int(11) NOT NULL ,
            `last_year_vacation_remaining` int(11) NULL,
            `begin_digital_recording_month` int(2) NULL,
            `begin_digital_recording_year` int(4) NULL,
            `begin_balance` int(11) NULL,
            `begin_vacation_claimed` int(11) NULL,
            PRIMARY KEY (id)
        ) ");
        
        //add db-table for timesheet
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_timesheets` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `contract_id` int(11) NOT NULL,
            `month` int(2) NOT NULL,
            `year` int(4) NOT NULL,
            `finished` tinyint(1) NOT NULL DEFAULT '0',
            `approved` tinyint(1) NOT NULL DEFAULT '0',
            `received` tinyint(1) NOT NULL DEFAULT '0',
            `complete` tinyint(1) NOT NULL DEFAULT '0',
            `sum` int(11) NULL,
            PRIMARY KEY (id)
        ) ");
        
        //add db-table for record
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_records` (
            `timesheet_id` int(11) NOT NULL,
            `day` int(2) NOT NULL,
            `begin` int(11) NULL,
            `end`  int(11) NULL,
            `break` int(11) NULL ,
            `sum` int(11) NULL ,
            `defined_comment` ENUM('Krank', 'Urlaub', 'Feiertag') COLLATE latin1_bin NULL,
            `comment` varchar(255) NOT NULL,
            `entry_mktime` date COLLATE latin1_bin NULL ,
            PRIMARY KEY (`timesheet_id`, `day`)
        ) ");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DROP TABLE stundenzettel_contracts");
        $db->exec("DROP TABLE stundenzettel_timesheets");
        $db->exec("DROP TABLE stundenzettel_records");
        
       $roles = RolePersistence::getAllRoles();
       foreach($roles as $role) {
            if($role->getRolename() == \Stundenzettelverwaltung\STUNDENVERWALTUNG_ROLE) {
                RolePersistence::deleteRole($role);
            }
        }
        SimpleORMap::expireTableScheme();
    }
}

