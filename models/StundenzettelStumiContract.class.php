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
    
    static function getCurrentContractId($user_id)
    {
        $contracts = self::findByStumi_id($user_id);
        $contract_id = '';
        foreach ($contracts as $contract) {
            if (intval($contract->contract_begin) < time() && intval($contract->contract_end) > time()) {
                $contract_id = $contract->id;
            }
        }
        return $contract_id;
    }
    
    function getContractDuration()
    {
        $begin_date = new \DateTime();
        $begin_date->setTimestamp($this->contract_begin);
        $end_date = new \DateTime();
        $end_date->setTimestamp($this->contract_end);
        
        $interval = date_diff($begin_date, $end_date);
        $month = $interval->y * 12 + $interval->m;
        if ($interval->d >15){
            $month++;   //php date_diff tut sich hier leider schwer 
                        //1.10.2020 bis 31.10.2020 ist ein Monat 
                        //aber 1.11.2020-30.11.2020 is 0 Monate und 29 tage
        }
        return $month;
    }
    
    function getVacationEntitlement($year)
    {
        $dezimal_entitlement = $this->contract_hours * $this->getContractDuration() * 0.077; //TODO nicht duration sondern pro Jahr
        $entitlement_hours = floor($dezimal_entitlement);
        $entitlement_minutes = ($dezimal_entitlement - $entitlement_hours) * 60;
        return sprintf("%02s", $entitlement_hours) . ':' . sprintf("%02s", round($entitlement_minutes) ); //round($entitlement_minutes, 3)
    }
    
    //function subtractTimes
    function getRemainingVacation($year)
    {
        return StundenzettelTimesheet::subtractTimes($this->getVacationEntitlement($year), $this->getClaimedVacation($year));
    }
    
    function getClaimedVacation($year)
    {
        $timesheets = StundenzettelTimesheet::findBySQL('`contract_id` LIKE ? AND `year` LIKE ?', [$this->id, $year]);
        $vacation_days = 0;
        foreach ($timesheets as $timesheet) {
            $records = StundenzettelRecord::findBySQL('`timesheet_id` = ? AND `defined_comment` = "Urlaub"', [$timesheet->id]);
            $vacation_days += sizeof($records);
        }
        
        return StundenzettelTimesheet::multiplyMinutes($this->default_workday_time_in_minutes, $vacation_days);
    }
    
    function getWorktimeBalance()
    {
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

    function add_missing_timesheets()
    {
        $current_month = date('m', time());
        $current_year = date('Y', time());
        $month = new DateTime();
        $month->setTimestamp($this->contract_begin);
        $i = 0;
        if ($this->contract_begin < strtotime($current_year . '-' . $current_month . '-01')) {
            while ($month->getTimestamp() < time()){
                $this->add_timesheet($month->format('m'), date('Y', $this->contract_begin));
                $month->modify('+1 month');
            }
        }
    }
    
    function add_timesheet($month, $year)
    {
        $timesheet = StundenzettelTimesheet::getContractTimesheet($this->id, $month, $year);
        if (!$timesheet) {
            if ( (intval($this->contract_begin) < strtotime($year . '-' . $month . '-28')) && (strtotime($year . '-' . $month . '-01') < intval($this->contract_end)) ) {
                $timesheet = new StundenzettelTimesheet();
                $timesheet->month = $month;
                $timesheet->year = $year;
                $timesheet->contract_id = $this->id;
                $timesheet->stumi_id = $this->stumi_id;
                $timesheet->inst_id = $this->inst_id;
                $timesheet->store();
            }
        }
    }
}
