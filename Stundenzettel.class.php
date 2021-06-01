<?php

/**
 * Stundenzettel.class.php
 *
 * ...
 *
 * @author  Annelene Sudau <asudau@uos.de>
 * @version 1.0
 */

require_once 'constants.inc.php';
require_once 'models/StundenzettelContract.class.php';
require_once 'models/StundenzettelTimesheet.class.php';

class Stundenzettel extends StudipPlugin implements SystemPlugin
{

    public function __construct()
    {
        parent::__construct();
        global $perm;

        //Personen mit Verwaltungsrolle für Stumis oder Stumis mit in StudIP hinterlegtem Arbeitsvertrag
        if( $GLOBALS['perm']->have_perm('tutor') && $this->hasStumiContract () ){
            $this->setupStundenzettelNavigation();
        } else if( $GLOBALS['perm']->have_perm('dozent') && $this->hasStumiAdminrole() ) {
            $this->setupAdminNavigation();
        } else if ($GLOBALS['perm']->have_perm('dozent') && $this->isStumiSupervisor()) {
            $this->setupSupervisorNavigation();
        }
    }

    public function initialize ()
    {
        
    }
    
    private function setupAdminNavigation()
    {
        $navigation = new Navigation('Stundenzettelverwaltung');
        $navigation->setURL(PluginEngine::getURL($this, array(), 'index'));

        $item = new Navigation(_('Übersicht'), PluginEngine::getURL($this, array(), 'index'));
        $navigation->addSubNavigation('index', $item);

        $item = new Navigation(_('Stundenzettel verwalten'), PluginEngine::getURL($this, array(), 'timesheet/admin_index'));
        $navigation->addSubNavigation('timesheets', $item);

        Navigation::addItem('tools/stundenzettelverwaltung', $navigation);  
    }
    
    private function setupStundenzettelNavigation()
    {
        $navigation = new Navigation('Stundenzettel');
        $navigation->setURL(PluginEngine::getURL($this, array(), 'timesheet/timesheet'));
        
        $item = new Navigation(_('Stundenerfassung'), PluginEngine::getURL($this, array(), 'timesheet/timesheet'));
        $navigation->addSubNavigation('timetracking', $item);
        
        $item = new Navigation(_('Alle Stundenzettel verwalten'), PluginEngine::getURL($this, array(), 'timesheet'));
        $navigation->addSubNavigation('timesheets', $item);
        
        $item = new Navigation(_('Vertragsübersicht'), PluginEngine::getURL($this, array(), 'index'));
        $navigation->addSubNavigation('index', $item);

        Navigation::addItem('tools/stundenzettelverwaltung', $navigation);  
    }
    
    private function setupSupervisorNavigation()
    {
        $navigation = new Navigation('Stundenzettelverwaltung');
        $navigation->setURL(PluginEngine::getURL($this, array(), 'index'));

        $item = new Navigation(_('Übersicht'), PluginEngine::getURL($this, array(), 'index'));
        $navigation->addSubNavigation('index', $item);

        $item = new Navigation(_('Stundenzettel verwalten'), PluginEngine::getURL($this, array(), 'timesheet/admin_index'));
        $navigation->addSubNavigation('timesheets', $item);

        Navigation::addItem('tools/stundenzettelverwaltung', $navigation);  
    }
    
    public function getCommentOptions ()
    {
        return array(
            'Krank',
            'Urlaub',
            'Feiertag'
        );
    }
    
    public function getMonths ()
    {
        return array('01', '02','03','04','05','06','07','08','09','10','11','12');
    }
    
    public function getYears ()
    {
        return array( '2020', '2021','2022','2023','2024','2025','2026','2027');
    }
    
    public function hasStumiAdminrole ()
    {
        return RolePersistence::isAssignedRole($GLOBALS['user']->user_id, \Stundenzettelverwaltung\STUNDENVERWALTUNG_ROLE);
    }
    
    public function getAdminInstIds () {
        $roles = RolePersistence::getAllRoles();
        foreach($roles as $role) {
            if($role->getRolename() == \Stundenzettelverwaltung\STUNDENVERWALTUNG_ROLE) {
                $role_id = $role->getRoleid();
            }
        }
        $inst_ids = RolePersistence::getAssignedRoleInstitutes($GLOBALS['user']->user_id, $role_id);
        //keine leeren Einträge
        return array_filter($inst_ids);
    }
    
    public function hasStumiContract ()
    {
        return StundenzettelContract::findByUser_id($GLOBALS['user']->user_id);
    }
    
    public function isStumiSupervisor ()
    {
        return StundenzettelContract::findBySupervisor($GLOBALS['user']->user_id);
    }
    
    public function can_access_contract_timesheets($contract_id)
    {
        $contract = StundenzettelContract::find($contract_id);
        if ($contract->supervisor == User::findCurrent()->user_id || $contract->user_id == User::findCurrent()->user_id ){
            return true;
        } else {
            return false;
        }
    }
    
    public function can_access_timesheet($timesheet_id) 
    {
        $timesheet = StundenzettelTimesheet::find($timesheet_id);
        return self::can_access_contract_timesheets($timesheet->contract_id);
    }

    public function perform($unconsumed_path)
    {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
        
    }

    private function setupAutoload()
    {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }
}
