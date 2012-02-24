<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class AddNotenbekanntgabeConfig extends DBMigration {
    public function up() {
        $db = DBManager::get();
        $db->exec(
            "INSERT IGNORE INTO `config` (
                `config_id` ,
                `parent_id` ,
                `field` ,
                `value` ,
                `is_default` ,
                `type` ,
                `range` ,
                `section` ,
                `position` ,
                `mkdate` ,
                `chdate` ,
                `description` ,
                `comment` ,
                `message_template`
            )
            VALUES (
                '46355e5a4fe781153d963a2c0c302007', 
                '', 
                'SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD', 
                '', 
                '0', 
                'string', 
                'global', 
                'plugins', 
                '0', 
                UNIX_TIMESTAMP(), 
                UNIX_TIMESTAMP(), 
                'Name eines booleanschen Nutzerdatenfeldes oder leer, wenn es so einen Sonderfall nicht gibt.', 
                '', 
                ''
            );
        ");
        $db->exec("
            ALTER TABLE `serienbriefe_templates` 
            ADD `notenbekanntgabe` INT( 1 ) NOT NULL DEFAULT '0' 
            AFTER `message` 
        ");
    }
}