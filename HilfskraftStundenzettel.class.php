<?php

/**
 * HilfskraftStundenzettel.class.php
 *
 * ...
 *
 * @author  Annelene Sudau <asudau@uos.de>
 * @version 1.0
 */


class HilfskraftStundenzettel extends StudipPlugin implements SystemPlugin
{

    const STUNDENVERWALTUNG_ROLE = 'Hilfskraft-Stundenzettelverwaltung';
    
    public function __construct()
    {
        parent::__construct();
        global $perm;

        if(RolePersistence::isAssignedRole($GLOBALS['user']->user_id,
                                                            self::STUNDENVERWALTUNG_ROLE)){
            $navigation = new Navigation('Hilfskraft-Stundenzettelverwaltung');
            $navigation->setImage(Icon::create('edit', 'navigation'));
            $navigation->setURL(PluginEngine::getURL($this, array(), 'index'));
            
            $item = new Navigation(_('Übersicht'), PluginEngine::getURL($this, array(), 'index'));
            $navigation->addSubNavigation('index', $item);
            
            $item = new Navigation(_('Stundenzettel verwalten'), PluginEngine::getURL($this, array(), 'timesheet'));
            $navigation->addSubNavigation('timesheets', $item);
            
            Navigation::addItem('tools/hilfskraft-stundenverwaltung', $navigation);  
        }    
    }

    public function initialize ()
    {
        
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
