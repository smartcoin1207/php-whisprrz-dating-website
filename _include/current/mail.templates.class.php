<?php
//Popcorn Added 2024-09-28

class CMailTemplates extends CHtmlBlock
{
    public $template_type = '';

    function action()
    {
        $cmd = get_param('cmd', '');
        $id = get_param('id', '');
        $template_type = get_param('template_type', '');
        
        if($cmd == 'get_templates') {
            try {
                $sql = "SELECT * FROM mail_templates WHERE user_id = " . to_sql(guid(), 'Number') . " AND type=" . to_sql($template_type, 'Text');
                $templates = DB::rows($sql);

                echo json_encode(array("templates" => $templates, "status" => "success"));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }

        if($cmd == 'get_template_detail') {
            try {
                $sql = "SELECT * FROM mail_templates WHERE user_id = " . to_sql(guid(), 'Number') . " AND id=" . to_sql($id, 'Number');
                $template = DB::row($sql);
                $template_pre = array(
                    'id' => $template['id'],
                    'subject' => $template['subject'],
                    'text' => $template['text'],
                    'title' => $template['title']
                );
                
                echo json_encode(array("template" => $template_pre, "status" => "success"));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }

        if ($cmd == 'add_template') {
            $title = get_param('title', '');
            $subject = get_param('subject', '');
            $text = get_param('text', '');

            try {
                $row = array('user_id' => guid(),
                         'title' => $title,
                         'subject' => $subject,
                         'text' => $text,
                         'type' => $template_type,
                );
                DB::insert('mail_templates', $row);
                $id = DB::insert_id();

                $sql = "SELECT * FROM mail_templates WHERE user_id = " . to_sql(guid(), 'Number') . " AND type=" . to_sql($template_type, 'Text');
                $templates = DB::rows($sql);
                $mail_templates_list = Common::listMailTemplates('', false, $template_type);
                echo json_encode(array("status" => "success", "templates" => $templates, "mail_templates_list" => $mail_templates_list ));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }

        if ($cmd == 'update_template') {
            $title = get_param('title', '');
            $subject = get_param('subject', '');
            $text = get_param('text', '');

            try {
                $row = array(
                    'title' => $title,
                    'subject' => $subject,
                    'text' => $text,
                );
                DB::update('mail_templates', $row, '`id`=' . to_sql($id, 'Number'));

                $sql = "SELECT * FROM mail_templates WHERE user_id = " . to_sql(guid(), 'Number') . " AND type=" . to_sql($template_type, 'Text');
                $templates = DB::rows($sql);
                $mail_templates_list = Common::listMailTemplates('', false, $template_type);

                echo json_encode(array("templates" => $templates, "mail_templates_list" => $mail_templates_list, "status" => "success"));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }

        if ($cmd == 'delete_template') {
            try {
                DB::delete('mail_templates', '`id`=' . to_sql($id, 'Number'));

                $sql = "SELECT * FROM mail_templates WHERE user_id = " . to_sql(guid(), 'Number') . " AND type=" . to_sql($template_type, 'Text');
                $templates = DB::rows($sql);
                $mail_templates_list = Common::listMailTemplates('', false, $template_type);

                echo json_encode(array("templates" => $templates, "mail_templates_list" => $mail_templates_list, "status" => "success"));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }
    }
    
    function parseBlock(&$html)
    {
        $html->setvar('mail_templates_list', Common::listMailTemplates('', false, $this->template_type));
        
        $html->setvar('template_type', $this->template_type);
        parent::parseBlock($html);
    }
}