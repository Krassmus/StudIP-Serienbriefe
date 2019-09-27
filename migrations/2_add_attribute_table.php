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
        Config::get()->create("SERIENBRIEFE_ATTRIBUTE_TABLE", array(
            'section' => "plugins",
            'range' => "global",
            'type' => "string",
            'value' => "",
            'description' => "Name einer Tabelle mit weiteren Attributen. Form: Tabellenname:FeldMitUserId"
        ));
    }
}