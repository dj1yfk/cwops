drop table cwops_scores;
CREATE TABLE `cwops_scores` (
`uid` bigint(5) NOT NULL default 0,
`aca` int NOT NULL default 0,
`acma` int NOT NULL default 0,
`cma` int NOT NULL default 0,
`dxcc` int NOT NULL default 0,
`wae` int NOT NULL default 0,
`was` int NOT NULL default 0,
`waz` int NOT NULL default 0,
`qtx` int NOT NULL default 0,
`mqtx` int NOT NULL default 0,
`ltmqtx` int NOT NULL default 0,
`ltqtx` int NOT NULL default 0,
`cmqtx` int NOT NULL default 0,
`cmmqtx` int NOT NULL default 0,
`updated` date NOT NULL
);
