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
    private static $groups = array(
        'admin' => 'Zusätzliche Datenfelder für Nutzer mit administratver Freischaltung',
        'doktorandendaten'=> 'Persönliche Daten',
        'promotionsdaten' => 'Daten zur Promotion',
        'ersteinschreibung'=> 'Daten zur Ersteinschreibung',
        'abschlusspruefung'=> 'Daten zur Promotion berechtigenden Abschlussprüfung',
        'hzb' => 'Daten zur Hochschulzugangsberechtigung (HZB)'
        );
    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'stundenzettel_records';

        parent::configure($config);
    }
    
    function calculate_sum(){
        $begin_array = explode(':', $this->begin);
        $end_array = explode(':', $this->end);
        $break_array = explode(':', $this->break);
        
        $minutes_total = 0;
        $hours_total = 0;
        
        //Pause auf Startzeit addieren
        if ((intval($begin_array[1]) + intval($break_array[1])) >= 60) {
            $begin_array[0] = intval($begin_array[0]) + 1;
            $begin_array[1] = (intval($begin_array[1]) + intval($break_array[1])) - 60;
        } else {
            $begin_array[1] = intval($begin_array[1]) + intval($break_array[1]);
        }
        $begin_array[0] = intval($begin_array[0]) + intval($break_array[0]);
        
        //Differenz aus korrigiertem Start und Ende
        if (($end_array[1] + (60 - $begin_array[1])) >= 60) {
            $minutes_total = ($end_array[1] + (60 - $begin_array[1])) - 60;
        } else {
            $end_array[0] -= 1;
            $minutes_total = $end_array[1] + (60 - $begin_array[1]);
        }
        
        $hours_total = $end_array[0] - $begin_array[0];
        return ($hours_total . ':' . $minutes_total);
   
    }
}