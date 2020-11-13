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
        
        $config['additional_fields']['default_workday_time']['get'] = function ($item) {
            $workday_minutes_total = $item->contract_hours * 2.75;
            $workday_hours = floor($workday_minutes_total / 60);
            $workday_minutes = $workday_minutes_total % 60;
            return sprintf("%02s", $workday_hours) . ':' . sprintf("%02s", $workday_minutes);
            
        };
        
        parent::configure($config);
    }
 
}
