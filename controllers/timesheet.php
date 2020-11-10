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
        
        // Check permissions to be on this site
        if ( !($this->plugin->hasStumiAdminrole() || $this->plugin->hasStumiContract ()) ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung."));
        }
        
        if ($this->plugin->hasStumiAdminrole ()) {
            $this->adminrole = true;
        }
        if ($this->plugin->hasStumiContract ()) {
            $this->stumirole = true;
        }

    }

    public function index_action($contract_id)
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timesheets');
//        $views = new ViewsWidget();
//        $views->addLink(_('Stundenzettel verwalten'),
//                        $this->url_for('index'))
//              ->setActive($action === 'index');
        
        $this->contract = StundenzettelStumiContract::find($contract_id);
        $this->timesheets = StundenzettelTimesheet::findByContract_id($contract_id); 
        
        $this->inst_id = $inst_id;
        $this->stumi_id = $stumi_id;
        
        $this->records = StundenzettelRecord::findByTimesheet_Id($timesheet_id, 'ORDER BY day ASC');

        //Sidebar::get()->addWidget($views);

    }
    
    public function timesheet_action($timesheet_id)
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timesheets');

        $this->timesheet = StundenzettelTimesheet::find($timesheet_id);
        
        $this->inst_id = $this->timesheet->inst_id;
        $this->stumi_id = $this->timesheet->stumi_id;

        $this->records = StundenzettelRecord::findByTimesheet_Id($timesheet_id, 'ORDER BY day ASC');

        //Sidebar::get()->addWidget($views);

    }
    
    
    public function save_timesheet_action($timesheet_id)
    {
        $record_ids_array = Request::getArray('record_id');
        $begin_array = Request::getArray('begin');
        $end_array = Request::getArray('end');
        $break_array = Request::getArray('break');
        $sum_array = Request::getArray('sum');
        $mktime_array = Request::getArray('entry_mktime');
        $defined_comment_array = Request::getArray('defined_comment');
        $comment_array = Request::getArray('comment');
        
        $limit = count($begin_array);
        for ($i = 1; $i <= $limit; $i++) {
           
            $record = StundenzettelRecord::find([$timesheet_id, $i]);
            if (!$record) {
                $record = new StundenzettelRecord();
                $record->timesheet_id = $timesheet_id;
                $record->day = $i;
            }
                $record->begin = $begin_array[$i];
                $record->end = $end_array[$i];
                $record->break = $break_array[$i];
                $record->sum = $record->calculate_sum();
                $record->entry_mktime = $mktime_array[$i];
                $record->defined_comment = $defined_comment_array[$i];
                $record->comment = $comment_array[$i];
                $record->store();         
        }
        
        $this->redirect('timesheet/index');
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
