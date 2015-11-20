CREATE TABLE IF NOT EXISTS `uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_filename` varchar(256) CHARACTER SET utf8 NOT NULL,
  `unique_filename` varchar(256) CHARACTER SET utf8 NOT NULL,
  `subfolder` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `mimetype` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `size` bigint(20) NOT NULL,
  `hash` varchar(256) CHARACTER SET utf8 NULL,
  `upload_id` varchar(100) CHARACTER SET utf8 NOT NULL,
  `complete` tinyint(1) NOT NULL DEFAULT 0,
  `label` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
