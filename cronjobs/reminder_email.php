<?php
/**
* reminder_email.php
*
* @author Manuel Schwarz <manschwa@uos.de>
* @access public
*/
require_once 'lib/classes/CronJob.class.php';


class ReminderEmail extends CronJob
{

    public static function getName()
    {
        return dgettext('Stundenzettel', 'Stundenzettel - Erinnerungsmail an NutzerInnen verschicken');
    }

    public static function getDescription()
    {
        return dgettext('Stundenzettel', 'Sendet Erinnerungsmail an NutzerInnen welche ihren Stundenzettel noch nicht abgegeben haben.');
    }

    public function execute($last_result, $parameters = array())
    {
        $month = (int)date('m');     // format: 5
        $year = (int)date('Y');      // format: 2021

        $month = $month - 1;
        if ($month == 0) {          // special case: turn of the year
            $month = 12;
            $year = $year - 1;
        }

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
        $mailtext = '<html>

            <body>
            <h2>Erinnerung: Bitte nutzen Sie den digitalen Stundenzettel</h2>
            <p>Sie erhalten diese E-Mail, da Sie im letzten Monat beim virtUOS angestellt waren und bisher noch keinen
               Stundenzettel im Stundenzettel-Plugin in Stud.IP angelegt haben. Bitte holen Sie dies schnellstmöglich nach
               und füllen diesen entsprechend mit Ihren Arbeitszeiten aus. Vielen Dank.</p>
            <p>Mit freundlichen Grüßen,</p>
            <p>Ihr virtUOS-Team</p>
            </body>
            </html>
            ';
        sendReminderMail($user_id, $mailtext);
    }

    private static function sendOverdueMail($user_id)
    {
        $mailtext = '<html>

            <body>
            <h2>Erinnerung: Abgabe Ihres Stundenzettels</h2>
            <p>Sie erhalten diese E-Mail, da Sie im letzten Monat beim virtUOS angestellt waren und Ihren Stundenzettel
               im Stundenzettel-Plugin in Stud.IP bisher noch nicht abgegeben haben. Bitte holen Sie dies schnellstmöglich nach
               sobald Sie Ihre Arbeitszeiten entprechend eingetragen haben. Vielen Dank.</p>
            <p>Mit freundlichen Grüßen,</p>
            <p>Ihr virtUOS-Team</p>
            </body>
            </html>
            ';
        sendReminderMail($user_id, $mailtext);
    }

    private static function sendReminderMail($user_id, $mailtext)
    {
            $recipient = User::find($user_id)->email;
            $sender = "sekretariat-virtuos@uni-osnabrueck.de";
            $subject = "Erinnerung: Stundenzettel virtUOS";

            $mail = new StudipMail();
            return $mail->addRecipient($recipient)
                 ->setSenderEmail($sender)
                 ->setSenderName('Sekretariat virtUOS')
                 ->setSubject($subject)
                 ->setBodyHtml($mailtext)
                 ->setBodyText(strip_tags($mailtext))
                 ->send();
    }
}
