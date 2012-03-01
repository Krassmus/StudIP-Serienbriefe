<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once dirname(__file__)."/classes/CSVImportProcessor.php";
require_once dirname(__file__)."/classes/serienbriefe_templates.php";

$handle = opendir(dirname(__file__)."/plugins");
while (($file = readdir($handle)) !== false) {
    if (strpos($file, ".observer.php") !== false) {
        include_once dirname(__file__)."/plugins/".$file;
    }
}

/**
 * Description of Serienbriefe
 *
 * @author Rasmus
 */
class Serienbriefe extends StudIPPlugin implements SystemPlugin {
    
    protected $datafields = array();
    
    public function __construct() {
        parent::__construct();
        $tab = new Navigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "index"));
        Navigation::addItem("/start/serienbriefe", $tab);
        $tab = new Navigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "index"));
        Navigation::addItem("/serienbriefe", $tab);
        $tab = new AutoNavigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "index"));
        Navigation::addItem("/serienbriefe/show", $tab);
    }
    
    public function index_action() {
        PageLayout::addHeadElement("script", array('src' => $this->getPluginURL()."/assets/serienbriefe.js"), "");
        if ($_SESSION['SERIENBRIEF_CSV']) {
            if (!is_string($_SESSION['SERIENBRIEF_CSV'])) {
                $_SESSION['SERIENBRIEF_CSV'] = "";
            }
            $GLOBALS['SERIENBRIEF_CSV'] = unserialize(gzuncompress($_SESSION['SERIENBRIEF_CSV']));
        }
        if (Request::get("reset")) {
            $GLOBALS['SERIENBRIEF_CSV'] = array('header' => array(), 'content' => array());
        }
        $db = DBManager::get();
        $msg = array();
        $this->datafields = $db->query("SELECT * FROM datafields WHERE object_type = 'user' ")->fetchAll(PDO::FETCH_ASSOC);
        if (Request::submitted("abschicken") && Request::get("message_delivery") && Request::get("subject_delivery")) {
            $count = 0;
            $messaging = new messaging();
            $_SESSION['not_delivered_users'] = array();
            if (is_array($GLOBALS['SERIENBRIEF_CSV']['content'])) foreach ($GLOBALS['SERIENBRIEF_CSV']['content'] as $user_data) {
                if ($user_data['user_id'] && (!get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD") || !Request::int('notenbekanntgabe') || $user_data[get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")])) {
                    $text = Request::get("message_delivery");
                    $subject = Request::get("subject_delivery");
                    foreach ($user_data as $key => $value) {
                        $subject = str_replace("{{".$key."}}", $value, $subject);
                        $text = str_replace("{{".$key."}}", $value, $text);
                    }
                    $success = $messaging->insert_message(addslashes($text), get_username($user_data['user_id']), $GLOBALS['user']->id, '', '', '', '', $subject, true);
                    if ($success) {
                        $count++;
                    } else {
                        $msg[] = array("error", sprintf("Nachricht konnte nicht an %s versendet werden.", $user_data['email']));
                    }
                } else {
                    $_SESSION['not_delivered_users'][] = $user_data;
                }
            }
            if ($count > 0) {
                $msg[] = array("success", sprintf("Nachricht wurde an %s Personen versendet.", $count));
            }
            if (count($_SESSION['not_delivered_users']) > 0) {
                $msg[] = array("info", sprintf("An %s Personen wurde die Nachricht nicht versendet. %sBericht dazu%s.", count($_SESSION['not_delivered_users']), '<a href="'.PluginEngine::getLink($this, array(), 'users_not_delivered_csv').'">', '</a>'));
            }
        }
        if (Request::submitted("speichern") && count($_POST) && Request::get("template_id")) {
            if (Request::get("template_id") && (Request::get("template_id") !== "new")) {
                $new_template = new serienbriefe_templates(Request::get("template_id"));
            } else {
                $new_template = new serienbriefe_templates();
            }
            $new_template['message'] = Request::get("message");
            $new_template['subject'] = Request::get("subject");
            $new_template['title'] = Request::get("title");
            $new_template['notenbekanntgabe'] = Request::int("notenbekanntgabe_template");
            $new_template['user_id'] = $GLOBALS['user']->id;
            $new_template->store();
            $msg[] = array("success", _("Template wurde gespeichert."));
        }
        if (Request::option("delete_template")) {
            $template = new serienbriefe_templates(Request::get("delete_template"));
            $template->delete();
            $msg[] = array("success", _("Template wurde gelöscht."));
        }
        if ($_FILES['csv_file']) {
            $GLOBALS['SERIENBRIEF_CSV'] = array('header' => array(), 'content' => array());
            $content = CSVImportProcessor_serienbriefe::getCSVDataFromFile($_FILES["csv_file"]['tmp_name']);
            @unlink($_FILES["csv_file"]['tmp_name']);
            $GLOBALS['SERIENBRIEF_CSV']['header'] = array_shift($content);
            foreach ($content as $line) {
                $data = new stdClass();
                foreach ($GLOBALS['SERIENBRIEF_CSV']['header'] as $key => $header_name) {
                    if (isset($line[$key])) {
                        $data->$header_name = $line[$key];
                    }
                }
                $data = (array) $this->getUserdata($data);
                $GLOBALS['SERIENBRIEF_CSV']['content'][] = $data;
            }
        }
        
        $template = $this->getTemplate("show.php", "with_infobox");
        $template->set_attribute("plugin", $this);
        $template->set_attribute("datafields", $this->datafields);
        $template->set_attribute('templates', serienbriefe_templates::findBySQL("1=1"));
        $template->set_attribute("msg", $msg);
        echo $template->render();
        if ($GLOBALS['SERIENBRIEF_CSV']) {
            $_SESSION['SERIENBRIEF_CSV'] = gzcompress(serialize($GLOBALS['SERIENBRIEF_CSV']));
        }
    }
    
    public function parse_text_action() {
        $output = array();
        $output['subject'] = studip_utf8encode(formatReady(studip_utf8decode(Request::get("subject"))));
        $output['message'] = studip_utf8encode(formatReady(studip_utf8decode(Request::get("message"))));
        echo json_encode($output);
    }
    
    public function users_not_delivered_csv_action() {
        $output = "";
        $header = array();
        foreach ($_SESSION['not_delivered_users'] as $user_line) {
            if (count($user_line) > count($header)) {
                $header = array_keys($user_line);
            }
        }
        $header[] = "Problem";
        if (is_array($_SESSION['not_delivered_users'])) {
            foreach ($header as $key => $fieldname) {
                if ($key > 0) {
                    $output .= ";";
                }
                $output .= '"'.str_replace('"', '""', $fieldname).'"';
            }
            foreach ($_SESSION['not_delivered_users'] as $user_data) {
                $output .= "\n";
                $number = 0;
                foreach ($header as $field) {
                    if ($number > 0) {
                        $output .= ";";
                    }
                    if ($field !== "Problem") {
                        $output .= '"'.str_replace('"', '""', $user_data[$field]).'"';
                    } else {
                        $problem = "";
                        if (!$user_data['user_id']) {
                            $problem = "Kein gültiger username oder Emailadresse.";
                        } elseif(get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD") && !$user_data[get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")]) {
                            $problem = "Nutzer ist nicht einverstanden mit dem Verschicken von Noten per Mail.";
                        }
                        $output .= '"'.$problem.'"';
                    }
                    $number++;
                }
            }
        }
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=serienbrief_bericht.csv");
        echo $output;
    }
    
    protected function getUserdata($data) {
        $db = DBManager::get();
        if ($data->username) {
            $data->user_id = get_userid($data->username);
        } elseif ($data->email) {
            $data->user_id = $db->query("SELECT user_id FROM auth_user_md5 WHERE Email = ".$db->quote($data['email'])." ")->fetch(PDO::FETCH_COLUMN, 0);
        }
        $user = $db->query(
            "SELECT * " .
            "FROM auth_user_md5 " .
                "INNER JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) " .
            "WHERE auth_user_md5.user_id = ".$db->quote($data->user_id)." " .
        "")->fetch(PDO::FETCH_ASSOC);
        if ($data->user_id && !$data->name) {
            $data->name = get_fullname($data->user_id, "no_title");
            $data->anrede = ($user['geschlecht'] == 2 ? "Frau " : ($user['geschlecht'] == 1 ? "Herr " : "")). get_fullname($data->user_id, "full");
            $data->sehrgeehrte = ($user['geschlecht'] == 2 ? "Sehr geehrte Frau " : ($user['geschlecht'] == 1 ? "Sehr geehrter Herr " : "Sehr geehrte(r) ")). get_fullname($data->user_id, "full");
        }
        if ($data->user_id && !$data->email) {
            $data->email = $user['Email'];
        }
        if ($data->user_id) {
            $df_entries = DataFieldEntry::getDataFieldEntries($data->user_id, "user");
            foreach ($this->datafields as $datafield) {
                if (!$data->{$datafield['name']}) {
                    $data->{$datafield['name']} = $df_entries[$datafield['datafield_id']]->getValue();
                }
            }
        }
        
        //Noch diese ganzen zusätzlichen Datenfelder wie studiengruppe oder studienort, 
        //die Standortspezifisch sein können.
        NotificationCenter::postNotification("serienbriefe_get_user_data", $data);
        
        return $data;
    }
    
    protected function getDisplayName() {
        return _("Serienbriefe");
    }
    
    protected function getTemplate($template_file_name, $layout = "without_infobox") {
        if (!$this->template_factory) {
            $this->template_factory = new Flexi_TemplateFactory(dirname(__file__)."/templates");
        }
        $template = $this->template_factory->open($template_file_name);
        if ($layout) {
            if (!PageLayout::getTitle()) {
                if (method_exists($this, "getDisplayName")) {
                    PageLayout::setTitle($this->getDisplayName());
                } else {
                    PageLayout::setTitle(get_class($this));
                }
            }
            $template->set_layout($GLOBALS['template_factory']->open($layout === "without_infobox" ? 'layouts/base_without_infobox' : 'layouts/base'));
        }
        return $template;
    }
    
}


