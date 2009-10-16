-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.1.30-community


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema mw15_db
--

CREATE DATABASE IF NOT EXISTS testdb;
USE testdb;

--
-- Definition of table `archive`
--

DROP TABLE IF EXISTS `archive`;
CREATE TABLE `archive` (
  `ar_namespace` int(11) NOT NULL DEFAULT '0',
  `ar_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ar_text` mediumblob NOT NULL,
  `ar_comment` tinyblob NOT NULL,
  `ar_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ar_user_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `archive`
--

/*!40000 ALTER TABLE `archive` DISABLE KEYS */;
/*!40000 ALTER TABLE `archive` ENABLE KEYS */;


--
-- Definition of table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `cat_pages` int(11) NOT NULL DEFAULT '0',
  `cat_subcats` int(11) NOT NULL DEFAULT '0',
  `cat_files` int(11) NOT NULL DEFAULT '0',
  `cat_hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_title` (`cat_title`),
  KEY `cat_pages` (`cat_pages`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `category`
--

/*!40000 ALTER TABLE `category` DISABLE KEYS */;
/*!40000 ALTER TABLE `category` ENABLE KEYS */;


--
-- Definition of table `categorylinks`
--

DROP TABLE IF EXISTS `categorylinks`;
CREATE TABLE `categorylinks` (
  `cl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `cl_to` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `cl_sortkey` varchar(70) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `cl_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `cl_from` (`cl_from`,`cl_to`),
  KEY `cl_sortkey` (`cl_to`,`cl_sortkey`,`cl_from`),
  KEY `cl_timestamp` (`cl_to`,`cl_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categorylinks`
--

/*!40000 ALTER TABLE `categorylinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `categorylinks` ENABLE KEYS */;


--
-- Definition of table `change_tag`
--

DROP TABLE IF EXISTS `change_tag`;
CREATE TABLE `change_tag` (
  `ct_rc_id` int(11) DEFAULT NULL,
  `ct_log_id` int(11) DEFAULT NULL,
  `ct_rev_id` int(11) DEFAULT NULL,
  `ct_tag` varchar(255) NOT NULL,
  `ct_params` blob,
  UNIQUE KEY `change_tag_rc_tag` (`ct_rc_id`,`ct_tag`),
  UNIQUE KEY `change_tag_log_tag` (`ct_log_id`,`ct_tag`),
  UNIQUE KEY `change_tag_rev_tag` (`ct_rev_id`,`ct_tag`),
  KEY `change_tag_tag_id` (`ct_tag`,`ct_rc_id`,`ct_rev_id`,`ct_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `change_tag`
--

/*!40000 ALTER TABLE `change_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `change_tag` ENABLE KEYS */;


--
-- Definition of table `externallinks`
--

DROP TABLE IF EXISTS `externallinks`;
CREATE TABLE `externallinks` (
  `el_from` int(10) unsigned NOT NULL DEFAULT '0',
  `el_to` blob NOT NULL,
  `el_index` blob NOT NULL,
  KEY `el_from` (`el_from`,`el_to`(40)),
  KEY `el_to` (`el_to`(60),`el_from`),
  KEY `el_index` (`el_index`(60))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `externallinks`
--

/*!40000 ALTER TABLE `externallinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `externallinks` ENABLE KEYS */;


--
-- Definition of table `filearchive`
--

DROP TABLE IF EXISTS `filearchive`;
CREATE TABLE `filearchive` (
  `fa_id` int(11) NOT NULL AUTO_INCREMENT,
  `fa_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `fa_archive_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `fa_storage_group` varbinary(16) DEFAULT NULL,
  `fa_storage_key` varbinary(64) DEFAULT '',
  `fa_deleted_user` int(11) DEFAULT NULL,
  `fa_deleted_timestamp` binary(14) DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `fa_deleted_reason` text,
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
  `fa_user_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `fa_timestamp` binary(14) DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `fa_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fa_id`),
  KEY `fa_name` (`fa_name`,`fa_timestamp`),
  KEY `fa_storage_group` (`fa_storage_group`,`fa_storage_key`),
  KEY `fa_deleted_timestamp` (`fa_deleted_timestamp`),
  KEY `fa_user_timestamp` (`fa_user_text`,`fa_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `filearchive`
--

/*!40000 ALTER TABLE `filearchive` DISABLE KEYS */;
/*!40000 ALTER TABLE `filearchive` ENABLE KEYS */;


--
-- Definition of table `hitcounter`
--

DROP TABLE IF EXISTS `hitcounter`;
CREATE TABLE `hitcounter` (
  `hc_id` int(10) unsigned NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=latin1 MAX_ROWS=25000;

--
-- Dumping data for table `hitcounter`
--

/*!40000 ALTER TABLE `hitcounter` DISABLE KEYS */;
/*!40000 ALTER TABLE `hitcounter` ENABLE KEYS */;


--
-- Definition of table `image`
--

DROP TABLE IF EXISTS `image`;
CREATE TABLE `image` (
  `img_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
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
  `img_user_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `img_timestamp` varbinary(14) NOT NULL DEFAULT '',
  `img_sha1` varbinary(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`img_name`),
  KEY `img_usertext_timestamp` (`img_user_text`,`img_timestamp`),
  KEY `img_size` (`img_size`),
  KEY `img_timestamp` (`img_timestamp`),
  KEY `img_sha1` (`img_sha1`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `image`
--

/*!40000 ALTER TABLE `image` DISABLE KEYS */;
/*!40000 ALTER TABLE `image` ENABLE KEYS */;


--
-- Definition of table `imagelinks`
--

DROP TABLE IF EXISTS `imagelinks`;
CREATE TABLE `imagelinks` (
  `il_from` int(10) unsigned NOT NULL DEFAULT '0',
  `il_to` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  UNIQUE KEY `il_from` (`il_from`,`il_to`),
  UNIQUE KEY `il_to` (`il_to`,`il_from`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `imagelinks`
--

/*!40000 ALTER TABLE `imagelinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `imagelinks` ENABLE KEYS */;


--
-- Definition of table `interwiki`
--

DROP TABLE IF EXISTS `interwiki`;
CREATE TABLE `interwiki` (
  `iw_prefix` varchar(32) NOT NULL,
  `iw_url` blob NOT NULL,
  `iw_local` tinyint(1) NOT NULL,
  `iw_trans` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `iw_prefix` (`iw_prefix`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `interwiki`
--

/*!40000 ALTER TABLE `interwiki` DISABLE KEYS */;
INSERT INTO `interwiki` (`iw_prefix`,`iw_url`,`iw_local`,`iw_trans`) VALUES 
 ('acronym',0x687474703A2F2F7777772E6163726F6E796D66696E6465722E636F6D2F61662D71756572792E6173703F537472696E673D6578616374264163726F6E796D3D2431,0,0),
 ('advogato',0x687474703A2F2F7777772E6164766F6761746F2E6F72672F2431,0,0),
 ('annotationwiki',0x687474703A2F2F7777772E7365656477696B692E636F6D2F706167652E63666D3F77696B6969643D33363826646F633D2431,0,0),
 ('arxiv',0x687474703A2F2F7777772E61727869762E6F72672F6162732F2431,0,0),
 ('c2find',0x687474703A2F2F63322E636F6D2F6367692F77696B693F46696E64506167652676616C75653D2431,0,0),
 ('cache',0x687474703A2F2F7777772E676F6F676C652E636F6D2F7365617263683F713D63616368653A2431,0,0),
 ('commons',0x687474703A2F2F636F6D6D6F6E732E77696B696D656469612E6F72672F77696B692F2431,0,0),
 ('corpknowpedia',0x687474703A2F2F636F72706B6E6F7770656469612E6F72672F77696B692F696E6465782E7068702F2431,0,0),
 ('dictionary',0x687474703A2F2F7777772E646963742E6F72672F62696E2F446963743F44617461626173653D2A26466F726D3D44696374312653747261746567793D2A2651756572793D2431,0,0),
 ('disinfopedia',0x687474703A2F2F7777772E646973696E666F70656469612E6F72672F77696B692E7068746D6C3F7469746C653D2431,0,0),
 ('docbook',0x687474703A2F2F77696B692E646F63626F6F6B2E6F72672F746F7069632F2431,0,0),
 ('doi',0x687474703A2F2F64782E646F692E6F72672F2431,0,0),
 ('drumcorpswiki',0x687474703A2F2F7777772E6472756D636F72707377696B692E636F6D2F696E6465782E7068702F2431,0,0),
 ('dwjwiki',0x687474703A2F2F7777772E737562657269632E6E65742F6367692D62696E2F64776A2F77696B692E6367693F2431,0,0),
 ('emacswiki',0x687474703A2F2F7777772E656D61637377696B692E6F72672F6367692D62696E2F77696B692E706C3F2431,0,0),
 ('elibre',0x687474703A2F2F656E6369636C6F70656469612E75732E65732F696E6465782E7068702F2431,0,0),
 ('foldoc',0x687474703A2F2F666F6C646F632E6F72672F3F2431,0,0),
 ('foxwiki',0x687474703A2F2F666F782E77696B69732E636F6D2F77632E646C6C3F57696B697E2431,0,0),
 ('freebsdman',0x687474703A2F2F7777772E467265654253442E6F72672F6367692F6D616E2E6367693F6170726F706F733D312671756572793D2431,0,0),
 ('gej',0x687474703A2F2F7777772E6573706572616E746F2E64652F6367692D62696E2F616B746976696B696F2F77696B692E706C3F2431,0,0),
 ('gentoo-wiki',0x687474703A2F2F67656E746F6F2D77696B692E636F6D2F2431,0,0),
 ('google',0x687474703A2F2F7777772E676F6F676C652E636F6D2F7365617263683F713D2431,0,0),
 ('googlegroups',0x687474703A2F2F67726F7570732E676F6F676C652E636F6D2F67726F7570733F713D2431,0,0),
 ('hammondwiki',0x687474703A2F2F7777772E64616972696B692E6F72672F48616D6D6F6E6457696B692F2431,0,0),
 ('hewikisource',0x687474703A2F2F68652E77696B69736F757263652E6F72672F77696B692F2431,1,0),
 ('hrwiki',0x687474703A2F2F7777772E687277696B692E6F72672F696E6465782E7068702F2431,0,0),
 ('imdb',0x687474703A2F2F75732E696D64622E636F6D2F5469746C653F2431,0,0),
 ('jargonfile',0x687474703A2F2F73756E69722E6F72672F617070732F6D6574612E706C3F77696B693D4A6172676F6E46696C652672656469726563743D2431,0,0),
 ('jspwiki',0x687474703A2F2F7777772E6A737077696B692E6F72672F77696B692F2431,0,0),
 ('keiki',0x687474703A2F2F6B65692E6B692F656E2F2431,0,0),
 ('kmwiki',0x687474703A2F2F6B6D77696B692E77696B697370616365732E636F6D2F2431,0,0),
 ('linuxwiki',0x687474703A2F2F6C696E757877696B692E64652F2431,0,0),
 ('lojban',0x687474703A2F2F7777772E6C6F6A62616E2E6F72672F74696B692F74696B692D696E6465782E7068703F706167653D2431,0,0),
 ('lqwiki',0x687474703A2F2F77696B692E6C696E75787175657374696F6E732E6F72672F77696B692F2431,0,0),
 ('lugkr',0x687474703A2F2F6C75672D6B722E736F75726365666F7267652E6E65742F6367692D62696E2F6C756777696B692E706C3F2431,0,0),
 ('mathsongswiki',0x687474703A2F2F5365656457696B692E636F6D2F706167652E63666D3F77696B6969643D32333726646F633D2431,0,0),
 ('meatball',0x687474703A2F2F7777772E7573656D6F642E636F6D2F6367692D62696E2F6D622E706C3F2431,0,0),
 ('mediazilla',0x687474703A2F2F6275677A696C6C612E77696B6970656469612E6F72672F2431,1,0),
 ('mediawikiwiki',0x687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F2431,0,0),
 ('memoryalpha',0x687474703A2F2F7777772E6D656D6F72792D616C7068612E6F72672F656E2F696E6465782E7068702F2431,0,0),
 ('metawiki',0x687474703A2F2F73756E69722E6F72672F617070732F6D6574612E706C3F2431,0,0),
 ('metawikipedia',0x687474703A2F2F6D6574612E77696B696D656469612E6F72672F77696B692F2431,0,0),
 ('moinmoin',0x687474703A2F2F7075726C2E6E65742F77696B692F6D6F696E2F2431,0,0),
 ('mozillawiki',0x687474703A2F2F77696B692E6D6F7A696C6C612E6F72672F696E6465782E7068702F2431,0,0),
 ('oeis',0x687474703A2F2F7777772E72657365617263682E6174742E636F6D2F6367692D62696E2F6163636573732E6367692F61732F6E6A61732F73657175656E6365732F656973412E6367693F416E756D3D2431,0,0),
 ('openfacts',0x687474703A2F2F6F70656E66616374732E6265726C696F732E64652F696E6465782E7068746D6C3F7469746C653D2431,0,0),
 ('openwiki',0x687474703A2F2F6F70656E77696B692E636F6D2F3F2431,0,0),
 ('patwiki',0x687474703A2F2F67617573732E666669692E6F72672F2431,0,0),
 ('pmeg',0x687474703A2F2F7777772E62657274696C6F772E636F6D2F706D65672F24312E706870,0,0),
 ('ppr',0x687474703A2F2F63322E636F6D2F6367692F77696B693F2431,0,0),
 ('pythoninfo',0x687474703A2F2F77696B692E707974686F6E2E6F72672F6D6F696E2F2431,0,0),
 ('rfc',0x687474703A2F2F7777772E7266632D656469746F722E6F72672F7266632F72666324312E747874,0,0),
 ('s23wiki',0x687474703A2F2F69732D726F6F742E64652F77696B692F696E6465782E7068702F2431,0,0),
 ('seattlewiki',0x687474703A2F2F73656174746C652E77696B69612E636F6D2F77696B692F2431,0,0),
 ('seattlewireless',0x687474703A2F2F73656174746C65776972656C6573732E6E65742F3F2431,0,0),
 ('senseislibrary',0x687474703A2F2F73656E736569732E786D702E6E65742F3F2431,0,0),
 ('slashdot',0x687474703A2F2F736C617368646F742E6F72672F61727469636C652E706C3F7369643D2431,0,0),
 ('sourceforge',0x687474703A2F2F736F75726365666F7267652E6E65742F2431,0,0),
 ('squeak',0x687474703A2F2F77696B692E73717565616B2E6F72672F73717565616B2F2431,0,0),
 ('susning',0x687474703A2F2F7777772E7375736E696E672E6E752F2431,0,0),
 ('svgwiki',0x687474703A2F2F77696B692E7376672E6F72672F2431,0,0),
 ('tavi',0x687474703A2F2F746176692E736F75726365666F7267652E6E65742F2431,0,0),
 ('tejo',0x687474703A2F2F7777772E74656A6F2E6F72672F76696B696F2F2431,0,0),
 ('tmbw',0x687474703A2F2F7777772E746D62772E6E65742F77696B692F2431,0,0),
 ('tmnet',0x687474703A2F2F7777772E746563686E6F6D616E69666573746F732E6E65742F3F2431,0,0),
 ('tmwiki',0x687474703A2F2F7777772E45617379546F7069634D6170732E636F6D2F3F706167653D2431,0,0),
 ('theopedia',0x687474703A2F2F7777772E7468656F70656469612E636F6D2F2431,0,0),
 ('twiki',0x687474703A2F2F7477696B692E6F72672F6367692D62696E2F766965772F2431,0,0),
 ('uea',0x687474703A2F2F7777772E74656A6F2E6F72672F7565612F2431,0,0),
 ('unreal',0x687474703A2F2F77696B692E6265796F6E64756E7265616C2E636F6D2F77696B692F2431,0,0),
 ('usemod',0x687474703A2F2F7777772E7573656D6F642E636F6D2F6367692D62696E2F77696B692E706C3F2431,0,0),
 ('vinismo',0x687474703A2F2F76696E69736D6F2E636F6D2F656E2F2431,0,0),
 ('webseitzwiki',0x687474703A2F2F776562736569747A2E666C7578656E742E636F6D2F77696B692F2431,0,0),
 ('why',0x687474703A2F2F636C75626C65742E636F6D2F632F632F7768793F2431,0,0),
 ('wiki',0x687474703A2F2F63322E636F6D2F6367692F77696B693F2431,0,0),
 ('wikia',0x687474703A2F2F7777772E77696B69612E636F6D2F77696B692F2431,0,0),
 ('wikibooks',0x687474703A2F2F656E2E77696B69626F6F6B732E6F72672F77696B692F2431,1,0),
 ('wikicities',0x687474703A2F2F7777772E77696B696369746965732E636F6D2F696E6465782E7068702F2431,0,0),
 ('wikif1',0x687474703A2F2F7777772E77696B6966312E6F72672F2431,0,0),
 ('wikihow',0x687474703A2F2F7777772E77696B69686F772E636F6D2F2431,0,0),
 ('wikinfo',0x687474703A2F2F7777772E77696B696E666F2E6F72672F696E6465782E7068702F2431,0,0),
 ('wikimedia',0x687474703A2F2F77696B696D65646961666F756E646174696F6E2E6F72672F77696B692F2431,0,0),
 ('wikiquote',0x687474703A2F2F656E2E77696B6971756F74652E6F72672F77696B692F2431,1,0),
 ('wikinews',0x687474703A2F2F656E2E77696B696E6577732E6F72672F77696B692F2431,1,0),
 ('wikisource',0x687474703A2F2F736F75726365732E77696B6970656469612E6F72672F77696B692F2431,1,0),
 ('wikispecies',0x687474703A2F2F737065636965732E77696B6970656469612E6F72672F77696B692F2431,1,0),
 ('wikitravel',0x687474703A2F2F77696B6974726176656C2E6F72672F656E2F2431,0,0),
 ('wiktionary',0x687474703A2F2F656E2E77696B74696F6E6172792E6F72672F77696B692F2431,1,0),
 ('wikipedia',0x687474703A2F2F656E2E77696B6970656469612E6F72672F77696B692F2431,1,0),
 ('wlug',0x687474703A2F2F7777772E776C75672E6F72672E6E7A2F2431,0,0),
 ('zwiki',0x687474703A2F2F7A77696B692E6F72672F2431,0,0),
 ('zzz wiki',0x687474703A2F2F77696B692E7A7A7A2E65652F696E6465782E7068702F2431,0,0),
 ('wikt',0x687474703A2F2F656E2E77696B74696F6E6172792E6F72672F77696B692F2431,1,0);
/*!40000 ALTER TABLE `interwiki` ENABLE KEYS */;


--
-- Definition of table `ipblocks`
--

DROP TABLE IF EXISTS `ipblocks`;
CREATE TABLE `ipblocks` (
  `ipb_id` int(11) NOT NULL AUTO_INCREMENT,
  `ipb_address` tinyblob NOT NULL,
  `ipb_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ipb_by` int(10) unsigned NOT NULL DEFAULT '0',
  `ipb_by_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ipblocks`
--

/*!40000 ALTER TABLE `ipblocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ipblocks` ENABLE KEYS */;


--
-- Definition of table `job`
--

DROP TABLE IF EXISTS `job`;
CREATE TABLE `job` (
  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_cmd` varbinary(60) NOT NULL DEFAULT '',
  `job_namespace` int(11) NOT NULL,
  `job_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `job_params` blob NOT NULL,
  PRIMARY KEY (`job_id`),
  KEY `job_cmd` (`job_cmd`,`job_namespace`,`job_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `job`
--

/*!40000 ALTER TABLE `job` DISABLE KEYS */;
/*!40000 ALTER TABLE `job` ENABLE KEYS */;


--
-- Definition of table `langlinks`
--

DROP TABLE IF EXISTS `langlinks`;
CREATE TABLE `langlinks` (
  `ll_from` int(10) unsigned NOT NULL DEFAULT '0',
  `ll_lang` varbinary(20) NOT NULL DEFAULT '',
  `ll_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  UNIQUE KEY `ll_from` (`ll_from`,`ll_lang`),
  KEY `ll_lang` (`ll_lang`,`ll_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `langlinks`
--

/*!40000 ALTER TABLE `langlinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `langlinks` ENABLE KEYS */;


--
-- Definition of table `logging`
--

DROP TABLE IF EXISTS `logging`;
CREATE TABLE `logging` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varbinary(10) NOT NULL DEFAULT '',
  `log_action` varbinary(10) NOT NULL DEFAULT '',
  `log_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  `log_user` int(10) unsigned NOT NULL DEFAULT '0',
  `log_namespace` int(11) NOT NULL DEFAULT '0',
  `log_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `log_comment` varchar(255) NOT NULL DEFAULT '',
  `log_params` blob NOT NULL,
  `log_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `type_time` (`log_type`,`log_timestamp`),
  KEY `user_time` (`log_user`,`log_timestamp`),
  KEY `page_time` (`log_namespace`,`log_title`,`log_timestamp`),
  KEY `times` (`log_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `logging`
--

/*!40000 ALTER TABLE `logging` DISABLE KEYS */;
/*!40000 ALTER TABLE `logging` ENABLE KEYS */;


--
-- Definition of table `math`
--

DROP TABLE IF EXISTS `math`;
CREATE TABLE `math` (
  `math_inputhash` varbinary(16) NOT NULL,
  `math_outputhash` varbinary(16) NOT NULL,
  `math_html_conservativeness` tinyint(4) NOT NULL,
  `math_html` text,
  `math_mathml` text,
  UNIQUE KEY `math_inputhash` (`math_inputhash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `math`
--

/*!40000 ALTER TABLE `math` DISABLE KEYS */;
/*!40000 ALTER TABLE `math` ENABLE KEYS */;


--
-- Definition of table `objectcache`
--

DROP TABLE IF EXISTS `objectcache`;
CREATE TABLE `objectcache` (
  `keyname` varbinary(255) NOT NULL DEFAULT '',
  `value` mediumblob,
  `exptime` datetime DEFAULT NULL,
  PRIMARY KEY (`keyname`),
  KEY `exptime` (`exptime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `objectcache`
--

/*!40000 ALTER TABLE `objectcache` DISABLE KEYS */;
/*!40000 ALTER TABLE `objectcache` ENABLE KEYS */;


--
-- Definition of table `oldimage`
--

DROP TABLE IF EXISTS `oldimage`;
CREATE TABLE `oldimage` (
  `oi_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `oi_archive_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `oi_size` int(10) unsigned NOT NULL DEFAULT '0',
  `oi_width` int(11) NOT NULL DEFAULT '0',
  `oi_height` int(11) NOT NULL DEFAULT '0',
  `oi_bits` int(11) NOT NULL DEFAULT '0',
  `oi_description` tinyblob NOT NULL,
  `oi_user` int(10) unsigned NOT NULL DEFAULT '0',
  `oi_user_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `oldimage`
--

/*!40000 ALTER TABLE `oldimage` DISABLE KEYS */;
/*!40000 ALTER TABLE `oldimage` ENABLE KEYS */;


--
-- Definition of table `page`
--

DROP TABLE IF EXISTS `page`;
CREATE TABLE `page` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_namespace` int(11) NOT NULL,
  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page`
--

/*!40000 ALTER TABLE `page` DISABLE KEYS */;
INSERT INTO `page` (`page_id`,`page_namespace`,`page_title`,`page_restrictions`,`page_counter`,`page_is_redirect`,`page_is_new`,`page_random`,`page_touched`,`page_latest`,`page_len`) VALUES 
 (1,0,0x4D61696E5F50616765,'',0,0,0,0.933958079035,0x3230303931303136313033393233,1,449);
/*!40000 ALTER TABLE `page` ENABLE KEYS */;


--
-- Definition of table `page_props`
--

DROP TABLE IF EXISTS `page_props`;
CREATE TABLE `page_props` (
  `pp_page` int(11) NOT NULL,
  `pp_propname` varbinary(60) NOT NULL,
  `pp_value` blob NOT NULL,
  UNIQUE KEY `pp_page_propname` (`pp_page`,`pp_propname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_props`
--

/*!40000 ALTER TABLE `page_props` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_props` ENABLE KEYS */;


--
-- Definition of table `page_restrictions`
--

DROP TABLE IF EXISTS `page_restrictions`;
CREATE TABLE `page_restrictions` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `page_restrictions`
--

/*!40000 ALTER TABLE `page_restrictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_restrictions` ENABLE KEYS */;


--
-- Definition of table `pagelinks`
--

DROP TABLE IF EXISTS `pagelinks`;
CREATE TABLE `pagelinks` (
  `pl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `pl_namespace` int(11) NOT NULL DEFAULT '0',
  `pl_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  UNIQUE KEY `pl_from` (`pl_from`,`pl_namespace`,`pl_title`),
  UNIQUE KEY `pl_namespace` (`pl_namespace`,`pl_title`,`pl_from`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pagelinks`
--

/*!40000 ALTER TABLE `pagelinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagelinks` ENABLE KEYS */;


--
-- Definition of table `protected_titles`
--

DROP TABLE IF EXISTS `protected_titles`;
CREATE TABLE `protected_titles` (
  `pt_namespace` int(11) NOT NULL,
  `pt_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `pt_user` int(10) unsigned NOT NULL,
  `pt_reason` tinyblob,
  `pt_timestamp` binary(14) NOT NULL,
  `pt_expiry` varbinary(14) NOT NULL DEFAULT '',
  `pt_create_perm` varbinary(60) NOT NULL,
  UNIQUE KEY `pt_namespace_title` (`pt_namespace`,`pt_title`),
  KEY `pt_timestamp` (`pt_timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `protected_titles`
--

/*!40000 ALTER TABLE `protected_titles` DISABLE KEYS */;
/*!40000 ALTER TABLE `protected_titles` ENABLE KEYS */;


--
-- Definition of table `querycache`
--

DROP TABLE IF EXISTS `querycache`;
CREATE TABLE `querycache` (
  `qc_type` varbinary(32) NOT NULL,
  `qc_value` int(10) unsigned NOT NULL DEFAULT '0',
  `qc_namespace` int(11) NOT NULL DEFAULT '0',
  `qc_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  KEY `qc_type` (`qc_type`,`qc_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `querycache`
--

/*!40000 ALTER TABLE `querycache` DISABLE KEYS */;
/*!40000 ALTER TABLE `querycache` ENABLE KEYS */;


--
-- Definition of table `querycache_info`
--

DROP TABLE IF EXISTS `querycache_info`;
CREATE TABLE `querycache_info` (
  `qci_type` varbinary(32) NOT NULL DEFAULT '',
  `qci_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  UNIQUE KEY `qci_type` (`qci_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `querycache_info`
--

/*!40000 ALTER TABLE `querycache_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `querycache_info` ENABLE KEYS */;


--
-- Definition of table `querycachetwo`
--

DROP TABLE IF EXISTS `querycachetwo`;
CREATE TABLE `querycachetwo` (
  `qcc_type` varbinary(32) NOT NULL,
  `qcc_value` int(10) unsigned NOT NULL DEFAULT '0',
  `qcc_namespace` int(11) NOT NULL DEFAULT '0',
  `qcc_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `qcc_namespacetwo` int(11) NOT NULL DEFAULT '0',
  `qcc_titletwo` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  KEY `qcc_type` (`qcc_type`,`qcc_value`),
  KEY `qcc_title` (`qcc_type`,`qcc_namespace`,`qcc_title`),
  KEY `qcc_titletwo` (`qcc_type`,`qcc_namespacetwo`,`qcc_titletwo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `querycachetwo`
--

/*!40000 ALTER TABLE `querycachetwo` DISABLE KEYS */;
/*!40000 ALTER TABLE `querycachetwo` ENABLE KEYS */;


--
-- Definition of table `recentchanges`
--

DROP TABLE IF EXISTS `recentchanges`;
CREATE TABLE `recentchanges` (
  `rc_id` int(11) NOT NULL AUTO_INCREMENT,
  `rc_timestamp` varbinary(14) NOT NULL DEFAULT '',
  `rc_cur_time` varbinary(14) NOT NULL DEFAULT '',
  `rc_user` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_user_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `rc_namespace` int(11) NOT NULL DEFAULT '0',
  `rc_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `rc_comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `rc_minor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_bot` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_cur_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_this_oldid` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_last_oldid` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_moved_to_ns` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_moved_to_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `recentchanges`
--

/*!40000 ALTER TABLE `recentchanges` DISABLE KEYS */;
/*!40000 ALTER TABLE `recentchanges` ENABLE KEYS */;


--
-- Definition of table `redirect`
--

DROP TABLE IF EXISTS `redirect`;
CREATE TABLE `redirect` (
  `rd_from` int(10) unsigned NOT NULL DEFAULT '0',
  `rd_namespace` int(11) NOT NULL DEFAULT '0',
  `rd_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`rd_from`),
  KEY `rd_ns_title` (`rd_namespace`,`rd_title`,`rd_from`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `redirect`
--

/*!40000 ALTER TABLE `redirect` DISABLE KEYS */;
/*!40000 ALTER TABLE `redirect` ENABLE KEYS */;


--
-- Definition of table `revision`
--

DROP TABLE IF EXISTS `revision`;
CREATE TABLE `revision` (
  `rev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rev_page` int(10) unsigned NOT NULL,
  `rev_text_id` int(10) unsigned NOT NULL,
  `rev_comment` tinyblob NOT NULL,
  `rev_user` int(10) unsigned NOT NULL DEFAULT '0',
  `rev_user_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 MAX_ROWS=10000000 AVG_ROW_LENGTH=1024;

--
-- Dumping data for table `revision`
--

/*!40000 ALTER TABLE `revision` DISABLE KEYS */;
INSERT INTO `revision` (`rev_id`,`rev_page`,`rev_text_id`,`rev_comment`,`rev_user`,`rev_user_text`,`rev_timestamp`,`rev_minor_edit`,`rev_deleted`,`rev_len`,`rev_parent_id`) VALUES 
 (1,1,1,'',0,0x4D6564696157696B692064656661756C74,0x3230303931303136313033393233,0,0,449,0);
/*!40000 ALTER TABLE `revision` ENABLE KEYS */;


--
-- Definition of table `searchindex`
--

DROP TABLE IF EXISTS `searchindex`;
CREATE TABLE `searchindex` (
  `si_page` int(10) unsigned NOT NULL,
  `si_title` varchar(255) NOT NULL DEFAULT '',
  `si_text` mediumtext NOT NULL,
  UNIQUE KEY `si_page` (`si_page`),
  FULLTEXT KEY `si_title` (`si_title`),
  FULLTEXT KEY `si_text` (`si_text`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `searchindex`
--

/*!40000 ALTER TABLE `searchindex` DISABLE KEYS */;
/*!40000 ALTER TABLE `searchindex` ENABLE KEYS */;


--
-- Definition of table `site_stats`
--

DROP TABLE IF EXISTS `site_stats`;
CREATE TABLE `site_stats` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `site_stats`
--

/*!40000 ALTER TABLE `site_stats` DISABLE KEYS */;
INSERT INTO `site_stats` (`ss_row_id`,`ss_total_views`,`ss_total_edits`,`ss_good_articles`,`ss_total_pages`,`ss_users`,`ss_active_users`,`ss_admins`,`ss_images`) VALUES 
 (1,0,1,0,1,1,-1,1,0);
/*!40000 ALTER TABLE `site_stats` ENABLE KEYS */;


--
-- Definition of table `tag_summary`
--

DROP TABLE IF EXISTS `tag_summary`;
CREATE TABLE `tag_summary` (
  `ts_rc_id` int(11) DEFAULT NULL,
  `ts_log_id` int(11) DEFAULT NULL,
  `ts_rev_id` int(11) DEFAULT NULL,
  `ts_tags` blob NOT NULL,
  UNIQUE KEY `tag_summary_rc_id` (`ts_rc_id`),
  UNIQUE KEY `tag_summary_log_id` (`ts_log_id`),
  UNIQUE KEY `tag_summary_rev_id` (`ts_rev_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tag_summary`
--

/*!40000 ALTER TABLE `tag_summary` DISABLE KEYS */;
/*!40000 ALTER TABLE `tag_summary` ENABLE KEYS */;


--
-- Definition of table `templatelinks`
--

DROP TABLE IF EXISTS `templatelinks`;
CREATE TABLE `templatelinks` (
  `tl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `tl_namespace` int(11) NOT NULL DEFAULT '0',
  `tl_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  UNIQUE KEY `tl_from` (`tl_from`,`tl_namespace`,`tl_title`),
  UNIQUE KEY `tl_namespace` (`tl_namespace`,`tl_title`,`tl_from`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `templatelinks`
--

/*!40000 ALTER TABLE `templatelinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `templatelinks` ENABLE KEYS */;


--
-- Definition of table `text`
--

DROP TABLE IF EXISTS `text`;
CREATE TABLE `text` (
  `old_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_text` mediumblob NOT NULL,
  `old_flags` tinyblob NOT NULL,
  PRIMARY KEY (`old_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 MAX_ROWS=10000000 AVG_ROW_LENGTH=10240;

--
-- Dumping data for table `text`
--

/*!40000 ALTER TABLE `text` DISABLE KEYS */;
INSERT INTO `text` (`old_id`,`old_text`,`old_flags`) VALUES 
 (1,0x3C6269673E2727274D6564696157696B6920686173206265656E207375636365737366756C6C7920696E7374616C6C65642E2727273C2F6269673E0A0A436F6E73756C7420746865205B687474703A2F2F6D6574612E77696B696D656469612E6F72672F77696B692F48656C703A436F6E74656E7473205573657227732047756964655D20666F7220696E666F726D6174696F6E206F6E207573696E67207468652077696B6920736F6674776172652E0A0A3D3D2047657474696E672073746172746564203D3D0A2A205B687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F4D616E75616C3A436F6E66696775726174696F6E5F73657474696E677320436F6E66696775726174696F6E2073657474696E6773206C6973745D0A2A205B687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F4D616E75616C3A464151204D6564696157696B69204641515D0A2A205B68747470733A2F2F6C697374732E77696B696D656469612E6F72672F6D61696C6D616E2F6C697374696E666F2F6D6564696177696B692D616E6E6F756E6365204D6564696157696B692072656C65617365206D61696C696E67206C6973745D,0x7574662D38);
/*!40000 ALTER TABLE `text` ENABLE KEYS */;


--
-- Definition of table `trackbacks`
--

DROP TABLE IF EXISTS `trackbacks`;
CREATE TABLE `trackbacks` (
  `tb_id` int(11) NOT NULL AUTO_INCREMENT,
  `tb_page` int(11) DEFAULT NULL,
  `tb_title` varchar(255) NOT NULL,
  `tb_url` blob NOT NULL,
  `tb_ex` text,
  `tb_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tb_id`),
  KEY `tb_page` (`tb_page`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `trackbacks`
--

/*!40000 ALTER TABLE `trackbacks` DISABLE KEYS */;
/*!40000 ALTER TABLE `trackbacks` ENABLE KEYS */;


--
-- Definition of table `transcache`
--

DROP TABLE IF EXISTS `transcache`;
CREATE TABLE `transcache` (
  `tc_url` varbinary(255) NOT NULL,
  `tc_contents` text,
  `tc_time` int(11) NOT NULL,
  UNIQUE KEY `tc_url_idx` (`tc_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `transcache`
--

/*!40000 ALTER TABLE `transcache` DISABLE KEYS */;
/*!40000 ALTER TABLE `transcache` ENABLE KEYS */;


--
-- Definition of table `updatelog`
--

DROP TABLE IF EXISTS `updatelog`;
CREATE TABLE `updatelog` (
  `ul_key` varchar(255) NOT NULL,
  PRIMARY KEY (`ul_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `updatelog`
--

/*!40000 ALTER TABLE `updatelog` DISABLE KEYS */;
/*!40000 ALTER TABLE `updatelog` ENABLE KEYS */;


--
-- Definition of table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_real_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_password` tinyblob NOT NULL,
  `user_newpassword` tinyblob NOT NULL,
  `user_newpass_time` binary(14) DEFAULT NULL,
  `user_email` tinytext NOT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`user_id`,`user_name`,`user_real_name`,`user_password`,`user_newpassword`,`user_newpass_time`,`user_email`,`user_options`,`user_touched`,`user_token`,`user_email_authenticated`,`user_email_token`,`user_email_token_expires`,`user_registration`,`user_editcount`) VALUES 
 (1,0x57696B695379736F70,'',0x3A423A32656536616539373A3639366236366561376630326234393062376437656335383866396462313835,'',NULL,'',0x717569636B6261723D310A756E6465726C696E653D320A636F6C733D38300A726F77733D32350A7365617263686C696D69743D32300A636F6E746578746C696E65733D350A636F6E7465787463686172733D35300A64697361626C65737567676573743D300A736B696E3D0A6D6174683D310A7573656E657772633D300A7263646179733D370A72636C696D69743D35300A776C6C696D69743D3235300A686964656D696E6F723D300A68696465706174726F6C6C65643D300A6E6577706167657368696465706174726F6C6C65643D300A686967686C6967687462726F6B656E3D310A737475627468726573686F6C643D300A707265766965776F6E746F703D310A707265766965776F6E66697273743D300A6564697473656374696F6E3D310A6564697473656374696F6E6F6E7269676874636C69636B3D300A656469746F6E64626C636C69636B3D300A6564697477696474683D300A73686F77746F633D310A73686F77746F6F6C6261723D310A6D696E6F7264656661756C743D300A646174653D64656661756C740A696D61676573697A653D320A7468756D6273697A653D320A72656D656D62657270617373776F72643D300A6E6F63616368653D300A646966666F6E6C793D300A73686F7768696464656E636174733D300A6E6F726F6C6C6261636B646966663D300A656E6F74696677617463686C69737470616765733D300A656E6F7469667573657274616C6B70616765733D310A656E6F7469666D696E6F7265646974733D300A656E6F74696672657665616C616464723D300A73686F776E756D626572737761746368696E673D310A66616E63797369673D300A65787465726E616C656469746F723D300A65787465726E616C646966663D300A666F7263656564697473756D6D6172793D300A73686F776A756D706C696E6B733D310A6A7573746966793D300A6E756D62657268656164696E67733D300A7573656C697665707265766965773D300A77617463686C697374646179733D330A657874656E6477617463686C6973743D300A77617463686C697374686964656D696E6F723D300A77617463686C69737468696465626F74733D300A77617463686C697374686964656F776E3D300A77617463686C69737468696465616E6F6E733D300A77617463686C697374686964656C69753D300A77617463686C69737468696465706174726F6C6C65643D300A77617463686372656174696F6E733D300A776174636864656661756C743D300A77617463686D6F7665733D300A776174636864656C6574696F6E3D300A6E6F636F6E766572746C696E6B3D300A67656E6465723D756E6B6E6F776E0A76617269616E743D656E0A6C616E67756167653D656E0A7365617263684E73303D31,0x3230303931303136313033393238,0x6666626665306531306462366539343439383332613435313265653930666532,NULL,0x0000000000000000000000000000000000000000000000000000000000000000,NULL,0x3230303931303136313033393233,0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;


--
-- Definition of table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE `user_groups` (
  `ug_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ug_group` varbinary(16) NOT NULL DEFAULT '',
  UNIQUE KEY `ug_user_group` (`ug_user`,`ug_group`),
  KEY `ug_group` (`ug_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_groups`
--

/*!40000 ALTER TABLE `user_groups` DISABLE KEYS */;
INSERT INTO `user_groups` (`ug_user`,`ug_group`) VALUES 
 (1,0x62757265617563726174),
 (1,0x7379736F70);
/*!40000 ALTER TABLE `user_groups` ENABLE KEYS */;


--
-- Definition of table `user_newtalk`
--

DROP TABLE IF EXISTS `user_newtalk`;
CREATE TABLE `user_newtalk` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_ip` varbinary(40) NOT NULL DEFAULT '',
  `user_last_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  KEY `user_id` (`user_id`),
  KEY `user_ip` (`user_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_newtalk`
--

/*!40000 ALTER TABLE `user_newtalk` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_newtalk` ENABLE KEYS */;


--
-- Definition of table `valid_tag`
--

DROP TABLE IF EXISTS `valid_tag`;
CREATE TABLE `valid_tag` (
  `vt_tag` varchar(255) NOT NULL,
  PRIMARY KEY (`vt_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `valid_tag`
--

/*!40000 ALTER TABLE `valid_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `valid_tag` ENABLE KEYS */;


--
-- Definition of table `watchlist`
--

DROP TABLE IF EXISTS `watchlist`;
CREATE TABLE `watchlist` (
  `wl_user` int(10) unsigned NOT NULL,
  `wl_namespace` int(11) NOT NULL DEFAULT '0',
  `wl_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `wl_notificationtimestamp` varbinary(14) DEFAULT NULL,
  UNIQUE KEY `wl_user` (`wl_user`,`wl_namespace`,`wl_title`),
  KEY `namespace_title` (`wl_namespace`,`wl_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `watchlist`
--

/*!40000 ALTER TABLE `watchlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `watchlist` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
