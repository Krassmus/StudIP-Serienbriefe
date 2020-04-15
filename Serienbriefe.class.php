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
require_once dirname(__file__)."/classes/SerienbriefeFolder.php";

$handle = opendir(dirname(__file__)."/plugins");
while (($file = readdir($handle)) !== false) {
    if (strpos($file, ".observer.php") !== false) {
        include_once dirname(__file__)."/plugins/".$file;
    }
}

class Serienbriefe extends StudIPPlugin implements SystemPlugin {

    protected $datafields = array();

    static public function setUsersForSerienbriefe($user_ids) {
        $csv = array('header' => array("user_id"), 'content' => array());
        foreach ($user_ids as $user_id) {
            $csv['content'][] = array('user_id' => $user_id);
        }
        self::setSerienbriefeData($csv);
    }

    static public function setSerienbriefeData($data)
    {
        $GLOBALS['SERIENBRIEF_CSV'] = $data;
        if ($GLOBALS['SERIENBRIEFE_NO_COMPRESS']) {
            $GLOBALS['user']->cfg->store("SERIENBRIEF_CSV", json_encode($GLOBALS['SERIENBRIEF_CSV']));
        } else {
            $_SESSION['SERIENBRIEF_CSV'] = gzcompress(json_encode($GLOBALS['SERIENBRIEF_CSV']));
        }
    }

    static public function getSerienbriefeData()
    {
        if (!$GLOBALS['SERIENBRIEF_CSV']) {
            if ($GLOBALS['SERIENBRIEFE_NO_COMPRESS']) {
                $content = $GLOBALS['user']->cfg->getValue("SERIENBRIEF_CSV");
            } else {
                $content = $_SESSION['SERIENBRIEF_CSV'] ? gzuncompress($_SESSION['SERIENBRIEF_CSV']) : null;
            }
            if (!$content) {
                return array();
            }
            $GLOBALS['SERIENBRIEF_CSV'] = json_decode($content, true);
        }
        return $GLOBALS['SERIENBRIEF_CSV'];
    }

    static public function resetSerienbriefeData()
    {
        unset($GLOBALS['SERIENBRIEF_CSV']);
        unset($_SESSION['SERIENBRIEF_CSV']);
    }

    public function __construct() {
        parent::__construct();
        /*if (Navigation::hasItem("/start")) {
            $tab = new Navigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "write/overview"));
            Navigation::addItem("/start/serienbriefe", $tab);
        }*/
        $tab = new Navigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "write/overview"));
        Navigation::addItem("/messaging/serienbriefe", $tab);

        $tab = new Navigation(_("Serienbriefe"), PluginEngine::getURL($this, array(), "write/overview"));
        Navigation::addItem("/start/serienbriefe", $tab);
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


