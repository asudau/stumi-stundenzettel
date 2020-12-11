<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar       $id
 * @property varchar       $timesheet_id
 * @property int           $day
 * @property int           $begin
 * @property int           $end
 * @property int           $break
 * @property decimal       $sum
 * @property enum          $defined_comment 
 * @property varchar       $comment
 * @property int           $entry_mktime

 */

class StundenzettelRecord extends \SimpleORMap
{
    private static $holidays_nds = array(
        '01.01.' => 'Neujahr',
        '10.04.' => 'Karfreitag',
        '01.05.' => 'Tag der Arbeit',
        '21.05.' => 'Himmelfahrt',
        '01.06.' => 'Pfingsten',
        '03.10.' => 'Tag der Deutschen Einheit',
        '31.10.' => 'Reformationstag',
        '24.12.' => 'Heiligabend',
        '25.12.' => '1. Weihnachstfeiertag',
        '26.12.' => '2. Weihnachtsfeiertag',
        '31.12.' => 'Silvester'
        );
    
    private static $uni_closed = array(
        '27.12.' => 'Universitätsbetrieb geschlossen',
        '28.12.' => 'Universitätsbetrieb geschlossen',
        '29.12.' => 'Universitätsbetrieb geschlossen',
        '30.12.' => 'Universitätsbetrieb geschlossen'
        );
    
    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'stundenzettel_records';
        
        $config['belongs_to']['timesheet'] = [
            'class_name'  => 'StundenzettelTimesheet',
            'foreign_key' => 'timesheet_id',];
        
        parent::configure($config);
    }
    
    function calculate_sum(){
        $begintime = strtotime($this->begin);
        $endtime = strtotime($this->end);
        if(in_array($this->defined_comment, ['Urlaub', 'Krank', 'Feiertag']) && !$this->isWeekend() ) {
            $this->sum = $this->timesheet->contract->default_workday_time;
        } else if( $begintime > 0 && $endtime > 0 ) {
            $this->sum = StundenzettelTimesheet::calcTimeDifference($this->begin, $this->end, $this->break);
        } else {
            $this->sum = '';
        }
    }
    
    function sum_to_seconds()
    {
        $sum = explode(':', $this->sum);
        return $sum[0]*3600 + $sum[1]*60;
    }
    
    function getWeekday() 
    {
        return date('w', strtotime($this->getDate()));
    }
    
    function getDate() 
    {
        $timesheet = StundenzettelTimesheet::find($this->timesheet_id);
        return sprintf("%02s", $this->day) . '.' . sprintf("%02s", $timesheet->month) . '.' . sprintf("%02s", $timesheet->year);
    }
    
    function isWeekend()
    {
        return in_array($this->getWeekday(), ['6', '0']);
    }
    
    function isHoliday()
    {
        return array_key_exists(substr($this->getDate(),0,6), self::$holidays_nds);
    }
    
    static function isDateWeekend($date)
    {
        $day = date('w', strtotime($date));
        return in_array($day, ['6', '0']);
    }
    
    static function isDateHoliday($date)
    {
        return array_key_exists(substr($date, 0, 6), self::$holidays_nds);
    }
    
    static function isUniClosed($date)
    {
        return array_key_exists(substr($date, 0, 6), self::$uni_closed);
    }
    
    static function isEditable($date)
    {
        $date_time = new DateTime($date);
        $today = new DateTime('now');
        return (!self::isUniClosed($date) && !self::isDateHoliday($date) && !self::isDateWeekend($date) && ($date_time <= $today));
    }
}