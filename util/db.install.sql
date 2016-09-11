DROP TABLE `poi`;
CREATE TABLE `poi` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `lat` float(9,6),
  `lon` float(9,6),
  `id_osm` int unsigned,
  `id_local` int unsigned NOT NULL,
  `brand` varchar(16) NOT NULL,
  `type` enum('bank','atm','supermarket','fuel'),
  `region` enum('RU-MSK','RU-MOS','RU-SPB','RU-SPE','RU-NIZ'),
  `city` varchar(24),
  `address` varchar(256),
  `name` varchar(256),
  `s1` varchar(32),
  `s2` varchar(32),
  `s3` varchar(32),
  `s4` varchar(32),
  `i1` int,
  `i2` int,
  `i3` int,
  `i4` int,
  `timeUpdate` datetime NOT NULL,
  `md5` varchar(32) NOT NULL
);

ALTER TABLE `poi`
ADD UNIQUE `id_local_brand` (`id_local`, `brand`);
