<?php
class UserFields extends CHtmlBlock
{
    private $set;
    private $gUser;
    private $gUserNscCoupleId = 0;
    private $countShow = 0;
    private $countShowConst = 0;
    private $countShowGroupInt = array('0' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0);
    private $countShowGroupChecks = array('0' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0);
    private $countShowGroupСheckbox = array('0' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0);

    private $selectionFields = array('admin' => array('text', 'textarea', 'int', 'from', 'checks', 'const'),
        'profile' => array('text', 'textarea', 'const'),
        'pr_check' => array('text', 'textarea'),
        'personal' => array('int', 'checkbox'), //nnsscc-diamond-20200312
        'update_text' => array('text'),
        'update_personal_urban' => array('int', 'checkbox', 'group'),
        'personal_edit_urban' => array('int', 'checkbox', 'group', 'text'),
        'personal_edit_urban_mobile' => array('int', 'checkbox', 'group', 'text', 'textarea'),
        'partner' => array('from', 'checks'),
        'search_advanced' => array('from', 'checks', 'checkbox'), //&&&, 'checkbox'
        'par_check' => array('const', 'from'),
        'join' => array('text', 'textarea', 'int'),
        'update_admin_urban' => array('text', 'textarea', 'int', 'checkbox'),
        'texts' => array('text', 'textarea'),
        'profile_html' => array('text', 'textarea', 'int', 'checkbox'), //20200214-nnsscc-diamond
        'profile_html_urban' => array('text', 'textarea', 'int', 'map', 'location', 'interests', 'group', 'checkbox', 'private_note'),
        'profile_html_urban_mobile' => array('interests'),
        'birthday' => array(),
    );

    private $block = array('int' => 'int',
        'text' => 'text',
        'textarea' => 'textarea',
        'checks' => 'check',
        'checkbox' => 'checkbox',
        'checks_gorup' => 'checks',
        'const' => 'const',
        'from_to' => 'p_from_to',
        'age' => 'age_range',
        'day' => 'day_options',
        'month' => 'month_options',
        'year' => 'year_options',
        'group' => 'group', //appearance_group,
        'interests' => 'interests',
        'map' => 'map',
        'location' => 'location_choice',
        'private_note' => 'private_note',
    );

    private $param = array('day' => 'day',
        'month' => 'month',
        'year' => 'year',
    );

    private $cleanBlocks = array('profile_html_urban' => array('personal', 'basic', 'interests'),
        'admin' => array('int', 'checkbox', 'personal_text'),
    );

    private $generalBlock = array('int' => 'fields_int',
        'texts' => 'fields_text',
        'text' => 'text',
        'textarea' => 'textarea',
        'cheks' => 'cheks',
        'from_to' => 'fields_checks',
        'checkbox' => 'fields_checkbox',
        'module' => 'module_fields_urban',
    );

    private $alwaysVisible = array();
    private $customFields = array();
    private $customBanFields = array();
    private $allowedGruops = array(0, 1, 2, 3, 4, 'const', 'basic', 'personal', 'physical', 'other');
    private $bannedGroups = array();
    private $currentTypeParse = false;
    private $paramDisplay = '';
    private $isChangesFields = false;
    private $textsApproval = array();

    protected $gFields;
    protected $gFieldsGroup = array(0 => array(), // Const -> Basic
        1 => array(), // Personal
        2 => array(), // Appearance
        3 => array(), // Empty
        4 => array(), // Basic
    );
    //protected $oneField;
    protected $typeParse = false;

    public $profileSectionsCount = 3;
    public $userId = false;
    public $formatValue = '';
    public $message = '';
    public $isMostPopularInterest = true;
    public $parseSearchModule = false;

    public $mode = 'edit';
    private $multipleField = array('private_note');

    public $cityInfo = null;

    static $parseTextDescrption = false;
    private $setAdminAboutProfile = false;

    public function __construct($name, $html_path, $isTextTemplate = false, $textTemplate = false, $noTemplate = false, $typeParse = false, $uid = false)
    {
        $this->typeParse = $typeParse;
        $this->userId = $uid; //(empty($uid)) ? guid() : $uid;
        $this->paramDisplay = get_param('display');
        $this->set = Common::getOption('set', 'template_options');
        $this->name = Common::getOption('name', 'template_options');
        $this->set = "old";
        $this->name = "oryx";

        parent::__construct($name, $html_path, $isTextTemplate = false, $textTemplate = false, $noTemplate = false);
    }

    public function parseBlock(&$html)
    {
        if ($this->typeParse != false) {
            $this->parseFieldsAll($html, $this->typeParse);
        }

        parent::parseBlock($html);
    }

    public function init()
    {
        global $g_user;
        global $g;

        $option = 'fields_not_available';
        if ($this->set == 'urban' && $this->typeParse == 'admin') {
            $option = 'fields_not_available_admin';
            $this->selectionFields['admin'] = array('text', 'textarea', 'int', 'const', 'checkbox');
            if ($this->name == 'edge') {
                //$this->selectionFields['admin'] = array('text', 'textarea', 'const');
            }
        }
        if ($this->name == 'edge') {
            //$this->selectionFields['update_admin_urban'] = array('text', 'textarea', 'const');
            if ($this->typeParse == 'personal_edit_urban_mobile' || $this->typeParse == 'birthday') {
                unset($g['user_var']['star_sign']);
            }
        }
        if ($this->set == 'urban'
            && ($this->typeParse == 'personal_edit_urban' || $this->typeParse == 'personal_edit_urban_mobile')) {
            unset($g['user_var']['star_sign']);
        }

        //We must move in the condition below - is not enough id in each field (from Urbana already recorded in the id)
        self::removeUnavailableField($option);
        $this->gFields = $g['user_var'];

        //Sorted into groups - is part isAllowed postponed
        $guid = guid();
        foreach ($this->gFields as $name => $field) {
            if ($field['status'] == 'active' || in_array($this->typeParse, $this->alwaysVisible)) {
                if ((!$guid || $guid == $this->userId) && in_array($name, $this->multipleField)) {
                    continue;
                }
                $group = $field['group'];
                $nameGroup = $this->getNameGruops($group);
                if ((in_array($group, $this->allowedGruops) || in_array($nameGroup, $this->allowedGruops))
                    && (!in_array($group, $this->bannedGroups) && !in_array($nameGroup, $this->bannedGroups))) {
                    if (!isset($this->gFieldsGroup[$group])) {
                        $this->gFieldsGroup[$group] = array();
                    }
                    $this->gFieldsGroup[$group][$name] = $field;
                } else {
                    unset($this->gFields[$name]);
                }
            } else {
                unset($this->gFields[$name]);
            }
        }
        
        //$this->gUser = User::getInfoFull($this->userId);
        if ($this->userId == false) {
            $this->gUser = $g_user;
        } elseif ($this->userId == 'empty') {
            // Переделать, чтобы задавал только нужные параметры ($this->userId = массив ключей)
            $this->gUser['country_id'] = $g_user['country_id'];
        } else {
            $this->gUser = User::getInfoFull($this->userId, 0, true);
        }
        if ($this->set == 'urban'
            && ($this->m_name == 'profile_html_urban' || in_array($this->typeParse, array('profile_html_urban', 'admin')))) {
            if (isset($this->gUser['horoscope'])) {
                $this->gUser['star_sign'] = $this->gUser['horoscope'];
            }
        }

        $this->setValueTexts();
    }

    private function getNameGruops($num)
    {
        $gruops = array(0 => 'const', 1 => 'personal', 2 => 'physical', 3 => 'other', 4 => 'basic');
        return (isset($gruops[$num])) ? $gruops[$num] : '';
    }

    private function isAllowed($data, $type, $name)
    {
        if (((isset($this->selectionFields[$type]) && in_array($data['type'], $this->selectionFields[$type])) || in_array($name, $this->customFields))
            && !in_array($name, $this->customBanFields)) {
            return true;
        } else {
            return false;
        }
    }

    public function setCustomFields($fields)
    {
        $this->customFields = $fields;
    }

    public function setBanCustomFields($fields)
    {
        $this->customBanFields = $fields;
    }

    public function setAllowedGruops($groups)
    {
        $this->allowedGruops = $groups;
    }

    public function setBanGruops($groups)
    {
        $this->bannedGroups = $groups;
    }

    public function setMostPopularInterest($value)
    {
        $this->isMostPopularInterest = $value;
    }

    private function getParam($name, $default = '')
    {
        $result = $this->getUser($name, $default);
        if ($this->mode != 'view') {
            $result = get_param($name, $result);
        }

        return $result;
    }

    private function getUser($name, $default = '')
    {
        return isset($this->gUser[$name]) ? $this->gUser[$name] : $default;
    }
    //nnsscc-diamond-20200323-start
    private function getParamNscCouple($name, $default = '')
    {
        $result = $this->getUserNscCouple($name, $default);
        if ($this->mode != 'view') {
            //$result = getParamNscCouple($name, $result);
        }

        return $result;
    }

    private function getUserNscCouple($name, $default = '')
    {
        $nsc_couple_id = $this->gUserNscCoupleId;
        $nsc_new_couple_row = DB::row('SELECT * FROM userinfo WHERE user_id = ' . $nsc_couple_id, 1);
        return isset($nsc_new_couple_row[$name]) ? $nsc_new_couple_row[$name] : $default;
    }
    //nnsscc-diamond-20200323-end

    public function setFormatValue($format)
    {
        $this->formatValue = $format;
    }

    private function formatValue($value)
    {
        switch ($this->formatValue) {
            case 'html':
                $value = nl2br($value);
                break;
            case 'text':
                $value = str_replace(array('"', "\r\n"), array('&#034;', "\n"), strip_tags($value));
                break;
            case 'entities':
                $value = htmlentities($value, ENT_COMPAT, 'UTF-8');
                break;
        }
        return $value;
    }

    private function cleanBlocks($html, $currenBlock, $group = '')
    {
        if ($group != '') {
            $group = '_' . $group;
        }

        if (isset($this->cleanBlocks[$this->currentTypeParse])) {
            foreach ($this->cleanBlocks[$this->currentTypeParse] as $cleanBlock) {
                if ($cleanBlock != $currenBlock) {
                    $html->setblockvar($cleanBlock . $group, '');
                }
            }
        }
        if (isset($this->selectionFields[$this->currentTypeParse])) {
            foreach ($this->selectionFields[$this->currentTypeParse] as $block) {
                if ($block != $currenBlock && isset($this->block[$block])) {
                    //$this->block[$block] . $group . '<br>';
                    $html->setblockvar($this->block[$block] . $group, '');
                }
            }
            //echo $currenBlock . '----<br>';
        }
    }

    private function parseField($html, $name, $data, $isGroup = false, $parse = true, $parse_js = true, $block = '')
    {
        $type = ($block == '') ? $this->block[$data['type']] : $block;
        $block = ($isGroup) ? $type . '_' . $data['group'] : $type;

        $title = l($data['title']);
        $html->setvar('name', $name);
        $html->setvar('field', $title);
        $html->setvar('field_js', toJs($title));

        if ($this->set == 'urban') {
            if (!guid() && Common::isOptionActive('hide_profile_data_for_guests_urban')) {
                $html->parse("{$block}_show_more", false);
            }
            if ($this->userId == guid() || in_array($name, $this->multipleField)) {
                $html->parse($block . '_handler_js', $parse_js);
                $html->parse("{$block}_edit", false);
                if ($data['type'] == 'text' || $data['type'] == 'textarea') {
                    $blockEditor = "{$block}_{$data['type']}";
                    if ($html->blockExists($blockEditor)) {
                        $blockClean = $data['type'] == 'text' ? $this->block['textarea'] : $this->block['text'];
                        $blockClean = "{$block}_{$blockClean}";
                        $html->clean($blockClean, false);
                        $html->parse($blockEditor, false);
                    }
                }
                $html->parse("{$block}_edit_field", false);
            } else {
                $html->parse("{$block}_visitor_field", false);
            }
            if ($this->typeParse == 'admin') {
                $html->setvar('disabled', ($name == 'star_sign') ? 'disabled' : '');
            }
        }

        $html->parse($block, $parse);
        $html->parse($block . '_js', $parse_js);
    }

    public function parseText($html, $name, $data, $parse_js = true, $isGroup = false, $block = '', $default = false)
    {
        $description = '';
        if (self::$parseTextDescrption || ($default && $this->set == 'urban' && empty($this->gUser[$name]))) {
            if ($this->userId == guid()) {
                $lVal = "field_description_{$name}";
                $desc = l($lVal);
                if ($desc != $lVal) {
                    if (!self::$parseTextDescrption && $this->name !== 'edge') {
                        $this->gUser[$name] = $desc;
                    }
                    $description = toAttrL($lVal);
                }
            } /* elseif ($name == 'interested_in') {
        $this->gUser[$name] = User::getLookingFor($this->userId);
        }*/
        }

        $html->setvar('field_description', $description);
        $html->setvar('maxlen', $data['length']);
        if ($this->gUserNscCoupleId == 0) {
            $value = $this->formatValue($this->getParam($name));
        } else {
            $value = $this->formatValue($this->getParamNscCouple($name));
        }

        /* URBAN */
        $forcedOptionName = 'forced_user_about_me';
        if (!guid()
            && $this->set == 'urban'
            && (Common::isOptionActive('hide_profile_data_for_guests_urban') || Common::isOptionActive($forcedOptionName))) {
            $value = hard_trim($value, 25);
        }
        /* URBAN */
        $myId = guid();
        if ($myId != $this->userId && (Common::isOptionActive($forcedOptionName) && $this->set == 'urban')) {
            $myValue = '';
            if ($myId) {
                $userInfo = User::getInfoFull($myId);
                if (isset($userInfo[$name])) {
                    $myValue = $userInfo[$name];
                }
            }
            if ($myValue == '') {
                $valueNeat = hard_trim($value, 25);
                if ($value != $valueNeat) {
                    $html->setvar('forced_phrase', toJs(l('forced_phrase_about_me')));
                    $html->setvar('field_name', $name);
                    $html->parse('forced_show_more', false);
                    $value = $valueNeat;
                } else {
                    $html->clean('forced_show_more');
                }
            } else {
                $html->clean('forced_show_more');
            }
        } else {
            $html->clean('forced_show_more');
        }

        if ($this->name === 'edge' && $this->mode === 'view' && $value === '') {
            // hide empty fields in the right side column
            return;
        }

        $html->setvar('value', $value);

        if ($html->varExists('value_input')) {
            $valueInput = heEmoji($this->getParam($name));
            $html->setvar('value_input', $valueInput);
        }

        if ($html->varExists('value_entities')) {
            if ($this->gUserNscCoupleId == 0) {
                $valueEntities = he_decode($this->getParam($name));
            } else {
                $valueEntities = he_decode($this->getParamNscCouple($name));
            }

            $html->setvar('value_entities', htmlentities($valueEntities, ENT_COMPAT, 'UTF-8'));
        }

        $html->setvar('type', $data['type']);
        $this->parseField($html, $name, $data, $isGroup, false, $parse_js, $block);
        $noBlock = ($data['type'] == 'text') ? $this->block['textarea'] : $this->block['text']; //Не надо?
        $html->setblockvar($noBlock, ''); //Не надо?

        $clean = ($block != '') ? $block : $data['type'];
        $this->cleanBlocks($html, $clean);
        $html->parse($this->generalBlock['texts'], true);
        $this->countShow++;
    }

    public function parseInt($html, $name, $data, $isGroup = true, $setTitle = false, $parse = true, $defaultValue = '-', $block = '')
    {
        $isParse = false;
        if ($data['number_values'] > 0) {
            if ($this->mode == 'edit') {
                $html->setvar('options', DB::db_options("SELECT id, title FROM " . $data['table']
                    . " ORDER BY id ASC", $this->getParam($name)));
            }
            $fieldValue = $defaultValue;

            if ($setTitle) {
                $sql = "SELECT `title`
                          FROM " . $data['table']
                . " WHERE `id` = " . to_sql($this->getParam($name), 'Number');
                $value = DB::result($sql);
                if ($value != '0') {
                    $fieldValue = $this->formatValue(l($value));
                }
                /* URBAN */
                $setFieldValue = $fieldValue;
                if (!guid()
                    && Common::getOption('name', 'template_options') == 'urban'
                    && Common::isOptionActive('hide_profile_data_for_guests_urban')) {
                    $setFieldValue = '';
                }
                /* URBAN */
                $html->setvar('value', $setFieldValue);
            }
            $this->parseField($html, $name, $data, $isGroup, $parse, true, $block);
            //$this->cleanBlocks($html, 'int');

            //$html->parse($this->generalBlock['int'], true);
            //$html->parse($this->generalBlock['module'], true);
            $isParse = (empty($fieldValue)) ? false : true;
        }
        if ($isParse) {
            $this->countShowGroupInt[$data['group']]++;
        }
        return $isParse;
    }

    public function parseIntNscCouple($html, $name, $data, $isGroup = true, $setTitle = false, $parse = true, $defaultValue = '-', $block = '')
    {
        $isParse = false;
        if ($data['number_values'] > 0) {
            if ($this->mode == 'edit') {
                $html->setvar('options', DB::db_options("SELECT id, title FROM " . $data['table']
                    . " ORDER BY id ASC", $this->getParamNscCouple($name)));
            }
            $fieldValue = $defaultValue;

            if ($setTitle) {
                $sql = "SELECT `title`
                          FROM " . $data['table']
                . " WHERE `id` = " . to_sql($this->getParamNscCouple($name), 'Number');
                $value = DB::result($sql);
                if ($value != '0') {
                    $fieldValue = $this->formatValue(l($value));
                }
                /* URBAN */
                $setFieldValue = $fieldValue;
                if (!guid()
                    && Common::getOption('name', 'template_options') == 'urban'
                    && Common::isOptionActive('hide_profile_data_for_guests_urban')) {
                    $setFieldValue = '';
                }
                /* URBAN */
                $html->setvar('value', $setFieldValue);
            }
            $this->parseField($html, $name, $data, $isGroup, $parse, true, $block);
            //$this->cleanBlocks($html, 'int');

            //$html->parse($this->generalBlock['int'], true);
            //$html->parse($this->generalBlock['module'], true);
            $isParse = (empty($fieldValue)) ? false : true;
        }
        if ($isParse) {
            $this->countShowGroupInt[$data['group']]++;
        }
        return $isParse;
    }

    public function parseSetValueJs($html, $type)
    {
        $this->init();
        foreach ($this->gFields as $name => $data) {
            if ($this->isAllowed($data, $type, $name)) {
                $html->setvar('id', $name);
                $html->setvar('value', $this->gUser[$name]);
                $html->parse('set_value_field_js');
            }
        }
    }

    public function parseConst($html, $name, $data)
    {
        // echo $name;
        if ($name == "relation") {
            $this->parseSlides($html, $name, $data);
            return false;
        } else if ($data['table'] != '' && $data['number_values'] > 0) {
            $html->setvar('options', DB::db_options("SELECT id, title FROM " . $data['table']
                . " ORDER BY id ASC", $this->getParam($name)));
            $this->parseField($html, $name, $data);
            $this->countShowConst++;
        }
    }

    public function parseFromTo($html, $name, $data, $isGroup = true, $parse = false, $from = '', $to = '')
    { #$from = 'first', $to = 'last_option'
    if ($data['number_values'] > 0) {
        $block = ($isGroup) ? $this->block['from_to'] . '_' . $data['group'] : $this->block['from_to'];
        $fieldTo = substr($name, 0, strlen($name) - 4) . "to";

        $html->setvar('name_from', $name);
        $html->setvar('name_to', $fieldTo);
        $html->setvar('field', l($data['title']));

        $html->setvar('from_options', DB::db_options("SELECT id, title FROM " . $data['table'] . " ORDER BY id ASC", get_param($name, $this->getUser($name, $from))));
        $html->setvar('to_options', DB::db_options("SELECT id, title FROM " . $data['table'] . " ORDER BY id ASC", get_param($fieldTo, $this->getUser($fieldTo, $to))));
        $html->parse($block, $parse);

        if ($isGroup) {
            $html->setblockvar($this->block['checks_gorup'] . '_' . $data['group'], '');
            $html->parse($this->generalBlock['from_to'] . '_' . $data['group'], true);
        }

        if ($this->parseSearchModule) {
            $html->setvar('name', $name);
            $html->clean($this->block['checkbox']);
            $html->clean($this->block['checks_gorup']);
            $html->parse($this->generalBlock['from_to'], true);
        }

        $this->countShowGroupChecks[$data['group']]++;
    }
    }

    public function parseRadio($html, $name, $data, $numColumns = 2, $isGroup = true, $block = '', $option = null)
    {
        $this->parseColums($html, $name, $data, $numColumns, $isGroup, $block, $option);
    }

    public function parseCheckbox($html, $name, $data, $numColumns = 2, $isGroup = true, $block = '', $option = null)
    {
        $this->parseColums($html, $name, $data, $numColumns, $isGroup, $block, $option);
    }
    //nnsscc-diamond-20200312-start
    public function parseHobbies($html, $name, $data, $numColumns = 4, $isGroup = true, $block = '', $option = null, $nsc_couple_id = 0)
    {
        $this->parseColumsHobbies($html, $name, $data, $numColumns, $isGroup, $block, $option, $nsc_couple_id);
    }
    //nnsscc-diamond-20200312-end
    public function parseCheckboxCustom($html, $name, $data, $isGroup = true, $parse = true)
    {
        if (!self::isActive($name)) {
            return;
        }

        $desc = '';
        $option = array();
        if ($this->parseSearchModule) {
            $param = get_param_array($name);
            if (!empty($param)) {
                $option[$data['id']] = get_param_array($name);
            }
        } else {
            $option = $this->getUser('checkbox', array());
        }

        $i = 0;
        if (!isset($option[$data['id']])) {
            $option[$data['id']] = array();
            $option[$data['id']][0] = '';
        }

        $blockItem = ($isGroup) ? 'checkbox_item' . '_' . $data['group'] : 'checkbox_item';
        $prf = ($this->set == 'urban' && $this->typeParse == 'admin') ? ':' : '';
        $html->setvar('field', l($data['title']) . $prf);
        if ($html->varExists('field_name')) {
            $html->setvar('field_name', l($data['title']) . $prf);
        }

        foreach ($option[$data['id']] as $key => $value) {
            $html->setvar('name', $name);
            if ($i > 0) {
                $html->setvar('field', '');
            }
            $html->setvar('id', $key);
            $html->setvar('num', $i);
            if ($html->varExists('value_selected')) {
                $html->setvar('value_selected', $value);
            }
            //var_dump($value);
            $html->setvar('options', DB::db_options("SELECT id, title FROM " . $data['table']
                . " ORDER BY id ASC", $value));
            $html->parse($blockItem, true);
            $i++;
        }

        if ($i != DB::count($data['table'])) {
            $html->setvar('display_add', 'block');
        } else {
            $html->setvar('display_add', 'none');
        }

        if ($i) {
            $this->countShowGroupСheckbox[$data['group']]++;
        }
        $type = $this->block[$data['type']];
        $block = ($isGroup) ? "{$type}_{$data['group']}" : $type;
        $html->parse($block, $parse);
        $html->setblockvar($blockItem, '');
        if ($this->parseSearchModule) {
            $html->setvar('name', 'p_' . $name);
            $html->clean($this->block['from_to']);
            $html->clean($this->block['checks_gorup']);
            $html->parse($this->generalBlock['from_to'], true);
            $html->clean($block);
        }
        return true;
    }

    public function parseCheckboxGeneral($html, $name, $data, $isGroup = true, $parse = true)
    {
        if (!self::isActive($name)) {
            return;
        }

        $html->setvar('name', $name);

        $isParse = false;
        $desc = '';
        $option = $this->getUser('checkbox', array());
        if (isset($option[$data['id']]) && !empty($option[$data['id']])) {
            $sql = 'SELECT `title`
                      FROM ' . to_sql($data['table'], 'Plain') .
            ' WHERE `id` IN (' . to_sql(implode(',', $option[$data['id']]), 'Plain') . ')';
            $all = DB::column($sql);
            foreach ($all as $value) {
                $desc .= ', ' . l($value);
            }
        }
        if ($desc != '') {
            $type = $this->block[$data['type']];
            $block = ($isGroup) ? $type . '_' . $data['group'] : $type;
            $title = l($data['title']);
            $html->setvar('field', $title);
            if (!guid()
                && Common::getOption('name', 'template_options') == 'urban'
                && Common::isOptionActive('hide_profile_data_for_guests_urban')) {
                $desc = '';
            } else {
                $desc = substr($desc, 1);
            }
            $html->setvar('value', $desc);
            $html->parse($block, $parse);
            $isParse = true;
        }

        return $isParse;
    }
    //nnsscc-diamond-20200312-start
    public function parseColumsHobbies($html, $name, $data, $numColumns = 2, $isGroup = true, $block = '', $option = null, $nsc_couple_id = 0)
    {
        if ($data['number_values'] > 0) {

            $html->setvar('name', $name);
            $html->setvar('field', l($data['title']));

            $p = ($block == '') ? $this->block[$data['type']] : $block;
            $n = ($isGroup) ? '_' . $data['group'] : '';
            $column = "{$p}_column{$n}";
            $columnAll = "{$p}_item{$n}";
            $blockGroup = "{$p}{$n}";

            $sql = "SELECT `id`, `title` FROM " . $data['table'] . ' ORDER BY id ASC';
            $html->setvar('options', DB::db_options($sql, $this->getParam($name)));
            $rows = DB::rows($sql, 0, true);

            if ($rows) {
                $i = 0;
                $total = count($rows);

                if ($html->varExists('count_column_first')) {
                    $countInColumn = $total - $numColumns;
                    if ($countInColumn >= 0) {
                        $countInColumn = $numColumns;
                    } else {
                        $countInColumn = $total;
                    }
                    $html->setvar('count_column_first', $countInColumn);
                }
                //$inColumn = ceil(($total + $add) / $numColumns);
                if ($option === null) {
                    //$option = $this->getUser('checkbox', array());
                    if ($nsc_couple_id == 0) { //nnsscc-diamond-20200323
                        $option = User::getInfoCheckbox($this->userId, $name, 1);
                    } else {
                        $option = User::getInfoCheckbox($nsc_couple_id, $name, 1);
                    }
                    //$option = (isset($option[$data['id']])) ? $option[$data['id']] : array();
                } //else {
                //$option = User::getInfoCheckbox($this->userId, $name, 1);
                //}
                $isFirstOptionChecked = false;
                if ($this->parseSearchModule && !guid() && empty($option)) {
                    $isFirstOptionChecked = true;
                }

                foreach ($rows as $row) {
                    $i++;
                    $html->setvar('id', $row[0]);
                    $type = 'normal';
                    if ($name == 'interests') {
                        $type = 'list';
                    }
                    if ($name == 'hobbies') { //nnsscc-diamond-20200312
                        $type = 'list';
                    }
                    //$html->setvar($name . '_class', self::getArrayNameIcoField($name, $row[0], $type));

                    $value = $this->translation($data['title'], $row['title']);
                    $html->setvar('title', $value);

                    $isChecked = (in_array($row[0], $option) || $isFirstOptionChecked);

                    $blockSelected = $name . '_selected';
                    if ($html->blockExists($blockSelected)) {
                        if ($isChecked) {
                            $html->parse($blockSelected, false);
                        } else {
                            $html->clean($blockSelected);
                        }
                    }

                    $html->setvar('checked', $isChecked ? ' checked' : '');
                    $isFirstOptionChecked = false;
                    //if ($i % $inColumn == 0 && $i != 0 && ($i != $total || $add > 0) && $numColumns != 1)
                    //if ($i % $numColumns == 0 || $numColumns == 1 || $i == $total)
                    if ($i % $numColumns == 0 && $numColumns != 1 && $i != $total) {
                        if ($html->varExists('count_column')) {
                            $countInColumn = $total - $i;
                            if ($countInColumn >= $numColumns) {
                                $countInColumn = $numColumns;
                            }
                            $html->setvar('count_column', $countInColumn);
                        }
                        $html->parse($column, false);
                    } else {
                        $html->setblockvar($column, '');
                    }

                    $html->parse($columnAll, true);
                    $html->parse('nnsscc_hobbies', true);
                }
                $html->parse('nnsscc_hobbies_parent', true);
                /* Filter */
                if (!self::isActive('orientation') && !self::isActive('age_range') && $html->blockexists('am_here_to_center')) {
                    $html->parse('am_here_to_center');
                }
                /* Filter */
                if ($nsc_couple_id != 0 && $nsc_couple_id == guid()) {
                    $html->parse("{$blockGroup}_edit", true);
                } else if ($this->userId == guid()) {
                    $html->parse("{$blockGroup}_edit", true);
                }
                $html->parse($blockGroup, false);
                $html->setblockvar($columnAll, '');

                if ($data['type'] == 'checkbox') {
                    $this->countShowGroupСheckbox[$data['group']]++;
                }
            }
        }
    }
    //nnsscc-diamond-20200312-end
    public function parseColums($html, $name, $data, $numColumns = 2, $isGroup = true, $block = '', $option = null)
    {
        if ($data['number_values'] > 0) {

            $html->setvar('name', $name);
            $html->setvar('field', l($data['title']));

            $p = ($block == '') ? $this->block[$data['type']] : $block;
            $n = ($isGroup) ? '_' . $data['group'] : '';
            $column = "{$p}_column{$n}";
            $columnAll = "{$p}_item{$n}";
            $blockGroup = "{$p}{$n}";

            $sql = "SELECT `id`, `title` FROM " . $data['table'] . ' ORDER BY id ASC';

            $rows = DB::rows($sql, 0, true);

            if ($rows) {
                $i = 0;
                $total = count($rows);

                if ($html->varExists('count_column_first')) {
                    $countInColumn = $total - $numColumns;
                    if ($countInColumn >= 0) {
                        $countInColumn = $numColumns;
                    } else {
                        $countInColumn = $total;
                    }
                    $html->setvar('count_column_first', $countInColumn);
                }
                //$inColumn = ceil(($total + $add) / $numColumns);
                if ($option === null) {
                    //$option = $this->getUser('checkbox', array());
                    $option = User::getInfoCheckbox($this->userId, $name, 1);
                    //$option = (isset($option[$data['id']])) ? $option[$data['id']] : array();
                } //else {
                //$option = User::getInfoCheckbox($this->userId, $name, 1);
                //}
                $isFirstOptionChecked = false;
                if ($this->parseSearchModule && !guid() && empty($option)) {
                    $isFirstOptionChecked = true;
                }
                foreach ($rows as $row) {
                    $i++;

                    $html->setvar('id', $row[0]);
                    $type = 'normal';
                    if ($name == 'interests') {
                        $type = 'list';
                    }
                    $html->setvar($name . '_class', self::getArrayNameIcoField($name, $row[0], $type));

                    $value = $this->translation($data['title'], $row['title']);

                    $html->setvar('title', $value);

                    $isChecked = (in_array($row[0], $option) || $isFirstOptionChecked);

                    $blockSelected = $name . '_selected';
                    if ($html->blockExists($blockSelected)) {
                        if ($isChecked) {
                            $html->parse($blockSelected, false);
                        } else {
                            $html->clean($blockSelected);
                        }
                    }

                    $html->setvar('checked', $isChecked ? ' checked' : '');
                    $isFirstOptionChecked = false;
                    //if ($i % $inColumn == 0 && $i != 0 && ($i != $total || $add > 0) && $numColumns != 1)
                    //if ($i % $numColumns == 0 || $numColumns == 1 || $i == $total)
                    if ($i % $numColumns == 0 && $numColumns != 1 && $i != $total) {
                        if ($html->varExists('count_column')) {
                            $countInColumn = $total - $i;
                            if ($countInColumn >= $numColumns) {
                                $countInColumn = $numColumns;
                            }
                            $html->setvar('count_column', $countInColumn);
                        }
                        $html->parse($column, false);
                    } else {
                        $html->setblockvar($column, '');
                    }

                    $html->parse($columnAll, true);
                }

                /* Filter */
                if (!self::isActive('orientation') && !self::isActive('age_range') && $html->blockexists('am_here_to_center')) {
                    $html->parse('am_here_to_center');
                }
                /* Filter */
                if ($this->userId == guid()) {
                    $html->parse("{$blockGroup}_edit", true);
                }
                $html->parse($blockGroup, false);
                $html->setblockvar($columnAll, '');

                //if ($isGroup) {
                //$html->setblockvar($this->block['from_to'] . '_' . $data['group'], '');
                //$html->parse($this->generalBlock['from_to'] . '_' . $data['group'], true);
                //}
                //DB::free_result();
                if ($data['type'] == 'checkbox') {
                    $this->countShowGroupСheckbox[$data['group']]++;
                }
                //$this->countShowGroupСheckbox[$data['group']]++;
            }
        }
    }

    public function parseChecks($html, $name, $data, $numColumns = 2, $add = 0, $isGroup = true, $block = '', $mask = true, $value = null, $prf = null)
    {
        global $p;
        if ($data['number_values'] > 0) {
            $html->setvar('name', $name);
            $html->setvar('field', l($data['title']));
            if ($value === null) {
                $mask = ($mask == true) ? $this->getUser($name, 0) : get_checks_param($name);
            } else {
                $mask = $value;
            }

            //$block = ($block == '') ? $this->block[$data['type']] : $block;
            $pb = ($block == '') ? $this->block[$data['type']] : $block;
            $n = ($isGroup) ? '_' . $data['group'] : '';
            //$pb = ($isGroup) ? $block : '';
            if ($name == "hobbies") {
                $pb = "check";
            }
            //nnsscc-diamond-20200315

            $column = $pb . '_column' . $n;
            $columnAll = $pb . $n;
            $blockGroup = $pb . 's' . $n;

            $sql = "SELECT id, title FROM " . $data['table'] . ' ORDER BY id ASC';

            $isAllOptionChecked = false;
            if ($this->parseSearchModule && !guid() && !$mask) {
                $isAllOptionChecked = true;
            }

            $rows = DB::rows($sql, 0, true);

            if ($rows) {
                $i = 0;
                $total_checks = count($rows);

                $isSearchMobile = $name == 'p_orientation' && $p == 'search.php' && $this->name == 'urban_mobile';
                if ($isSearchMobile) {
                    $countInColumn = $total_checks - $numColumns;
                    if ($countInColumn >= 0) {
                        $countInColumn = $numColumns;
                    } else {
                        $countInColumn = $total_checks;
                    }
                    $html->setvar('count_column_first', $countInColumn);
                }

                $in_column = ceil(($total_checks + $add) / $numColumns);

                $labelField = $html->blockExists('label_field');
                foreach ($rows as $row) {
                    if ($labelField) {
                        if ($i) {
                            $html->parse('label_field_empty', false);
                            $html->clean('label_field');
                        } else {
                            $html->parse('label_field', false);
                            $html->clean('label_field_empty');
                        }
                    }
                    $i++;
                    $html->setvar('id', $row[0]);

                    $value = $this->translation($data['title'], $row['title'], $prf);

                    $html->setvar('title', $value);

                    $isChecked = ($mask & (1 << ($row[0] - 1))) || $isAllOptionChecked;

                    $blockSelected = $name . '_selected';
                    if ($html->blockExists($blockSelected)) {
                        if ($isChecked) {
                            $html->parse($blockSelected, false);
                        } else {
                            $html->clean($blockSelected);
                        }
                    }

                    if ($isChecked) {
                        $html->setvar('checked', ' checked');
                    } else {
                        $html->setvar('checked', '');
                    }

                    if (($isSearchMobile && $i % $numColumns == 0 && $numColumns != 1 && $i != $total_checks)
                        || (!$isSearchMobile && $i % $in_column == 0 && $i != 0 && ($i != $total_checks || $add > 0) && $numColumns != 1)) {
                        if ($isSearchMobile) {
                            $countInColumn = $total_checks - $i;
                            if ($countInColumn >= $numColumns) {
                                $countInColumn = $numColumns;
                            }
                            $html->setvar('count_column', $countInColumn);
                        }
                        $html->parse($column, false);
                    } else {
                        $html->setblockvar($column, '');
                    }

                    /*if ($i % $in_column == 0 && $i != 0 && ($i != $total_checks || $add > 0) && $numColumns != 1)
                    $html->parse($column, false);
                    else
                    $html->setblockvar($column, '');*/
                    $html->parse($columnAll, true);
                }
                /* Looking For */
                if (!self::isActive('age_range')) {
                    $html->parse($pb . '_style', false);
                }
                /* Looking For */

                if ($mask == 0) {
                    $html->setvar('nm_check', ' checked');
                } else {
                    $html->setvar('nm_check', '');
                }

                $html->parse($blockGroup, false);
                $html->setblockvar($columnAll, '');

                if ($isGroup) {
                    $html->setblockvar($this->block['from_to'] . '_' . $data['group'], '');
                    $html->parse($this->generalBlock['from_to'] . '_' . $data['group'], true);
                }

                if ($this->parseSearchModule) {
                    $html->clean($this->block['from_to'], '');
                    $html->parse($this->generalBlock['from_to'], true);
                } elseif ($isGroup && $this->currentTypeParse == 'search_advanced') {
                    $html->clean($blockGroup);
                }

                DB::free_result();
                $this->countShowGroupChecks[$data['group']]++;
            }
        }
    }

    //parse slides of what are you looking for and I am looking for ...
    public function parseSlides($html, $name, $data, $user_id = '11')
    {

        global $g_user;
        if ($this->gUserNscCoupleId == 0) {
            $relation_user = $g_user;
        } else if ($this->gUserNscCoupleId != 0) {
            $relation_user = DB::row("SELECT * FROM user WHERE user_id = '" . $this->gUserNscCoupleId . "'");
        }

        // var_dump($g_user); die();

        $user_id = $relation_user['user_id']; //user_id of user table
        $mask = ""; //value from scale sliders fields of user table.
        $mask = isset($relation_user[$name]) ? $relation_user[$name] : "";
        $maskArray = [];

        $parts = explode(", ", $mask);

        foreach ($parts as $part) {
            if (strpos($part, ":") !== false) {
                list($key, $value) = explode(":", $part);
                $maskArray[(int) $key] = (int) $value;
            }
        }

        // get from const_relation, const_orientation table
        $csql = "SELECT * FROM const_" . $name . ";";

        if ($name == "p_orientation") {
            $csql = "SELECT * FROM const_orientation;";
        }

        $c_rows = DB::rows($csql);

        // get levels from looking_level table
        $l_sql = "SELECT id, title FROM looking_level;";
        $l_levels = DB::rows($l_sql);

        foreach ($c_rows as $key => $c_row) {

            $checked = "";
            $disabled = "";
            $slides_name = "";
            $slides_value = "";
            $slides_id = "p_looking_" . $name . "_" . $c_row['id'];
            $slides_name = $slides_id;

            if (isset($maskArray[$c_row['id']])) {
                $slides_value = $maskArray[$c_row['id']];
                $checked = "checked";
                $disabled = "";
            } else {
                $slides_value = "";
                $checked = "";
                $disabled = "disabled = true";
            }

            $html->setvar('scale_field', $c_row['title']);

            $html->setvar('slides_name', $slides_name);

            $match = false;

            foreach ($l_levels as $level_id => $level_title) {
                if (isset($maskArray[$c_row['id']]) && $level_title['id'] == $maskArray[$c_row['id']]) {
                    $match = true;
                    break;
                }
            }

            $c = 0;
            foreach ($l_levels as $level_id => $level_title) {
                $radio_checked = isset($maskArray[$c_row['id']]) && $level_title['id'] == $maskArray[$c_row['id']] ? "checked" : "";
                $html->setvar('relation_radio_value', $level_title['id']);
                $html->setvar('relation_radio_label', $level_title['title']);
                $html->setvar('relation_radio_id', "p_looking_" . $name . "_" . $c_row['id'] . "_" . $level_title['id']);

                $html->setvar('radio_checked', $radio_checked);
                if ($c == 2 && !$match) {
                    $html->setvar('radio_checked', "checked");
                }

                $html->setvar('radio_disabled', $disabled);

                $html->parse('radio_item', true);
                $c++;
            }

            $html->setvar('slides_name', $slides_name);
            $html->setvar('slides_value', $slides_value);
            $html->setvar('checked', $checked);
            $html->setvar('disabled', $disabled);

            $html->setvar('slides_id', $slides_id);
            $html->parse('what_looking_for_relation_row', true);
            $html->clean('radio_item');

        }

        $html->setvar('looking_name', "p_looking_" . $name);
        $html->setvar('field', l($data['title']));
        $html->parse('what_looking_for_relation', true);

        $html->clean('what_looking_for_relation_row');

    }

    // make static and add wherever displayed interests (CIm)
    public function parseInterests($html, $name, $data)
    {
        global $p;

        if (!self::isActive($name)) {
            return;
        }

        $isCustomPart = Common::isOptionActive('custom_show_part_interests', 'template_options');
        $isGuid = (guid() == $this->userId);
        if ($isCustomPart && $p != 'profile_interests_edit.php') {
            if ($isGuid && $this->paramDisplay == '') {
                return;
            }
            $numberCustomPart = Common::getOption('custom_show_part_interests_number', 'template_options');
        }

        $userInterests = User::getInterests($this->userId);
        /* Move to getInterests in IM */
        $userInterestsAll = array();

        if (!$isGuid) {
            $guidInterests = User::getInterests(guid());
            $guidInterestsAll = array();
            foreach ($guidInterests as $item) {
                $guidInterestsAll[$item['id']] = $item;
                $guidInterestsAll[$item['id']]['main'] = 1;
            }
            foreach ($userInterests as $item) {
                $userInterestsAll[$item['id']] = $item;
            }
            $userInterests = array_merge(array_intersect_key($guidInterestsAll, $userInterestsAll), array_diff_key($userInterestsAll, $guidInterestsAll));
        }
        /* Move to getInterests in IM */

        $i = 0;
        $isHideProfile = !guid() && Common::isOptionActive('hide_profile_data_for_guests_urban');
        $numberInterests = count($userInterests);
        $isParseMorePart = false;
        $trimCustomPart = Common::getOption('custom_show_part_interests_trim', 'template_options');
        foreach ($userInterests as $item) {
            $userInterestsAll[] = $item['id'];
            $html->setvar('int_id', $item['id']);
            $html->setvar('cat_id', $item['category']);
            $title = $item['interest'];
            $titleUpper = mb_ucfirst($title);
            $html->setvar('interest', $titleUpper);
            if ($trimCustomPart && $this->paramDisplay != 'profile_info' && $p != 'profile_interests_edit.php') {
                $trim = rand($trimCustomPart[0], $trimCustomPart[1]);
                $title = hard_trim($title, $trim);
            }
            $html->setvar('interest', $title);
            if (isset($item['main'])) {
                $html->parse('main_interest', false);
                $type = 'shared';
            } else {
                $type = 'normal';
                $html->clean('main_interest');
            }
            $html->setvar('interest_class', self::getArrayNameIcoField($name, $item['category'], $type));

            if ($isGuid) {
                $html->setvar('title_interes', l('delete') . ' ');
                $html->setvar('interest_he', he($titleUpper));
                $html->parse('main_interest_cr', false);
            } else {
                $html->setvar('title_interes', l('show_users_who_have_this_interest'));
                $html->setvar('interest_he', '');
                $html->parse('main_interest_cr', false);
                $html->parse('search_click', false);
            }

            $html->parse('list_interest_user_item');
            $i++;
            if ($isCustomPart && $this->paramDisplay != 'profile_info' && $p != 'profile_interests_edit.php'
                && $numberInterests > $numberCustomPart
                && $i == $numberCustomPart) {
                $isParseMorePart = true;
                break;
            }
            if (!guid() && $i == 2) {
                if ($isHideProfile && $i == 2) {
                    break;
                }
            }
        }
        $blockMorePart = 'list_interest_user_more_part';
        if ($isParseMorePart && $html->blockExists($blockMorePart)) {
            $count = $numberInterests - $numberCustomPart;
            $html->setvar($blockMorePart . '_data_value', $count);
            $html->setvar($blockMorePart . '_data_tmpl', toAttrL('show_more_part_interests'));
            $html->setvar($blockMorePart . '_value', lSetVars('show_more_part_interests', array('number' => $count)));
            $html->parse($blockMorePart);
        }

        $isInterests = true;
        if (count($userInterestsAll)) {
            $html->parse('list_interest_user_title');

            if ($isHideProfile) {
                $html->parse('list_interest_user_show_more');
            }
            if ($isGuid) {
                $html->parse('main_interest_delete');
                if ($html->blockExists('main_interest_edit')) {
                    $html->parse('main_interest_edit');
                }
            }

            $html->parse('list_interest_user');
            $isInterests = false;
        }

        if ($isGuid) {
            if ($isInterests) {
                if ($html->blockExists('main_interest_edit')) {
                    $html->parse('main_interest_edit');
                }
                $html->parse('list_interest_user_title');
                $html->parse('list_interest_user');
            }

            $catInt = DB::select($data['table']);
            $numberNotEmptyCategory = self::getNumberNotEmptyCategory($catInt);

            if ($this->isMostPopularInterest) {
                array_unshift($catInt, array('id' => 1, 'title' => l('Most popular')));
            }

            if ($numberNotEmptyCategory) {
                $blockInterestCustom = 'interests_custom';
                $blockInterestsCustom = 'interests_custom';
                $blockInterestCustomItem = $blockInterestCustom . '_item';
                $blockInterestCustomMore = $blockInterestCustom . '_more';
                $blockInterestsCustomHide = $blockInterestsCustom . '_hide';
                $blockInterestsCustomCategory = $blockInterestsCustom . '_category_item';
                $blockInterestsCustomCategoryList = $blockInterestsCustomCategory . '_list';
                foreach ($catInt as $item) {
                    $html->clean($blockInterestCustomItem);
                    $html->clean($blockInterestCustomMore);
                    $html->clean($blockInterestsCustomCategory . '_selected');
                    $html->clean($blockInterestsCustomCategory . '_selected_title');
                    $html->clean($blockInterestsCustomCategoryList . '_hide'); //??? not
                    $html->clean($blockInterestsCustomHide);
                    $html->clean('interests_custom_item');
                    $html->clean('interests_custom_more');
                    $html->setvar('id', $item['id']);
                    $html->setvar('interest_class', self::getArrayNameIcoField($name, $item['id'], 'list'));
                    $html->setvar('title', l($item['title']));

                    $where = '';
                    if ($item['id'] != 1) {
                        $where = ' WHERE `category` = ' . to_sql($item['id'], 'Number');
                    }
                    $sql = 'SELECT * FROM `interests`' . $where .
                    ' ORDER BY ' . "lang = " . to_sql(Common::getOption('lang_loaded', 'main')) . " DESC, counter DESC, id DESC";
                    if (DB::query($sql, 1)) {
                        $i = 0;
                        while ($row = DB::fetch_row(1)) {
                            if ($i == $numberNotEmptyCategory) {
                                $html->parse($blockInterestCustomMore, false);
                                break;
                            }
                            if (!in_array($row['id'], $userInterestsAll)) {
                                $i++;
                                $html->setvar('int_id', $row['id']);
                                $html->setvar('cat_id', $row['category']);
                                $html->setvar('interest', mb_ucfirst($row['interest']));
                                $html->parse($blockInterestCustomItem, true);
                            }
                        }
                    }
                    if ($i) {
                        if ($item['id'] == 1) {
                            $html->parse($blockInterestsCustomCategory . '_selected', false);
                            $html->parse($blockInterestsCustomCategory . '_selected_title', false);
                            $html->parse($blockInterestsCustomCategoryList . '_hide', false); //??? not
                        } else {
                            $html->parse($blockInterestsCustomHide, true);
                        }
                        $html->parse($blockInterestsCustomCategoryList, true);
                        $html->parse($blockInterestsCustomCategory, true);
                        $html->parse($blockInterestsCustom, true);
                    } else {
                        $html->parse($blockInterestsCustomCategoryList, true);
                    }
                }
            }

            if ($this->typeParse == 'profile_html_urban_mobile') {
                $this->parseColums($html, $name, $data, 2, false, 'interests_category');
            }
            $html->parse('interests_edit');
        }
        $html->parse('interests');
        //$this->countShowGroupСheckbox[$data['group']]++;
        //$this->parseCheckbox($html, $name, $data, 1, false, $this->block['interests'], array());
        $this->cleanBlocks($html, $this->block['interests']);
        $this->countShow++;
    }

    public static function getNumberNotEmptyCategory($category = null)
    {
        if ($category === null) {
            $category = DB::select('const_interests');
        }

        $numberNotEmptyCategory = 0;
        foreach ($category as $item) {
            $where = ' WHERE `category` = ' . to_sql($item['id'], 'Number');
            $sql = 'SELECT * FROM `interests`' . $where . ' LIMIT 1';
            DB::query($sql, 1);
            if (DB::num_rows(1)) {
                $numberNotEmptyCategory++;
            }
        }

        return $numberNotEmptyCategory;
    }

    public function getCityInfo()
    {
        if (!$this->cityInfo) {
            $city = DB::one('geo_city', '`city_id` = ' . to_sql($this->getUser('city_id', 0), 'Number'));
            $this->cityInfo = $city;
        }

        return $this->cityInfo;
    }

    public function parseChoiceLocation($html, $name, $data)
    {
        global $g_user;

        if (!self::isActive($name)) {
            return;
        }

        $city = $this->getCityInfo();
        $block = 'location_title';
        if (!empty($city)) {
            $html->setvar($block . '_city', l($city['city_title']) . ', ');
        }
        if (guid() == $this->userId) {
            $html->parse('city_choose');
        } else if ($g_user['user_id']) {
            if ($html->blockexists($block . '_visitors')) {
                $html->parse($block . '_visitors');
            }
        }
        $html->setvar($block . '_country', l($this->getUser('country', 0)));

        $html->parse($this->block['location'], true);
        $this->cleanBlocks($html, 'location');
        $this->countShow++;
    }

    public function parseMap($html, $name, $data)
    {
        global $g_user;

        if (!self::isActive($name)) {
            return;
        }

        $city = $this->getCityInfo();

        $block = 'map_title';
        if (!empty($city)) {
            if (Common::getOption('maps_service') == 'Bing') {
                $html->setvar('url', Common::getMapImageUrl($city['lat'] / IP::MULTIPLICATOR, ($city['long']) / IP::MULTIPLICATOR, 459, 277, 10, true));
            } elseif (Common::getOption('maps_service') == 'Google') {
                $html->setvar('url', Common::getMapImageUrl($city['lat'] / IP::MULTIPLICATOR, ($city['long']) / IP::MULTIPLICATOR, 459, 277, 1, true));
            }
        } else {
            $html->parse($block . '_map_hide');
        }

        $html->parse($this->block['map'], true);
        $this->cleanBlocks($html, $this->block['map']);
        $this->countShow++;
    }

    public function parsePrivateNote($html, $name, $data)
    {
        global $g_user;

        if (!self::isActive($name)) {
            return;
        }
        $sql = 'SELECT `comment`
                  FROM `users_private_note`
                 WHERE `user_id`=' . to_sql($this->userId)
        . ' AND `from_user_id`=' . to_sql(guid());
        $value = DB::result($sql);
        if (!$value) {
            $value = l('it_will_be_visible_only_to_you');
        }

        $html->setvar('field_description', toAttrL('it_will_be_visible_only_to_you'));

        $html->setvar('value_no_format', $value);
        $html->setvar('value', nl2br($value));
        if ($html->varExists('value_input')) {
            $html->setvar('value_input', heEmoji($value));
        }
        $html->setvar('type', 'textarea');
        $html->setvar('private_note_user_id', $this->userId);
        $this->parseField($html, $name, $data, false, false, true, $this->block['private_note']);
        $this->cleanBlocks($html, $this->block['private_note']);
        $this->countShow++;
    }

    public function parseGroup($html, $name, $data, $isGroup = false, $parse = true)
    {

        if (!self::isActive($name)) {
            return;
        }

        $isParse = false;
        $desc = array();
        $appearance = $this->gFieldsGroup[2];
        if (!empty($appearance)) {
            foreach ($appearance as $name => $field) {
                $id = $this->getUser($name);
                if (!empty($id)) {
                    $sql = 'SELECT `title`
                              FROM ' . to_sql($field['table'], 'Plain') .
                    ' WHERE `id` = ' . to_sql($id, 'Number');
                    $desc[] = lSetVars('profile_appearance', array('field' => l($field['title']), 'value' => l(DB::result($sql))));
                }
            }
        }
        if (!empty($desc)) {
            $type = $this->block[$data['type']];
            $block = ($isGroup) ? $type . '_' . $data['group'] : $type;
            $title = l($data['title']);
            $html->setvar('field', $title);
            if (!guid() && Common::isOptionActive('hide_profile_data_for_guests_urban')) {
                $desc = '';
            } else {
                $desc = implode(l('profile_appearance_delimiter'), $desc);
                if (l('profile_personal_group_fields_allow_ucfirst') == 'Y') {
                    $desc = mb_ucfirst(mb_strtolower($desc, 'UTF-8'), 'UTF-8');
                }
            }
            $html->setvar('value', $desc);
            $html->parse($block, $parse);
            $isParse = true;
        }
        return $isParse;
    }

    public function parseModule($html, $name, $numModule, $block, $isGroup = false, $setTitle = false, $parse = true, $first = 0, $last = 1)
    {

        if (!self::isActive($name)) {
            return;
        }

        $j = false;
        $desc = '';
        $appearance = $this->gFieldsGroup[2];
        if (!empty($appearance)) {
            if ($first != 1) {
                $html->parse('field_item_group_decor_start', false);
            }
            foreach ($appearance as $name => $data) {
                if ($data['type'] != 'int') {
                    unset($appearance[$name]);
                }
            }
            $countOption = count($appearance);
            foreach ($appearance as $name => $data) {
                if ($first != 1 && $j) {
                    $html->clean('field_item_group_decor_start');
                }
                $j = true;
                $this->parseInt($html, $name, $data, $isGroup, $setTitle, $parse);
                $this->cleanBlocks($html, $data['type']);
                if ($last) {
                    if ($countOption == 1) {
                        $html->parse('field_item_group_decor_end', false);
                    } else {
                        $html->clean('field_item_group_decor_end');
                    }
                }
                $html->parse($block, true);
                $countOption--;
            }
            if ($last) {
                $html->clean('field_item_group_decor_end');
            }
        }
    }

    public function parseDate($html, $year = '', $month = '', $day = '', $first = '', $last = '', $default = '', $formatMonth = 'F')
    {
        if (User::isDisabledBirthday()) {
            return false;
        }
        $this->parseDay($html, $year, $month, $day);
        $this->parseMonth($html, $month, $formatMonth);
        $this->parseYear($html, $first, $last, $default);

        if ($html->blockExists('field_birthday_bl')) {
            $html->parse('field_birthday_bl', false);
        }
    }

    public function parseMonth($html, $month = '', $format = 'F')
    {
        if (empty($month)) {
            $date = $this->getDate();
            $month = (int) $date[1];
        }
        $html->setvar($this->block['month'], h_options(Common::plListMonths($format), get_param($this->param['month'], $month)));
    }

    public function parseDay($html, $year = '', $month = '', $day = '')
    {
        if (empty($day) && empty($month) && empty($year)) {
            $date = $this->getDate();
            $day = (int) $date[2];
            $month = (int) $date[1];
            $year = (int) $date[0];

        }
        $countday = date("t", strtotime($year . '-' . $month));

        $html->setvar($this->block['day'], n_options(1, $countday, get_param($this->param['day'], $day)));
    }

    public function parseYear($html, $first = '', $last = '', $default = '')
    {
        if (empty($first) && empty($last) && empty($default)) {
            $date = $this->getDate();
            $first = date('Y') - Common::getOption('users_age_max');
            $last = date('Y') - Common::getOption('users_age');
            $default = (int) $date[0];
        }

        $html->setvar($this->block['year'], n_options($first, $last, get_param($this->param['year'], $default)));
    }

    public function getDate()
    {
        return explode('-', $this->gUser['birth']);
    }

    public function parseLocation($html, $isJoin = false)
    {
        if ($isJoin) {
            $geoInfo = getDemoCapitalCountry(); //IP::geoInfoCity();
            $country = get_session('j_country');
            $state = get_param('state', $geoInfo['state_id']);
            $city = get_param("city", $geoInfo['city_id']);
        } else {
            $country = get_param('country', $this->getUser('country_id', ''));
            $state = get_param('state', $this->getUser('state_id', ''));
            $city = get_param('city', $this->getUser('city_id', ''));
        }

        //$country = ($country == '' ? '' : $country);

        if ($html->varExists('country_options')) {
            $html->setvar('country_options', Common::listCountries($country));
        }

        if ($html->varExists('state_options')) {
            $html->setvar('state_options', Common::listStates($country, $state));
        }

        if ($html->varExists('city_options')) {
            $state = DB::result('SELECT `state_id` FROM `geo_state` WHERE `state_id` = ' . to_sql($state, 'Number'));
            if ($state != '' && $state != 0) {
                $html->setvar('city_options', Common::listCities($state, $city));
            }
        }
    }

    public function parseCountry($html)
    {
        $country = get_param('country', $this->gUser['country_id']);
        $html->setvar('country_options', Common::listCountries($country));
    }

    public function parseAge($html, $byUser = true)
    {
        $pAgeFromValue = $userAge = Common::getOption('users_age');
        $pAgeToValue = $userAgeMax = Common::getOption('users_age_max');

        if ($byUser) {
            $pAgeFromValue = $this->getUser('p_age_from', $userAge);
            $pAgeToValue = $this->getUser('p_age_to', $userAgeMax);
        }

        $pAgeFrom = get_param('p_age_from', $pAgeFromValue);
        $pAgeTo = get_param('p_age_to', $pAgeToValue);
        if ($pAgeTo == 0) {
            $pAgeTo = $userAgeMax;
        }

        $html->setvar('p_age_from_options', n_options($userAge, $userAgeMax, $pAgeFrom));
        $html->setvar('p_age_to_options', n_options($userAge, $userAgeMax, $pAgeTo));
        $block = $this->block['age'];
        if ($this->set == 'urban' && $this->typeParse == 'admin') {
            $block = $this->block['age'] . '_urban';
        }
        /* Looking For */
        if (!self::isActive('orientation')) {
            $html->parse('looking_fields_style', false);
        }
        /* Looking For */
        $html->parse($block, false);
        $html->parse($block . '_js', false);
        $this->countShowConst++;
    }

    public function parsePlan($html)
    {
        $set = Common::getOption('set', 'template_options');
        $sql = 'SELECT * FROM `payment_plan` WHERE `set` = ' . to_sql($set) . ' AND `type` = ' . to_sql('payment', 'Text') .  ' ORDER BY `item` ASC';
        $rows = DB::rows($sql);

        $plans['0'] = "None";

        foreach ($rows as $key => $row) {
            $plans[$row['item']] = $row['item_name'];
        }

        $html->setvar('membership_plan_options', h_options($plans, $this->gUser['type']));
        $html->parse('membership_plan', false);
    }

    public function parseTrialPlan($html)
    {
        $set = Common::getOption('set', 'template_options');
        $sql = 'SELECT * FROM `payment_plan` WHERE `set` = ' . to_sql($set) . ' AND `type` = ' . to_sql('payment', 'Text') .  ' ORDER BY `item` ASC';
        $rows = DB::rows($sql);

        $plans['0'] = "None";

        foreach ($rows as $key => $row) {
            $plans[$row['item']] = $row['item_name'];
        }

        $html->setvar('trial_plan_options', h_options($plans, $this->gUser['trial_plan_type']));
        $html->parse('trial_plan', false);
    } 

    public function parseType($html)
    {
        $set = Common::getOption('set', 'template_options');
        $sql = 'SELECT * FROM `config` WHERE `module` = '.to_sql('trial', 'Text') . ' AND `option` = '.to_sql('item_name', 'Text').' LIMIT 1';
        $row = DB::row($sql);

        if($row['value']) {
            $trial_label = $row['value'];
        }

        $types = array(
            'trial' => $trial_label ? $trial_label: l('trial'),
            'free' => l('free'),
            'payment' => l('Payment'));

        $html->setvar('type_options', h_options($types, $this->gUser['site_access_type']));
        $html->parse('type_plan', false);
    }

    public function parseRadius($html)
    {
        $unit = l(Common::getOption('unit_distance'));
        /*$radius = array('0'   => l('Exact location only'),
        '25'  => '25 '  . $unit,
        '50'  => '50 '  . $unit,
        '75'  => '75 '  . $unit,
        '100' => '100 ' . $unit,
        '125' => '125 ' . $unit,
        '150' => '150 ' . $unit,
        '175' => '175 ' . $unit,
        '200' => '200 ' . $unit,
        );*/
        $radius = array('0' => l('Exact location only'));
        $max = intval(Common::getOption('max_search_distance'));
        if ($max > 0) {
            $interval = round($max / 8);
            $min = $interval;
            for ($i = 0; $i < 7; $i++) {
                $radius[$min] = $min . " " . $unit;
                $min += $interval;
            }
        }
        $radius[$max] = $max . " " . $unit;

        $html->setvar('radius_options', h_options($radius, get_param('radius', 0)));
    }

    public static function parseStatus($html)
    {
        $status = array();

        if (Common::isOptionActive('online_tab_enabled')) {
            $status['online'] = l('online');
        }
        if (Common::isOptionActive('new_tab_enabled')) {
            $status['new'] = l('new');
        }
        if (Common::isOptionActive('birthdays_tab_enabled ')) {
            $status['birthday'] = l('birthday');
        }
        if (!empty($status)) {
            $status['all'] = l('all');
            $html->setvar('status_options', h_options($status, get_param('status', 'all')));
            $html->parse('tab_enabled', false);
            $html->parse('search_field_user_status_enabled', false);
        }
    }

    public function parseOrientationForAction($html)
    {
        $isParseBlock = false;
        if (Common::isEdgeLmsMode()) {
            $orientation = DB::result("SELECT title FROM const_lms_user_types WHERE id = " . to_sql($this->gUser['lms_user_type']));
            $html->setvar('field_orientation_value', l($orientation));
            $html->parse('field_orientation_edit_off', false);
            $isParseBlock = true;
        } elseif (self::isActiveOrientation()) {
            $html->setvar('orientation_options', DB::db_options("SELECT id, title FROM const_orientation", $this->gUser['orientation']));
            $html->parse('field_orientation_edit_on', false);
            $isParseBlock = true;
        } elseif (self::isActive('orientation')) {
            $orientation = DB::result("SELECT title FROM const_orientation WHERE id = " . to_sql($this->gUser['orientation']));
            $html->setvar('field_orientation_value', l($orientation));
            $html->parse('field_orientation_edit_off', false);
            $isParseBlock = true;
        }
        if ($isParseBlock && $html->blockExists('field_orientation_edit_bl')) {
            $html->parse('field_orientation_edit_bl', false);
        }
    }

    public function setValueTexts()
    {
        if (!isset($this->gUser['user_id']) || $this->gUser['user_id'] != guid() || !Common::isOptionActive('texts_approval')) {
            return;
        }
        DB::query("SELECT * FROM `texts` WHERE `user_id` = " . to_sql(guid(), 'Number') . " ORDER BY id DESC LIMIT 1");
        if ($rows = DB::fetch_row()) {
            foreach ($rows as $name => $data) {
                if ($name != 'id' && $name != 'user_id' && !is_int($name) && !empty($data)) {
                    $this->gUser[$name] = $data;
                    $this->textsApproval[$name] = $data;
                }
            }
        }
    }

    public function preparedSqlUpdate($type = 'profile', $isCleanEmpty = false, $nsc_couple_id = 0)
    {

        $sql = '';
        $this->isChangesFields = false;
        $isFilter = Common::isOptionActive('filter');
        $isApproval = Common::isOptionActive('texts_approval');
        foreach ($this->gFields as $name => $data) {
            if ($this->isAllowed($data, $type, $name)) {
                if (($data['type'] == 'text') || ($data['type'] == 'textarea')) {
                    // tags are forbidden in profile text fields
                    $tmp = trim(Common::filterProfileText(get_param($name, $this->gUser[$name])));
                    if ($isFilter) {
                        $_POST[$name] = $tmp;
                    }
                    if ($tmp != $this->gUser[$name]) {
                        if ($isApproval && !$this->isChangesFields
                            && empty($tmp) && !isset($this->textsApproval[$name])) {
                            $this->isChangesFields = false;
                        } else {
                            $this->isChangesFields = true;
                        }
                    }
                    $isSql = true;
                    if ($isCleanEmpty && !empty($tmp)) {
                        $isSql = false;
                    }
                    if ($isSql) {
                        $sql .= ', ' . $name . '=' . to_sql($tmp, 'Text');
                    }
                } elseif ($data['type'] == 'int') {

                    $sql .= ', ' . $name . "='" . ((int) get_param($name, $this->gUser[$name]) . "'");
                } elseif ($data['type'] == 'checkbox') {
                    // Сразу обновляется поле - таблица отдельная
                    if ($nsc_couple_id == 0) {
                        $this->updateCustomCheckbox($name);
                    } else {
                        $this->updateCustomCheckboxNscCouple($name);
                    }

                } elseif ($data['type'] == 'checks') {
                    if (get_param($name . '_nm', '') == -1) {
                        $sql .= ', ' . $name . '=0';

                    } else {
                        $sql .= ', ' . $name . '=' . to_sql(get_checks_param($name), 'Number');
                    }
                } elseif ($data['type'] == 'from') {
                    $nameTo = substr($name, 0, strlen($name) - 4) . 'to';
                    $paramFrom = get_param($name, '');
                    $paramTo = get_param($nameTo, '');
                    if ($paramFrom != -1) {
                        //if ($paramFrom > $paramTo
                        //|| $paramFrom < 1) {
                        //$this->message .= l($data['title']) . ' ' .  l('incorrect') . '<br>';
                        //} else {
                        $sql .= ', ' . $name . '=' . to_sql($paramFrom, 'Number');
                        //$sql .= ', ' . $nameTo . '=' . to_sql($paramTo, 'Number');
                        // }
                    } else {
                        $sql .= ', ' . $name . "=" . 0;
                    }
                    $sql .= ', ' . $nameTo . '=' . to_sql($paramTo, 'Number');
                }
            }
        }
        return ($sql != '') ? substr($sql, 2) : $sql;
    }

    public function preparedSqlPartnerAge()
    {
        $sql = 'p_age_from=' . to_sql(get_param('p_age_from'), 'Number') . ', ';
        $sql .= 'p_age_to=' . to_sql(get_param('p_age_to'), 'Number') . ' ';
        return $sql;
    }

    public function updatePartner($user_id)
    {
        if (Common::isOptionActive('partner_settings')) {
            $sql = $this->preparedSqlUpdate('partner');
            if ($sql != '' && $this->message == '') {
                DB::execute("UPDATE `userpartner` SET " . $sql . " WHERE `user_id` = " . to_sql($user_id, 'Number'));
            }
            if ($this->message == '') {
                $sql = $this->preparedSqlPartnerAge();
                DB::execute("UPDATE `user` SET " . $sql . " WHERE `user_id` = " . to_sql($user_id, 'Number'));
            }
        }
    }

    public function parseLookingForSocialTemplate(&$html, $filters)
    {
        global $g;

        $optiontTmplName = Common::getOption('name', 'template_options');
        $uid = guid();

        $filtersInfo = array();
        if ($filters) {
            $filtersInfo = json_decode($filters, true);
        }

        if (self::isActive('age_range')) {
            $this->parseAge($html);
        }

        if (self::isActive('orientation') && !Common::isEdgeLmsMode()) {
            $numColumn = 1;
            $this->parseChecks($html, 'p_orientation', $g['user_var']['orientation'], $numColumn, 0, false, 'p_orientation', true, null, 'filter');
        }

        $isActiveLocation = Common::isOptionActive('location_enabled', "{$optiontTmplName}_join_page_settings");
        if ($isActiveLocation) {
            $filtersInfo = User::checkLocationFilter($filtersInfo);

            $country = get_param('country', isset($filtersInfo['country']['value']) ? $filtersInfo['country']['value'] : 0);
            $state = get_param('state', isset($filtersInfo['state']['value']) ? $filtersInfo['state']['value'] : 0);
            $city = get_param('city', isset($filtersInfo['city']['value']) ? $filtersInfo['city']['value'] : 0);

            $peopleNearby = get_param_int('people_nearby');
            if ($peopleNearby) {
                //$country = 0;//people_nearby
            }

            $html->setvar('country_options', Common::listCountries($country, false, false, false, false));

            if ($country) {
                $html->setvar('state_options', Common::listStates($country, $state));
            }
            if ($state) {
                $html->setvar('city_options', Common::listCities($state, $city));
            }

            $html->parse('filter_location_show', false);
        } else {
            $geoDefaultInfo = IP::geoInfoCityDefault();
            $locationInfo = array(
                'country_id' => $geoDefaultInfo['country_id'],
                'state_id' => $geoDefaultInfo['state_id'],
                'city_id' => $geoDefaultInfo['city_id'],
            );
            $html->assign('filter', $locationInfo);
            $html->parse('filter_location_default', false);
        }

        if (!isset($filtersInfo['status']) || !$filtersInfo['status']['value']) {
            $filtersInfo['status']['value'] = 'all';
        }

        $html->parse('module_search_status_' . $filtersInfo['status']['value'], false);
    }

    public function parseLookingFor(&$html, $alwaysParseOrientations = true)
    {
        global $g;
        global $p;
        global $g_user;

        $fieldsWithHidden = true;

        //When suddenly the old template to disable
        $isAge = false;
        if (self::isActive('age_range')) {
            $this->parseAge($html);
            $isAge = true;
            $fieldsWithHidden = false;
        } else {
            $html->clean('age_range');
        }

        $isAmHereTo = false;
        $uid = guid();
        $numColumn = 1;
        if ($this->name == 'urban_mobile' && $p == 'search.php') {
            $numColumn = 3;
        }
        if (self::isActive('i_am_here_to')) {
            $option = ($uid) ? array($this->getUser('i_am_here_to')) : array();
            if ($this->name == 'urban_mobile' && $p != 'search.php') {
                $this->gFields['i_am_here_to']['type'] = 'int';
                $this->parseInt($html, 'i_am_here_to', $this->gFields['i_am_here_to'], false, false, true, '', 'i_am_here_to');
            } else {
                $this->parseRadio($html, 'i_am_here_to', $this->gFields['i_am_here_to'], $numColumn, false, 'i_am_here_to', $option);
            }
            $isAmHereTo = true;
        } else {
            $html->parse('field_i_am_here_to_off');
        }

        if ($this->name == 'impact' || $this->name == 'impact_mobile' || $this->name == 'edge') {
            /*if (self::isActiveSexuality()) {
            $values = array();
            if ($uid) {
            $values = User::getValueFilterField('p_sexuality', array());
            }
            $values = get_checks_array($values);
            $this->parseChecks($html, 'sexuality', $g['user_var']['sexuality'], 2, 0, false, 'sexuality', true, $values);
            }*/
            if (User::isSearchNearMe()) {
                $html->parse('field_near_me_checked');
            }
            if ($this->name !== 'edge' || Common::isOptionActive('location_enabled', 'edge_join_page_settings')) {
                $html->parse('field_near_me');
            }
        }

        $isOrientation = false;
        if (self::isActive('orientation')) {
            if (Common::isOptionActive('your_orientation')) {
                if (guid()) {
                    $html->parse('p_orientations_hide');
                }
            } else {
                $isOrientation = true;
                $fieldsWithHidden = false;
            }
            if ($alwaysParseOrientations || !Common::isOptionActive('your_orientation')) {
                $this->parseChecks($html, 'p_orientation', $g['user_var']['orientation'], $numColumn, 0, false, 'p_orientation', true);
            }

            if (Common::isOptionActive('your_orientation')) {
                if (guser('p_orientation')) {
                    $name = User::getTitleOrientationLookingFor(array('p_orientation' => guser('p_orientation')));
                    $html->setvar('name', $name);
                    $html->parse('looking_your_orientation');
                    $isOrientation = true;
                }

                if (!guid()) {
                    $isOrientation = true;
                    $fieldsWithHidden = false;
                }
            }
        }

        if (($isAge || $isOrientation) && $html->blockexists('orientations_and_age')) {
            $html->parse('orientations_and_age');
        } elseif (!$isAmHereTo && $html->blockexists('slider_center')) {
            $html->parse('slider_center');
        }

        if ($fieldsWithHidden) {
            $html->setvar('fields_with_hidden', 'fields_with_hidden');
        }

        $block = 'i_am_here_to_and_orientations';
        if ($html->blockExists($block) && ($isAmHereTo || $isOrientation)) {
            $html->parse($block);
        }
        //}
        return $isAge || $isAmHereTo || $isOrientation;
    }

    public static function isLookingFor()
    {
        return self::isActive('age_range') || self::isActive('i_am_here_to') || self::isActive('looking');
    }

    public static function updateLookingFor($user_id)
    {
        $data = array();

        $optionTmplName = Common::getOption('name', 'template_options');
        $isImpactSet = ($optionTmplName == 'impact' || $optionTmplName == 'impact_mobile' || $optionTmplName == 'edge');
        $titleAges = '';
        if (self::isActive('age_range')) {
            $data['p_age_from'] = get_param('p_age_from');
            $data['p_age_to'] = get_param('p_age_to');
            $titleAges = lSetVars('for_loking_for_ages_impact', array('age_from' => $data['p_age_from'], 'age_to' => $data['p_age_to']));
            //$sql = self::preparedSqlPartnerAge();
            //DB::execute("UPDATE `user` SET " . $sql . " WHERE `user_id` = " . to_sql($user_id, 'Number'));
        }

        if (self::isActive('i_am_here_to')) {
            $data['i_am_here_to'] = get_param('i_am_here_to');
            //$set = '`i_am_here_to`= ' . to_sql($data['i_am_here_to'], 'Number');
            //DB::execute('UPDATE `user` SET ' . $set . ' WHERE `user_id` = ' . to_sql($user_id, 'Number'));
        }
        //if (self::isActive('looking')) {
        /*
        $gender = array(1 => 'B', 2 => 'M', 3 => 'F');
        $data['default_online_view'] = $gender[get_param('gender', 1)];
        $sql = array('default_online_view' => $data['default_online_view']);
        DB::update('user', $sql, 'user_id = ' . to_sql($user_id, 'Number'));
         */
        //}

        if ($isImpactSet) {
            /*$titleSexuality = '';
            if (self::isActiveSexuality()) {
            $pSexuality = get_param_array('p_sexuality');
            $userSearchFilters = array('p_sexuality' =>
            array(
            'field' => 'sexuality',
            'value' => $pSexuality,
            )
            );
            User::updateParamsFilterUserInfoForData('user_search_filters', $userSearchFilters);
            $titleSexuality = User::getTitleFromSetOfValues($pSexuality);
            }*/
            $isSearchNearMe = intval(get_param('search_near_me') == 'on');
            $nearMeRadius = Common::getOption('near_me_radius', 'template_options');
            $radius = intval(Common::getOption('default_search_distance'));
            $userinfo = User::getInfoFull($user_id);
            $filter = User::getParamsFilter('user_search_filters', $userinfo['user_search_filters']);
            $filter = json_decode($filter, true);

            if (isset($filter['radius'])) {
                $radius = intval($filter['radius']['value']);
            }
            $setRadius = 0;
            $titleNearMe = '';
            if ($isSearchNearMe) {
                if ($radius > $nearMeRadius) {
                    $setRadius = $nearMeRadius;
                    $titleNearMe = l('near_me');
                }
            } elseif ($radius <= $nearMeRadius) {
                $titleNearMe = l('distance_does_not_matter');
                $setRadius = Common::getOption('max_search_distance');
            }
            if ($setRadius) {
                $_GET['radius'] = $setRadius;
                User::updateFilterAll(null, array('radius'));
                Cache::delete('search_near_me_' . $user_id);
                Cache::delete('search_near_me_title_' . $user_id);
            }
        }
        if (User::noYourOrientationSearch()) {
            $data['p_orientation'] = get_checks_param('p_orientation');
        }
        if ($data) {
            User::update($data, $user_id);
        }

        if ($isImpactSet) {
            $titleOrientation = User::getTitleOrientationLookingFor(null, null, $user_id);
            if ($titleOrientation) {
                $titleOrientation = ucfirst($titleOrientation);
            }
            $responseData = array('near_me' => $titleNearMe,
                'orientation' => $titleOrientation,
                'ages' => $titleAges);
        } else {
            $responseData = User::getLookingFor($user_id);
        }
        return $responseData;
    }

    public function updateInfo($user_id, $type = 'profile', $status = false, $nsc_couple_id = 0) //nnsscc-diamond-20200323

    {
        //&&&
        if ($this->set == 'urban') {
            unset($this->gFields['star_sign']);
        }
        if ($nsc_couple_id != 0) {
            $this->gUserNscCoupleId = $nsc_couple_id;
        }
        $sql = $this->preparedSqlUpdate($type, false, $nsc_couple_id);
        if ($sql != '' && $this->message == '') {
            DB::execute("UPDATE userinfo SET " . $sql . " WHERE user_id=" . to_sql($user_id, "Number"));
        }

    }

    public function updateCheckbox($user_id, $type = 'profile', $status = false)
    {
        $sql = $this->preparedSqlUpdate($type);
        if (($sql != '') && ($this->message == '')) {
            DB::execute($sql);
        }

    }

    public function updateCustomCheckbox($field)
    {
        $options = get_param_array($field);
        $id = $this->gFields[$field]['id'];

        $sql = 'DELETE FROM `users_checkbox`
                 WHERE `user_id` = ' . to_sql($this->userId, 'Number') .
        ' AND `field` = ' . to_sql($id, 'Number');
        DB::execute($sql);

        $prepareSql = '';
        foreach ($options as $value) {
            if (!empty($value)) {
                $prepareSql .= ', (' . to_sql($id, 'Number') . ', ' . to_sql($this->userId, 'Number') . ', ' . to_sql($value, 'Number') . ')';
            }
        }
        //echo $prepareSql;
        if ($prepareSql != '') {
            $prepareSql = substr($prepareSql, 1);
            $prepareSql = "INSERT INTO `users_checkbox` (`field`, `user_id`, `value`) VALUES {$prepareSql};";
            DB::execute($prepareSql);
        }
    }
    public function updateCustomCheckboxNscCouple($field) //nnsscc-diamond-20200323

    {
        $options = get_param_array($field);

        $id = $this->gFields[$field]['id'];

        $sql = 'DELETE FROM `users_checkbox`
                 WHERE `user_id` = ' . to_sql($this->gUserNscCoupleId, 'Number') .
        ' AND `field` = ' . to_sql($id, 'Number');
        DB::execute($sql);

        $prepareSql = '';
        foreach ($options as $value) {
            if (!empty($value)) {
                $prepareSql .= ', (' . to_sql($id, 'Number') . ', ' . to_sql($this->gUserNscCoupleId, 'Number') . ', ' . to_sql($value, 'Number') . ')';
            }
        }
        //echo $prepareSql;
        if ($prepareSql != '') {
            $prepareSql = substr($prepareSql, 1);
            $prepareSql = "INSERT INTO `users_checkbox` (`field`, `user_id`, `value`) VALUES {$prepareSql};";
            DB::execute($prepareSql);
        }
    }
    public function updateTexts($user_id, $type = 'profile')
    {
        global $g_user;

        g_user_full();

        $sqlTo = $this->preparedSqlUpdate($type);
        if ($sqlTo != '' && $this->isChangesFields) {
            $id = DB::result('SELECT id FROM texts WHERE user_id = ' . to_sql($user_id, 'Number') . ' ORDER BY id DESC LIMIT 1');
            if ($id) {
                DB::execute('UPDATE texts SET ' . $sqlTo . ' WHERE user_id = ' . to_sql($user_id, 'Number') . ' AND id = ' . to_sql($id, 'Number'));
                DB::execute('UPDATE userinfo SET ' . $sqlTo . ' WHERE user_id = ' . to_sql($user_id, 'Number'));
            } else {
                DB::execute('INSERT INTO texts SET user_id = ' . to_sql($user_id, 'Number') . ', ' . $sqlTo);
            }

            $isDeleteTexts = true;
            $fields = DB::row('SELECT * FROM texts WHERE user_id = ' . to_sql($user_id, 'Number') . ' ORDER BY id DESC LIMIT 1');
            foreach ($fields as $name => $value) {
                if ($name != 'id' && $name != 'user_id' && !is_int($name)
                    && (!empty($value) && $g_user[$name] != $value)) {
                    $isDeleteTexts = false;
                    break;
                }
            }
            if ($isDeleteTexts) {
                DB::delete('texts', 'user_id = ' . to_sql($user_id, 'Number') . ' AND id = ' . to_sql($id, 'Number'));
            }
        }
        $sqlToEmpty = $this->preparedSqlUpdate($type, true);
        if ($sqlToEmpty != '') {
            DB::execute('UPDATE userinfo SET ' . $sqlToEmpty . ' WHERE user_id = ' . to_sql($user_id, 'Number'));
        }
    }
    public function updateTextsNscCouple($user_id, $type = 'profile')
    {
        global $g_user;

        g_user_full();

        $sqlTo = $this->preparedSqlUpdate($type, false, $user_id);

        if ($sqlTo != '' && $this->isChangesFields) {
            $id = DB::result('SELECT id FROM texts WHERE user_id = ' . to_sql($user_id, 'Number') . ' ORDER BY id DESC LIMIT 1');
            if ($id) {
                DB::execute('UPDATE texts SET ' . $sqlTo . ' WHERE user_id = ' . to_sql($user_id, 'Number') . ' AND id = ' . to_sql($id, 'Number'));
                DB::execute('UPDATE userinfo SET ' . $sqlTo . ' WHERE user_id = ' . to_sql($user_id, 'Number'));
            } else {
                DB::execute('INSERT INTO texts SET user_id = ' . to_sql($user_id, 'Number') . ', ' . $sqlTo);
            }

            $isDeleteTexts = true;
            $fields = DB::row('SELECT * FROM texts WHERE user_id = ' . to_sql($user_id, 'Number') . ' ORDER BY id DESC LIMIT 1');
            foreach ($fields as $name => $value) {
                if ($name != 'id' && $name != 'user_id' && !is_int($name)
                    && (!empty($value) && $g_user[$name] != $value)) {
                    $isDeleteTexts = false;
                    break;
                }
            }
            if ($isDeleteTexts) {
                DB::delete('texts', 'user_id = ' . to_sql($user_id, 'Number') . ' AND id = ' . to_sql($id, 'Number'));
            }
        }
        $sqlToEmpty = $this->preparedSqlUpdate($type, true, $user_id);
        if ($sqlToEmpty != '') {
            DB::execute('UPDATE userinfo SET ' . $sqlToEmpty . ' WHERE user_id = ' . to_sql($user_id, 'Number'));
        }
    }
    public function updateTextsApproval($type = 'profile')
    {
        global $g_user;
        if (Common::isOptionActive('texts_approval')) {
            $this->updateTexts(guid(), $type);
            if ($this->isChangesFields && Common::isEnabledAutoMail('approve_text_admin')) {
                $vars = array(
                    'name' => User::getInfoBasic($g_user['user_id'], 'name'),
                );
                Common::sendAutomail(Common::getOption('administration', 'lang_value'), Common::getOption('info_mail', 'main'), 'approve_text_admin', $vars);
            }
        } else {
            $this->updateInfo(guid(), $type);
        }
    }
    public function updateTextsApprovalNscCouple($type = 'profile', $nsc_id = 0) //nnsscc-diamond

    {
        global $g_user;
        if (Common::isOptionActive('texts_approval')) {
            $this->updateTextsNscCouple($nsc_id, $type);
            if ($this->isChangesFields && Common::isEnabledAutoMail('approve_text_admin')) {
                $vars = array(
                    'name' => User::getInfoBasic($nsc_id, 'name'),
                );
                Common::sendAutomail(Common::getOption('administration', 'lang_value'), Common::getOption('info_mail', 'main'), 'approve_text_admin', $vars);
            }
        } else {
            $this->updateInfo($nsc_id, $type, false, $nsc_id);
        }
    }

    public function verification($type = 'profile')
    {
        $this->init();
        foreach ($this->gFields as $name => $data) {
            if ($this->isAllowed($data, $type, $name)) {
                if (($data['type'] == 'text') || ($data['type'] == 'textarea')) {
                    if ($type == 'admin') {
                        continue;
                    }
                    if (mb_strlen(get_param($name, $this->getParam($name)), 'UTF-8') > $data['length']) {
                        $this->message .= sprintf(l('fields_error'), $data['title']) . '<br>';
                    }
                } elseif ($data['type'] == 'int') {

                } elseif ($data['type'] == 'checks') {

                } elseif ($data['type'] == 'from') {
                    $nameTo = mb_substr($name, 0, mb_strlen($name) - 4) . 'to';
                    $paramFrom = get_param($name, 0);
                    $paramTo = get_param($nameTo, 0);
                    if ($paramFrom > $paramTo) {
                        $this->message .= l($data['title']) . ' ' . l('incorrect') . '<br>';
                    }
                }
                // elseif ($data['type'] == 'const' && Common::isOptionActive('partner_settings')) {
                //     if ($name == 'age_range') {
                //         if (get_param('p_age_from', '') > get_param('p_age_to', '')
                //             || get_param('p_age_from', '') < Common::getOption('users_age')
                //             || get_param("p_age_to", "") > Common::getOption('users_age_max')) {
                //             $this->message = l('partner_age_incorect') . '<br>' . $this->message;
                //         }
                //     } else {

                //     }
                // }
            }
        }
    }

    public function isVisualGroup($type, $group = '')
    {
        $fields = 'countShowGroup' . $type;
        $count = $this->$fields;
        if ($group == '') {
            return (array_sum($count) > 0) ? true : false;
        } else {
            return ($count[$group] > 0) ? true : false;
        }
    }

    public function parseSections($html, $type, $section)
    {
        for ($i = 1; $i <= $this->profileSectionsCount; $i++) {
            if ($this->isVisualGroup($type, $i)) {
                $html->parse($section . '_' . $i, false);
            }
        }
    }

    public function parseTypeSections($html, $name, $data)
    {
        $type = $data['type'];
        switch ($type) {
            case 'text':
            case 'textarea':
                $parseMethod = 'parseText';
                break;

            case 'from':
                $parseMethod = 'parseFromTo';
                break;

            default:
                if ($name == "hobbies") {
                    $parseMethod = 'parseHobbies'; //nnsscc-diamond-20200312
                } else {
                    $parseMethod = 'parse' . $type;
                }
                break;
        }
        $this->$parseMethod($html, $name, $data);
    }

    public function parseFieldsAll(&$html, $type = 'profile', $init = true, $nsc_id = 0)
    {
        global $g_user;

        $this->currentTypeParse = $type;
        if ($init) {
            $this->init();
        }
        $isPartner = Common::isOptionActive('partner_settings');
        $isPersonal = Common::isOptionActive('personal_settings');
        $i = 0;
        foreach ($this->gFieldsGroup as $num => $group) {
            $countFieldGroup = count($group);
            foreach ($group as $name => $data) {
                $countFieldGroup--;
                if ($this->isAllowed($data, $type, $name)) {
                    $i++;
                    switch ($type) {
                        case 'admin':
                            $isParse = false;
                            if (($data['type'] == 'text') || ($data['type'] == 'textarea')) {
                                $block = '';
                                if ($isPersonal && $this->set == 'urban'
                                    && $data['group'] == 1 && $data['type'] == 'text') {
                                    $block = 'personal_text_1';
                                    $this->countShowGroupInt[1]++;
                                    $isParse = true;
                                    $data['type'] = 'personal_text';
                                }
                                $this->parseText($html, $name, $data, false, false, $block);
                            } elseif ($data['type'] == 'int' && $isPersonal) {
                                if ($data['group'] == 1) {
                                    $isParse = $this->parseInt($html, $name, $data, true, false, false);
                                } else {
                                    // if($this->set == 'urban' && $data['group'] == 3) {
                                    //     break;
                                    // }
                                    $this->parseInt($html, $name, $data);
                                }
                            } elseif ($data['type'] == 'const') {
                                $this->parseConst($html, $name, $data);
                            } elseif ($data['type'] == 'checkbox' && $isPersonal) {
                                $isParse = $this->parseCheckboxCustom($html, $name, $data, true, false);
                                $this->countShowGroupInt[1]++;
                            } else {
                                if ($isPartner && $data['type'] != 'int') {
                                    if ($data['type'] == 'from') {
                                        $this->parseFromTo($html, $name, $data);
                                    } else {
                                        $this->parseChecks($html, $name, $data);
                                    }
                                }
                            }
                            if ($isParse) {
                                $this->cleanBlocks($html, $data['type'], $data['group']);
                                $html->parse('personal_fields_item_1', true);
                            }
                            break;

                        case 'search_advanced':
                            if ($isPersonal) {

                                $fieldGroup = true;
                                if ($this->parseSearchModule) {
                                    $fieldGroup = false;
                                }
                                if ($this->parseSearchModule && $data['type'] == 'checkbox') {
                                    $this->parseCheckboxCustom($html, $name, $data, false, false);
                                } elseif ($data['type'] == 'from') {
                                    $this->parseFromTo($html, $name, $data, $fieldGroup);
                                } else {
                                    $this->parseChecks($html, $name, $data, 2, 0, $fieldGroup, '', false);
                                }
                            } else {
                                break 2;
                            }
                            break;

                        case 'profile_html':
                            if (($data['type'] == 'text') || ($data['type'] == 'textarea')) {
                                if ($this->gUser[$name] != '') {
                                    $this->parseText($html, $name, $data, false);
                                }
                            } elseif ($data['type'] == 'checkbox' && $isPersonal) { //start-nnsscc-diamond-20200214
                                //$isParse = $this->parseCheckboxCustom($html, $name, $data, true, false);
                                $isParse = $this->parseCheckboxGeneral($html, $name, $data, true);
                                //$this->countShowGroupInt[1]++;//end-nnsscc-diamond-20200214
                            } else {
                                if ($isPersonal) {
                                    $this->parseInt($html, $name, $data, true, true);
                                }
                            }
                            break;

                        case 'profile_html_urban':
                            if ($num == 2 || ($num == 3 && $data['type'] != 'private_note')) {
                                break;
                            }
                            $isParseGeneralBlock = true;
                            if ($data['type'] == 'text' || $data['type'] == 'textarea') {
                                if (!($this->name === 'edge' && $this->gUser[$name] == '') && ($this->gUser[$name] != ''
                                    || ($this->name != 'urban_mobile' && $num != 1
                                        && ($this->userId == guid() /*|| ($this->userId != guid() && $name == 'interested_in')*/)))) {
                                    $this->parseText($html, $name, $data, true, false, 'basic', true);
                                } else {
                                    $this->cleanBlocks($html, 'basic'); //???
                                    $isParseGeneralBlock = false;
                                }
                            } elseif ($data['type'] == 'location') {
                                $this->parseChoiceLocation($html, $name, $data);
                            } elseif ($data['type'] == 'map') {
                                $this->parseMap($html, $name, $data);
                            } elseif ($data['type'] == 'private_note') {
                                $this->parsePrivateNote($html, $name, $data);
                            } elseif ($data['type'] == 'interests') {
                                $this->parseInterests($html, $name, $data);
                            } elseif ($data['type'] == 'group') {
                                $isParseGeneralBlock = $this->parseGroup($html, $name, $data, true);
                            } elseif ($data['type'] == 'checkbox') {
                                $isParseGeneralBlock = $this->parseCheckboxGeneral($html, $name, $data, true);
                            } else if ($data['type'] != 'map' && $data['type'] != 'private_note') {
                                $isParseGeneralBlock = $this->parseInt($html, $name, $data, true, true, false, '');
                            }
                            if ($num != 1 && $isParseGeneralBlock) {
                                $html->parse($this->generalBlock['module'], true);
                            } else {
                                if ($data['type'] != 'int' && $isParseGeneralBlock) {
                                    $this->countShowGroupInt[1]++;
                                }
                                if ($isParseGeneralBlock) {
                                    $html->parse('personal_item', true);
                                }
                            }
                            break;

                        case 'personal_edit_urban':
                        case 'personal_edit_urban_mobile':
                            $isParseAppearance = false;
                            if ($num == 1) {
                                if ($data['type'] == 'group') {
                                    $isParseAppearance = true;
                                } elseif ($data['type'] == 'checkbox') {
                                    $isParseAppearance = !$this->parseCheckboxCustom($html, $name, $data, false, false);
                                } elseif ($data['type'] == 'text') {
                                    $this->parseText($html, $name, $data, true, false);
                                } else {
                                    $isParseAppearance = !$this->parseInt($html, $name, $data, false, true, false);
                                }
                                if (!$isParseAppearance) {
                                    $this->cleanBlocks($html, $data['type']);
                                    $html->parse('field_item', true);
                                }
                            } elseif ($type == 'personal_edit_urban_mobile') {
                                if ($data['type'] == 'textarea' || $data['type'] == 'text') {
                                    $block = '';
                                    if ($data['type'] == 'text') {
                                        $block = 'basic_text';
                                    } else {
                                        $html->clean('basic_text');
                                    }
                                    $this->parseText($html, $name, $data, true, false, $block);
                                }
                            }
                            if ($isParseAppearance) {
                                $this->parseModule($html, $name, 2, 'field_item', false, true, false, $i, $countFieldGroup);
                            }
                            break;

                        case 'interests_category_urban':
                            $this->parseRadio($html, $name, $data, 2, false, 'interests_category');
                            break;

                        case 'edit_looking_for_urban':
                            //$this->parseRadio($html, $name, $data, 1, false, $name, array($this->getUser('i_am_here_to')));
                            break;

                        default:
                            if ($nsc_id == 0) {

                                $this->gUserNscCoupleId = 0;
                                $this->parseTypeSections($html, $name, $data);

                            } else {
                                //$this->parseTypeSections($html, $name, $data);
                                $this->gUserNscCoupleId = $nsc_id;
                                if ($data['type'] == 'int') {
                                    $this->parseIntNscCouple($html, $name, $data, $nsc_id);
                                } else if ($name == "hobbies") {
                                    $this->parseHobbies($html, $name, $data, 4, true, '', null, $nsc_id); //nnsscc-diamond-20200323
                                } else if ($data['type'] == 'text' || $data['type'] == 'textarea') {
                                    // echo $name;
                                    $this->parseTypeSections($html, $name, $data);
                                } else if ($name == 'relation') {
                                    $this->parseTypeSections($html, $name, $data);

                                }
                            }
                            break;
                    }
                }
            }
            // End parsing fields group
            switch ($type) {
                case 'profile_html_urban':
                    if ($num == 1) {
                        $isParse = false;
                        if ($this->userId == guid()) { // && $this->countShowGroupInt[1]
                            $html->parse('personal_edit', false);
                            $isParse = true;
                        }
                        $blockGeneral = 'personal';
                        if ($this->name == 'impact' || $this->name == 'edge') {
                            $blockGeneral = 'personal_impact';
                        }
                        if ($this->countShowGroupInt[1] || ($this->name !== 'edge' && $isParse)) {
                            $html->parse($blockGeneral, false);
                        }
                        $this->cleanBlocks($html, $blockGeneral);
                        $html->parse($this->generalBlock['module'], true);
                    }
                    break;
                case 'personal_edit_urban':
                case 'personal_edit_urban_mobile':
                    $html->parse('field', false);
                    break;
            }
        }

        switch ($type) {
            case 'birthday':
                $this->parseDate($html);
                break;

            case 'profile':
                $this->parseDate($html);
                $this->parseLocation($html);

                if ($this->countShow > 0) {

                    if ($nsc_id == 0) {
                        if (Common::isOptionActive('texts_approval')) {
                            global $g_user;
                            $count = DB::count('texts', ' `user_id` = ' . $g_user['user_id'] . ' ');
                            if ($count) {
                                $html->parse('essays1_text');
                                $html->parse('essays2_text');
                            }
                        }
                        $html->parse('basic_fields_text', false);
                        $html->parse('title_basic_fields_text', false);
                    } else {
                        if (Common::isOptionActive('texts_approval')) {
                            global $g_user;
                            $count = DB::count('texts', ' `user_id` = ' . $nsc_id . ' ');
                            if ($count) {
                                $html->parse('essays1_text');
                                $html->parse('essays2_text');
                            }
                        }
                        $html->parse('basic_fields_text', false);
                        $html->parse('title_basic_fields_text', false);
                    }
                }
                break;

            case 'personal':
                if ($nsc_id == 0) {
                    if ($this->isVisualGroup('Int')) {
                        $this->parseSections($html, 'Int', 'personal_fields');
                        $html->parse('personal_fields', false);
                        $html->parse('btn_bottom', false);
                    }
                } else {
                    if ($this->isVisualGroup('Int')) {
                        $this->parseSections($html, 'Int', 'personal_fields');
                        $html->parse('personal_fields', false);
                        $html->parse('btn_bottom', false);
                    }
                }
                break;

            case 'profile_html_urban':
                if ($this->name == 'urban_mobile') {
                    $city = $this->getUser('city', '');
                    if ($city) {
                        $html->setvar('city', l($city));
                        $html->parse('location', false);
                    }
                }

                break;

            case 'personal_edit_urban':
                if ($this->isVisualGroup('Int') || $this->countShowGroupСheckbox[1]) {
                    $html->parse('btn_save', false);
                    $html->parse('personal_fields', false);
                }
                break;

            case 'personal_edit_urban_mobile':
                $isParse = $this->parseLookingFor($html);
                if ($isParse) {
                    $html->parse('looking_fields');
                }
                $this->parseLocation($html);
                if ($this->isVisualGroup('Int')
                    || $this->countShowGroupСheckbox[1]
                    || $this->countShow) {
                    $html->parse('personal_fields', false);
                }
                break;

            case 'partner':
                if (isset($this->gFields['age_range']) && $this->gFields['age_range']['status'] == 'active') {
                    $this->parseAge($html);
                }
                if (($this->countShowGroupChecks[1] + $this->countShow) > 0) {
                    $html->parse('btn_group_1', false);
                }
                if ($this->isVisualGroup('Checks')) {
                    $this->parseSections($html, 'Checks', 'partner_fields');
                    $html->parse('btn_bottom', false);
                }
                break;

            case 'join':
                //start-nnsscc_diamond
                //$html->parse('new_single', false);//nnsscc_diamond
                //$orientation = $this->gUser['orientation'];
                $orientation = get_session("j_orientation");
                $html->setvar("join_orientation", $orientation);
                /*
                if($orientation==5){
                $html->parse('new_couple_join', true);
                }else{
                $html->parse('new_single_join', true);
                }
                 */

                $couple_profile = get_session("j_couple_profile");
                $html->setvar("couple_profile", $couple_profile);
                if ($couple_profile == 1) {
                    $html->parse('new_couple_profile', true);
                    $html->setvar("couple_profile_title", "Step 3. Partner Information");
                } else {
                    $html->parse('new_single_join', true);
                }
                //end-nnsscc_diamond
                if (self::isActive('age_range')) {
                    $this->parseAge($html);
                    $html->parse('partner_on');
                    $html->parse('partner_on2');
                }
                if (self::isActive('relation')) {
                    $this->parseConst($html, 'relation', $this->gFields['relation']);
                }
                if ($this->countShowConst > 0) {
                    $html->parse('relation_age_fields', false);
                }
                if ($this->countShow > 0) {
                    if (Common::isOptionActive('texts_approval')) {
                        $html->parse('essays1_text');
                        $html->parse('essays2_text');
                    }
                    $html->parse('basic_fields_text', false);
                    //$html->parse('title_basic_fields_text', false);
                }
                if ($this->isVisualGroup('Int')) {
                    $this->parseSections($html, 'Int', 'join_fields');
                    $html->parse('partner_fields', false);
                }
                if (Common::isOptionActive('texts_approval')) {
                    $html->parse('essays1_text');
                    $html->parse('essays2_text');
                }
                $this->parseLocation($html, true);

                break;

            case 'admin':

                $this->parseDate($html);
                $this->parseLocation($html);
                $this->parseType($html);
                $this->parsePlan($html);
                $this->parseTrialPlan($html);

                // Rade 2023-10-02 add start
                // if ($this->set != 'urban') {
                //     $this->parseType($html);
                // }
                // Rade 2023-10-02 add end
                $isParseModernPersonal = false;
                $range = false;
                if ($this->set == 'urban') {
                    if ($this->name != 'edge_OFF') {
                        $isParse = $this->parseLookingFor($html);
                        if ($isParse) {
                            $html->parse('edit_looking');
                            $isParseModernPersonal = true;
                        }
                    }
                } else {
                    if (User::noYourOrientationSearch()) {
                        $this->parseChecks($html, 'p_orientation', $this->gFields['orientation'], 2, 0, false, 'p_orientation', true);
                        $isParseModernPersonal = true;
                        $html->parse('edit_looking');
                    }
                    if (self::isActive('age_range')) {
                        $this->parseAge($html);
                        $range = true;
                    }
                    if ($isPartner && ($this->isVisualGroup('Checks') || $range)) {
                        if ($this->isVisualGroup('Checks')) {
                            $this->parseSections($html, 'Checks', 'partner_fields');
                        }
                        $html->parse('partner_enabled');
                    }
                }

                if ($this->setAdminAboutProfile) {
                    $isParseModernPersonal = true;
                    $html->parse('personal_fields_text', false);
                }

                if ($isPersonal) {
                    if ($isParseModernPersonal) {
                        $html->parse('personal_text_and_looking');
                    }
                    if ($this->isVisualGroup('Int')) {
                        $this->parseSections($html, 'Int', 'personal_fields');
                    }
                    $html->parse('personal_enabled');
                }
                break;

            case 'search_advanced':

                $this->parseSearchModule = false;

                if ($this->isVisualGroup('Checks')) {
                    $html->parse('fields_criteria', false);
                }
                if (self::isActive('relation') && isset($this->gFields['income'])) {
                    // $this->parseChecks($html, 'p_relation', $this->gFields['relation'], 2, 0, false, 'p_relation', false); // Rade 2023-10=02
                    $this->parseChecks($html, 'p_relation', $this->gFields['income'], 3, 0, false, 'p_relation', false); //nnsscc-diamond-20200309
                }

                if (User::noYourOrientationSearch()) {
                    $this->parseChecks($html, 'p_orientation', $this->gFields['orientation'], 1, 0, false, 'p_orientation', false); //nnsscc-diamond-20200309
                    // $this->parseChecks($html, 'p_orientation', $this->gFields['orientation'], 2, 0, false, 'p_orientation', false); // Rade 2023-10=02
                }

                if (UserFields::isActive('orientation') && Common::isOptionActive('your_orientation')) {
                    if (isset($g_user['p_orientation']) && $g_user['p_orientation']) {
                        CSearch::parseChecks($html, "orientation", "SELECT id, title FROM const_orientation ORDER BY id ASC", $g_user['p_orientation'], 2, 0, true, 'orientation_search');
                    }
                }

                $sql = 'SELECT `register`
                          FROM `user`
                         WHERE `register` != "0000-00-00 00:00:00"
                      ORDER BY `register` ASC LIMIT 1';
                $registrationYearFirst = substr(DB::result($sql), 0, 4);
                $registrationYearLast = date('Y');
                if ($registrationYearFirst == 0) {
                    $registrationYearFirst = $registrationYearLast;
                }
                $this->parseDate($html, $registrationYearLast, 1, 1, $registrationYearFirst, $registrationYearLast);

                $this->block['day'] = 'day_options_to';
                $this->block['month'] = 'month_options_to';
                $this->block['year'] = 'year_options_to';
                $this->param['day'] = 'day_to';
                $this->param['month'] = 'month_to';
                $this->param['year'] = 'year_to';
                $this->parseDate($html, $registrationYearLast, date('n'), date('j'), $registrationYearFirst, $registrationYearLast, $registrationYearLast);

                $this->parseAge($html, false);
                $this->parseLocation($html);
                $this->parseStatus($html);
                $this->parseRadius($html);

                break;

            case 'profile_html':
                $html->setvar('user_name', $this->gUser['name']);
                if ($this->countShow > 0) {
                    $html->parse('fields_essay', false);
                }

                $this->parseScales($html, "relation", $this->gUser);

                $about = false;
                if ($this->countShowGroupInt[1] > 0) {
                    $html->parse('fields_about_1', false);
                    $about = true;
                }
                if ($this->countShowGroupInt[2] > 0) {
                    $html->parse('fields_about_2', false);
                    $about = true;
                }
                if ($about) {
                    $partner_t = DB::row("SELECT * FROM var_nickname WHERE id='" . $this->gUser['partner_type'] . "'");

                    $html->setvar('partner_type', " " . $partner_t['title']);
                    $html->parse('fields_about', false);
                }
                if ($this->countShowGroupInt[3] > 0) {
                    $html->parse('fields_other_3', false);
                    $html->parse('fields_other', false);
                    $html->parse('border_oryx', false);
                }
                //$html->parse('users_list_item', false);
                break;

        }
    }

//parse slides of what are you looking for and I am looking for ...
public function parseScales($html, $name, $flip_user)
{

    $mask = ""; //value from scale sliders fields of user table.
    $mask = $flip_user[$name];
    $maskArray = [];

    $parts = explode(", ", $mask);

    foreach ($parts as $part) {
        if (strpos($part, ":") !== false) {
            list($key, $value) = explode(":", $part);
            $maskArray[(int) $key] = (int) $value;
        }
    }

    // if($flip_user['user_id'] == "11") {
    //     var_dump($maskArray); die();
    // }

    // get from const_relation, const_orientation table
    $csql = "SELECT * FROM const_" . $name . ";";

    if ($name == "p_orientation") {
        $csql = "SELECT * FROM const_orientation;";
    }

    $c_rows = DB::rows($csql);

    // get levels from looking_level table
    $l_sql = "SELECT id, title FROM looking_level;";
    $l_levels = DB::rows($l_sql);

    $scale_back_colors = ['#e91720', '#f79122', '#f8eb10', '#92d14f', '#3ab34a'];

    // var_dump($c_rows); die();
    foreach ($c_rows as $key => $c_row) {

        $scale_field_name = $c_row['title'];
        $html->setvar('scale_field_name', $scale_field_name);

        $scale_value = 3;
        foreach ($l_levels as $level_id => $level_title) {
            if (isset($maskArray[$c_row['id']]) && $level_title['id'] == $maskArray[$c_row['id']]) {
                $scale_value = (int) $level_title['id'];
                break;
            }
        }

        $item_index = 0;
        foreach ($l_levels as $level_id => $level_title) {
            $back_item_style = "background-color: rgb(28, 27, 27)";

            $html->setvar('back_item_style', $back_item_style);
            $html->parse('scale_back_item', true);
            $item_index++;
        }

        $scale_length = $scale_value * 21 - 3;
        $scale_var_style = "background-color: " . $scale_back_colors[$scale_value - 1] . "; width: " . $scale_length . "px;";

        if ($scale_value == 5) {
            $scale_length = $scale_value * 21 - 5;
            $scale_var_style = "background-color: " . $scale_back_colors[$scale_value - 1] . "; width: " . $scale_length . "px;";

            $scale_var_style = $scale_var_style . "border-top-right-radius: 7px; border-bottom-right-radius: 7px;";
        }

        $html->setvar('scale_bar_back_style', "background-color: " . $scale_back_colors[$scale_value - 1] . "; color: black;");

        $html->setvar('scale_bar_style', $scale_var_style);

        $scale_level_name = l("set_" . $l_levels[$scale_value - 1]['title']);
        $html->setvar('scale_level_name', $scale_level_name);
        $partner_t = DB::row("SELECT * FROM var_nickname WHERE id='" . $flip_user['partner_type'] . "'");

        $html->setvar('scales_relation', "<font size='+1' style='color: #8123a0;'>" . $partner_t['title'] . "</font> " . l('partner_relation'));

        $html->parse('scale_slider', true);
        $html->clean('scale_back_item');

    }
    $html->parse('what_looking_sliders', true);

    $html->clean('scale_slider');

} 
   public static function updateAllPosition($fields)
    {

        $fieldsAllPosition = Config::getOptionsAll('user_var', false, false, true);

        if (is_array($fields)) {
            foreach ($fields as $group => $fieldsValue) {
                foreach ($fieldsValue as $position => $option) {
                    if ($option != 'no' && $option != '') {
                        self::updateOnePosition($option, $group, $position, $fieldsAllPosition);
                    }

                }
            }
        }

    }

    public static function updateOnePosition($option, $group, $position, $fieldsAllPosition)
    {

        global $g;

        //if ($group > 0) {

        if ($g['user_var'][$option]['group'] != $group) {
            self::updateRelatedItem($option, 'group', $group);
        }
        if ($fieldsAllPosition[$option]['position'] != $position) {
            self::updateRelatedPosition($option, $position);
        }
        //} elseif ($fieldsAllPosition[$option]['position'] != $position) {
        //self::updateRelatedPosition($option, $position);
        //}

    }

    public static function updateStatus($option)
    {
        global $g;

        $status = ($g['user_var'][$option]['status'] == 'active') ? 'inactive' : 'active';

        self::updateRelatedItem($option, 'status', $status);
    }

    public static function updateNumberValue($option)
    {
        global $g;

        $number = DB::count($g['user_var'][$option]['table']);
        self::updateRelatedItem($option, 'number_values', $number);
    }

    public static function updateRelatedItem($option, $index, $value, $isUpdateBasicField = false)
    {
        global $g;

        $fieldChecks = 'p_' . $option;
        $fieldFrom = 'p_' . $option . '_from';
        $fieldTo = 'p_' . $option . '_to';

        $type = self::isFieldsSelectionChecks($option);

        $g['user_var'][$option][$index] = $value;
        if ($index == 'group') {
            $g['user_var'][$option]['group'] = $value;
        }

        Config::update('user_var', $option, serialize($g['user_var'][$option]), false);
        if ($isUpdateBasicField) {
            return;
        }
        if ($type == 'selection') {
            $g['user_var'][$fieldChecks][$index] = $value;
            if ($index == 'group') {
                $g['user_var'][$fieldChecks]['group'] = $value;
            }

            Config::update('user_var', $fieldChecks, serialize($g['user_var'][$fieldChecks]), false);

        } elseif ($type == 'checks') {

            $g['user_var'][$fieldFrom][$index] = $value;
            if ($index == 'group') {
                $g['user_var'][$fieldFrom]['group'] = $value;
            }

            Config::update('user_var', $fieldFrom, serialize($g['user_var'][$fieldFrom]), false);
            $g['user_var'][$fieldTo][$index] = $value;
            if ($index == 'group') {
                $g['user_var'][$fieldTo]['group'] = $value;
            }

            Config::update('user_var', $fieldTo, serialize($g['user_var'][$fieldTo]), false);
        }
    }

    public static function updateRelatedPosition($option, $position)
    {

        $type = self::isFieldsSelectionChecks($option);

        $fieldChecks = 'p_' . $option;
        $fieldFrom = 'p_' . $option . '_from';
        $fieldTo = 'p_' . $option . '_to';

        Config::updatePosition('user_var', $option, $position);

        if ($type == 'selection') {
            Config::updatePosition('user_var', $fieldChecks, $position);
        } elseif ($type == 'checks') {
            Config::updatePosition('user_var', $fieldFrom, $position);
            Config::updatePosition('user_var', $fieldTo, $position);
        }
    }

    public static function getNameField($field, $type, $from = true)
    {
        if ($type == 'selection') {
            return 'p_' . $field;
        } elseif ($type == 'checks') {
            return $from ? 'p_' . $field . '_from' : 'p_' . $field . '_to';
        }
        return '';
    }

    public static function isFieldsSelectionChecks($option)
    {
        global $g;

        $fieldChecks = 'p_' . $option;
        $fieldFrom = 'p_' . $option . '_from';
        $fieldTo = 'p_' . $option . '_to';

        if (isset($g['user_var'][$option]['table'])) {
            $table = $g['user_var'][$option]['table'];
        } else {
            $table = 'no';
        }

        if (isset($g['user_var'][$fieldChecks])
            && (isset($g['user_var'][$fieldChecks]['table']) && $g['user_var'][$fieldChecks]['table'] == $table)) {
            return 'selection';
        } elseif (isset($g['user_var'][$fieldFrom])
            && isset($g['user_var'][$fieldTo])
            && ($g['user_var'][$fieldFrom]['table'] == $table)
            && ($g['user_var'][$fieldTo]['table'] == $table)) {
            return 'checks';
        } else {
            return false;
        }
    }           

    public static function isActive($option, $param = 'status', $neededValue = 'active')
    {

        global $g;

        $active = false;


        if (self::getField($option, $param) == $neededValue
            && $g['user_var'][$option]['number_values'] > 0) {
            $active = true;
        }

        return $active;
    }

    public static function isActiveOrientation()
    {
        return self::isActive('orientation') && Common::isOptionActive('allow_users_to_change_profile_type');
    }

    /* Impact */
    public static function isActiveSexuality()
    {
        global $g;
        if (!isset($g['user_var']['sexuality'])) {
            return false;
        }
        if (self::isFieldsSelectionChecks('sexuality') == 'selection') {
            return self::isActive('sexuality');
        }
        return false;
    }

    public static function isActiveAboutMe()
    {
        global $g;
        if (isset($g['user_var']['about_me'])
            && ($g['user_var']['about_me']['type'] == 'textarea'
                || $g['user_var']['about_me']['type'] == 'text')) {
            return $g['user_var']['about_me']['status'] == 'active';
        }
        return false;
    }

    public static function parseAboutMe($html)
    {
        global $g;
        if (self::isActiveAboutMe()) {
            $data = $g['user_var']['about_me'];
            if ($data['type'] == 'text') {
                $html->setvar('max_length', $data['length']);
                $html->parse('about_me_length', false);
            }
            $html->parse('about_me', false);
        }
    }

    public static function getAnswerData($option)
    {
        $data = self::getField($option, 'answer');
        if ($data != null) {
            $data = json_decode($data, true);
        }
        if (!$data) {
            $data = array('yes' => array(), 'no' => array());
        }
        return $data;
    }

    public static function checkFiledQuestion($field, $checkIsActive = true, $chart = false)
    {
        global $g;
        if (!isset($g['user_var'][$field]) || (!self::isActive($field) && $checkIsActive)) {
            return false;
        }
        $data = $g['user_var'][$field];
        $notAllowFields = false;
        if (!$chart) {
            $notAllowFields = in_array($data['table'], array('var_star_sign', 'var_language'));
        }
        if (!isset($data['table']) || !$data['table']
            || $notAllowFields
            || ($data['table'] == 'var_sexuality' && UserFields::isFieldsSelectionChecks('sexuality') == 'selection')
            || $data['type'] == 'const') {
            return false;
        }
        $type = '';
        $allowFields = array('radio', 'checkbox');
        if ($chart) {
            $allowFields = array('checkbox');
        }
        if (in_array($data['type'], $allowFields)) {
            $type = $data['type'];
        } else {
            $type = self::isFieldsSelectionChecks($field);
            if (!in_array($type, array('selection', 'checks'))) {
                $type = '';
            }
        }
        if ($type) {
            $data['name'] = $field;
            $data['type_field'] = $type;
            return $data;
        } else {
            return false;
        }
    }
    /* Impact */

    public static function removeUnavailableField($option = 'fields_not_available')
    {
        global $g;

        $availableField = Common::getOption($option, 'template_options');
        if ($option === 'fields_not_available' || $option === 'fields_not_available_admin') {
            if (Common::isEdgeLmsMode()) {
                $availableField[] = 347;
            } else {
                $availableField[] = 171;
            }
        }
        $tmplSet = Common::getTmplSet();

        if (isset($g['user_var']) && is_array($availableField)) {
            foreach ($g['user_var'] as $key => $field) {
                if (in_array($field['id'], $availableField) || ($tmplSet === 'old' && $field['type'] === 'checkbox')) {
                    unset($g['user_var'][$key]);
                }
            }
        }
    }

    public static function getField($option, $param)
    {
        global $g;

        $result = null;
        if (isset($g['user_var'][$option][$param])) {
            $result = $g['user_var'][$option][$param];
        }

        return $result;
    }

    public function setUser($uid)
    {
        $this->userId = $uid;
    }

    public static function translation($field, $value, $prf = null)
    {
        static $tmpl = null;
        if (!$tmpl) {
            $tmpl = Common::getOption('name', 'template_options');
        }
        $keys = array($tmpl . '_' . $prf . '_' . $field . '_' . $value, $prf . '_' . $field . '_' . $value, $tmpl . '_' . $field . '_' . $value, $field . '_' . $value, $value);
        return lCascade($value, $keys);
    }

    public static function checksToParamsArray($table, $value, $dbIndex = DB_MAX_INDEX)
    {
        $result = array();
        if ($value) {
            $sql = 'SELECT `id` FROM ' . to_sql($table, 'Plain');
            if (DB::query($sql, $dbIndex)) {
                while ($row = DB::fetch_row($dbIndex)) {
                    if ($value & (1 << ($row['id'] - 1))) {
                        $result[] = $row['id'];
                    }
                }
            }
        }

        return $result;
    }

    public static function parseFieldsStyle(&$html, $fields = array('i_am_here_to', 'interests'))
    {
        $typeUploadIco = self::getTypeUploadIcoField();
        foreach ($fields as $field) {
            $table = UserFields::getField($field, 'table');
            if (!$table) {
                continue;
            }
            $rows = DB::rows('SELECT * FROM ' . $table);
            foreach ($typeUploadIco[$field] as $type) {
                $block = 'field_' . $field . '_' . $type . '_style';
                if ($html->blockexists($block)) {
                    if ($field == 'interests') {
                        $html->setvar('name', self::getArrayNameIcoField($field, 1, $type));
                        $html->parse($block, true);
                    }
                    foreach ($rows as $row) {
                        $html->setvar('name', self::getArrayNameIcoField($field, $row['id'], $type));
                        $html->parse($block, true);
                    }
                }
            }
        }
    }

    public static function getTypeUploadIcoField($isMobile = false)
    {
        // $isMobile -> Admin
        $optionTmplName = Common::getOption('name', 'template_options');

        $type = array('i_am_here_to' => array('normal', 'hover'),
            'interests' => array('list', 'normal', 'shared', 'search'));

        if ($optionTmplName == 'urban_mobile' || $isMobile) {
            $type = array('i_am_here_to' => array('normal'),
                'interests' => array('list', 'normal', 'shared', 'category', 'category_selected'),
                'orientation' => array('normal'));
        }

        return $type;
    }

    public static function getArrayNameIcoField($field, $id, $type = '', $isMobile = false)
    {
        // $isMobile -> Admin
        $optionTmplName = Common::getOption('name', 'template_options');
        $prf = array('i_am_here_to' => array('normal' => '', 'hover' => 'hover'),
            'interests' => array('list' => '', 'normal' => 'profile', 'shared' => 'common', 'search' => 'search'));

        if ($optionTmplName == 'urban_mobile' || $isMobile) {
            $prf = array('i_am_here_to' => array('normal' => ''),
                'interests' => array('list' => '', 'normal' => 'profile', 'shared' => 'common', 'category' => 'category', 'category_selected' => 'category_selected'),
                'orientation' => array('normal' => ''));
        }
        $icoName = array();
        if ($type != '') {

            if (isset($prf[$field])) {
                $icoName = self::getNameIcoField($field, $id, $prf[$field][$type]); //$icoName[$type];
            }

        } else {
            foreach ($prf[$field] as $key => $item) {
                $icoName[$key] = self::getNameIcoField($field, $id, $item);
            }
        }

        return $icoName;
    }

    public static function getNameIcoField($field, $id, $prf)
    {
        if ($prf != '') {
            $prf = '_' . $prf;
        }
        return 'field_' . $field . '_' . $id . $prf;
    }

    public static function isColumnInTable($table, $nameColumn)
    {
        $result = false;
        $colums = DB::rows("SHOW COLUMNS FROM {$table}", DB_MAX_INDEX);
        foreach ($colums as $item) {
            if ($item[0] == $nameColumn) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    public static function getSearchOrientation($orientationId)
    {
        $orientation = User::getOrientationInfo($orientationId);
        return User::getOrientationInfo($orientation['search']);
    }

    public function parseMobileAdvancedFilter(&$html)
    {
        global $g;
        global $g_user;

        if (Common::getOption('set', 'template_options') == 'urban') {
            User::setGetParamsFilter('user_search_filters_mobile');
        }

        $display = get_param('display');
        if ($html->varExists('display_params')) {
            $html->setvar('display_params', $display);
        }

        $status = array('' => l('search_show_all'),
            'new' => l('search_show_new'),
            'online' => l('search_show_online'),
        );
        $html->setvar('status_options', h_options($status, get_param('status')));

        //if (!Common::isOptionActive('no_profiles_without_photos_search')) {
        if (!$display) {
            if ($html->blockExists('with_photo_checked') && get_param('with_photo', 1)) {
                $html->parse('with_photo_checked', false);
            }
            $html->parse('with_photo_partl', false);
            $html->parse('with_photo_resizel', false);
            $html->parse('with_photo_param', false);
        }
        //}

        $peopleNearby = get_param_int('people_nearby');
        $html->setvar('people_nearby', $peopleNearby);

        $country = get_param('country', $g_user['country_id']);
        $countrySelect = $country;
        $state = get_param('state', $g_user['state_id']);
        $city = get_param('city', $g_user['city_id']);
        if ($peopleNearby) {
            $countrySelect = 'people_nearby';

        }
        $html->setvar('country_options', Common::listCountries($countrySelect, false, false, false, false));
        $html->setvar('state_options', Common::listStates($country, $state));

        $state = DB::result("SELECT state_id, state_title FROM geo_state WHERE country_id=" . to_sql($country, "Number") . " AND state_id=" . to_sql($state, "Number") . ";");

        if ($state != '' and $state != 0) {
            //$city_options = DB::db_options("SELECT city_id, city_title FROM geo_city WHERE state_id=" . to_sql($state, "Number") . ";", get_param("city", $g_user['city_id']));
            $html->setvar('city_options', Common::listCities($state, $city));
        }

        $this->selectionFields['search_advanced'] = array('from', 'checks', 'group');
        $this->parseLookingFor($html);
        if ($html->varExists('radius')) {
            $html->setvar('radius', get_param('radius', 0));
            $maxSearchDistance = intval(Common::getOption('max_search_distance'));
            $html->setvar('radius_max', $maxSearchDistance + 1);
            $isMaxFilterDistanceCountry = intval(Common::getOption('max_filter_distance') == 'max_search_country');
            $html->setvar('max_filter_distance_country', $isMaxFilterDistanceCountry);
            $vars = array('unit' => l(Common::getOption('unit_distance')));
            $html->setvar('slider_within', lSetVars('slider_within', $vars));
        }

        $html->setvar('keyword', get_param('keyword'));
        $html->setvar('search_name', get_param('search_name'));
    }

}

class CUsersProfileHtml extends UserFields
{

    public function parseBlock(&$html)
    {

        $this->parseFieldsAll($html, $this->m_name);

        parent::parseBlock($html);
    }
}
