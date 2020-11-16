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
            $workday_hours = floor($item->default_workday_time_in_minutes / 60);
            $workday_minutes = $item->default_workday_time_in_minutes % 60;
            return sprintf("%02s", $workday_hours) . ':' . sprintf("%02s", $workday_minutes);
            
        };
        
        $config['additional_fields']['default_workday_time_in_minutes']['get'] = function ($item) {
            $workday_minutes_total = round($item->contract_hours /4.348 / 5 * 60);//* 2.75;
            return $workday_minutes_total;
            
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
    
    function getVacationEntitlement($year){
        $dezimal_entitlement = $this->contract_hours * $this->getContractDuration() * 0.077;
        $entitlement_hours = floor($dezimal_entitlement);
        $entitlement_minutes = ($dezimal_entitlement - $entitlement_hours) * 60;
        return sprintf("%02s", $entitlement_hours) . ':' . sprintf("%02s", round($entitlement_minutes) ); //round($entitlement_minutes, 3)
    }
    
    function getRemainingVacation($year){
        $claimed_vacation = strtotime($this->getClaimedVacation($year));
        $vacation = strtotime($this->getVacationEntitlement($year));
        $remaining_vacation = $vacation - $claimed_vacation;
        $minutes = ($remaining_vacation/60)%60;
        $hours = floor(($remaining_vacation/60)/ 60);
        return sprintf("%02s", $hours) . ':' . sprintf("%02s", $minutes);
    }
    
    function getClaimedVacation($year){
        $timesheets = StundenzettelTimesheet::findBySQL('`contract_id` LIKE ? AND `year` LIKE ?', [$this->id, $year]);
        $vacation_days = 0;
        foreach ($timesheets as $timesheet) {
            $records = StundenzettelRecord::findBySQL('`timesheet_id` = ? AND `defined_comment` = "Urlaub"', [$timesheet->id]);
            $vacation_days += sizeof($records);
        }
        
        $vacation_minutes_total = ($vacation_days * $this->default_workday_time_in_minutes);
        $vacation_hours = floor($vacation_minutes_total / 60);
        $vacation_minutes = $vacation_minutes_total % 60;
        return sprintf("%02s", $vacation_hours) . ':' . sprintf("%02s", $vacation_minutes);
    }
    
    function getWorktimeBalance(){
        $timesheets = StundenzettelTimesheet::findBySQL('`contract_id` LIKE ?', [$this->id]);
        $balance_hours = 0.0;
        $balance_minutes = 0.0;
        foreach ($timesheets as $timesheet) {
            if (strtotime($timesheet->year . '-' . $timesheet->month . '-28') < time()){
                $balance_hours += explode(':', $timesheet->sum)[0] - $this->contract_hours; //TODO 02:30 - 12
                $balance_minutes += explode(':', $timesheet->sum)[1];
            }
        }
        $balance_hours += floor($balance_minutes/60);
        $balance_minutes = $balance_minutes % 60;
        
        return sprintf("%02s", $balance_hours) . ':' . sprintf("%02s", $balance_minutes);
    }
 
}
