<?php

//require_once __DIR__ . '/../models/...class.php';

class IndexController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Studentische MitarbeiterInnen - Übersicht"));
        
        // Check permissions to be on this site
        if ( !($this->plugin->hasStumiAdminrole() || $this->plugin->hasStumiContract ()) ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }

    }

    public function index_action($inst_id = '477d184367f48cc210f74bb4f779c7b7')
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/index');
        $views = new ViewsWidget();
        $views->addLink(_('Übersicht über Studentische MitarbeiterInnen'),
                        $this->url_for('index'))
              ->setActive($action === 'index');
        $this->inst_id = $inst_id;
        
        $groups = Statusgruppen::findBySQL('`name` LIKE ? AND `range_id` LIKE ?', ['%Studentische%', $inst_id]);
        foreach ($groups as $group) {
            foreach ($group->members as $member) {
                $this->stumis[] = User::find($member->user_id);
                $this->stumi_contracts[$member->user_id] = StundenzettelStumiContract::findBySQL('`stumi_id` LIKE ? AND `inst_id` LIKE ?', [$member->user_id, $inst_id]);
            }
        }
        
        
        
        Sidebar::get()->addWidget($views);

    }
    
    public function institute_settings_action(){
        $groups = Statusgruppen::findByRangeID($inst_id);
        
    }
    
    // customized #url_for for plugins
    public function url_for($to = '')
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }
    
}
