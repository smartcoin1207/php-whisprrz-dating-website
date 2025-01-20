<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */


class CSelectUserList extends CHtmlBlock
{
    public $userlist_type;
    public $event_id;
    private $table_name = 'saved_user_list';

    public function action()
    {
        global $g;

        $this->table_name = 'saved_user_list';
        $this->userlist_type = get_param('userlist_type', '');
        $this->event_id = get_param('event_id', '');

        $cmd = get_param('cmd', '');

        if ($cmd == 'get_one_saved_userlist') {
            $id = get_param('id', '');

            try {
                $sql = "SELECT * FROM  "  . $this->table_name . " WHERE id=" . to_sql($id, 'Number') . " LIMIT 1";
                $row = DB::row($sql);

                $detail = self::getListDetail($row);
                echo json_encode(array("userlist_detail" => $row, "status" => "success"));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }

        if ($cmd == 'get_all_users') {
            $all_users = self::getAllUsers();

            $detail = array(
                'title' => '',
                'all_users' => $all_users,
            );

            echo json_encode(array("detail" => $detail, "status" => "success"));
        }

        if ($cmd == 'get_one_userlist_edit') {
            $all_users = self::getAllUsers();

            $id = get_param('id', '');
            $sql = "SELECT * FROM  "  . $this->table_name . " WHERE id=" . to_sql($id, 'Number') . " LIMIT 1";
            $row = DB::row($sql);

            $detail = self::getListDetail($row);
            
            $detail['all_users'] = $all_users;
            echo json_encode(array("detail" => $detail, "status" => "success"));
        }

        if ($cmd == 'get_all_saved_userlist') {
            try {
                $sql = "SELECT * FROM " . $this->table_name . " WHERE type=" . to_sql($this->userlist_type, 'Text') . " AND `user_id`=" . to_sql(guid(), 'Number') . " AND event_id=" . to_sql($this->event_id, 'Number') . " ORDER BY `id` DESC";
                
                $rows = DB::rows($sql);

                $all_list = [];
                foreach ($rows as $row) {
                    $detail = self::getListDetail($row);
                    $all_list[] = $detail;
                }
                echo json_encode(array("all_userlist" => $all_list, "status" => "success"));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }

        if ($cmd == 'add_saved_userlist') {
            $users = get_param_array('users');
            $title = get_param('title');

            $row = array(
                'user_id' => guid(),
                'user_ids' => json_encode($users),
                'event_id' => $this->event_id,
                'type' => $this->userlist_type,
                'title' => $title,
            );

            DB::insert($this->table_name, $row);
            $insert_id = DB::insert_id();

            $sql = "SELECT * FROM " . $this->table_name . " WHERE `id`=" . to_sql($insert_id, 'Number') . " LIMIT 1";
            $row = DB::row($sql);

            echo json_encode(array("userlist_detail" => $row, "status" => "success"));
        }

        if ($cmd == 'update_saved_userlist') {
            $is_edit = get_param('is_edit', '');
            $id = get_param('id', '');
            $users = get_param_array('users');
            $title = get_param('title');
            if (count($users) == 0 || !$title) {
                echo json_encode(array("status" => "error"));
                exit;
            }

            $row = array(
                'user_id' => guid(),
                'user_ids' => json_encode($users),
                'event_id' => $this->event_id,
                'type' => $this->userlist_type,
                'title' => $title,
            );

            try {
                if ($is_edit == 'edit') {
                    DB::update($this->table_name, $row, '`id`=' . to_sql($id, 'Number'));
                } else {
                    DB::insert($this->table_name, $row);
                    $insert_id = DB::insert_id();
                    $id = $insert_id;
                }

                $sql = "SELECT * FROM  " . $this->table_name . " WHERE id=" . to_sql($id, 'Number') . " LIMIT 1";
                $row = DB::row($sql);
                $detail = self::getListDetail($row);
                $saved_user_list = self::getSavedUserList($this->event_id, $this->userlist_type);

                echo json_encode(array("detail" => $detail, "saved_user_list" => $saved_user_list, "status" => "success"));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }

        //Delete saved_userlist
        if ($cmd == 'delete_saved_userlist') {
            $id = get_param('id', '');
            $this->userlist_type = get_param('userlist_type', '');

            try {
                DB::delete($this->table_name, '`id`=' . to_sql($id, 'Number'));
            
                $sql = "SELECT * FROM " . $this->table_name . " WHERE type=" . to_sql($this->userlist_type, 'Text') . " AND `user_id`=" . to_sql(guid(), 'Number') . " ORDER BY `id` DESC";
                $rows = DB::rows($sql);

                $all_list = [];
                foreach ($rows as $row) {
                    $detail = self::getListDetail($row);
                    $all_list[] = $detail;
                }

                $saved_user_list = self::getSavedUserList($this->event_id, $this->userlist_type);
                echo json_encode(array("all_userlist" => $all_list, "saved_user_list" => $saved_user_list, "status" => "success", "message" => 'Successfully Deleted'));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }
    }
    
    function getListDetail($row) {
        if (!$row) {
            return null;
        }
        $user_ids = $row['user_ids'];
        $user_ids_array = json_decode($user_ids, true);
        if (count($user_ids_array) > 0) {
            $user_ids_string = implode(',', $user_ids_array);

            $user_sql = "SELECT u.user_id, u.name FROM user AS u WHERE u.user_id IN($user_ids_string)";
            $users = DB::rows($user_sql);
            
            $users_count = count($users);
            $user_names = array_map(function($user) {
                return $user['name'];
            }, $users);
            $user_names_string = implode(',', $user_names);
    
            $user_id_array = array_map(function($user) {
                return $user['user_id'];
            }, $users);
            $user_ids_string = implode(',', $user_id_array);
        } else {
            $user_ids_string = '';
            $user_names_string = '';
            $users_count = 0;
            $users = null;
        }

        $detail = array(
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'event_id' => $row['event_id'],
            'title' => $row['title'],
            'type' => $row['type'],
            'count' => $users_count,
            'user_names_string' => $user_names_string,
            'user_ids_string' => $user_ids_string,
            'users' => $users,
        );

        return $detail;
    }

    function getAllUsers() {
        if ($this->userlist_type == 'group') {
            $all_users_sql = "SELECT u.user_id, u.name FROM groups_social_subscribers AS gs LEFT JOIN user AS u ON gs.user_id = u.user_id WHERE gs.group_id = " . to_sql($this->event_id, 'Number');
        } elseif ($this->userlist_type == 'event') {
            $all_users_sql = "SELECT u.user_id, u.name FROM events_event_guest AS eg LEFT JOIN user AS u ON eg.user_id = u.user_id WHERE eg.event_id = " . to_sql($this->event_id, 'Number');
        } elseif ($this->userlist_type == 'hotdate') {
            $all_users_sql = "SELECT u.user_id, u.name FROM hotdates_hotdate_guest AS hg LEFT JOIN user AS u ON hg.user_id = u.user_id WHERE eg.hotdate_id = " . to_sql($this->event_id, 'Number');
        } elseif ($this->userlist_type == 'partyhou') {
            $all_users_sql = "SELECT u.user_id, u.name FROM partyhouz_partyhou_guest AS pg LEFT JOIN user AS u ON pg.user_id = u.user_id WHERE pg.partyhou_id = " . to_sql($this->event_id, 'Number');
        } elseif ($this->userlist_type == 'user') {
            $all_users_sql = "SELECT u.user_id, u.name FROM user WHERE hide_time != 0 ";
        }

        $all_users = DB::rows($all_users_sql);
        return $all_users;
    }

    function getSavedUserList($event_id, $userlist_type = '')
    {
        $sql = "SELECT id, title FROM saved_user_list WHERE `user_id` = " . to_sql(guid(), 'Number') . " AND `event_id` = " . to_sql($event_id, 'Number') . " AND `type` = " . to_sql($userlist_type, 'Text') . " ORDER BY `id` DESC";

        return Common::getSavedUserList($sql);
    }

    public function parseBlock(&$html)
    {
        $html->setvar('saved_user_list', self::getSavedUserList($this->event_id, $this->userlist_type));
        $html->setvar('userlist_type', $this->userlist_type);
        $html->setvar('event_id', $this->event_id);

        parent::parseBlock($html);
    }
}
