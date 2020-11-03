<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar       $id
 * @property varchar       $stumi_id
 * @property varchar       $contract_id
 * @property varchar       $inst_id
 * @property int           $month
 * @property int           $year
 * @property tinyint       $finished
 * @property tinyint       $approved
 * @property tinyint       $received
 * @property tinyint       $complete
 * @property decimal       $sum

 */

class StundenzettelTimesheet extends \SimpleORMap
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
        $config['db_table'] = 'stundenzettel_timesheets';

        parent::configure($config);
    }
}