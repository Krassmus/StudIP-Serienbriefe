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
require_once dirname(__file__)."/classes/SerienbriefeTemplate.php";

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
        $tab = new Navigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "write/overview"));
        Navigation::addItem("/start/serienbriefe", $tab);
        $tab = new Navigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "write/overview"));
        Navigation::addItem("/serienbriefe", $tab);
        $tab = new AutoNavigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "write/overview"));
        Navigation::addItem("/serienbriefe/overview", $tab);
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

    protected function getDisplayName() {
        return _("Serienbriefe");
    }

}


