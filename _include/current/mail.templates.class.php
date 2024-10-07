<?php
//Popcorn Added 2024-09-28

class CMailTemplates extends CHtmlBlock
{
    function action()
    {
        $cmd = get_param('cmd', '');
        $id = get_param('id', '');
        
        if($cmd == 'get_template') {
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

        if($cmd == 'save_template') {
            $title = get_param('title', '');
            $subject = get_param('subject', '');
            $text = get_param('text', '');

            try {
                $row = array('user_id' => guid(),
                         'title' => $title,
                         'subject' => $subject,
                         'text' => $text,
                );
                DB::insert('mail_templates', $row);
                $id = DB::insert_id();
                echo json_encode(array("status" => "success"));
            } catch (\Throwable $th) {
                echo json_encode(array("status" => "error"));
            }
        }
    }
    
    function parseBlock(&$html)
    {
        $html->setvar('mail_templates_list', Common::listMailTemplates());
        parent::parseBlock($html);
    }
}