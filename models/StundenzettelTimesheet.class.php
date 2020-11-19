<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar       $id
 * @property varchar       $stumi_id
 * @property varchar       $contract_id
 * @property varchar       $inst_id
 * @property int           $month
 * @property int           $year
 * @property tinyint       $finished
 * @property tinyint       $approved
 * @property tinyint       $received
 * @property tinyint       $complete
 * @property decimal       $sum

 */

class StundenzettelTimesheet extends \SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'stundenzettel_timesheets';
        
        $config['belongs_to']['contract'] = [
            'class_name'  => 'StundenzettelStumiContract',
            'foreign_key' => 'contract_id',];

        parent::configure($config);
    }
    
    static function getContractTimesheet($contract_id, $month, $year){
        $timesheet = StundenzettelTimesheet::findOneBySQL('`contract_id` LIKE ? AND `month` = ? AND `year` = ?', [$contract_id, $month, $year]);
        return $timesheet;
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
        $pdf->Image($GLOBALS['ABSOLUTE_PATH_STUDIP'] . '/' . 
                    PluginEngine::getPlugin('HilfskraftStundenzettel')->getPluginPath().
                '/assets/images/formblatt.png', 0, 0, 1200, 1550, '', '', '', false, 300);
        
        $records = StundenzettelRecord::findByTimesheet_Id($this->id, 'ORDER BY day ASC');

        $pdf->SetY(42); 
            
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        //$this->SetTextColor(0,127,75);
        // Page number
        $pdf->SetX(90);
        $pdf->Write(5, studip_utf8encode(User::find($this->stumi_id)->nachname . ', ' .  User::find($this->stumi_id)->vorname));
        $pdf->Ln();
        $pdf->SetX(90);
        $pdf->Write(5, studip_utf8encode(Institute::find($this->inst_id)->name ));
        $pdf->Ln();
        $pdf->SetX(90);
        $pdf->Write(6, studip_utf8encode($this->month . '/' . $this->year));
        $pdf->Ln();
        $pdf->SetX(90);
        $pdf->Write(5, studip_utf8encode(StundenzettelStumiContract::find($this->contract_id)->contract_hours));
        $pdf->Ln(21);
        $pdf->SetFont('helvetica', '', 9.5);
        
        //$this->Cell(0, 0, $content, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
        foreach ($records as $record){
            $record->calculate_sum();
            $content = $record->begin . '  ' . $record->break . '  ' . $record->end . '  ' . $record->sum . '  ' .
                    $record->defined_comment . '  ' . $record->comment . '   ' . $record->entry_mktime ;
            $pdf->SetX(44);
            $pdf->Write($line_height, $record->begin);
            $pdf->SetX(61);
            $pdf->Write($line_height, $record->break);
            $pdf->SetX(74);
            $pdf->Write($line_height, $record->end);
            $pdf->SetX(94);
            $pdf->Write($line_height, $record->sum);
            $pdf->SetX(114);
            $pdf->Write($line_height, ($record->begin) ? date('d.m.Y', strtotime($record->entry_mktime)) : '');
            $pdf->SetX(144);
            $pdf->Write($line_height, $record->defined_comment . ' ' . $record->comment);
            
            $pdf->Ln();
            
        }

        $pdf->SetX(94);
        $pdf->Write($line_height, $record->timesheet->sum);
  
        $fileid = time();   
        $pdf->Output( 'Stundenzettel' . $fileid, 'D');
      
    }
    
    function calculate_sum(){
        $records = StundenzettelRecord::findByTimesheet_Id($this->id);
        $sum_seconds = 0;
        foreach ($records as $record){
            $sum += $record->sum_to_seconds();   
        }
        $minutes = ($sum/60)%60;
        $hours = floor(($sum/60)/ 60);
        $this->sum = sprintf("%02s", $hours) . ':' . sprintf("%02s", $minutes);
        $this->store();
    }
    
    static function calcTimeDifference($timea, $timeb, $break = '0:0'){
        $timea_pts = explode(':', $timea);
        $timeb_pts = explode(':', $timeb);
        $break_pts = explode(':', $break);
        
        $begin_minutes = intval($timea_pts[1]);
        $begin_hours = intval($timea_pts[0]);

        $end_minutes = intval($timeb_pts[1]);
        $end_hours = intval($timeb_pts[0]);

        $break_minutes = intval($break_pts[1]);
        $break_hours = intval($break_pts[0]);

        $minutes_total = 0;
        $hours_total = 0;

        //reduce timeslot by break
        if (($begin_minutes + $break_minutes) >= 60) {
            $begin_hours = $begin_hours + 1;
            $begin_minutes = ($begin_minutes + $break_minutes) - 60;
        } else {
            $begin_minutes = $begin_minutes + $break_minutes;
        }
        $begin_hours = $begin_hours + $break_hours;

        if (($end_minutes + (60 - $begin_minutes)) >= 60) {
            $minutes_total = ($end_minutes + (60 - $begin_minutes)) - 60;
        } else {
            $end_hours -= 1;
            $minutes_total = $end_minutes + (60 - $begin_minutes);
        }

        $hours_total = $end_hours - $begin_hours;

        return (sprintf("%02s", $hours_total) . ':' . sprintf("%02s", $minutes_total));
    }
    
    static function addTimes($timea, $timeb){
        $timea_pts = explode(':', $timea);
        $timeb_pts = explode(':', $timeb);
        
        $timea_minutes = intval($timea_pts[1]);
        $timea_hours = intval($timea_pts[0]);

        $timeb_minutes = intval($timeb_pts[1]);
        $timeb_hours = intval($timeb_pts[0]);
        
        $minutes_total = 0;
        $hours_total = 0;
        
        if (($timea_minutes + $timeb_minutes) >= 60) {
            $hours_total += 1;
            $minutes_total = ($timea_minutes + $timeb_minutes) - 60;
        } else {
            $$minutes_total = $timea_minutes + $timeb_minutes;
        }
        
        $hours_total = $timea_hours + $timeb_hours;
        return (sprintf("%02s", $hours_total) . ':' . sprintf("%02s", $minutes_total));        
    }
    
    static function multiplyMinutes($minutes, $factor){
        $minutes_total = $minutes * $factor;
        $hours = floor($minutes_total / 60);
        $minutes = $minutes_total % 60;
        return sprintf("%02s", $hours) . ':' . sprintf("%02s", $minutes);
    }
    
    static function multiplyTime($time, $factor){
        //TODO
    }
}