<?php
/**
* reminder_email.php
*
* @author Manuel Schwarz <manschwa@uos.de>
* @access public
*/

require_once __DIR__ . '/../models/StundenzettelContract.class.php';
require_once __DIR__ . '/../models/StundenzettelTimesheet.class.php';
require_once __DIR__ . '/../models/StundenzettelRecord.class.php';
require_once __DIR__ . '/../models/StundenzettelInstituteSetting.class.php';
require_once __DIR__ . '/../Stundenzettel.class.php';

class ReminderEmail extends CronJob
{

    public static function getName()
    {
        return 'Stundenzettel - Erinnerungsmail an NutzerInnen verschicken';
    }

    public static function getDescription()
    {
        return 'Sendet Erinnerungsmail an NutzerInnen welche ihren Stundenzettel noch nicht abgegeben haben.';
    }

    public function execute($last_result, $parameters = array())
    {
        $month = (int) date('m', strtotime('first day of last month'));    // format: 5
        $year = (int) date('Y', strtotime('first day of last month'));     // format: 2021

        $contracts = StundenzettelContract::getContractsByMonth($month, $year);
        foreach ($contracts as $contract) {
            
            $no_recording_required = ($contract->begin_digital_recording_year == $year) && ($month < $contract->begin_digital_recording_month);
            
            if (!$no_recording_required){
                $timesheet = StundenzettelTimesheet::getContractTimesheet($contract->id, $month, $year);
                if (!$timesheet) {
                    self::sendMissingTimesheetMail($contract->id, $month, $year);
                    //echo 'Erinnerung -anlegen- versendet an ' . User::find($contract->user_id)->username . ' für Zeitraum ' . $month . ' ' . $year;
                } elseif ($timesheet->overdue && !$timesheet->finished) {
                    //echo 'Erinnerung -einreichen- versendet an ' . User::find($contract->user_id)->username . ' für Zeitraum ' . $month . ' ' . $year;
                    self::sendOverdueMail($contract->id, $month, $year);
                }
            }
        }
    }

    private static function sendMissingTimesheetMail($contract_id, $month, $year)
    {
        $contract = StundenzettelContract::find($contract_id);
        $user_id = $contract->user_id;
        $subject = sprintf('Erinnerung: Abgabe für %s überfällig - Bitte nutzen Sie den digitalen Stundenzettel', strftime("%B", mktime(0, 0, 0, $month, 10)));
        $mailtext = "Sie erhalten diese automatisch generierte E-Mail, da Sie im letzten Monat als Hilfskraft an der Universität Osnabrück angestellt waren "
            . "und bisher noch keinen Stundenzettel in Stud.IP angelegt und eingereicht haben.\n"
            . "Bitte tragen Sie unter\n"
            .  $GLOBALS['ABSOLUTE_URI_STUDIP'] . sprintf("plugins.php/stundenzettel/timesheet/select/%s/%s/%s\n\n", $contract_id, $month, $year)
            . "\n unverzüglich Ihre Arbeitszeiten ein und reichen Sie den digitalen Stundenzettel ein.\n"
            . "Vielen Dank. \n\n"
            . "Mit freundlichen Grüßen,\n"
            . "Ihr virtUOS-Team";
        self::sendReminderMail($user_id, $subject, $mailtext, $contract_id);
    }

    private static function sendOverdueMail($contract_id, $month, $year)
    {
        $contract = StundenzettelContract::find($contract_id);
        $user_id = $contract->user_id;
        $subject = sprintf('Erinnerung: Abgabe Ihres Stundenzettels für %s', strftime("%B", mktime(0, 0, 0, $month, 10)));
        $mailtext = "Sie erhalten diese automatisch generierte E-Mail, da Sie im letzten Monat als Hilfskraft an der Universität Osnabrück angestellt waren "
            . "und Ihren digitalen Stundenzettel in Stud.IP bisher noch nicht abgegeben haben.\n"
            . "Bitte holen Sie dies unverzüglich nach, sobald Sie Ihre Arbeitszeiten entprechend eingetragen haben:\n"
            .  $GLOBALS['ABSOLUTE_URI_STUDIP'] . sprintf("plugins.php/stundenzettel/timesheet/select/%s/%s/%s\n\n", $contract_id, $month, $year)
            . "\n Vielen Dank. \n\n"
            . "Mit freundlichen Grüßen,\n"
            . "Ihr virtUOS-Team";
        self::sendReminderMail($user_id, $subject, $mailtext, $contract_id);
    }

    private static function sendReminderMail($user_id, $subject, $mailtext, $contract_id)
    {
            $recipient = User::find($user_id)->email;
            $contract = StundenzettelContract::find($contract_id);
            $settings = StundenzettelInstituteSetting::find($contract->inst_id);
            $sender = $settings->inst_mail;

            $mail = new StudipMail();
            return $mail->addRecipient($recipient)
                 ->setSenderEmail($sender)
                 ->setSenderName('Sekretariat virtUOS')
                 ->setSubject($subject)
                 ->setBodyText($mailtext)
                 ->send();
    }
}
