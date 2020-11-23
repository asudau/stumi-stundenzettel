<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar       $contract_id
 * @property int           $begin_digital_recording_month
 * @property int           $begin_digital_recording_year
 * @property varchar       $vacation_claimed
 * @property varchar       $balance
 */

class StundenzettelContractBegin extends \SimpleORMap
{
    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'stundenzettel_contract_begin';
        
        parent::configure($config);
    }

}
