CREATE TABLE IF NOT EXISTS `serienbriefe_templates` (
    `serienbrief_id` VARCHAR( 32 ) NOT NULL ,
    `title` VARCHAR( 50 ) NOT NULL ,
    `subject` TEXT NOT NULL ,
    `content` TEXT NOT NULL ,
    `user_id` VARCHAR( 32 ) NOT NULL ,
    `chdate` BIGINT NOT NULL ,
    `mkdate` BIGINT NOT NULL
) ENGINE = MYISAM;