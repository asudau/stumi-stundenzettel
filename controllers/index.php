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

    }

    public function index_action()
    {
        Navigation::activateItem('tools/hilfskraft-stundenverwaltung/index');
        $user = User::findCurrent();

        if ($this->adminrole) {
            
            //get institutes for thie user
            foreach($user->institute_memberships->pluck('institut_id') as $inst_id){
                $this->inst_id[] = $inst_id;
            }
            if (sizeof($this->inst_id) == 0) { //local testing with root
                $this->inst_id[] = '477d184367f48cc210f74bb4f779c7b7';
            }
        
            //get all stumis an contracts
            $groups = Statusgruppen::findBySQL('`name` LIKE ? AND `range_id` LIKE ?', ['%Studentische%', $this->inst_id[0]]);
            foreach ($groups as $group) {
                foreach ($group->members as $member) {
                    $this->stumis[] = User::find($member->user_id);
                    $this->stumi_contracts[$member->user_id] = StundenzettelStumiContract::findBySQL('`stumi_id` LIKE ? AND `inst_id` LIKE ?', [$member->user_id, $this->inst_id[0]]);
                }
            }
        }
        
        if ($this->supervisorrole) {
            
            //get stumis for this user
            $stumi_contracts = StundenzettelStumiContract::findBySupervisor(User::findCurrent()->user_id);
            foreach($stumi_contracts as $contract){
                if(!in_array($contract->stumi_id, $this->stumis)){
                    $this->stumis[] = User::find($contract->stumi_id);
                    $this->stumi_contracts[$contract->stumi_id] = StundenzettelStumiContract::findBySQL('`stumi_id` LIKE ? AND `supervisor` LIKE ?', [$contract->stumi_id, User::findCurrent()->user_id]);
                }
            }   
            //setup navigation
//            $views = new ViewsWidget();
//            $views->addLink(_('Übersicht über Studentische MitarbeiterInnen'),
//                            $this->url_for('index'))
//                  ->setActive($action === 'index');
//            
//            Sidebar::get()->addWidget($views);
            
        }
        
        if ($this->stumirole) {

            $this->stumi = User::find($GLOBALS['user']->user_id);
            $this->stumi_contracts = StundenzettelStumiContract::findByStumi_id($this->stumi->user_id);
            foreach($this->stumi_contracts as $contract){
                if(!in_array($inst_id, $this->inst_id)){
                    $this->inst_id[] = $inst_id;
                }
            }    
        } 
    }
    
    public function new_action($inst_id, $stumi_id)
    {   
        $this->inst_id = $inst_id;
        $this->stumi = User::find($stumi_id);
        
        $this->search = QuickSearch::get('user_id', new StandardSearch('user_id'))
            ->withButton(array('search_button_name' => 'search_user', 'reset_button_name' => 'reset_search'))
            ->render();
        
    }
    
    public function edit_action($contract_id)
    {   
        $this->contract = StundenzettelStumiContract::find($contract_id);
        $this->inst_id = $this->contract->inst_id;
        $this->stumi = User::find($this->contract->stumi_id);
        $supervisor = User::find($this->contract->supervisor);
        
        $this->search = QuickSearch::get('user_id', new StandardSearch('user_id'))
            ->defaultValue($this->contract->supervisor, $supervisor->vorname . ' ' . $supervisor->nachname)
            ->withButton(array('search_button_name' => 'search_user', 'reset_button_name' => 'reset_search'))
            ->render();

        $this->render_action('new');
    }
    
    public function add_contract_begin_data_action($contract_id)
    {   
        $this->contract = StundenzettelStumiContract::find($contract_id);
        $this->stumi = User::find($this->contract->stumi_id);
        $this->contract_data = StundenzettelContractBegin::find($contract_id); 
    }
    
    public function save_action($inst_id, $stumi_id, $contract_id)
    {   
        if ($this->plugin->hasStumiAdminrole ()) {
            
            $this->adminrole = true;
            $contract = StundenzettelStumiContract::find($contract_id);
            $message = _("Änderungen gespeichert.");
            
            if (!$contract){
                $contract = new StundenzettelStumiContract();
                $message = _("Vertrag angelegt.");
            }
            
            //get all stumis an contracts
            $contract->inst_id = $inst_id;
            $contract->stumi_id = $stumi_id;
            $contract->contract_begin = strtotime(Request::get('begin'));
            $contract->contract_end = strtotime(Request::get('end'));
            $contract->contract_hours = Request::get('hours');
            $contract->supervisor = Request::get('user_id');            
            $contract->store();
            
            $contract->add_missing_timesheets();
            
            PageLayout::postMessage(MessageBox::success($message)); 
            
        } else {
            PageLayout::postMessage(MessageBox::error(_("Keine Berechtigung."))); 
        }
        
        $this->redirect('index/');

    }
    
    public function delete_action($contract_id)
    {   
        $contract = StundenzettelStumiContract::find($contract_id);
            
        if($contract->delete()){
            PageLayout::postMessage(MessageBox::success(_("Vertrag gelöscht"))); 
            
        } else {
            PageLayout::postMessage(MessageBox::error(_("Vertrag konnte nicht gelöscht werden"))); 
        }
        
        $this->redirect('index/');

    }
    
    public function save_contract_begin_data_action($contract_id)
    {   
        $begin_data = StundenzettelContractBegin::find($contract_id);
//        $this->contract = StundenzettelStumiContract::find($contract_id);
//        $this->inst_id = $this->contract->inst_id;
//        $this->stumi = User::find($this->contract->stumi_id);
        if (!$begin_data) {
            $begin_data = new StundenzettelContractBegin();
            $begin_data->contract_id = $contract_id;
        }
        
        $begin_data->begin_digital_recording_month = Request::get('begin_month');
        $begin_data->begin_digital_recording_year = Request::get('begin_year');
        $begin_data->vacation_claimed = Request::get('vacation_claimed');
        $begin_data->balance = Request::get('balance');
        
        if($begin_data->store()){
            PageLayout::postMessage(MessageBox::success(_("Vertragsdaten gespeichert"))); 
        } else {
            PageLayout::postMessage(MessageBox::error(_("Daten konnten nicht gespeichert werden"))); 
        }
        
        $this->redirect('index/');
  
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
