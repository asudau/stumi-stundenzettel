<?php
/**
* reminder_email.php
*
* @author Manuel Schwarz <manschwa@uos.de>
* @access public
*/

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
        $month = (int) date('m', strtotime('last month'));    // format: 5
        $year = (int) date('Y', strtotime('last month'));     // format: 2021

        $contracts = StundenzettelContract::getContractsByMonth($month, $year);
        foreach ($contracts as $contract) {
            $timesheet = StundenzettelTimesheet::getContractTimesheet($contract->id, $month, $year);
            if (!$timesheet) {
                sendMissingTimesheetMail($contract->user_id);
            } elseif ($timesheet->overdue && !$timesheet->finished) {
                sendOverdueMail($contract->user_id);
            }
        }
    }

    private static function sendMissingTimesheetMail($user_id)
    {
        $subject = 'Erinnerung: Bitte nutzen Sie den digitalen virtUOS-Stundenzettel';
        $mailtext = "Sie erhalten diese automatisch generierte E-Mail, da Sie im letzten Monat beim virtUOS angestellt waren "
            . "und bisher noch keinen Stundenzettel im Stundenzettel-Plugin in Stud.IP angelegt haben.\n"
            . "Bitte holen Sie dies schnellstmöglich nach und füllen diesen entsprechend mit Ihren Arbeitszeiten aus.\n"
            . "Vielen Dank. \n\n"
            . "Mit freundlichen Grüßen,\n"
            . "Ihr virtUOS-Team";
        sendReminderMail($user_id, $subject, $mailtext);
    }

    private static function sendOverdueMail($user_id)
    {
        $subject = 'Erinnerung: Abgabe Ihres virtUOS-Stundenzettels';
        $mailtext = "Sie erhalten diese automatisch generierte E-Mail, da Sie im letzten Monat beim virtUOS angestellt waren "
            . "und Ihren Stundenzettel im Stundenzettel-Plugin in Stud.IP bisher noch nicht abgegeben haben.\n"
            . "Bitte holen Sie dies schnellstmöglich nach sobald Sie Ihre Arbeitszeiten entprechend eingetragen haben.\n"
            . "Vielen Dank. \n\n"
            . "Mit freundlichen Grüßen,\n"
            . "Ihr virtUOS-Team";
        sendReminderMail($user_id, $subject, $mailtext);
    }

    private static function sendReminderMail($user_id, $subject, $mailtext)
    {
            $recipient = User::find($user_id)->email;
            $sender = "sekretariat-virtuos@uni-osnabrueck.de";

            $mail = new StudipMail();
            return $mail->addRecipient($recipient)
                 ->setSenderEmail($sender)
                 ->setSenderName('Sekretariat virtUOS')
                 ->setSubject($subject)
                 ->setBodyText($mailtext)
                 ->send();
    }
}
