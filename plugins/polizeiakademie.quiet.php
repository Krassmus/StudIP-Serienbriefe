<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class serienbriefe_user_data_generator {
    
    public function alterUserData($event, $data) {
        if ($data->user_id && !$data->studiengruppe) {
            $data->studiengruppe = $this->getStudiengruppe($data->user_id);
        }
        if ($data->studiengruppe && !$data->studienort) {
            $data->studienort = $this->getStudienort($data->studiengruppe);
        } else {
            $data->studienort = false;
        }
    }
    
    protected function getStudiengruppe($user_id) {
        $studiengruppe_type = 10;
        $db = DBManager::get();
        return $db->query(
            "SELECT Name, COUNT(*) AS number " .
            "FROM seminare " .
                "INNER JOIN seminar_user ON (seminare.Seminar_id = seminar_user.Seminar_id) " .
            "WHERE seminare.status = ".$db->quote($studiengruppe_type)." " .
                "AND seminar_user.user_id = ".$db->quote($user_id)." " .
            "GROUP BY seminar_user.user_id " .
            //"HAVING number = 1 " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
    }
    
    protected function getStudienort($studiengruppe) {
        switch ($studiengruppe[0]) {
            case "O":
                return "Oldenburg";
            case "H":
                return "Hann.Münden";
            case "N":
                return "Nienburg";
        }
        return "";
    }
}

$serienbriefe_user_data_generator = new serienbriefe_user_data_generator();

NotificationCenter::addObserver(
    $serienbriefe_user_data_generator, 
    "alterUserData", 
    "serienbriefe_get_user_data"
);

