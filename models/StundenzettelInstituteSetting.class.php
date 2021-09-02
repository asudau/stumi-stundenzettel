<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar       $id
 * @property varchar       $institute_id
 * @property varchar       $inst_mail      //Sekretariat oder zuständige Verwaltungsmitarbeiterin
 * @property varchar       $hilfskraft_statusgruppen
 * @property int           $entry_mktime

 */


class StundenzettelInstituteSetting extends \SimpleORMap
{
    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'stundenzettel_institute_settings';
        
        $config['belongs_to']['institute'] = [
            'class_name'  => 'Institute',
            'foreign_key' => 'institute_id',];
        
        $config['additional_fields']['stumi_statusgroups']['get'] = function ($item) {
            foreach (explode(',', $item->hilfskraft_statusgruppen) as $statusgruppen_id){
                $groups[] = Statusgruppen::find($statusgruppen_id);
            }
            return $groups;
        };
        
        $config['additional_fields']['stumi_statusgroup_ids']['get'] = function ($item) {
            return explode(',', $item->hilfskraft_statusgruppen);
        };
        
        parent::configure($config);
    }
    
    public function __construct($id = null)
    {
        parent::__construct($id);

    }
    
}