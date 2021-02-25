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
        
        $this->balance_pattern = '^(00:00)|(0)|((0[1-9]|1\d|2[0-3]):([0-5]\d))|(([1-9]|1\d|2[0-3]):([0-5]\d))|(00:(0[1-5]|[1-9]0|[1-5][1-9]))$';
        $this->balance_pattern = '^(-{0,1})([0-9]{0,3}):[0-5][0-9]$';

    }

    public function index_action()
    {
        Navigation::activateItem('tools/stundenzettelverwaltung/index');
        $user = User::findCurrent();

        if ($this->adminrole) {
            
            $this->search = isset(Request::get('search_user'))? Request::get('search_user') : '';
        
            $search_user = new SearchWidget($this->url_for('index/'));
            $search_user->setTitle('Nutzer suchen');
            
            $search_user->addNeedle(_('Name'), 'search_user', true, null, null, $this->search);
            Sidebar::get()->addWidget($search_user);
            
            //get institutes for the user
            foreach($user->institute_memberships->pluck('institut_id') as $inst_id){
                $this->inst_id[] = $inst_id;
            }
            if (sizeof($this->inst_id) == 0) { //local testing with root
                $this->inst_id[] = '477d184367f48cc210f74bb4f779c7b7';
            } else { //TODO temporäre direkte Zuweisung der Einrichtung virtUOS, muss später dynamisch erfolgen
                $this->inst_id[] = '355217603013d7675d68951087429924';
            }
        
            //get all stumis and contracts
            $groups = Statusgruppen::findBySQL('`name` LIKE ? AND `range_id` LIKE ?', ['%Studentische%', $this->inst_id[0]]);
            foreach ($groups as $group) {
                foreach ($group->members as $member) {
                    $stumi = User::find($member->user_id);
                    if (!$this->search || strpos(strtolower($stumi->username . ' ' . $stumi->vorname . ' ' . $stumi->nachname), strtolower($this->search))) {
                        $this->stumis[] = $stumi;
                        $this->stumi_contracts[$member->user_id] = StundenzettelContract::findBySQL('`user_id` LIKE ? AND `inst_id` LIKE ?', [$member->user_id, $this->inst_id[0]]);
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
                    $this->stumi_contracts[$contract->user_id] = StundenzettelContract::findBySQL('`user_id` LIKE ? AND `supervisor` LIKE ?', [$contract->user_id, User::findCurrent()->user_id]);
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
            $this->stumi_contracts = StundenzettelContract::findByStumi_id($this->stumi->user_id);
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
