CREATE TABLE IF NOT EXISTS `songs_song` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `logicalId` varchar(127),
  `path` varchar(511),
  `options` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE(`logicalId`),
  UNIQUE(`name`),
  INDEX `logicalId` (`logicalId` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
