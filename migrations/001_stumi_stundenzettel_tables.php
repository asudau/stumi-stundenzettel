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
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_stumi_contracts` (
            `id` varchar(32) COLLATE latin1_bin NOT NULL,
            `stumi_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `inst_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `contract_hours` int(11) COLLATE latin1_bin NOT NULL,
            `supervisor` varchar(32) COLLATE latin1_bin NULL,
            `contract_begin` int(11) COLLATE latin1_bin NOT NULL ,
            `contract_end` int(11) COLLATE latin1_bin NOT NULL ,
            PRIMARY KEY (id)
        ) ");
        
        //add db-table for timesheet
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_timesheets` (
            `id` varchar(32) COLLATE latin1_bin NOT NULL,
            `contract_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `stumi_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `inst_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `month` int(2) COLLATE latin1_bin NOT NULL,
            `year` int(4) COLLATE latin1_bin NOT NULL,
            `finished` tinyint(1) COLLATE latin1_bin NOT NULL DEFAULT '0',
            `approved` tinyint(1) COLLATE latin1_bin NOT NULL DEFAULT '0',
            `received` tinyint(1) COLLATE latin1_bin NOT NULL DEFAULT '0',
            `complete` tinyint(1) COLLATE latin1_bin NOT NULL DEFAULT '0',
            `sum` text COLLATE latin1_bin NULL,
            PRIMARY KEY (id)
        ) ");
        
        //add db-table for record
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_records` (
            `id` varchar(32) COLLATE latin1_bin NOT NULL,
            `timesheet_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `day` int(2) COLLATE latin1_bin NOT NULL,
            `begin` varchar(5) COLLATE latin1_bin NULL,
            `end`  varchar(5) COLLATE latin1_bin NULL,
            `break` varchar(5) COLLATE latin1_bin NULL ,
            `sum` varchar(5) COLLATE latin1_bin NULL ,
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

        $db->exec("DROP TABLE stundenzettel_stumi_contracts");
        $db->exec("DROP TABLE stundenzettel_timesheets");
        $db->exec("DROP TABLE stundenzettel_records");

        SimpleORMap::expireTableScheme();
    }
}

