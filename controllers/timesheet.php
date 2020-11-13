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
        $this->timesheets = StundenzettelTimesheet::findByContract_id($contract_id, 'ORDER by `year` ASC, `month` ASC'); 
        
        $this->inst_id = $inst_id;
        $this->stumi_id = $stumi_id;
        
        $this->records = StundenzettelRecord::findByTimesheet_Id($timesheet_id, 'ORDER BY day ASC');

        //Sidebar::get()->addWidget($views);

    }
    
    public function select_action($contract_id)
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timesheets');
        $month = Request::get('month');
        $year = Request::get('year');
        $contract = StundenzettelStumiContract::find($contract_id);
        $this->timesheet = StundenzettelTimesheet::findOneBySQL('`contract_id` LIKE ? AND `month` LIKE ? AND `year` LIKE ?', [$contract_id, $month, $year]);
        if (!$this->timesheet) {
            if ( (intval($contract->contract_begin) < strtotime($year . '-' . $month . '-28')) && (strtotime($year . '-' . $month . '-01') < intval($contract->contract_end)) ) {
                $timesheet = new StundenzettelTimesheet();
                $timesheet->month = $month;
                $timesheet->year = $year;
                $timesheet->contract_id = $contract_id;
                $timesheet->stumi_id = $contract->stumi_id;
                $timesheet->inst_id = $contract->inst_id;
                $timesheet->store();
                $this->redirect('timesheet/timesheet/' . $timesheet->id);
            } else {
                PageLayout::postMessage(MessageBox::error(_("Dieser Monat liegt außerhalb des Vertragszeitraums."))); 
                $this->render_action('timesheet');
            }
        } else {
            $this->redirect('timesheet/timesheet/' . $this->timesheet->id);
        }
        
    }
    
    
    public function timesheet_action($timesheet_id)
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timesheets');
        
        $sidebar = Sidebar::Get();
        //Sidebar::Get()->setTitle('Stundenzettel von ' . $GLOBALS['user']->username);
        
        $actions = new ActionsWidget();
        $actions->setTitle('Aktionen');
        $actions->addLink(
                _('Stundenzettel einreichen'),
                PluginEngine::getLink($this->plugin, [], 'timesheet/send/' . $timesheet_id ),
                Icon::create('share', 'new'),
                ['title' => 'Achtung, anschließend keine Bearbeitung mehr möglich!',
                    'onclick' => "return confirm('Bearbeitung abschließen und Stundenzettel offiziell einreichen?')"]
            );
        if (true) {
            $actions->addLink(
                _('PDF-zum Ausdruck generieren'),
                PluginEngine::getLink($this->plugin, [], 'timesheet/pdf/' . $timesheet_id ),
                Icon::create('file-pdf', 'clickable')
            );
        }
        $sidebar->addWidget($actions);

        $this->timesheet = StundenzettelTimesheet::find($timesheet_id);
        $this->days_per_month = cal_days_in_month(CAL_GREGORIAN, $this->timesheet->month, $this->timesheet->year);
        
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
        //$sum_array = Request::getArray('sum');
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
                $record->entry_mktime = $mktime_array[$i];
                $record->defined_comment = ($record->isHoliday()) ? 'Feiertag' : $defined_comment_array[$i];
                $record->comment = $comment_array[$i];
                $record->calculate_sum();
                $record->store();         
        }
        
        $timesheet = $record->timesheet;
        $timesheet->calculate_sum();
        
        PageLayout::postMessage(MessageBox::success(_("Änderungen gespeichert."))); 
        $this->redirect('timesheet/timesheet/' . $timesheet_id);
    }
    
    public function pdf_action($timesheet_id)
    {
        $timesheet = StundenzettelTimesheet::find($timesheet_id);
        if($timesheet){
            $timesheet->build_pdf();
        } else {
            PageLayout::postMessage(MessageBox::success(_("Stundenzettel konnte nicht generiert werden.")));
            $this->redirect('timesheet/index');
        }
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
