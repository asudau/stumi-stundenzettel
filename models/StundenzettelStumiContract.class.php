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
    
    static function getCurrentContractId($user_id) {
        $contracts = self::findByStumi_id($user_id);
        $contract_id = '';
        foreach ($contracts as $contract) {
            if (intval($contract->contract_begin) < time() && intval($contract->contract_end) > time()) {
                $contract_id = $contract->id;
            }
        }
        return $contract_id;
    }
    
    function getContractDuration(){
        
        $begin_date = new \DateTime();
        $begin_date->setTimestamp($this->contract_begin);
        $end_date = new \DateTime();
        $end_date->setTimestamp($this->contract_end);
        
        $interval = date_diff($begin_date, $end_date);
        $month = $interval->y * 12 + $interval->m;
        if ($interval->d >15){
            $month++;   //php date_diff tut sich hier leider schwer 
                        //1.10.2020 bis 31.10.2020 ist ein monate 
                        //aber 1.11.2020-30.11.2020 is 0 monate und 29 tage
        }
        return $month;
    }
    
    function getVacationEntitlement(){
        $entitlement = $this->contract_hours * $this->getContractDuration() * 0.077;
        return $entitlement;
    }
    
 
}
