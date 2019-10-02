CREATE TABLE `cwops_members` (
`nr` bigint(5) NOT NULL default 0,
`callsign` varchar(64) NOT NULL default '',
`joined` date NOT NULL,
`left` date NOT NULL
);
