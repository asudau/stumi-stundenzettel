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
            `id` varchar(32) NOT NULL,
            `stumi_id` varchar(32) NOT NULL,
            `inst_id` varchar(32) NOT NULL,
            `contract_hours` int(11) NOT NULL,
            `supervisor` varchar(32) NULL,
            `contract_begin` int(11) NOT NULL ,
            `contract_end` int(11) NOT NULL ,
            PRIMARY KEY (id)
        ) ");
        
        //add db-table for timesheet
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_timesheets` (
            `id` varchar(32) NOT NULL,
            `contract_id` varchar(32) NOT NULL,
            `stumi_id` varchar(32) NOT NULL,
            `inst_id` varchar(32) NOT NULL,
            `month` int(2) NOT NULL,
            `year` int(4) NOT NULL,
            `finished` tinyint(1) NOT NULL DEFAULT '0',
            `approved` tinyint(1) NOT NULL DEFAULT '0',
            `received` tinyint(1) NOT NULL DEFAULT '0',
            `complete` tinyint(1) NOT NULL DEFAULT '0',
            `sum` DECIMAL (6, 2) NOT NULL,
            PRIMARY KEY (id)
        ) ");
        
        //add db-table for record
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_records` (
            `id` varchar(32) NOT NULL,
            `timesheet_id` varchar(32) NOT NULL,
            `day` varchar(32) NOT NULL,
            `begin` int(11) NULL,
            `end`  int(11) NULL,
            `break` int(11) NULL ,
            `sum` DECIMAL (6, 2) NULL ,
            `defined_comment` ENUM('Krank', 'Urlaub', 'Feiertag') NULL,
            `comment` varchar(255) NOT NULL,
            `entry_mktime` int(11) NULL ,
            PRIMARY KEY (id)
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

