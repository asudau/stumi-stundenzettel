<?php

//require_once __DIR__ . '/../models/...class.php';

class TimesheetController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Stundenzettel verwalten"));

    }

    public function index_action($stumi_id='4f9fba21e13a1a4d5858591f7c145e69', $inst_id = '477d184367f48cc210f74bb4f779c7b7')
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timesheets');
//        $views = new ViewsWidget();
//        $views->addLink(_('Stundenzettel verwalten'),
//                        $this->url_for('index'))
//              ->setActive($action === 'index');
        $this->inst_id = $inst_id;
        $this->stumi_id = $stumi_id;
        $timesheet_id = '477d184367f48cc210f74bb4f779c724';
        $this->timesheet = StundenzettelTimesheet::find($timesheet_id);
        $this->records = StundenzettelRecord::findByTimesheet_Id($timesheet_id, 'ORDER BY day ASC');

        //Sidebar::get()->addWidget($views);

    }
    
    public function pdf_action($timesheet_id = '477d184367f48cc210f74bb4f779c724')
    {
        $timesheet = StundenzettelTimesheet::find($timesheet_id);
        $this->path = $timesheet->build_pdf();
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
