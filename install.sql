CREATE TABLE IF NOT EXISTS `serienbriefe_templates` (
  `serienbrief_id` varchar(32) NOT NULL,
  `title` varchar(50) NOT NULL,
  `subject` text NOT NULL,
  `message` text NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `chdate` bigint(20) NOT NULL,
  `mkdate` bigint(20) NOT NULL,
  PRIMARY KEY (`serienbrief_id`)
) ENGINE=MyISAM;