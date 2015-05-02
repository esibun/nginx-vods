# Pre-installation
- Install nginx-rtmp/php/mysql
- Install ffmpeg/ffprobe and ensure they are on PATH and executable by PHP

# Installation
### MySQL
- CREATE DATABASE vods
- ``CREATE TABLE IF NOT EXISTS `api_cache` (
  `cachekey` varchar(30) NOT NULL,
  `value` varchar(100) NOT NULL,
  `expiry` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;``
- ``CREATE TABLE IF NOT EXISTS `videos` (
  `filename` char(27) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(65) NOT NULL,
  `duration` mediumint(9) NOT NULL,
  `thumbnail` enum('yes','no') NOT NULL DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;``
- ``ALTER TABLE `api_cache`
  ADD PRIMARY KEY (`cachekey`);``
- ``ALTER TABLE `videos`
  ADD PRIMARY KEY (`filename`), ADD UNIQUE KEY `filename` (`filename`);``

### index.php
- Change the MySQL info to match your installation's username/password
- Change/add streamkeys on line 13 to match any streamkeys you're using
- Change the title if wanted to match your preferred username
 
### update.php
- Change the title if wanted to match your preferred username
- Change the MySQL info to match your installation's username/password

### Miscellaneous
- Update ava.png in the images folder to the avatar you wish to use
