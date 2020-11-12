<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar       $id
 * @property varchar       $stumi_id
 * @property varchar       $inst_id
 * @property int           $contract_hours
 * @property varchar       $supervisor
 * @property int           $contract_begin
 * @property int           $contract_end
 */

class StundenzettelStumiContract extends \SimpleORMap
{
    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'stundenzettel_stumi_contracts';
        
        parent::configure($config);
    }
 
}
