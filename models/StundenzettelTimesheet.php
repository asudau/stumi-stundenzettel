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
    private static $groups = array(
        'admin' => 'Zusätzliche Datenfelder für Nutzer mit administratver Freischaltung',
        'doktorandendaten'=> 'Persönliche Daten',
        'promotionsdaten' => 'Daten zur Promotion',
        'ersteinschreibung'=> 'Daten zur Ersteinschreibung',
        'abschlusspruefung'=> 'Daten zur Promotion berechtigenden Abschlussprüfung',
        'hzb' => 'Daten zur Hochschulzugangsberechtigung (HZB)'
        );
    
    protected static function configure($config = array())
    {
        $config['db_table'] = 'stundenzettel_timesheets';

        parent::configure($config);
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

        $pdf->SetY(45);
        $pdf->SetX(90);
            
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        //$this->SetTextColor(0,127,75);
        // Page number
        $pdf->Cell(0, 0, studip_utf8encode(Institute::find($this->inst_id)->name ), 0, 1, 'L', 0, '', 0, false, 'C', 'C');
        $pdf->Ln(32);
        $pdf->SetFont('helvetica', '', 9.5);
        
        //$this->Cell(0, 0, $content, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
        foreach ($records as $record){
            $content = $record->begin . '  ' . $record->break . '  ' . $record->end . '  ' . $record->sum . '  ' .
                    $record->defined_comment . '  ' . $record->comment . '   ' . $record->entry_mktime ;
            $pdf->SetX(44);
            $pdf->Write($line_height, $record->begin);
            $pdf->SetX(61);
            $pdf->Write($line_height, $record->break);
            $pdf->SetX(74);
            $pdf->Write($line_height, $record->end);
            $pdf->SetX(94);
            $pdf->Write($line_height, $record->calculate_sum());
            $pdf->SetX(114);
            $pdf->Write($line_height, date('d.m.Y', $record->entry_mktime));
            $pdf->SetX(144);
            $pdf->Write($line_height, $record->defined_comment . ' ' . $record->comment);
            
            $pdf->Ln();
//            $pdf->Cell(0, 0, $record->begin, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
//            $pdf->Cell(0, 0, $record->break, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
//            $pdf->Cell(0, 0, $record->end, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
//            $pdf->Cell(0, 0, $record->sum, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
//            $pdf->Cell(0, 0, $record->defined_comment, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
//            $pdf->Cell(0, 0, $record->comment, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
//            $pdf->Cell(0, 0, $record->entry_mktime, 0, 1, 'L', 0, '', 0, false, 'C', 'C');
            
        }

//        $pdf->SetTopMargin(40);
//        $pdf->SetLeftMargin(20);
//        $pdf->SetRightMargin(20);
       
//        $x = 35;
//        $y = 50;
//        $w = 60;
//        $h = 60;
        
        
            //$html = $this->htmlentitiesOutsideHTMLTags($note_content[0], ENT_HTML401);
//            $html = 'Hiermit wird bescheinigt, dass <br><br><br><br><b>Herr/Frau '. $user 
//                    . '</b><br><br><br>am ' . date("d.m.Y",time()) 
//                    . '<br><br><br>an der Mitarbeiterschulung der Fa. ' . $institute
//                    . '</b><br><br><br>zum Thema<br>'
//                    . '<h1 style="text-align:center">' . $seminar . '</h1>'
//                    . '<br><br><br><br><br>erfolgreich teilgenommen hat.'
//                    . '<br><br><br><br><br><br><br><br>Stephan Beume<br>'
//                    . 'Rechtsanwalt<br>'
//                    . 'Fachanwalt für Arbeitsrecht<br>'
//                    . 'Datenschutzbeauftragter (TÜV)<br>';
//            $pdf->writeHTMLCell('0', '0', '30', '80', studip_utf8encode($html), false, 0, false, 0);
        
        $fileid = time();   
        //$pdf->Output('/tmp/zertifikat'. $fileid, 'F');
        //return '/tmp/zertifikat'. $fileid;
        //$pdf->Output( $TMP_PATH .'/zertifikat' . $fileid, 'D');
        $pdf->Output( 'Stundenzettel' . $fileid, 'D');
        //return $TMP_PATH . '/zertifikat' . $fileid;
        //exit("delivering pdf file");
    }
    
}