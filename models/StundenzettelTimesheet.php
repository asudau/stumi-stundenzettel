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
     
        
        // create new PDF document
        $pdf = new Tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'ISO-8859-1', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();
        $pdf->Image($GLOBALS['ABSOLUTE_PATH_STUDIP'] . '/' . PluginEngine::getPlugin('HilfskraftStundenzettel')->getPluginPath().'/assets/images/formblatt.png', 0, 0, 1200, 1550, '', '', '', false, 300);
            
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
        $pdf->Output( $TMP_PATH .'/zertifikat' . $fileid, 'F');
        return $TMP_PATH . '/zertifikat' . $fileid;
        //exit("delivering pdf file");
    }
}