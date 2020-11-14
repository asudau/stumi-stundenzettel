<?php

/**
 * HilfskraftStundenzettel.class.php
 *
 * ...
 *
 * @author  Annelene Sudau <asudau@uos.de>
 * @version 1.0
 */

require_once 'constants.inc.php';

class HilfskraftStundenzettel extends StudipPlugin implements SystemPlugin
{

    
    public function __construct()
    {
        parent::__construct();
        global $perm;

        //Personen mit Verwaltungsrolle für Stumis oder Stumis mit in StudIP hinterlegtem Arbeitsvertrag
        if( $this->hasStumiContract () ){
            $this->setupHilfskraftNavigation();
        } else if( $this->hasStumiAdminrole() ) {
            $this->setupAdminNavigation();
        }
    }

    public function initialize ()
    {
        
    }
    
    private function setupAdminNavigation()
    {
        $navigation = new Navigation('Hilfskraft-Stundenzettelverwaltung');
        $navigation->setURL(PluginEngine::getURL($this, array(), 'index'));

        $item = new Navigation(_('Übersicht'), PluginEngine::getURL($this, array(), 'index'));
        $navigation->addSubNavigation('index', $item);

        $item = new Navigation(_('Stundenzettel verwalten'), PluginEngine::getURL($this, array(), 'timesheet'));
        $navigation->addSubNavigation('timesheets', $item);

        Navigation::addItem('tools/hilfskraft-stundenverwaltung', $navigation);  
    }
    
    private function setupHilfskraftNavigation()
    {
        $navigation = new Navigation('Stundenzettelverwaltung');
        $navigation->setURL(PluginEngine::getURL($this, array(), 'index'));
        
        $item = new Navigation(_('Stundenerfassung'), PluginEngine::getURL($this, array(), 'timesheet/timesheet'));
        $navigation->addSubNavigation('timetracking', $item);
        
        $item = new Navigation(_('Vertragsübersicht'), PluginEngine::getURL($this, array(), 'index'));
        $navigation->addSubNavigation('index', $item);
        
        $item = new Navigation(_('Alle Stundenzettel verwalten'), PluginEngine::getURL($this, array(), 'timesheet'));
        $navigation->addSubNavigation('timesheets', $item);

        Navigation::addItem('tools/hilfskraft-stundenverwaltung', $navigation);  
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
    
    public function hasStumiContract ()
    {
        return StundenzettelStumiContract::findByStumi_id($GLOBALS['user']->user_id);
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
