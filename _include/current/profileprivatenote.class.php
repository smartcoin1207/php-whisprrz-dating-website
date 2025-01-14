<?php
class ProfilePrivateNote extends CHtmlBlock
{
    static function update()
    {
        global $g_user;

        $message = '';
        $responseData = false;
        $uid = get_param('uid');
        if ($g_user && $uid) {
            $comment = trim(get_param('comment'));
            $sql = 'SELECT `id`
                      FROM `users_private_note`
                     WHERE `user_id`=' . to_sql($uid)
                   . ' AND `from_user_id`=' . to_sql($g_user['user_id']);
            $id = DB::result($sql);
            if ($id) {
                DB::update('users_private_note', array('comment' => $comment), '`id` = ' . to_sql($id));
            } else {
                $data = array('user_id' => $uid,
                              'from_user_id' => $g_user['user_id'],
                              'comment' => $comment);
                DB::insert('users_private_note', $data);
            }

            if($comment == '') {
                $comment = l('it_will_be_visible_only_to_you');
            }

            if(get_param('no_format')) {
                $result = $comment;
            } else {
                $result = nl2br($comment);
            }

            return $result;
        }

        return $responseData;
    }

    function parseBlock(&$html)
	{
		global $g_user;
        $uid = get_param('uid');
        $html->setvar('user_id', $uid);
        $sql = 'SELECT `comment`
                  FROM `users_private_note`
                 WHERE `user_id`=' . to_sql($uid)
               . ' AND `from_user_id`=' . to_sql(guid());
        $value = DB::result($sql);
        if (!$value) {
            $value = '';
        }
        $html->setvar('value', $value);
		parent::parseBlock($html);
	}
}