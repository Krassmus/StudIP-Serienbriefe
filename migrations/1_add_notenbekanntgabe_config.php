<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class AddNotenbekanntgabeConfig extends Migration {
    public function up() {

        DBManager::get()->exec("
        CREATE TABLE IF NOT EXISTS `serienbriefe_templates` (
        `serienbrief_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `chdate` bigint(20) NOT NULL,
  `mkdate` bigint(20) NOT NULL,
  PRIMARY KEY (`serienbrief_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

    }
}