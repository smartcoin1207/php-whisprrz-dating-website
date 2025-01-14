<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include('../_include/core/administration_start.php');

class CSupportTickets extends CHtmlList
{
    function action()
    {
        global $p;

        $cmd = get_param('cmd');

        if ($cmd == "delete_ticket") {
            $ticket_id = get_param('ticket_id');

            $listTickets = explode(',', $ticket_id);
            foreach ($listTickets as $id) {
                $where = 'id = ' . to_sql($id);
                $ticket = DB::one('support_tickets', $where);
                if ($ticket && !empty($ticket['attachment'])) {
                    $patch = Common::getOption('url_files', 'path');
                    $patch . "support_ticket/" . $ticket['attachment'];
                    unlink($patch . "support_ticket/" . $ticket['attachment']);
                }

                $ticket_replies = DB::select('ticket_replies', 'ticket_id =' . to_sql($id));
                foreach ($ticket_replies as $ticket_replie) {
                    if ($ticket_replie && !empty($ticket_replie['attachment'])) {
                        $patch = Common::getOption('url_files', 'path');
                        $patch . "support_ticket/" . $ticket_replie['attachment'];
                        unlink($patch . "support_ticket/" . $ticket_replie['attachment']);
                    }
                    DB::delete('ticket_replies', 'id =' . to_sql($ticket_replie['id']));
                }
                DB::delete('support_tickets', $where);
            }
            redirect("{$p}?action=delete");
        }
    }
    function init()
    {
        global $g;

        $this->m_on_page = 20;
        $this->m_on_bar = 10;

        $this->m_sql_count = "SELECT COUNT(ST.id) FROM support_tickets AS ST " . $this->m_sql_from_add;
        $this->m_sql = "SELECT ST.*, UF.name AS name_from, UT.name AS name_assign
                          FROM support_tickets AS ST
                          JOIN user AS UF ON UF.user_id = ST.user_from
                          JOIN user AS UT ON UT.user_id = ST.assign_to" . $this->m_sql_from_add;

        $this->m_field['id'] = array("id", null);
        $this->m_field['date'] = array("date", null);
        $this->m_field['user_from'] = array("user_from", null);
        $this->m_field['assign_to'] = array("assign_to", null);
        $this->m_field['priority'] = array("priority", null);
        $this->m_field['title'] = array("title", null);
        $this->m_field['msg'] = array("msg", null);
        $this->m_field['status'] = array("status", null);
        $this->m_field['name_from'] = array("name_from", null);
        $this->m_field['name_assign'] = array("name_assign", null);
        $this->m_field['image_yes_no'] = array("image_yes_no", null);

        $this->m_sql_where = "1";
        $this->m_sql_order = "priority";
        #$this->m_debug = "Y";
    }

    function onPostParse(&$html)
    {
        if ($this->m_total != 0) {
            $html->parse('no_delete');
        }
    }

    function onItem(&$html, $row, $i, $last)
    {
        global $g;

        $this->m_field['date'][1] = date("d M, Y", strtotime($row['date']));
        $this->m_field['priority'][1] = ucfirst($row['priority']);
        $this->m_field['msg'][1] = nl2br($row['msg']);
        $this->m_field['image_yes_no'][1] = !empty($row['attachment']) ? l('yes') : l('no');
        //$this->m_field['status'][1] = ($row['status'] == '1') ? l('open') : l('close');
        parent::onItem($html, $row, $i, $last);
    }
}

$page = new CSupportTickets('main', $g['tmpl']['dir_tmpl_administration'] . 'support_tickets.html');
$header = new CAdminHeader('header', $g['tmpl']['dir_tmpl_administration'] . '_header.html');
$page->add($header);
$footer = new CAdminFooter('footer', $g['tmpl']['dir_tmpl_administration'] . '_footer.html');
$page->add($footer);

$page->add(new CAdminPageMenuBlock());

include('../_include/core/administration_close.php');
