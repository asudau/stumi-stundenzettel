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
        if ( !($this->plugin->hasStumiAdminrole() || $this->plugin->hasStumiContract () || $this->plugin->isStumiSupervisor()) ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung."));
        }
        
        if ($this->plugin->hasStumiAdminrole ()) {
            $this->adminrole = true;
        }
        if ($this->plugin->hasStumiContract ()) {
            $this->stumirole = true;
        }
        if ($this->plugin->isStumiSupervisor ()) {
            $this->supervisorrole = true;
        }

    }

    public function index_action($contract_id = NULL)
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timesheets');
        
        //allgemeine Stundenzettel-Übersichtsseite für Stumis verwendet automatisch den aktuell laufenden Vertrag
        if (!$contract_id && $this->stumirole) {
            $contract_id = StundenzettelStumiContract::getCurrentContractId($GLOBALS['user']->user_id);
        }
        $this->contract = StundenzettelStumiContract::find($contract_id);
        $this->timesheets = StundenzettelTimesheet::findByContract_id($contract_id, 'ORDER by `year` ASC, `month` ASC'); 
        $this->stumi = User::find($this->contract->stumi_id);
        
        $this->records = StundenzettelRecord::findByTimesheet_Id($timesheet_id, 'ORDER BY day ASC');
        
        $this->status_infos = StundenzettelStumiContract::getStaus_array();

    }
    
    public function select_action($contract_id, $month = '', $year = '')
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timesheets');
        if (Request::get('month')) {
            $month = Request::get('month');
            $year = Request::get('year');
        }
        $contract = StundenzettelStumiContract::find($contract_id);
        $this->timesheet = StundenzettelTimesheet::getContractTimesheet($contract_id, $month, $year);
        if (!$this->timesheet && $this->stumirole) {
            if ($contract->monthPartOfContract($month, $year)) {
            //if ( (intval($contract->contract_begin) < strtotime($year . '-' . $month . '-28')) && (strtotime($year . '-' . $month . '-01') < intval($contract->contract_end)) ) {
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
                $this->no_timesheet = true;
                $this->month = $month;
                $this->year = $year;
                $this->contract = $contract;
                $this->render_action('timesheet');
            }
        } else if (!$this->timesheet && $this->adminrole){
            PageLayout::postMessage(MessageBox::error(_("Für diesen Monat liegt kein Stundenzettel vor."))); 
            $this->render_action('timesheet');
        } else {
            $this->redirect('timesheet/timesheet/' . $this->timesheet->id);
        }
        
    }
    
    
    public function timesheet_action($timesheet_id)
    {
        if ($this->stumirole){
            Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timetracking');
        } else {
            Navigation::activateItem('tools/hilfskraft-stundenverwaltung/timesheets');
        }
        
        $sidebar = Sidebar::Get();
        //Sidebar::Get()->setTitle('Stundenzettel von ' . $GLOBALS['user']->username);
        
        if(!$timesheet_id && $this->stumirole){
            $contract_id = StundenzettelStumiContract::getCurrentContractId($GLOBALS['user']->user_id);
            //$timesheet = StundenzettelTimesheet::getContractTimesheet($contract_id, date('m', time()), date('Y', time()));
            //$timesheet_id = $timesheet->id;
            $this->redirect('timesheet/select/' . $contract_id . '/' . date('m', time()) . '/' . date('Y', time()));
        }

        $this->timesheet = StundenzettelTimesheet::find($timesheet_id);
        $this->days_per_month = cal_days_in_month(CAL_GREGORIAN, $this->timesheet->month, $this->timesheet->year); 
        $this->inst_id = $this->timesheet->inst_id;
        $this->stumi_id = $this->timesheet->stumi_id;
        $this->records = StundenzettelRecord::findByTimesheet_Id($timesheet_id, 'ORDER BY day ASC');
        
        if($this->timesheet->locked){
            PageLayout::postMessage(MessageBox::info(_("Bearbeitung gesperrt."))); 
        }
        
        if($this->stumirole){
            $actions = new ActionsWidget();
            $actions->setTitle('Aktionen');
            
            if (!$this->timesheet->finished) {
                $actions->addLink(
                        _('Stundenzettel einreichen'),
                        PluginEngine::getLink($this->plugin, [], 'timesheet/send/' . $timesheet_id ),
                        Icon::create('share', 'new'),
                        ['title' => 'Achtung, anschließend keine Bearbeitung mehr möglich!',
                            'onclick' => "return confirm('Bearbeitung abschließen und Stundenzettel offiziell einreichen?')"]
                    );
            } else {
                $actions->addLink(
                        _('Stundenzettel wurde eingereicht'),
                        PluginEngine::getLink($this->plugin, [], 'timesheet/timesheet/' . $timesheet_id ),
                        Icon::create('lock-locked', 'new'),
                        ['title' => 'Keine Bearbeitung mehr möglich!',
                            'disabled' => "disabled"]
                    );
            }
            if (true) {
                $actions->addLink(
                    _('PDF-zum Ausdruck generieren'),
                    PluginEngine::getLink($this->plugin, [], 'timesheet/pdf/' . $timesheet_id ),
                    Icon::create('file-pdf', 'clickable')
                );
            }
            $sidebar->addWidget($actions);
        }

    }
    
    
    public function save_timesheet_action($timesheet_id)
    {
        $timesheet = StundenzettelTimesheet::find($timesheet_id);
        
         if (!$timesheet->locked) {
             
            $record_ids_array = Request::getArray('record_id');
            $begin_array = Request::getArray('begin');
            $end_array = Request::getArray('end');
            $break_array = Request::getArray('break');
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
            
         } else {
            PageLayout::postMessage(MessageBox::success(_("Bearbeitung ist gesperrt."))); 
            $this->redirect('timesheet/timesheet/' . $timesheet_id);
         }
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
    
    public function send_action($timesheet_id)
    {
        $timesheet = StundenzettelTimesheet::find($timesheet_id);
        if($timesheet){
            $timesheet->finished = true;
            $timesheet->store();
            PageLayout::postMessage(MessageBox::success(_("Stundenzettel wurde eingereicht und kann nun nicht mehr bearbeitet werden.")));
            $this->redirect('timesheet/index');
        } else {
            PageLayout::postMessage(MessageBox::error(_("Fehler: kein Stundenzettel gefunden.")));
            $this->redirect('timesheet/index');
        }
    }
    
    public function approve_action($timesheet_id)
    {
        $timesheet = StundenzettelTimesheet::find($timesheet_id);
        if($timesheet && User::findCurrent()->user_id == $timesheet->contract->supervisor){
            $timesheet->approved = true;
            $timesheet->store();
            PageLayout::postMessage(MessageBox::success(_("Korrektheit der Angaben wurde von Ihnen bestätigt.")));
            $this->redirect('timesheet/index/'. $timesheet->contract->id);
        } else {
            PageLayout::postMessage(MessageBox::error(_("Fehler: Sie sind nicht berechtigt diesen Stundenzettel zu bewerten.")));
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
