CREATE TABLE `cwops_log` (
  `id` bigint(5) NOT NULL auto_increment,
  `mycall` varchar(64) NOT NULL default '',
  `date` date NOT NULL,
  `year` int NOT NULL default 0,
  `band` float NOT NULL default 0,
  `nr` int NOT NULL default 0,
  `hiscall` varchar(64) NOT NULL default '',
  `dxcc` int NOT NULL default 0,
  `wae` varchar(2) NOT NULL default '',
  `waz` int NOT NULL default 0,
  `was` varchar(2) NOT NULL default '',
  PRIMARY KEY (`ID`),
  KEY (`mycall`),
  KEY(`nr`)
) AUTO_INCREMENT = 1;
