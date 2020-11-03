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
}