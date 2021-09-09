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
        Navigation::activateItem('contents/stundenzettelverwaltung/index');
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
            foreach ($this->inst_ids as $inst_id) {
                $settings = StundenzettelInstituteSetting::find($inst_id);
                if ($settings) {
                    $this->groups[$inst_id] = $settings->stumi_statusgroups;
                }
                foreach ($this->groups[$inst_id] as $group) {
                    foreach ($group->members as $member) {
                        $stumi = User::find($member->user_id);
                        if (!$this->search || strpos(strtolower($stumi->username . ' ' . $stumi->vorname . ' ' . $stumi->nachname), strtolower($this->search))) {
                            $this->inst_data[$inst_id][$group->id]->stumis[] = $stumi;
                            $this->inst_data[$inst_id][$group->id]->stumi_contracts[$member->user_id] = StundenzettelContract::findBySQL('`user_id` = ? AND `inst_id` = ?', [$member->user_id, $inst_id]);
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
    
    public function edit_institute_settings_action($inst_id)
    {
        if ( !$this->plugin->isInstAdmin($inst_id) ) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }
        $this->groups = Statusgruppen::findByRange_ID($inst_id);
        
        $settings = StundenzettelInstituteSetting::find($inst_id);
        if ($settings) {
            $this->stumi_group_ids = $settings->hilfskraft_statusgruppen;
            $this->inst_mail = $settings->inst_mail;
        }
        
        $this->inst_id = $inst_id;
        
    }
    
    public function save_institute_settings_action($inst_id)
    {
        if ( !$this->plugin->isInstAdmin($inst_id)) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }
        $settings = StundenzettelInstituteSetting::find($inst_id);
        
        if (!$settings ){
            $settings = new StundenzettelInstituteSetting($inst_id);
        }
        
        $settings->inst_mail = Request::get('email');
        $settings->hilfskraft_statusgruppen = implode(',', Request::getArray('statusgruppen'));
        
        if($settings->store()){
            PageLayout::postMessage(MessageBox::success(_("Konfiguration gespeichert"))); 
        } else {
            PageLayout::postMessage(MessageBox::error(_("Daten konnten nicht gespeichert werden"))); 
        }
        
        $this->redirect('index/');       
    }
    
    public function mail_action($user_id){
        $user = User::find($user_id);
        $this->empfaengermail = $user->email;
        $this->empfaenger = sprintf('%s %s', $user->vorname, $user->nachname);
    }
    
    public function send_form_action($empfaenger_mail){
        
        if ( !$this->adminrole && !$this->supervisorrole) {
            throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung"));
        }
        
        if (Request::get('message_body')){
            $mailtext   = Studip\Markup::purifyHtml(Request::get('message_body'));
            $betreff    = '[Stundenzettel] ' . Studip\Markup::purifyHtml(Request::get('message_subject'));
            $mail       = new StudipMail();
            $success    = $mail->addRecipient($empfaenger)
                 ->addRecipient(User::findCurrent()->email, 'cc')
                 ->setReplyToEmail( User::findCurrent()->email)
                 ->setSenderEmail( User::findCurrent()->email)
                 ->setSenderName( User::findCurrent()->vorname . ' ' . User::findCurrent()->nachname )
                 ->setSubject($betreff)
                 ->setBodyHtml($mailtext)
                 ->setBodyHtml(strip_tags($mailtext))  
                 ->send();
            
        } if ($success){
            $message = MessageBox::success(_('eMail wurde versendet! Eine Kopie ging in CC an Sie..'));
            PageLayout::postMessage($message);
        } else {
            $message = MessageBox::error(_('Da ist was schief gegangen, Ihre Mail konnte nicht versendet werden.'));
            PageLayout::postMessage($message);
        }
        $this->response->add_header('X-Dialog-Close', '1');
        $this->redirect('timesheet/admin_index');
    }
    
}
