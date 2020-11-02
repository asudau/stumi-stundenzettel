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
            
            //$item = new Navigation(_('Studentische MitarbeiterInnen'), PluginEngine::getURL($this, array(), 'index/members'));
            //$navigation->addSubNavigation('members', $item);
            
            Navigation::addItem('tools/hilfskraft-stundenverwaltung', $navigation);  
        }    
    }

    public function initialize ()
    {
        
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
