<?php

require_once __DIR__ . '/../constants.inc.php';

class AddTableInstituteSettings extends Migration
{
    public function description()
    {
        return 'Add table for individual institute settings';
    }

    public function up()
    {
        
        $db = DBManager::get();
        //add db-table for stumis
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_institute_settings` (
            `inst_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `inst_mail` varchar(255) NULL,
            `hilfskraft_statusgruppen` varchar(255) NULL,
            PRIMARY KEY (inst_id)
        ) "); 

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DROP TABLE stundenzettel_institute_settings");
        
        SimpleORMap::expireTableScheme();
    }
}

