<?php

require_once __DIR__ . '/../constants.inc.php';

class ContractBeginDataTable extends Migration
{
    public function description()
    {
        return 'table for contract-begin-data ';
    }

    public function up()
    {
        
        $db = DBManager::get();
        //add db-table 
        $db->exec("CREATE TABLE IF NOT EXISTS `stundenzettel_contract_begin` (
            `contract_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `balance` varchar(6) COLLATE latin1_bin NOT NULL,
            `vacation_claimed` varchar(6) COLLATE latin1_bin NOT NULL,
            `begin_digital_recording_month` int(2) COLLATE latin1_bin NOT NULL,
            `begin_digital_recording_year` int(4) COLLATE latin1_bin NOT NULL,
            PRIMARY KEY (contract_id)
        ) ");
        
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DROP TABLE stundenzettel_contract_begin");

        SimpleORMap::expireTableScheme();
    }
}

