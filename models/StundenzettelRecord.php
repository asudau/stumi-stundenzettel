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
        $begintime = strtotime($this->begin);
        $endtime = strtotime($this->end);
        $breaktime_pts = explode(':', $this->break);
        
        if( $begintime > 0 && $endtime > 0 ) {
        $sum = $endtime - $begintime - $breaktime_pts[0]*3600 - $breaktime_pts[1]*60;
        //return date('h:i', $sum);
        $minutes = ($sum/60)%60;
        $hours = floor(($sum/60)/ 60);
        return sprintf("%02s", $hours) . ':' . sprintf("%02s", $minutes);
        } else {
            return '';
        }
    }
}