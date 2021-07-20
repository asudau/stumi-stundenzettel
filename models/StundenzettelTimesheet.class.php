<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar       $id
 * @property varchar       $contract_id
 * @property int           $month
 * @property int           $year
 * @property tinyint       $finished
 * @property tinyint       $approved
 * @property tinyint       $received
 * @property tinyint       $complete
 * @property decimal       $sum
 * @property boolen        $month_completed  //calcultated for each day
 */

class StundenzettelTimesheet extends \SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'stundenzettel_timesheets';
        
        $config['belongs_to']['contract'] = [
            'class_name'  => 'StundenzettelContract',
            'foreign_key' => 'contract_id',];
        
        $config['additional_fields']['timesheet_balance']['get'] = function ($item) {
            if ($item->month_completed){
                return $item->sum - ($item->contract->contract_hours * 3600);
            } else {
                return '';
            }
        };
        
         $config['additional_fields']['vacation']['get'] = function ($item) {
            $records = StundenzettelRecord::findBySQL('`timesheet_id` = ? AND `defined_comment` = "Urlaub"', [$item->id]);
            foreach ($records as $record) {
                $vacation += $record['sum'];
            }
            return $vacation;
        };
        
        //gibt an, ob der Monat, für welchen der Stundenzettel angelegt wurde bereits abgelaufen ist
        $config['additional_fields']['month_completed']['get'] = function ($item) {
            $days_per_month = cal_days_in_month(CAL_GREGORIAN, $item->month, $item->year);
            return strtotime($item->year . '-' . $item->month . '-' . $days_per_month) < time();
        };
        
        $config['additional_fields']['locked']['get'] = function ($item) {
            return $item->finished;     
        };
        
        $config['additional_fields']['int_status']['get'] = function ($item) {
            return $item->finished + $item->approved + $item->received + $item->complete ;     
        };
        
        $config['additional_fields']['overdue']['get'] = function ($item) {
            $offset_overdue = '01';
            $due_time = strtotime("+1 month", strtotime($item->year . '-' . $item->month . '-' . $offset_overdue . ' 00:01'));
            return $due_time < time();;     
        };

        parent::configure($config);
    }
    
    static function getContractTimesheet($contract_id, $month, $year){
        $timesheet = StundenzettelTimesheet::findOneBySQL('`contract_id` = ? AND `month` = ? AND `year` = ?', [$contract_id, $month, $year]);
        return $timesheet;
    }
    
    function getCurrentState($status, $user_status){
        if ($user_status == 'stumi'){
            switch ($status){
                case 'finished':
                    if ($this->finished) return 'true';
                    if (!$this->finished && $this->overdue) return 'overdue';
                    else return 'false';
                case 'approved':
                    if ($this->approved) return 'true';
                    if (!$this->approved && $this->finished) return 'waiting';
                    else return 'false';
                case 'received':
                    if ($this->received) return 'true';
                    if (!$this->received && $this->overdue) return 'overdue';
                    else return 'false';
                case 'complete':
                    if ($this->received) return 'true';
                    else return 'false';
            }
        }
        
        if ($user_status == 'supervisor'){
            switch ($status){
                case 'finished':
                    if ($this->finished) return 'true';
                    if (!$this->finished && $this->overdue) return 'overdue';
                    else return 'false';
                case 'approved':
                    if ($this->approved) return 'true';
                    if (!$this->approved && $this->finished && !$this->overdue) return 'waiting';
                    if (!$this->approved && $this->finished && $this->overdue) return 'overdue';
                    else return 'false';
                case 'received':
                    if ($this->received) return 'true';
                    if (!$this->received && $this->overdue) return 'overdue';
                    else return 'false';
                case 'complete':
                    if ($this->received) return 'true';
                    else return 'false';
            }
        }
        
         if ($user_status == 'admin'){
            switch ($status){
                case 'finished':
                    if ($this->finished) return 'true';
                    if (!$this->finished && $this->overdue) return 'overdue';
                    else return 'false';
                case 'approved':
                    if ($this->approved) return 'true';
                    if (!$this->approved && $this->finished) return 'waiting';
                    if (!$this->approved && $this->finished && $this->overdue) return 'overdue';
                    else return 'false';
                case 'received':
                    if ($this->received) return 'true';
                    if (!$this->received && $this->approved) return 'waiting';
                    if (!$this->received && $this->approved && $this->overdue) return 'overdue';
                    else return 'false';
                case 'complete':
                    if ($this->complete) return 'true';
                    if (!$this->complete && $this->received) return 'waiting';
                    if (!$this->complete && $this->received && $this->overdue) return 'overdue';
                    else return 'false';
            }
        }
    }
    
    function build_pdf()
    {
        global $STUDIP_BASE_PATH, $TMP_PATH;
        require_once $STUDIP_BASE_PATH.'/vendor/tcpdf/tcpdf.php';
        //require_once $STUDIP_BASE_PATH.'/public/plugins_packages/elan-ev/Zertifikats_Plugin/models/zertifikatpdf.class.php';
        $line_height = 3;
        
        // create new PDF document
        $pdf = new Tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'ISO-8859-1', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();
        $pdf->Image(PluginEngine::getPlugin('Stundenzettel')->getPluginPath().
                '/assets/images/Arbeitszeitnachweis.png', 0, 0, 1200, 1550, '', '', '', false, 300);
        
        $records = StundenzettelRecord::findByTimesheet_Id($this->id, 'ORDER BY day ASC');

        $pdf->SetY(42); 
            
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        //$this->SetTextColor(0,127,75);
        // Page number
        $pdf->SetX(90);
        $pdf->Write(5, User::find($this->contract->user_id)->nachname . ', ' .  User::find($this->contract->user_id)->vorname);
        $pdf->Ln();
        $pdf->SetX(90);
        $pdf->Write(5, my_substr(Institute::find($this->contract->inst_id)->name ,0,50));
        $pdf->Ln();
        $pdf->SetX(90);
        $pdf->Write(6, $this->month . '/' . $this->year);
        $pdf->Ln();
        $pdf->SetX(90);
        $pdf->Write(5, StundenzettelContract::find($this->contract_id)->contract_hours);
        $pdf->Ln(21);
        $pdf->SetFont('helvetica', '', 9.5);
        
        //$this->Cell(0, 0, $content, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
        foreach ($records as $record){
            //$record->calculate_sum();
            $content = $record->begin . '  ' . $record->break . '  ' . $record->end . '  ' . $record->sum . '  ' .
                    $record->defined_comment . '  ' . $record->comment . '   ' . $record->entry_mktime ;
            $pdf->SetX(44);
            $pdf->Write($line_height, ($record->begin) ? self::stundenzettel_strftime('%H:%M', $record->begin) : '');
            $pdf->SetX(61);
            $pdf->Write($line_height, ($record->break) ? self::stundenzettel_strftimespan($record->break) : '');
            $pdf->SetX(74);
            $pdf->Write($line_height, ($record->end) ? self::stundenzettel_strftime('%H:%M', $record->end) : '');
            $pdf->SetX(94);
            $pdf->Write($line_height, ($record->sum) ? self::stundenzettel_strftimespan($record->sum) : '');
            $pdf->SetX(114);
            $pdf->Write($line_height, ($record->sum && $record->defined_comment != 'Feiertag') ? date('d.m.Y', strtotime($record->entry_mktime)) : '');
            $pdf->SetX(136);
            $pdf->Write($line_height, $record->defined_comment . ' ' . $record->comment);
            
            $pdf->Ln();
            
        }

        $pdf->SetY(209);
        $pdf->SetX(94);
        $pdf->Write($line_height, self::stundenzettel_strftimespan($record->timesheet->sum));
  
        $fileid = time();   
        $pdf->Output( 'Stundenzettel_' . $this->month . '-' . $this->year . '_' . User::find($this->contract->user_id)->nachname . '.pdf', 'D');
      
    }
    
    function can_edit($user){
         if (($this->contract->user_id == $user->user_id) && !$this->locked){
             return true;
         } else return false;
    }
    
    //TODO InstAdmin identifizieren und Zugriff erlauben
    function can_read($user){
        if ($this->contract->can_read($user)) {
            if ($this->contract->user_id == $user->user_id ||
                    (($this->contract->supervisor == $user->user_id) && $this->finished)) {
                return true;
            }           
        }
    }
    
    function calculate_sum(){
        //if (!$this->locked){
            $records = StundenzettelRecord::findByTimesheet_Id($this->id);
            $sum = 0;
            foreach ($records as $record){
                $sum += $record->sum;   
            }
            $this->sum = $sum;
            $this->store();
        //}
    }
    
    //wird für vacation Berechnung noch benutzt
    static function subtractTimes($timea, $timeb){
        $timea_pts = explode(':', $timea);
        $timeb_pts = explode(':', $timeb);
        
        $timea_minutes = intval($timea_pts[1]) + intval($timea_pts[0]) * 60;
        $timeb_minutes = intval($timeb_pts[1]) + intval($timeb_pts[0]) * 60;
        
        $minutes_total = $timea_minutes - $timeb_minutes;
        $hours = floor($minutes_total / 60);
        if (($minutes_total % 60) != 0 && $hours < 0) {
            $hours = $hours + 1;
            $minutes = $minutes_total % 60;
        } else {
            $minutes = $minutes_total % 60;
        }
 
        return (sprintf("%02s", $hours) . ':' . sprintf("%02s", abs($minutes)));        
    }
    
    static function stundenzettel_strtotimespan($string){
        $negative = strpos($string, '-');
        $pts = explode(':', $string);
        $minutes = intval($pts[1]);
        $hours = intval($pts[0]);
        $minutes_total = $minutes + abs($hours) * 60;
        $timespan = $minutes_total * 60;
        if ($negative === false) {
            return $timespan;
        } else {
            return '-' . $timespan;
        }
    }
    
    static function stundenzettel_strftimespan($timespan){
        $hours = floor(abs($timespan) / 3600);
        $minutes = ($timespan % 3600) / 60;
        $str = (sprintf("%02s", $hours) . ':' . sprintf("%02s", abs($minutes))); 
        if ($timespan < 0) {
            return '-' . $str;
        } else return $str;
    }
    
    static function stundenzettel_strtotime($string){
        $hours = intval(explode(':', $string)[0]);
        $time = strtotime($string);
        if ($hours < 0) {
            return '-' . $time;
        } else {
            return $time;
        }
    }
    
    static function stundenzettel_strftime($format, $time){
        $str = strftime($format, $time);
        if ($time < 0){
            return '-' . $str;
        } else {
            return $str;
        }
    }
}