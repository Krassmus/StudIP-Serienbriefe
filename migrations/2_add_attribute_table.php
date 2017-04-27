<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class AddAttributeTable extends Migration {
    public function up() {
        DBManager::get()->exec(
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
                MD5('SERIENBRIEFE_ATTRIBUTE_TABLE'), 
                '', 
                'SERIENBRIEFE_ATTRIBUTE_TABLE', 
                '', 
                '0', 
                'string', 
                'global', 
                'plugins', 
                '0', 
                UNIX_TIMESTAMP(), 
                UNIX_TIMESTAMP(), 
                'Name einer Tabelle mit weiteren Attributen. Form: Tabellenname:FeldMitUserId', 
                '',
                ''
            );
        ");
    }
}