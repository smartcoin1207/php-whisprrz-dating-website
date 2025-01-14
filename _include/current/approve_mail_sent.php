<?php
// include("./_include/core/main_start.php");

class CApproveMail
{
    public static function approve_sent_mail($id, $subject, $text, $admin = false)
    {

            
            if (trim($subject) == '') {
                $subject = l('no_subject');
            }

            if ($subject != '' && $text != '') {
                $textHash = md5(mb_strtolower($text, 'UTF-8'));


                if ($id != 0) {
                    $idMailFrom = 0;
                    $sqlInto = '';
                    $sqlValue = '';
                    if (get_param('type') != 'postcard') {
                        $sqlInto = ', text_hash';
                        $sqlValue = ', ' . to_sql($textHash);
                    }

                        $idMailFrom = DB::insert_id();
                        $g_user_id = 12;
                        
                        DB::execute("
                        INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent, new, type, receiver_read" . $sqlInto . ")
                        VALUES(
                        " . $g_user_id . ",
                        " . $g_user_id . ",
                        " . to_sql($id, "Number") . ",
                        " . 3 . ",
                        " . to_sql($subject, 'Text') . ",
                        " . to_sql($text, 'Text') . ",
                        " . time() . ",
                        'N',
                        " . to_sql(get_param('type')) . ",
                        'N'" . $sqlValue . ")
                        ");

                        DB::execute("
                        INSERT INTO mail_msg (user_id, user_from, user_to, folder, subject, text, date_sent, type, receiver_read, sent_id" . $sqlInto . ")
                            VALUES(
                            " . to_sql($id, "Number") . ",
                            " . $g_user_id . ",
                            " . to_sql($id, "Number") . ",
                            " . 1 . ",
                            " . to_sql($subject, 'Text') . ",
                            " . to_sql($text, 'Text') . ",
                            " . time() . ",
                            " . to_sql(get_param('type')) . ",
                            'N',
                            " . to_sql($idMailFrom, 'Number') . $sqlValue . ")
                        ");

                        $userToInfo = User::getInfoBasic($id);

                        if($userToInfo)
                        {
                            Common::usersms('new_mail_sms', $userToInfo, 'set_sms_alert_rm');
                        }

                        DB::execute("UPDATE user SET new_mails=new_mails+1 WHERE user_id=" . to_sql($id, "Number") . "");
                }
            }

       
    }
}