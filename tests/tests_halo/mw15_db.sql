-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Oktober 2009 um 11:40
-- Server Version: 5.1.30
-- PHP-Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `testdb`
--

DROP DATABASE IF EXISTS testdb;
CREATE DATABASE IF NOT EXISTS testdb;
USE testdb;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `archive`
--

DROP TABLE IF EXISTS `archive`;
CREATE TABLE IF NOT EXISTS `archive` (
  `ar_namespace` int(11) NOT NULL DEFAULT '0',
  `ar_title` varbinary(255) NOT NULL DEFAULT '',
  `ar_text` mediumblob NOT NULL,
  `ar_comment` tinyblob NOT NULL,
  `ar_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ar_user_text` varbinary(255) NOT NULL,
  `ar_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `ar_minor_edit` tinyint(4) NOT NULL DEFAULT '0',
  `ar_flags` tinyblob NOT NULL,
  `ar_rev_id` int(10) unsigned DEFAULT NULL,
  `ar_text_id` int(10) unsigned DEFAULT NULL,
  `ar_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ar_len` int(10) unsigned DEFAULT NULL,
  `ar_page_id` int(10) unsigned DEFAULT NULL,
  `ar_parent_id` int(10) unsigned DEFAULT NULL,
  KEY `name_title_timestamp` (`ar_namespace`,`ar_title`,`ar_timestamp`),
  KEY `usertext_timestamp` (`ar_user_text`,`ar_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `archive`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_title` varbinary(255) NOT NULL,
  `cat_pages` int(11) NOT NULL DEFAULT '0',
  `cat_subcats` int(11) NOT NULL DEFAULT '0',
  `cat_files` int(11) NOT NULL DEFAULT '0',
  `cat_hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_title` (`cat_title`),
  KEY `cat_pages` (`cat_pages`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `category`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categorylinks`
--

DROP TABLE IF EXISTS `categorylinks`;
CREATE TABLE IF NOT EXISTS `categorylinks` (
  `cl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `cl_to` varbinary(255) NOT NULL DEFAULT '',
  `cl_sortkey` varbinary(70) NOT NULL DEFAULT '',
  `cl_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `cl_from` (`cl_from`,`cl_to`),
  KEY `cl_sortkey` (`cl_to`,`cl_sortkey`,`cl_from`),
  KEY `cl_timestamp` (`cl_to`,`cl_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `categorylinks`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `change_tag`
--

DROP TABLE IF EXISTS `change_tag`;
CREATE TABLE IF NOT EXISTS `change_tag` (
  `ct_rc_id` int(11) DEFAULT NULL,
  `ct_log_id` int(11) DEFAULT NULL,
  `ct_rev_id` int(11) DEFAULT NULL,
  `ct_tag` varbinary(255) NOT NULL,
  `ct_params` blob,
  UNIQUE KEY `change_tag_rc_tag` (`ct_rc_id`,`ct_tag`),
  UNIQUE KEY `change_tag_log_tag` (`ct_log_id`,`ct_tag`),
  UNIQUE KEY `change_tag_rev_tag` (`ct_rev_id`,`ct_tag`),
  KEY `change_tag_tag_id` (`ct_tag`,`ct_rc_id`,`ct_rev_id`,`ct_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `change_tag`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `externallinks`
--

DROP TABLE IF EXISTS `externallinks`;
CREATE TABLE IF NOT EXISTS `externallinks` (
  `el_from` int(10) unsigned NOT NULL DEFAULT '0',
  `el_to` blob NOT NULL,
  `el_index` blob NOT NULL,
  KEY `el_from` (`el_from`,`el_to`(40)),
  KEY `el_to` (`el_to`(60),`el_from`),
  KEY `el_index` (`el_index`(60))
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `externallinks`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `filearchive`
--

DROP TABLE IF EXISTS `filearchive`;
CREATE TABLE IF NOT EXISTS `filearchive` (
  `fa_id` int(11) NOT NULL AUTO_INCREMENT,
  `fa_name` varbinary(255) NOT NULL DEFAULT '',
  `fa_archive_name` varbinary(255) DEFAULT '',
  `fa_storage_group` varbinary(16) DEFAULT NULL,
  `fa_storage_key` varbinary(64) DEFAULT '',
  `fa_deleted_user` int(11) DEFAULT NULL,
  `fa_deleted_timestamp` binary(14) DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `fa_deleted_reason` blob,
  `fa_size` int(10) unsigned DEFAULT '0',
  `fa_width` int(11) DEFAULT '0',
  `fa_height` int(11) DEFAULT '0',
  `fa_metadata` mediumblob,
  `fa_bits` int(11) DEFAULT '0',
  `fa_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE') DEFAULT NULL,
  `fa_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart') DEFAULT 'unknown',
  `fa_minor_mime` varbinary(32) DEFAULT 'unknown',
  `fa_description` tinyblob,
  `fa_user` int(10) unsigned DEFAULT '0',
  `fa_user_text` varbinary(255) DEFAULT NULL,
  `fa_timestamp` binary(14) DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `fa_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fa_id`),
  KEY `fa_name` (`fa_name`,`fa_timestamp`),
  KEY `fa_storage_group` (`fa_storage_group`,`fa_storage_key`),
  KEY `fa_deleted_timestamp` (`fa_deleted_timestamp`),
  KEY `fa_user_timestamp` (`fa_user_text`,`fa_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `filearchive`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hitcounter`
--

DROP TABLE IF EXISTS `hitcounter`;
CREATE TABLE IF NOT EXISTS `hitcounter` (
  `hc_id` int(10) unsigned NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=latin1 MAX_ROWS=25000;

--
-- Daten für Tabelle `hitcounter`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `image`
--

DROP TABLE IF EXISTS `image`;
CREATE TABLE IF NOT EXISTS `image` (
  `img_name` varbinary(255) NOT NULL DEFAULT '',
  `img_size` int(10) unsigned NOT NULL DEFAULT '0',
  `img_width` int(11) NOT NULL DEFAULT '0',
  `img_height` int(11) NOT NULL DEFAULT '0',
  `img_metadata` mediumblob NOT NULL,
  `img_bits` int(11) NOT NULL DEFAULT '0',
  `img_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE') DEFAULT NULL,
  `img_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart') NOT NULL DEFAULT 'unknown',
  `img_minor_mime` varbinary(32) NOT NULL DEFAULT 'unknown',
  `img_description` tinyblob NOT NULL,
  `img_user` int(10) unsigned NOT NULL DEFAULT '0',
  `img_user_text` varbinary(255) NOT NULL,
  `img_timestamp` varbinary(14) NOT NULL DEFAULT '',
  `img_sha1` varbinary(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`img_name`),
  KEY `img_usertext_timestamp` (`img_user_text`,`img_timestamp`),
  KEY `img_size` (`img_size`),
  KEY `img_timestamp` (`img_timestamp`),
  KEY `img_sha1` (`img_sha1`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `image`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `imagelinks`
--

DROP TABLE IF EXISTS `imagelinks`;
CREATE TABLE IF NOT EXISTS `imagelinks` (
  `il_from` int(10) unsigned NOT NULL DEFAULT '0',
  `il_to` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `il_from` (`il_from`,`il_to`),
  UNIQUE KEY `il_to` (`il_to`,`il_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `imagelinks`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `interwiki`
--

DROP TABLE IF EXISTS `interwiki`;
CREATE TABLE IF NOT EXISTS `interwiki` (
  `iw_prefix` varbinary(32) NOT NULL,
  `iw_url` blob NOT NULL,
  `iw_local` tinyint(1) NOT NULL,
  `iw_trans` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `iw_prefix` (`iw_prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `interwiki`
--

INSERT INTO `interwiki` (`iw_prefix`, `iw_url`, `iw_local`, `iw_trans`) VALUES
('acronym', 0x687474703a2f2f7777772e6163726f6e796d66696e6465722e636f6d2f61662d71756572792e6173703f537472696e673d6578616374264163726f6e796d3d2431, 0, 0),
('advogato', 0x687474703a2f2f7777772e6164766f6761746f2e6f72672f2431, 0, 0),
('annotationwiki', 0x687474703a2f2f7777772e7365656477696b692e636f6d2f706167652e63666d3f77696b6969643d33363826646f633d2431, 0, 0),
('arxiv', 0x687474703a2f2f7777772e61727869762e6f72672f6162732f2431, 0, 0),
('c2find', 0x687474703a2f2f63322e636f6d2f6367692f77696b693f46696e64506167652676616c75653d2431, 0, 0),
('cache', 0x687474703a2f2f7777772e676f6f676c652e636f6d2f7365617263683f713d63616368653a2431, 0, 0),
('commons', 0x687474703a2f2f636f6d6d6f6e732e77696b696d656469612e6f72672f77696b692f2431, 0, 0),
('corpknowpedia', 0x687474703a2f2f636f72706b6e6f7770656469612e6f72672f77696b692f696e6465782e7068702f2431, 0, 0),
('dictionary', 0x687474703a2f2f7777772e646963742e6f72672f62696e2f446963743f44617461626173653d2a26466f726d3d44696374312653747261746567793d2a2651756572793d2431, 0, 0),
('disinfopedia', 0x687474703a2f2f7777772e646973696e666f70656469612e6f72672f77696b692e7068746d6c3f7469746c653d2431, 0, 0),
('docbook', 0x687474703a2f2f77696b692e646f63626f6f6b2e6f72672f746f7069632f2431, 0, 0),
('doi', 0x687474703a2f2f64782e646f692e6f72672f2431, 0, 0),
('drumcorpswiki', 0x687474703a2f2f7777772e6472756d636f72707377696b692e636f6d2f696e6465782e7068702f2431, 0, 0),
('dwjwiki', 0x687474703a2f2f7777772e737562657269632e6e65742f6367692d62696e2f64776a2f77696b692e6367693f2431, 0, 0),
('elibre', 0x687474703a2f2f656e6369636c6f70656469612e75732e65732f696e6465782e7068702f2431, 0, 0),
('emacswiki', 0x687474703a2f2f7777772e656d61637377696b692e6f72672f6367692d62696e2f77696b692e706c3f2431, 0, 0),
('foldoc', 0x687474703a2f2f666f6c646f632e6f72672f3f2431, 0, 0),
('foxwiki', 0x687474703a2f2f666f782e77696b69732e636f6d2f77632e646c6c3f57696b697e2431, 0, 0),
('freebsdman', 0x687474703a2f2f7777772e467265654253442e6f72672f6367692f6d616e2e6367693f6170726f706f733d312671756572793d2431, 0, 0),
('gej', 0x687474703a2f2f7777772e6573706572616e746f2e64652f6367692d62696e2f616b746976696b696f2f77696b692e706c3f2431, 0, 0),
('gentoo-wiki', 0x687474703a2f2f67656e746f6f2d77696b692e636f6d2f2431, 0, 0),
('google', 0x687474703a2f2f7777772e676f6f676c652e636f6d2f7365617263683f713d2431, 0, 0),
('googlegroups', 0x687474703a2f2f67726f7570732e676f6f676c652e636f6d2f67726f7570733f713d2431, 0, 0),
('hammondwiki', 0x687474703a2f2f7777772e64616972696b692e6f72672f48616d6d6f6e6457696b692f2431, 0, 0),
('hewikisource', 0x687474703a2f2f68652e77696b69736f757263652e6f72672f77696b692f2431, 1, 0),
('hrwiki', 0x687474703a2f2f7777772e687277696b692e6f72672f696e6465782e7068702f2431, 0, 0),
('imdb', 0x687474703a2f2f75732e696d64622e636f6d2f5469746c653f2431, 0, 0),
('jargonfile', 0x687474703a2f2f73756e69722e6f72672f617070732f6d6574612e706c3f77696b693d4a6172676f6e46696c652672656469726563743d2431, 0, 0),
('jspwiki', 0x687474703a2f2f7777772e6a737077696b692e6f72672f77696b692f2431, 0, 0),
('keiki', 0x687474703a2f2f6b65692e6b692f656e2f2431, 0, 0),
('kmwiki', 0x687474703a2f2f6b6d77696b692e77696b697370616365732e636f6d2f2431, 0, 0),
('linuxwiki', 0x687474703a2f2f6c696e757877696b692e64652f2431, 0, 0),
('lojban', 0x687474703a2f2f7777772e6c6f6a62616e2e6f72672f74696b692f74696b692d696e6465782e7068703f706167653d2431, 0, 0),
('lqwiki', 0x687474703a2f2f77696b692e6c696e75787175657374696f6e732e6f72672f77696b692f2431, 0, 0),
('lugkr', 0x687474703a2f2f6c75672d6b722e736f75726365666f7267652e6e65742f6367692d62696e2f6c756777696b692e706c3f2431, 0, 0),
('mathsongswiki', 0x687474703a2f2f5365656457696b692e636f6d2f706167652e63666d3f77696b6969643d32333726646f633d2431, 0, 0),
('meatball', 0x687474703a2f2f7777772e7573656d6f642e636f6d2f6367692d62696e2f6d622e706c3f2431, 0, 0),
('mediawikiwiki', 0x687474703a2f2f7777772e6d6564696177696b692e6f72672f77696b692f2431, 0, 0),
('mediazilla', 0x687474703a2f2f6275677a696c6c612e77696b6970656469612e6f72672f2431, 1, 0),
('memoryalpha', 0x687474703a2f2f7777772e6d656d6f72792d616c7068612e6f72672f656e2f696e6465782e7068702f2431, 0, 0),
('metawiki', 0x687474703a2f2f73756e69722e6f72672f617070732f6d6574612e706c3f2431, 0, 0),
('metawikipedia', 0x687474703a2f2f6d6574612e77696b696d656469612e6f72672f77696b692f2431, 0, 0),
('moinmoin', 0x687474703a2f2f7075726c2e6e65742f77696b692f6d6f696e2f2431, 0, 0),
('mozillawiki', 0x687474703a2f2f77696b692e6d6f7a696c6c612e6f72672f696e6465782e7068702f2431, 0, 0),
('oeis', 0x687474703a2f2f7777772e72657365617263682e6174742e636f6d2f6367692d62696e2f6163636573732e6367692f61732f6e6a61732f73657175656e6365732f656973412e6367693f416e756d3d2431, 0, 0),
('openfacts', 0x687474703a2f2f6f70656e66616374732e6265726c696f732e64652f696e6465782e7068746d6c3f7469746c653d2431, 0, 0),
('openwiki', 0x687474703a2f2f6f70656e77696b692e636f6d2f3f2431, 0, 0),
('patwiki', 0x687474703a2f2f67617573732e666669692e6f72672f2431, 0, 0),
('pmeg', 0x687474703a2f2f7777772e62657274696c6f772e636f6d2f706d65672f24312e706870, 0, 0),
('ppr', 0x687474703a2f2f63322e636f6d2f6367692f77696b693f2431, 0, 0),
('pythoninfo', 0x687474703a2f2f77696b692e707974686f6e2e6f72672f6d6f696e2f2431, 0, 0),
('rfc', 0x687474703a2f2f7777772e7266632d656469746f722e6f72672f7266632f72666324312e747874, 0, 0),
('s23wiki', 0x687474703a2f2f69732d726f6f742e64652f77696b692f696e6465782e7068702f2431, 0, 0),
('seattlewiki', 0x687474703a2f2f73656174746c652e77696b69612e636f6d2f77696b692f2431, 0, 0),
('seattlewireless', 0x687474703a2f2f73656174746c65776972656c6573732e6e65742f3f2431, 0, 0),
('senseislibrary', 0x687474703a2f2f73656e736569732e786d702e6e65742f3f2431, 0, 0),
('slashdot', 0x687474703a2f2f736c617368646f742e6f72672f61727469636c652e706c3f7369643d2431, 0, 0),
('sourceforge', 0x687474703a2f2f736f75726365666f7267652e6e65742f2431, 0, 0),
('squeak', 0x687474703a2f2f77696b692e73717565616b2e6f72672f73717565616b2f2431, 0, 0),
('susning', 0x687474703a2f2f7777772e7375736e696e672e6e752f2431, 0, 0),
('svgwiki', 0x687474703a2f2f77696b692e7376672e6f72672f2431, 0, 0),
('tavi', 0x687474703a2f2f746176692e736f75726365666f7267652e6e65742f2431, 0, 0),
('tejo', 0x687474703a2f2f7777772e74656a6f2e6f72672f76696b696f2f2431, 0, 0),
('theopedia', 0x687474703a2f2f7777772e7468656f70656469612e636f6d2f2431, 0, 0),
('tmbw', 0x687474703a2f2f7777772e746d62772e6e65742f77696b692f2431, 0, 0),
('tmnet', 0x687474703a2f2f7777772e746563686e6f6d616e69666573746f732e6e65742f3f2431, 0, 0),
('tmwiki', 0x687474703a2f2f7777772e45617379546f7069634d6170732e636f6d2f3f706167653d2431, 0, 0),
('twiki', 0x687474703a2f2f7477696b692e6f72672f6367692d62696e2f766965772f2431, 0, 0),
('uea', 0x687474703a2f2f7777772e74656a6f2e6f72672f7565612f2431, 0, 0),
('unreal', 0x687474703a2f2f77696b692e6265796f6e64756e7265616c2e636f6d2f77696b692f2431, 0, 0),
('usemod', 0x687474703a2f2f7777772e7573656d6f642e636f6d2f6367692d62696e2f77696b692e706c3f2431, 0, 0),
('vinismo', 0x687474703a2f2f76696e69736d6f2e636f6d2f656e2f2431, 0, 0),
('webseitzwiki', 0x687474703a2f2f776562736569747a2e666c7578656e742e636f6d2f77696b692f2431, 0, 0),
('why', 0x687474703a2f2f636c75626c65742e636f6d2f632f632f7768793f2431, 0, 0),
('wiki', 0x687474703a2f2f63322e636f6d2f6367692f77696b693f2431, 0, 0),
('wikia', 0x687474703a2f2f7777772e77696b69612e636f6d2f77696b692f2431, 0, 0),
('wikibooks', 0x687474703a2f2f656e2e77696b69626f6f6b732e6f72672f77696b692f2431, 1, 0),
('wikicities', 0x687474703a2f2f7777772e77696b696369746965732e636f6d2f696e6465782e7068702f2431, 0, 0),
('wikif1', 0x687474703a2f2f7777772e77696b6966312e6f72672f2431, 0, 0),
('wikihow', 0x687474703a2f2f7777772e77696b69686f772e636f6d2f2431, 0, 0),
('wikimedia', 0x687474703a2f2f77696b696d65646961666f756e646174696f6e2e6f72672f77696b692f2431, 0, 0),
('wikinews', 0x687474703a2f2f656e2e77696b696e6577732e6f72672f77696b692f2431, 1, 0),
('wikinfo', 0x687474703a2f2f7777772e77696b696e666f2e6f72672f696e6465782e7068702f2431, 0, 0),
('wikipedia', 0x687474703a2f2f656e2e77696b6970656469612e6f72672f77696b692f2431, 1, 0),
('wikiquote', 0x687474703a2f2f656e2e77696b6971756f74652e6f72672f77696b692f2431, 1, 0),
('wikisource', 0x687474703a2f2f736f75726365732e77696b6970656469612e6f72672f77696b692f2431, 1, 0),
('wikispecies', 0x687474703a2f2f737065636965732e77696b6970656469612e6f72672f77696b692f2431, 1, 0),
('wikitravel', 0x687474703a2f2f77696b6974726176656c2e6f72672f656e2f2431, 0, 0),
('wikt', 0x687474703a2f2f656e2e77696b74696f6e6172792e6f72672f77696b692f2431, 1, 0),
('wiktionary', 0x687474703a2f2f656e2e77696b74696f6e6172792e6f72672f77696b692f2431, 1, 0),
('wlug', 0x687474703a2f2f7777772e776c75672e6f72672e6e7a2f2431, 0, 0),
('zwiki', 0x687474703a2f2f7a77696b692e6f72672f2431, 0, 0),
('zzz wiki', 0x687474703a2f2f77696b692e7a7a7a2e65652f696e6465782e7068702f2431, 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ipblocks`
--

DROP TABLE IF EXISTS `ipblocks`;
CREATE TABLE IF NOT EXISTS `ipblocks` (
  `ipb_id` int(11) NOT NULL AUTO_INCREMENT,
  `ipb_address` tinyblob NOT NULL,
  `ipb_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ipb_by` int(10) unsigned NOT NULL DEFAULT '0',
  `ipb_by_text` varbinary(255) NOT NULL DEFAULT '',
  `ipb_reason` tinyblob NOT NULL,
  `ipb_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `ipb_auto` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_anon_only` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_create_account` tinyint(1) NOT NULL DEFAULT '1',
  `ipb_enable_autoblock` tinyint(1) NOT NULL DEFAULT '1',
  `ipb_expiry` varbinary(14) NOT NULL DEFAULT '',
  `ipb_range_start` tinyblob NOT NULL,
  `ipb_range_end` tinyblob NOT NULL,
  `ipb_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_block_email` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_allow_usertalk` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ipb_id`),
  UNIQUE KEY `ipb_address` (`ipb_address`(255),`ipb_user`,`ipb_auto`,`ipb_anon_only`),
  KEY `ipb_user` (`ipb_user`),
  KEY `ipb_range` (`ipb_range_start`(8),`ipb_range_end`(8)),
  KEY `ipb_timestamp` (`ipb_timestamp`),
  KEY `ipb_expiry` (`ipb_expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `ipblocks`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `job`
--

DROP TABLE IF EXISTS `job`;
CREATE TABLE IF NOT EXISTS `job` (
  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_cmd` varbinary(60) NOT NULL DEFAULT '',
  `job_namespace` int(11) NOT NULL,
  `job_title` varbinary(255) NOT NULL,
  `job_params` blob NOT NULL,
  PRIMARY KEY (`job_id`),
  KEY `job_cmd` (`job_cmd`,`job_namespace`,`job_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `job`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `langlinks`
--

DROP TABLE IF EXISTS `langlinks`;
CREATE TABLE IF NOT EXISTS `langlinks` (
  `ll_from` int(10) unsigned NOT NULL DEFAULT '0',
  `ll_lang` varbinary(20) NOT NULL DEFAULT '',
  `ll_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `ll_from` (`ll_from`,`ll_lang`),
  KEY `ll_lang` (`ll_lang`,`ll_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `langlinks`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `logging`
--

DROP TABLE IF EXISTS `logging`;
CREATE TABLE IF NOT EXISTS `logging` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varbinary(10) NOT NULL DEFAULT '',
  `log_action` varbinary(10) NOT NULL DEFAULT '',
  `log_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  `log_user` int(10) unsigned NOT NULL DEFAULT '0',
  `log_namespace` int(11) NOT NULL DEFAULT '0',
  `log_title` varbinary(255) NOT NULL DEFAULT '',
  `log_comment` varbinary(255) NOT NULL DEFAULT '',
  `log_params` blob NOT NULL,
  `log_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `type_time` (`log_type`,`log_timestamp`),
  KEY `user_time` (`log_user`,`log_timestamp`),
  KEY `page_time` (`log_namespace`,`log_title`,`log_timestamp`),
  KEY `times` (`log_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `logging`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `math`
--

DROP TABLE IF EXISTS `math`;
CREATE TABLE IF NOT EXISTS `math` (
  `math_inputhash` varbinary(16) NOT NULL,
  `math_outputhash` varbinary(16) NOT NULL,
  `math_html_conservativeness` tinyint(4) NOT NULL,
  `math_html` blob,
  `math_mathml` blob,
  UNIQUE KEY `math_inputhash` (`math_inputhash`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `math`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `objectcache`
--

DROP TABLE IF EXISTS `objectcache`;
CREATE TABLE IF NOT EXISTS `objectcache` (
  `keyname` varbinary(255) NOT NULL DEFAULT '',
  `value` mediumblob,
  `exptime` datetime DEFAULT NULL,
  PRIMARY KEY (`keyname`),
  KEY `exptime` (`exptime`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `objectcache`
--

INSERT INTO `objectcache` (`keyname`, `value`, `exptime`) VALUES
('testdb:messages:en', 0x4bb432b4aa2eb632b7520a730d0af6f4f753b2ceb432b4ae0500, '2009-10-10 09:32:02'),
('testdb:pcache:idhash:1-0!1!0!!en!2', 0xad566d6fdb380cde67ff0ac51fee3e257e69d65e14c78743d16b0b2c6b86f56e1f86a1506c26162acb86242fcd86fdf7918e9325e976e8d62bd2bc9014f9907c44fa864731f767c25830378dab1be7f3f8847fb6fc25f7cb5b7870fed8f2687836e27e52a7c95c2ef12d9d422ec53b792f59212c9b0368669b2c036b178d526b26b5754229c80749304ff11f8f7949800eeaf4bcd2b6518eb902582258616031f10be76a1e0425383158a1df92020c2ab30ce8577005aae678d08176d6679912d64e7c4407460bc51ce1644e3a053febca809af8ba5a544a552b3ffd07ebf0bb65978dcc210944ca1695c16cf0bd144e569ae1abb1522f5bf8e490d96ae156c2c0a04dd0c394b42811c725388786775809e320f799cc1f0b530a9214719ad85ae85d62b97416320ae8a7ef77450aa6c2164d4d750fa4cee1615017f59f9bb4a742eabb9958c26fa2acc7a23d3b213fedefced924da55e90255ac1373d6c1623b587492a07d48020296b2037ce5aa5f80c895d4e0a7c78759772409302d2f69549a28993e6af56ab51ab4ada1227e6bcf54e846286ad0422e1bd316fdce6e223cb1f3bfe6f9880807666c6bc694b46d5db0d798d42f24f6f75f6ffe8f2c5a374790bf5d4ad4fe17488bee29117b743f4a21552974ab23ce07bbf87da175d5e80c9e80fd59de7f98122a405860e485c876d4880069e67949afdf67de6b58cd66685022c30dd49571de0c3f4d45f309afb3ae72601906749cc54114b67fdeacb2ae0f0fc8dc1c2f7ca61a34b2f21370160671383a8b5ec66cbe7660bd5b286b251c3061964d8963e40776170f35682b3f02abdbf9ca1698634ba72e7848c1bd7e7f8bfcadf808147c6b9f89ac1d32ae60f7b066390ead0c07cd90d7ad86cb1ca76fc1a37ed88b7a61af07ba1733c2efb0ec781bcb9ac56138c220a370741287278c626d063a4ef757422f1b9c18afa4beb7fe58f0907ffe82ba0875e798dfb23212f61567a44010386aec8dcaa76229337f3ce721790c695f100bb64b0305fe56714e786f11d536f8212e92fe8166ff82b134f3c6edfa8906a783217d3f25b04720db705d23f6e5f19efc3adfd310f8eb12d3ddb7a6325c744cde4518d2fa1ba2eaa736c958f288708ff8b3e650e766183ed10d0d82eecce9097ff6056c5d6d498017e96db785b65da6a7822bdc8ddf5151475e5797b4f6cdfa407a85bbe2da4179dca7cd03c75555ed7716eb57be13466faab12fed021e537566aa1a8c3ba06a8c9479b1ff54f3a2bca68539ab94ccd67bec7c6c984b8be459b7543e227777a9bec3e1283ef3c75fbe02, '2009-10-10 09:32:03');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `oldimage`
--

DROP TABLE IF EXISTS `oldimage`;
CREATE TABLE IF NOT EXISTS `oldimage` (
  `oi_name` varbinary(255) NOT NULL DEFAULT '',
  `oi_archive_name` varbinary(255) NOT NULL DEFAULT '',
  `oi_size` int(10) unsigned NOT NULL DEFAULT '0',
  `oi_width` int(11) NOT NULL DEFAULT '0',
  `oi_height` int(11) NOT NULL DEFAULT '0',
  `oi_bits` int(11) NOT NULL DEFAULT '0',
  `oi_description` tinyblob NOT NULL,
  `oi_user` int(10) unsigned NOT NULL DEFAULT '0',
  `oi_user_text` varbinary(255) NOT NULL,
  `oi_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `oi_metadata` mediumblob NOT NULL,
  `oi_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE') DEFAULT NULL,
  `oi_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart') NOT NULL DEFAULT 'unknown',
  `oi_minor_mime` varbinary(32) NOT NULL DEFAULT 'unknown',
  `oi_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `oi_sha1` varbinary(32) NOT NULL DEFAULT '',
  KEY `oi_usertext_timestamp` (`oi_user_text`,`oi_timestamp`),
  KEY `oi_name_timestamp` (`oi_name`,`oi_timestamp`),
  KEY `oi_name_archive_name` (`oi_name`,`oi_archive_name`(14)),
  KEY `oi_sha1` (`oi_sha1`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `oldimage`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `page`
--

DROP TABLE IF EXISTS `page`;
CREATE TABLE IF NOT EXISTS `page` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_namespace` int(11) NOT NULL,
  `page_title` varbinary(255) NOT NULL,
  `page_restrictions` tinyblob NOT NULL,
  `page_counter` bigint(20) unsigned NOT NULL DEFAULT '0',
  `page_is_redirect` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `page_is_new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `page_random` double unsigned NOT NULL,
  `page_touched` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `page_latest` int(10) unsigned NOT NULL,
  `page_len` int(10) unsigned NOT NULL,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `name_title` (`page_namespace`,`page_title`),
  KEY `page_random` (`page_random`),
  KEY `page_len` (`page_len`)
) ENGINE=InnoDB  DEFAULT CHARSET=binary AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `page`
--

INSERT INTO `page` (`page_id`, `page_namespace`, `page_title`, `page_restrictions`, `page_counter`, `page_is_redirect`, `page_is_new`, `page_random`, `page_touched`, `page_latest`, `page_len`) VALUES
(1, 0, 'Main_Page', '', 2, 0, 0, 0.148775637885, '20091009093127', 1, 449);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pagelinks`
--

DROP TABLE IF EXISTS `pagelinks`;
CREATE TABLE IF NOT EXISTS `pagelinks` (
  `pl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `pl_namespace` int(11) NOT NULL DEFAULT '0',
  `pl_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `pl_from` (`pl_from`,`pl_namespace`,`pl_title`),
  UNIQUE KEY `pl_namespace` (`pl_namespace`,`pl_title`,`pl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `pagelinks`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `page_props`
--

DROP TABLE IF EXISTS `page_props`;
CREATE TABLE IF NOT EXISTS `page_props` (
  `pp_page` int(11) NOT NULL,
  `pp_propname` varbinary(60) NOT NULL,
  `pp_value` blob NOT NULL,
  UNIQUE KEY `pp_page_propname` (`pp_page`,`pp_propname`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `page_props`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `page_restrictions`
--

DROP TABLE IF EXISTS `page_restrictions`;
CREATE TABLE IF NOT EXISTS `page_restrictions` (
  `pr_page` int(11) NOT NULL,
  `pr_type` varbinary(60) NOT NULL,
  `pr_level` varbinary(60) NOT NULL,
  `pr_cascade` tinyint(4) NOT NULL,
  `pr_user` int(11) DEFAULT NULL,
  `pr_expiry` varbinary(14) DEFAULT NULL,
  `pr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`pr_id`),
  UNIQUE KEY `pr_pagetype` (`pr_page`,`pr_type`),
  KEY `pr_typelevel` (`pr_type`,`pr_level`),
  KEY `pr_level` (`pr_level`),
  KEY `pr_cascade` (`pr_cascade`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `page_restrictions`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `protected_titles`
--

DROP TABLE IF EXISTS `protected_titles`;
CREATE TABLE IF NOT EXISTS `protected_titles` (
  `pt_namespace` int(11) NOT NULL,
  `pt_title` varbinary(255) NOT NULL,
  `pt_user` int(10) unsigned NOT NULL,
  `pt_reason` tinyblob,
  `pt_timestamp` binary(14) NOT NULL,
  `pt_expiry` varbinary(14) NOT NULL DEFAULT '',
  `pt_create_perm` varbinary(60) NOT NULL,
  UNIQUE KEY `pt_namespace_title` (`pt_namespace`,`pt_title`),
  KEY `pt_timestamp` (`pt_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `protected_titles`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `querycache`
--

DROP TABLE IF EXISTS `querycache`;
CREATE TABLE IF NOT EXISTS `querycache` (
  `qc_type` varbinary(32) NOT NULL,
  `qc_value` int(10) unsigned NOT NULL DEFAULT '0',
  `qc_namespace` int(11) NOT NULL DEFAULT '0',
  `qc_title` varbinary(255) NOT NULL DEFAULT '',
  KEY `qc_type` (`qc_type`,`qc_value`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `querycache`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `querycachetwo`
--

DROP TABLE IF EXISTS `querycachetwo`;
CREATE TABLE IF NOT EXISTS `querycachetwo` (
  `qcc_type` varbinary(32) NOT NULL,
  `qcc_value` int(10) unsigned NOT NULL DEFAULT '0',
  `qcc_namespace` int(11) NOT NULL DEFAULT '0',
  `qcc_title` varbinary(255) NOT NULL DEFAULT '',
  `qcc_namespacetwo` int(11) NOT NULL DEFAULT '0',
  `qcc_titletwo` varbinary(255) NOT NULL DEFAULT '',
  KEY `qcc_type` (`qcc_type`,`qcc_value`),
  KEY `qcc_title` (`qcc_type`,`qcc_namespace`,`qcc_title`),
  KEY `qcc_titletwo` (`qcc_type`,`qcc_namespacetwo`,`qcc_titletwo`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `querycachetwo`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `querycache_info`
--

DROP TABLE IF EXISTS `querycache_info`;
CREATE TABLE IF NOT EXISTS `querycache_info` (
  `qci_type` varbinary(32) NOT NULL DEFAULT '',
  `qci_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  UNIQUE KEY `qci_type` (`qci_type`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `querycache_info`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `recentchanges`
--

DROP TABLE IF EXISTS `recentchanges`;
CREATE TABLE IF NOT EXISTS `recentchanges` (
  `rc_id` int(11) NOT NULL AUTO_INCREMENT,
  `rc_timestamp` varbinary(14) NOT NULL DEFAULT '',
  `rc_cur_time` varbinary(14) NOT NULL DEFAULT '',
  `rc_user` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_user_text` varbinary(255) NOT NULL,
  `rc_namespace` int(11) NOT NULL DEFAULT '0',
  `rc_title` varbinary(255) NOT NULL DEFAULT '',
  `rc_comment` varbinary(255) NOT NULL DEFAULT '',
  `rc_minor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_bot` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_cur_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_this_oldid` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_last_oldid` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_moved_to_ns` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_moved_to_title` varbinary(255) NOT NULL DEFAULT '',
  `rc_patrolled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_ip` varbinary(40) NOT NULL DEFAULT '',
  `rc_old_len` int(11) DEFAULT NULL,
  `rc_new_len` int(11) DEFAULT NULL,
  `rc_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_logid` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_log_type` varbinary(255) DEFAULT NULL,
  `rc_log_action` varbinary(255) DEFAULT NULL,
  `rc_params` blob,
  PRIMARY KEY (`rc_id`),
  KEY `rc_timestamp` (`rc_timestamp`),
  KEY `rc_namespace_title` (`rc_namespace`,`rc_title`),
  KEY `rc_cur_id` (`rc_cur_id`),
  KEY `new_name_timestamp` (`rc_new`,`rc_namespace`,`rc_timestamp`),
  KEY `rc_ip` (`rc_ip`),
  KEY `rc_ns_usertext` (`rc_namespace`,`rc_user_text`),
  KEY `rc_user_text` (`rc_user_text`,`rc_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `recentchanges`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `redirect`
--

DROP TABLE IF EXISTS `redirect`;
CREATE TABLE IF NOT EXISTS `redirect` (
  `rd_from` int(10) unsigned NOT NULL DEFAULT '0',
  `rd_namespace` int(11) NOT NULL DEFAULT '0',
  `rd_title` varbinary(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`rd_from`),
  KEY `rd_ns_title` (`rd_namespace`,`rd_title`,`rd_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `redirect`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `revision`
--

DROP TABLE IF EXISTS `revision`;
CREATE TABLE IF NOT EXISTS `revision` (
  `rev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rev_page` int(10) unsigned NOT NULL,
  `rev_text_id` int(10) unsigned NOT NULL,
  `rev_comment` tinyblob NOT NULL,
  `rev_user` int(10) unsigned NOT NULL DEFAULT '0',
  `rev_user_text` varbinary(255) NOT NULL DEFAULT '',
  `rev_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `rev_minor_edit` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rev_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rev_len` int(10) unsigned DEFAULT NULL,
  `rev_parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rev_id`),
  UNIQUE KEY `rev_page_id` (`rev_page`,`rev_id`),
  KEY `rev_timestamp` (`rev_timestamp`),
  KEY `page_timestamp` (`rev_page`,`rev_timestamp`),
  KEY `user_timestamp` (`rev_user`,`rev_timestamp`),
  KEY `usertext_timestamp` (`rev_user_text`,`rev_timestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=binary MAX_ROWS=10000000 AVG_ROW_LENGTH=1024 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `revision`
--

INSERT INTO `revision` (`rev_id`, `rev_page`, `rev_text_id`, `rev_comment`, `rev_user`, `rev_user_text`, `rev_timestamp`, `rev_minor_edit`, `rev_deleted`, `rev_len`, `rev_parent_id`) VALUES
(1, 1, 1, '', 0, 'MediaWiki default', '20091009093127', 0, 0, 449, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `searchindex`
--

DROP TABLE IF EXISTS `searchindex`;
CREATE TABLE IF NOT EXISTS `searchindex` (
  `si_page` int(10) unsigned NOT NULL,
  `si_title` varchar(255) NOT NULL DEFAULT '',
  `si_text` mediumtext NOT NULL,
  UNIQUE KEY `si_page` (`si_page`),
  FULLTEXT KEY `si_title` (`si_title`),
  FULLTEXT KEY `si_text` (`si_text`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `searchindex`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `site_stats`
--

DROP TABLE IF EXISTS `site_stats`;
CREATE TABLE IF NOT EXISTS `site_stats` (
  `ss_row_id` int(10) unsigned NOT NULL,
  `ss_total_views` bigint(20) unsigned DEFAULT '0',
  `ss_total_edits` bigint(20) unsigned DEFAULT '0',
  `ss_good_articles` bigint(20) unsigned DEFAULT '0',
  `ss_total_pages` bigint(20) DEFAULT '-1',
  `ss_users` bigint(20) DEFAULT '-1',
  `ss_active_users` bigint(20) DEFAULT '-1',
  `ss_admins` int(11) DEFAULT '-1',
  `ss_images` int(11) DEFAULT '0',
  UNIQUE KEY `ss_row_id` (`ss_row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `site_stats`
--

INSERT INTO `site_stats` (`ss_row_id`, `ss_total_views`, `ss_total_edits`, `ss_good_articles`, `ss_total_pages`, `ss_users`, `ss_active_users`, `ss_admins`, `ss_images`) VALUES
(1, 2, 1, 0, 1, 1, -1, 1, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tag_summary`
--

DROP TABLE IF EXISTS `tag_summary`;
CREATE TABLE IF NOT EXISTS `tag_summary` (
  `ts_rc_id` int(11) DEFAULT NULL,
  `ts_log_id` int(11) DEFAULT NULL,
  `ts_rev_id` int(11) DEFAULT NULL,
  `ts_tags` blob NOT NULL,
  UNIQUE KEY `tag_summary_rc_id` (`ts_rc_id`),
  UNIQUE KEY `tag_summary_log_id` (`ts_log_id`),
  UNIQUE KEY `tag_summary_rev_id` (`ts_rev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `tag_summary`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `templatelinks`
--

DROP TABLE IF EXISTS `templatelinks`;
CREATE TABLE IF NOT EXISTS `templatelinks` (
  `tl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `tl_namespace` int(11) NOT NULL DEFAULT '0',
  `tl_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `tl_from` (`tl_from`,`tl_namespace`,`tl_title`),
  UNIQUE KEY `tl_namespace` (`tl_namespace`,`tl_title`,`tl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `templatelinks`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `text`
--

DROP TABLE IF EXISTS `text`;
CREATE TABLE IF NOT EXISTS `text` (
  `old_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_text` mediumblob NOT NULL,
  `old_flags` tinyblob NOT NULL,
  PRIMARY KEY (`old_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=binary MAX_ROWS=10000000 AVG_ROW_LENGTH=10240 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `text`
--

INSERT INTO `text` (`old_id`, `old_text`, `old_flags`) VALUES
(1, 0x3c6269673e2727274d6564696157696b6920686173206265656e207375636365737366756c6c7920696e7374616c6c65642e2727273c2f6269673e0a0a436f6e73756c7420746865205b687474703a2f2f6d6574612e77696b696d656469612e6f72672f77696b692f48656c703a436f6e74656e7473205573657227732047756964655d20666f7220696e666f726d6174696f6e206f6e207573696e67207468652077696b6920736f6674776172652e0a0a3d3d2047657474696e672073746172746564203d3d0a2a205b687474703a2f2f7777772e6d6564696177696b692e6f72672f77696b692f4d616e75616c3a436f6e66696775726174696f6e5f73657474696e677320436f6e66696775726174696f6e2073657474696e6773206c6973745d0a2a205b687474703a2f2f7777772e6d6564696177696b692e6f72672f77696b692f4d616e75616c3a464151204d6564696157696b69204641515d0a2a205b68747470733a2f2f6c697374732e77696b696d656469612e6f72672f6d61696c6d616e2f6c697374696e666f2f6d6564696177696b692d616e6e6f756e6365204d6564696157696b692072656c65617365206d61696c696e67206c6973745d, 0x7574662d38);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trackbacks`
--

DROP TABLE IF EXISTS `trackbacks`;
CREATE TABLE IF NOT EXISTS `trackbacks` (
  `tb_id` int(11) NOT NULL AUTO_INCREMENT,
  `tb_page` int(11) DEFAULT NULL,
  `tb_title` varbinary(255) NOT NULL,
  `tb_url` blob NOT NULL,
  `tb_ex` blob,
  `tb_name` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`tb_id`),
  KEY `tb_page` (`tb_page`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `trackbacks`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `transcache`
--

DROP TABLE IF EXISTS `transcache`;
CREATE TABLE IF NOT EXISTS `transcache` (
  `tc_url` varbinary(255) NOT NULL,
  `tc_contents` blob,
  `tc_time` int(11) NOT NULL,
  UNIQUE KEY `tc_url_idx` (`tc_url`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `transcache`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `updatelog`
--

DROP TABLE IF EXISTS `updatelog`;
CREATE TABLE IF NOT EXISTS `updatelog` (
  `ul_key` varbinary(255) NOT NULL,
  PRIMARY KEY (`ul_key`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `updatelog`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varbinary(255) NOT NULL DEFAULT '',
  `user_real_name` varbinary(255) NOT NULL DEFAULT '',
  `user_password` tinyblob NOT NULL,
  `user_newpassword` tinyblob NOT NULL,
  `user_newpass_time` binary(14) DEFAULT NULL,
  `user_email` tinyblob NOT NULL,
  `user_options` blob NOT NULL,
  `user_touched` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `user_token` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `user_email_authenticated` binary(14) DEFAULT NULL,
  `user_email_token` binary(32) DEFAULT NULL,
  `user_email_token_expires` binary(14) DEFAULT NULL,
  `user_registration` binary(14) DEFAULT NULL,
  `user_editcount` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `user_email_token` (`user_email_token`)
) ENGINE=InnoDB  DEFAULT CHARSET=binary AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_real_name`, `user_password`, `user_newpassword`, `user_newpass_time`, `user_email`, `user_options`, `user_touched`, `user_token`, `user_email_authenticated`, `user_email_token`, `user_email_token_expires`, `user_registration`, `user_editcount`) VALUES
(1, 'WikiSysop', '', 0x3a423a36656532626337343a3431303566366263363064613339383039333066656330376338316636376637, '', NULL, '', 0x717569636b6261723d310a756e6465726c696e653d320a636f6c733d38300a726f77733d32350a7365617263686c696d69743d32300a636f6e746578746c696e65733d350a636f6e7465787463686172733d35300a64697361626c65737567676573743d300a736b696e3d0a6d6174683d310a7573656e657772633d300a7263646179733d370a72636c696d69743d35300a776c6c696d69743d3235300a686964656d696e6f723d300a68696465706174726f6c6c65643d300a6e6577706167657368696465706174726f6c6c65643d300a686967686c6967687462726f6b656e3d310a737475627468726573686f6c643d300a707265766965776f6e746f703d310a707265766965776f6e66697273743d300a6564697473656374696f6e3d310a6564697473656374696f6e6f6e7269676874636c69636b3d300a656469746f6e64626c636c69636b3d300a6564697477696474683d300a73686f77746f633d310a73686f77746f6f6c6261723d310a6d696e6f7264656661756c743d300a646174653d64656661756c740a696d61676573697a653d320a7468756d6273697a653d320a72656d656d62657270617373776f72643d300a6e6f63616368653d300a646966666f6e6c793d300a73686f7768696464656e636174733d300a6e6f726f6c6c6261636b646966663d300a656e6f74696677617463686c69737470616765733d300a656e6f7469667573657274616c6b70616765733d310a656e6f7469666d696e6f7265646974733d300a656e6f74696672657665616c616464723d300a73686f776e756d626572737761746368696e673d310a66616e63797369673d300a65787465726e616c656469746f723d300a65787465726e616c646966663d300a666f7263656564697473756d6d6172793d300a73686f776a756d706c696e6b733d310a6a7573746966793d300a6e756d62657268656164696e67733d300a7573656c697665707265766965773d300a77617463686c697374646179733d330a657874656e6477617463686c6973743d300a77617463686c697374686964656d696e6f723d300a77617463686c69737468696465626f74733d300a77617463686c697374686964656f776e3d300a77617463686c69737468696465616e6f6e733d300a77617463686c697374686964656c69753d300a77617463686c69737468696465706174726f6c6c65643d300a77617463686372656174696f6e733d300a776174636864656661756c743d300a77617463686d6f7665733d300a776174636864656c6574696f6e3d300a6e6f636f6e766572746c696e6b3d300a67656e6465723d756e6b6e6f776e0a76617269616e743d656e0a6c616e67756167653d656e0a7365617263684e73303d31, '20091009093224', '701f195b0e2c8b1e91632524a7b10e94', NULL, '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0', NULL, '20091009093126', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE IF NOT EXISTS `user_groups` (
  `ug_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ug_group` varbinary(16) NOT NULL DEFAULT '',
  UNIQUE KEY `ug_user_group` (`ug_user`,`ug_group`),
  KEY `ug_group` (`ug_group`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `user_groups`
--

INSERT INTO `user_groups` (`ug_user`, `ug_group`) VALUES
(1, 'bureaucrat'),
(1, 'sysop');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_newtalk`
--

DROP TABLE IF EXISTS `user_newtalk`;
CREATE TABLE IF NOT EXISTS `user_newtalk` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_ip` varbinary(40) NOT NULL DEFAULT '',
  `user_last_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  KEY `user_id` (`user_id`),
  KEY `user_ip` (`user_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `user_newtalk`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `valid_tag`
--

DROP TABLE IF EXISTS `valid_tag`;
CREATE TABLE IF NOT EXISTS `valid_tag` (
  `vt_tag` varbinary(255) NOT NULL,
  PRIMARY KEY (`vt_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `valid_tag`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `watchlist`
--

DROP TABLE IF EXISTS `watchlist`;
CREATE TABLE IF NOT EXISTS `watchlist` (
  `wl_user` int(10) unsigned NOT NULL,
  `wl_namespace` int(11) NOT NULL DEFAULT '0',
  `wl_title` varbinary(255) NOT NULL DEFAULT '',
  `wl_notificationtimestamp` varbinary(14) DEFAULT NULL,
  UNIQUE KEY `wl_user` (`wl_user`,`wl_namespace`,`wl_title`),
  KEY `namespace_title` (`wl_namespace`,`wl_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Daten für Tabelle `watchlist`
--

