SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `password` (
  `index` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `name` text NOT NULL,
  `pwd` text NOT NULL,
  `other` text,
  PRIMARY KEY (`index`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pin` (
  `userid` int(11) NOT NULL,
  `device` varchar(10) NOT NULL,
  `pinsig` text,
  `pinpk` binary(64) NOT NULL,
  `ua` varchar(500) DEFAULT '',
  `createtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `errortimes` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`,`device`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pwdusrrecord` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(510) NOT NULL,
  `salt` binary(64) NOT NULL,
  `fields` text NOT NULL,
  `email` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `blockip` (
  `ip` varchar(18) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `history` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `ua` varchar(500),
  `outcome` int(11) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `files` (
  `userid` int(11) NOT NULL,
  `index` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `files` mediumblob NOT NULL,
  PRIMARY KEY (`userid`,`index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
