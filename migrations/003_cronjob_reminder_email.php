<?php

/**
 * 003_cronjob_reminder_email.php
 *
 * @author Manuel Schwarz <manschwa@uos.de>
 */
class CronjobReminderEmail extends Migration
{

    const FILENAME = 'public/plugins_packages/virtUOS/Stundenzettel/cronjobs/ReminderEmail.php';

    public function description()
    {
        return 'Add cronjob for sending e-mails for overdue or missing timesheets.';
    }

    public function up()
    {
        $task_id = CronjobScheduler::registerTask(self::FILENAME, true);

        // Schedule job to run every day at 03:00 o'clock
        if ($task_id) {
            CronjobScheduler::schedulePeriodic($task_id, 0, 3);
        }
    }

    function down()
    {
        if ($task_id = CronjobTask::findByFilename(self::FILENAME)->task_id) {
            CronjobScheduler::unregisterTask($task_id);
        }
    }
}
