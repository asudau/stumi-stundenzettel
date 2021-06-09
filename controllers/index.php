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
        if ( !($this->plugin->hasStumiAdminrole() || $this->plugin->hasStumiContract () || $this->plugin->isStumiSupervisor()) ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
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
        
        $this->balance_pattern = '^(-{0,1})([0-9]{1,3}):[0-5][0-9]$';

    }

    public function index_action()
    {
        Navigation::activateItem('tools/stundenzettelverwaltung/index');
        $user = User::findCurrent();

        if ($this->adminrole) {
            
            $this->search = Request::get('search_user')? Request::get('search_user') : '';
            $search_user = new SearchWidget($this->link_for('index'));
            $search_user->setTitle('Nutzer suchen');
            
            $search_user->addNeedle(_('Name'), 'search_user', true, null, null, $this->search);
            Sidebar::get()->addWidget($search_user);
            
            //get institutes for the admin-user
            $this->inst_ids = $this->plugin->getAdminInstIds();
            $this->inst_data = array();
        
            //get all stumis and contracts
            //TODO get groups for each institute by configured group_name
            foreach ($this->inst_ids as $inst_id) {
                $groups = Statusgruppen::findBySQL('`name` LIKE ? AND `range_id` = ?', ['%Studentische%', $inst_id]);
                foreach ($groups as $group) {
                    foreach ($group->members as $member) {
                        $stumi = User::find($member->user_id);
                        if (!$this->search || strpos(strtolower($stumi->username . ' ' . $stumi->vorname . ' ' . $stumi->nachname), strtolower($this->search))) {
                            $this->inst_data[$inst_id]->stumis[] = $stumi;
                            $this->inst_data[$inst_id]->stumi_contracts[$member->user_id] = StundenzettelContract::findBySQL('`user_id` = ? AND `inst_id` = ?', [$member->user_id, $inst_id]);
                        }
                    }
                }
            }
        }
        
        if ($this->supervisorrole) {
            
            //get stumis for this user
            $stumi_contracts = StundenzettelContract::findBySupervisor(User::findCurrent()->user_id);
            foreach($stumi_contracts as $contract){
                if(!in_array($contract->user_id, $this->user_ids)){
                    $this->user_ids[] = $contract->user_id;
                    $this->stumis[] = User::find($contract->user_id);
                    $this->stumi_contracts[$contract->user_id] = StundenzettelContract::findBySQL('`user_id` = ? AND `supervisor` = ?', [$contract->user_id, User::findCurrent()->user_id]);
                }
            }   
            
        }
        
        if ($this->stumirole) {

            $this->stumi = User::find($GLOBALS['user']->user_id);
            $this->stumi_contracts = StundenzettelContract::findByUser_id($this->stumi->user_id);
            foreach($this->stumi_contracts as $contract){
                if(!in_array($inst_id, $this->inst_id)){
                    $this->inst_id[] = $inst_id;
                }
            }    
        } 
    }
    
    public function new_action($inst_id, $user_id)
    {   
        if ( !$this->adminrole ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }
        
        $this->inst_id = $inst_id;
        $this->stumi = User::find($user_id);
        
        $this->search = QuickSearch::get('user_id', new StandardSearch('user_id'))
            ->withButton(array('search_button_name' => 'search_user', 'reset_button_name' => 'reset_search'))
            ->render();
        
    }
    
    public function edit_action($contract_id, $following_contract = NULL)
    {   
        if ( !$this->adminrole ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }
        
        $this->contract = StundenzettelContract::find($contract_id);
        $this->inst_id = $this->contract->inst_id;
        $this->stumi = $this->contract->stumi;
        $supervisor = User::find($this->contract->supervisor);
        $this->following_contract = $following_contract;
        
        $this->search = QuickSearch::get('user_id', new StandardSearch('user_id'))
            ->defaultValue($this->contract->supervisor, $supervisor->vorname . ' ' . $supervisor->nachname)
            ->withButton(array('search_button_name' => 'search_user', 'reset_button_name' => 'reset_search'))
            ->render();

        $this->render_action('new');
    }
    
    public function add_contract_begin_data_action($contract_id)
    {   
        if ( !$this->adminrole ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }
        
        $this->contract = StundenzettelContract::find($contract_id);
        $this->stumi = User::find($this->contract->user_id);
    }
    
    public function save_action($inst_id, $user_id, $contract_id = NULL)
    {   
        if ( !$this->adminrole ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }

        $contract = StundenzettelContract::find($contract_id);
        $message = _("Änderungen gespeichert.");

        if (!$contract || Request::get('following_contract')){
            $contract = new StundenzettelContract();
            $message = _("Vertrag angelegt.");
        }

        //get all stumis an contracts
        $contract->inst_id = $inst_id;
        $contract->user_id = $user_id;
        $contract->contract_begin = strtotime(Request::get('begin'));
        $contract->contract_end = strtotime(Request::get('end'));
        $contract->contract_hours = Request::get('hours');
        $contract->supervisor = Request::get('user_id');            
        $contract->store();

        //TODO 
        //$contract->reassign_timesheets();

        PageLayout::postMessage(MessageBox::success($message)); 
        
        $this->redirect('index/');

    }
    
    public function delete_action($contract_id)
    {   
        if ( !$this->adminrole ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }
        
        $contract = StundenzettelContract::find($contract_id);
            
        if($contract->delete()){
            PageLayout::postMessage(MessageBox::success(_("Vertrag gelöscht"))); 
            
        } else {
            PageLayout::postMessage(MessageBox::error(_("Vertrag konnte nicht gelöscht werden"))); 
        }
        
        $this->redirect('index/');

    }
    
    public function save_contract_begin_data_action($contract_id)
    {   
        if ( !$this->adminrole ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }
        
        $contract = StundenzettelContract::find($contract_id);
        
        $contract->begin_digital_recording_month = Request::get('begin_month');
        $contract->begin_digital_recording_year = Request::get('begin_year');
        $contract->begin_vacation_claimed = StundenzettelTimesheet::stundenzettel_strtotimespan(Request::get('vacation_claimed'));
        $contract->begin_balance = StundenzettelTimesheet::stundenzettel_strtotimespan(Request::get('balance'));
        $contract->last_year_vacation_remaining = StundenzettelTimesheet::stundenzettel_strtotimespan(Request::get('begin_last_year_vacation_remainig'));
        
        if($contract->store()){
            PageLayout::postMessage(MessageBox::success(_("Vertragsdaten gespeichert"))); 
        } else {
            PageLayout::postMessage(MessageBox::error(_("Daten konnten nicht gespeichert werden"))); 
        }
        
        $this->redirect('index/');
  
    }
    
    public function institute_settings_action(){
        $groups = Statusgruppen::findByRangeID($inst_id);
        
    }
    
}
