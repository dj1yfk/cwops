CREATE TABLE `cwops_users` (
`id` bigint(5) NOT NULL auto_increment,
`callsign` varchar(64) NOT NULL default '',
`email` varchar(64) NOT NULL default '',
`password` varchar(64) NOT NULL default '',
PRIMARY KEY (`ID`)
) AUTO_INCREMENT = 1;
