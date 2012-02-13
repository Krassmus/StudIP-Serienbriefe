<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once dirname(__file__)."/classes/serienbriefe_templates.php";

$handle = opendir(dirname(__file__)."/plugins");
while (($file = readdir($handle)) !== false) {
    if (strpos($file, ".php") !== false) {
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
        if (Request::get("reset")) {
            $_SESSION['SERIENBRIEF_CSV'] = array('header' => array(), 'content' => array());
        }
        $db = DBManager::get();
        $msg = array();
        $this->datafields = $db->query("SELECT * FROM datafields WHERE object_type = 'user' ")->fetchAll(PDO::FETCH_ASSOC);
        if (Request::submitted("abschicken") && Request::get("message_delivery") && Request::get("subject_delivery")) {
            $count = 0;
            $messaging = new messaging();
            if (is_array($_SESSION['SERIENBRIEF_CSV']['content'])) foreach ($_SESSION['SERIENBRIEF_CSV']['content'] as $user_data) {
                if ($user_data['user_id']) {
                    $text = Request::get("message_delivery");
                    $subject = Request::get("subject_delivery");
                    foreach ($user_data as $key => $value) {
                        $subject = str_replace("{{".$key."}}", $value, $subject);
                        $text = str_replace("{{".$key."}}", $value, $text);
                    }
                    //$success = $messaging->sendingEmail($rec_user_id, $snd_user_id, $message, $subject, $message_id);
                    //StudipMail::sendMessage($user_data['email'], $subject, $text);
                    $success = $messaging->insert_message($text, get_username($user_data['user_id']), $GLOBALS['user']->id, '', '', '', '', $subject, true);
                    if ($success) {
                        $count++;
                    } else {
                        $msg[] = array("error", sprintf("Nachricht konnte nicht an %s versendet werden.", $user_data['email']));
                    }
                }
            }
            if ($count > 0) {
                $msg[] = array("success", sprintf("Nachricht wurde an %s Personen versendet.", $count));
            }
        }
        if (Request::submitted("speichern") && count($_POST) && Request::get("template_id")) {
            $new_template = new serienbriefe_templates(Request::get("template_id") !== "new" ? Request::get("template_id") : null);
            $new_template['message'] = Request::get("message");
            $new_template['subject'] = Request::get("subject");
            $new_template['title'] = Request::get("title");
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
            $_SESSION['SERIENBRIEF_CSV'] = array('header' => array(), 'content' => array());
            $content = CSVImportProcessor::getCSVDataFromFile($_FILES["csv_file"]['tmp_name']);
            $_SESSION['SERIENBRIEF_CSV']['header'] = array_shift($content);
            foreach ($content as $line) {
                $data = new stdClass();
                foreach ($_SESSION['SERIENBRIEF_CSV']['header'] as $key => $header_name) {
                    if (isset($line[$key])) {
                        $data->$header_name = $line[$key];
                    }
                }
                $data = (array) $this->getUserdata($data);
                $_SESSION['SERIENBRIEF_CSV']['content'][] = $data;
            }
        }
        
        $template = $this->getTemplate("show.php", "with_infobox");
        $template->set_attribute("plugin", $this);
        $template->set_attribute("datafields", $this->datafields);
        $template->set_attribute('templates', serienbriefe_templates::findBySQL("1=1"));
        $template->set_attribute("msg", $msg);
        echo $template->render();
    }
    
    protected function getUserdata($data) {
        $db = DBManager::get();
        if ($data->username) {
            $data->user_id = get_userid($data->username);
        } elseif ($data->email) {
            $data->user_id = $db->query("SELECT user_id FROM auth_user_md5 WHERE Email = ".$db->quote($data['email'])." ")->fetch(PDO::FETCH_COLUMN, 0);
        }
        $user = new User($data->user_id);
        if ($data->user_id && !$data->name) {
            $data->name = $user->getFullName("no_title");
            $data->anrede = ($user['geschlecht'] == 2 ? "Frau " : ($user['geschlecht'] == 1 ? "Herr " : "")). $user->getFullName("full");
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


