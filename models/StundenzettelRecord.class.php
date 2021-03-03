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

require_once 'lib/calendar_functions.inc.php';

class StundenzettelRecord extends \SimpleORMap
{
    
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
    
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->registerCallback('before_store', 'before_store');
    }
    
    //formale Vorgaben zum Ausfüllen der Stundenzettel prüfen
    protected function before_store()
    {
        if ($this->sum < 0){
            throw new Exception(sprintf(_('Gesamtsumme der Arbeitszeit pro Tag muss positiv sein.')));
        }
        if ($this->sum && ($this->sum > (10*3600))){
            throw new Exception(sprintf(_('Die tägliche Arbeitszeit darf 10 Stunden nicht überschreiten.')));
        } else if ($this->sum && ($this->sum > (9*3600)) && ($this->break < 2700)){
            throw new Exception(sprintf(_('Bei einer Arbeitszeit von mehr als neun Stunden ist eine Pause von mindestens 45 Minuten gesetzlich vorgeschrieben.')));
        } else if ($this->sum && ($this->sum > (6*3600)) && ($this->break < 1800)){
            throw new Exception(sprintf(_('Bei einer Arbeitszeit von mehr als sechs Stunden ist eine Pause von mindestens 30 Minuten gesetzlich vorgeschrieben.')));
        }
        if ($this->begin && ($this->begin < strtotime($this->getDate() . ' 06:00'))){
            throw new Exception(sprintf(_('Arbeitszeit kann frühestens ab 6 Uhr erfasst werden.')));
        }
        if ($this->end && ($this->end > strtotime($this->getDate() . ' 23:00'))){
            throw new Exception(sprintf(_('Arbeitszeit kann bis maximal 23 Uhr erfasst werden')));
        }
    }
    
    function calculate_sum(){
        if(in_array($this->defined_comment, ['Urlaub', 'Krank', 'Feiertag']) && !$this->isWeekend() ) {
            $this->sum = StundenzettelTimesheet::stundenzettel_strtotimespan($this->timesheet->contract->default_workday_time);
        } else {//if( $this->begin > 0 && $this->end > $this->begin ) {
            $this->sum = $this->end - $this->begin - $this->break;
        } //else {
//            $this->sum = '';
//        }
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
        $holiday = holiday(strtotime($this->getDate()));
        if ($holiday && ($holiday['col'] == 3 || $holiday['name'] == 'Reformationstag')){
            return true ;
        } else return false;
    }
    
    function isUniClosed()
    {
        return array_key_exists(substr($this->getDate(),0,6), self::$uni_closed);
    }
    
    static function isDateWeekend($date)
    {
        $day = date('w', strtotime($date));
        return in_array($day, ['6', '0']);
    }
    
    static function isDateHoliday($date)
    {
        $holiday = holiday(strtotime($date));
        if ($holiday && ($holiday['col'] == 3 || $holiday['name'] == 'Reformationstag')){
            return true ;
        } else return false;
    }
    
    static function isUniClosedOnDate($date)
    {
        return array_key_exists(substr($date, 0, 6), self::$uni_closed);
    }
    
    static function isEditable($date)
    {
        $date_time = new DateTime($date);
        $today = new DateTime('now');
        return (!self::isUniClosedOnDate($date) && !self::isDateHoliday($date) && !self::isDateWeekend($date) && ($date_time <= $today));
    }
}