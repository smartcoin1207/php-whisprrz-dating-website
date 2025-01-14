<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

class EmailQueue extends Table {

    protected $table = 'email_queue';

    private static $isAddModeActive = true;

    //public $debug = true;

    public function send()
    {
        self::$isAddModeActive = false;

        $sendingTime = time();

        $limit = intval(Common::getOption('email_queue_limit'));

        // set 600 seconds timeout for locking if something with database before next attempt
        $emails = $this->select('sending_time = 0 OR sending_time < ' . ($sendingTime - 600), $limit, 'id ASC');
        foreach($emails as $email) {
            // set exlusive access to current email only
            $row = array('sending_time' => $sendingTime);
            $where = 'sending_time = ' . to_sql($email['sending_time']) . '
                AND id = ' . to_sql($email['id']);
            // update email for blocking
            $this->update($row, $where);

            $this->load($email['id']);

            // check if success lock
            if($this->getSendingTime() == $sendingTime) {
                $this->delete($email['id']);
                send_mail($email['to'], $email['from'], $email['subject'], $email['message'], $email['name']);
                $this->logger('Sent', "{$email['id']} - {$email['to']}");
            } else {
                $this->logger('Blocked', "{$email['id']} - {$email['to']}");
            }

            // test delay
            // usleep(rand(100000, 500000));
        }

        self::$isAddModeActive = true;
    }

    public function getSendingTime()
    {
        return $this->getItem('sending_time');
    }

    public static function isActive()
    {
        return Common::isOptionActive('use_email_queue');
    }

    public static function isAddModeActive()
    {
        return self::$isAddModeActive;
    }

}