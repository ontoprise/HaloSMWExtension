-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.1.49-community


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema empty_mw117
--

CREATE DATABASE IF NOT EXISTS empty_mw117;
USE empty_mw117;

--
-- Definition of table `archive`
--

DROP TABLE IF EXISTS `archive`;
CREATE TABLE `archive` (
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
  KEY `usertext_timestamp` (`ar_user_text`,`ar_timestamp`),
  KEY `ar_revid` (`ar_rev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `cat_title` varbinary(255) NOT NULL,
  `cat_pages` int(11) NOT NULL DEFAULT '0',
  `cat_subcats` int(11) NOT NULL DEFAULT '0',
  `cat_files` int(11) NOT NULL DEFAULT '0',
  `cat_hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_title` (`cat_title`),
  KEY `cat_pages` (`cat_pages`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `cl_to` varbinary(255) NOT NULL DEFAULT '',
  `cl_sortkey` varbinary(230) NOT NULL DEFAULT '',
  `cl_sortkey_prefix` varbinary(255) NOT NULL DEFAULT '',
  `cl_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cl_collation` varbinary(32) NOT NULL DEFAULT '',
  `cl_type` enum('page','subcat','file') NOT NULL DEFAULT 'page',
  UNIQUE KEY `cl_from` (`cl_from`,`cl_to`),
  KEY `cl_sortkey` (`cl_to`,`cl_type`,`cl_sortkey`,`cl_from`),
  KEY `cl_timestamp` (`cl_to`,`cl_timestamp`),
  KEY `cl_collation` (`cl_collation`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `ct_tag` varbinary(255) NOT NULL,
  `ct_params` blob,
  UNIQUE KEY `change_tag_rc_tag` (`ct_rc_id`,`ct_tag`),
  UNIQUE KEY `change_tag_log_tag` (`ct_log_id`,`ct_tag`),
  UNIQUE KEY `change_tag_rev_tag` (`ct_rev_id`,`ct_tag`),
  KEY `change_tag_tag_id` (`ct_tag`,`ct_rc_id`,`ct_rev_id`,`ct_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `change_tag`
--

/*!40000 ALTER TABLE `change_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `change_tag` ENABLE KEYS */;


--
-- Definition of table `external_user`
--

DROP TABLE IF EXISTS `external_user`;
CREATE TABLE `external_user` (
  `eu_local_id` int(10) unsigned NOT NULL,
  `eu_external_id` varbinary(255) NOT NULL,
  PRIMARY KEY (`eu_local_id`),
  UNIQUE KEY `eu_external_id` (`eu_external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `external_user`
--

/*!40000 ALTER TABLE `external_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `external_user` ENABLE KEYS */;


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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `externallinks`
--

/*!40000 ALTER TABLE `externallinks` DISABLE KEYS */;
INSERT INTO `externallinks` (`el_from`,`el_to`,`el_index`) VALUES 
 (1,0x687474703A2F2F6D6574612E77696B696D656469612E6F72672F77696B692F48656C703A436F6E74656E7473,0x687474703A2F2F6F72672E77696B696D656469612E6D6574612E2F77696B692F48656C703A436F6E74656E7473),
 (1,0x687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F4D616E75616C3A436F6E66696775726174696F6E5F73657474696E6773,0x687474703A2F2F6F72672E6D6564696177696B692E7777772E2F77696B692F4D616E75616C3A436F6E66696775726174696F6E5F73657474696E6773),
 (1,0x687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F4D616E75616C3A464151,0x687474703A2F2F6F72672E6D6564696177696B692E7777772E2F77696B692F4D616E75616C3A464151),
 (1,0x68747470733A2F2F6C697374732E77696B696D656469612E6F72672F6D61696C6D616E2F6C697374696E666F2F6D6564696177696B692D616E6E6F756E6365,0x68747470733A2F2F6F72672E77696B696D656469612E6C697374732E2F6D61696C6D616E2F6C697374696E666F2F6D6564696177696B692D616E6E6F756E6365);
/*!40000 ALTER TABLE `externallinks` ENABLE KEYS */;


--
-- Definition of table `filearchive`
--

DROP TABLE IF EXISTS `filearchive`;
CREATE TABLE `filearchive` (
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
  `fa_minor_mime` varbinary(100) DEFAULT 'unknown',
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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `img_name` varbinary(255) NOT NULL DEFAULT '',
  `img_size` int(10) unsigned NOT NULL DEFAULT '0',
  `img_width` int(11) NOT NULL DEFAULT '0',
  `img_height` int(11) NOT NULL DEFAULT '0',
  `img_metadata` mediumblob NOT NULL,
  `img_bits` int(11) NOT NULL DEFAULT '0',
  `img_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE') DEFAULT NULL,
  `img_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart') NOT NULL DEFAULT 'unknown',
  `img_minor_mime` varbinary(100) NOT NULL DEFAULT 'unknown',
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
  `il_to` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `il_from` (`il_from`,`il_to`),
  UNIQUE KEY `il_to` (`il_to`,`il_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `iw_prefix` varbinary(32) NOT NULL,
  `iw_url` blob NOT NULL,
  `iw_api` blob NOT NULL,
  `iw_wikiid` varbinary(64) NOT NULL,
  `iw_local` tinyint(1) NOT NULL,
  `iw_trans` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `iw_prefix` (`iw_prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `interwiki`
--

/*!40000 ALTER TABLE `interwiki` DISABLE KEYS */;
INSERT INTO `interwiki` (`iw_prefix`,`iw_url`,`iw_api`,`iw_wikiid`,`iw_local`,`iw_trans`) VALUES 
 (0x6163726F6E796D,0x687474703A2F2F7777772E6163726F6E796D66696E6465722E636F6D2F61662D71756572792E6173703F537472696E673D6578616374264163726F6E796D3D2431,'','',0,0),
 (0x6164766F6761746F,0x687474703A2F2F7777772E6164766F6761746F2E6F72672F2431,'','',0,0),
 (0x616E6E6F746174696F6E77696B69,0x687474703A2F2F7777772E7365656477696B692E636F6D2F706167652E63666D3F77696B6969643D33363826646F633D2431,'','',0,0),
 (0x6172786976,0x687474703A2F2F7777772E61727869762E6F72672F6162732F2431,'','',0,0),
 (0x633266696E64,0x687474703A2F2F63322E636F6D2F6367692F77696B693F46696E64506167652676616C75653D2431,'','',0,0),
 (0x6361636865,0x687474703A2F2F7777772E676F6F676C652E636F6D2F7365617263683F713D63616368653A2431,'','',0,0),
 (0x636F6D6D6F6E73,0x687474703A2F2F636F6D6D6F6E732E77696B696D656469612E6F72672F77696B692F2431,'','',0,0),
 (0x636F72706B6E6F777065646961,0x687474703A2F2F636F72706B6E6F7770656469612E6F72672F77696B692F696E6465782E7068702F2431,'','',0,0),
 (0x64696374696F6E617279,0x687474703A2F2F7777772E646963742E6F72672F62696E2F446963743F44617461626173653D2A26466F726D3D44696374312653747261746567793D2A2651756572793D2431,'','',0,0),
 (0x646973696E666F7065646961,0x687474703A2F2F7777772E646973696E666F70656469612E6F72672F77696B692E7068746D6C3F7469746C653D2431,'','',0,0),
 (0x646F63626F6F6B,0x687474703A2F2F77696B692E646F63626F6F6B2E6F72672F746F7069632F2431,'','',0,0),
 (0x646F69,0x687474703A2F2F64782E646F692E6F72672F2431,'','',0,0),
 (0x6472756D636F72707377696B69,0x687474703A2F2F7777772E6472756D636F72707377696B692E636F6D2F696E6465782E7068702F2431,'','',0,0),
 (0x64776A77696B69,0x687474703A2F2F7777772E737562657269632E6E65742F6367692D62696E2F64776A2F77696B692E6367693F2431,'','',0,0),
 (0x656C69627265,0x687474703A2F2F656E6369636C6F70656469612E75732E65732F696E6465782E7068702F2431,'','',0,0),
 (0x656D61637377696B69,0x687474703A2F2F7777772E656D61637377696B692E6F72672F6367692D62696E2F77696B692E706C3F2431,'','',0,0),
 (0x666F6C646F63,0x687474703A2F2F666F6C646F632E6F72672F3F2431,'','',0,0),
 (0x666F7877696B69,0x687474703A2F2F666F782E77696B69732E636F6D2F77632E646C6C3F57696B697E2431,'','',0,0),
 (0x667265656273646D616E,0x687474703A2F2F7777772E467265654253442E6F72672F6367692F6D616E2E6367693F6170726F706F733D312671756572793D2431,'','',0,0),
 (0x67656A,0x687474703A2F2F7777772E6573706572616E746F2E64652F6367692D62696E2F616B746976696B696F2F77696B692E706C3F2431,'','',0,0),
 (0x67656E746F6F2D77696B69,0x687474703A2F2F67656E746F6F2D77696B692E636F6D2F2431,'','',0,0),
 (0x676F6F676C65,0x687474703A2F2F7777772E676F6F676C652E636F6D2F7365617263683F713D2431,'','',0,0),
 (0x676F6F676C6567726F757073,0x687474703A2F2F67726F7570732E676F6F676C652E636F6D2F67726F7570733F713D2431,'','',0,0),
 (0x68616D6D6F6E6477696B69,0x687474703A2F2F7777772E64616972696B692E6F72672F48616D6D6F6E6457696B692F2431,'','',0,0),
 (0x686577696B69736F75726365,0x687474703A2F2F68652E77696B69736F757263652E6F72672F77696B692F2431,'','',1,0),
 (0x687277696B69,0x687474703A2F2F7777772E687277696B692E6F72672F696E6465782E7068702F2431,'','',0,0),
 (0x696D6462,0x687474703A2F2F75732E696D64622E636F6D2F5469746C653F2431,'','',0,0),
 (0x6A6172676F6E66696C65,0x687474703A2F2F73756E69722E6F72672F617070732F6D6574612E706C3F77696B693D4A6172676F6E46696C652672656469726563743D2431,'','',0,0),
 (0x6A737077696B69,0x687474703A2F2F7777772E6A737077696B692E6F72672F77696B692F2431,'','',0,0),
 (0x6B65696B69,0x687474703A2F2F6B65692E6B692F656E2F2431,'','',0,0),
 (0x6B6D77696B69,0x687474703A2F2F6B6D77696B692E77696B697370616365732E636F6D2F2431,'','',0,0),
 (0x6C696E757877696B69,0x687474703A2F2F6C696E757877696B692E64652F2431,'','',0,0),
 (0x6C6F6A62616E,0x687474703A2F2F7777772E6C6F6A62616E2E6F72672F74696B692F74696B692D696E6465782E7068703F706167653D2431,'','',0,0),
 (0x6C7177696B69,0x687474703A2F2F77696B692E6C696E75787175657374696F6E732E6F72672F77696B692F2431,'','',0,0),
 (0x6C75676B72,0x687474703A2F2F6C75672D6B722E736F75726365666F7267652E6E65742F6367692D62696E2F6C756777696B692E706C3F2431,'','',0,0),
 (0x6D617468736F6E677377696B69,0x687474703A2F2F5365656457696B692E636F6D2F706167652E63666D3F77696B6969643D32333726646F633D2431,'','',0,0),
 (0x6D65617462616C6C,0x687474703A2F2F7777772E7573656D6F642E636F6D2F6367692D62696E2F6D622E706C3F2431,'','',0,0),
 (0x6D6564696177696B6977696B69,0x687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F2431,'','',0,0),
 (0x6D656469617A696C6C61,0x68747470733A2F2F6275677A696C6C612E77696B696D656469612E6F72672F2431,'','',1,0),
 (0x6D656D6F7279616C706861,0x687474703A2F2F7777772E6D656D6F72792D616C7068612E6F72672F656E2F696E6465782E7068702F2431,'','',0,0),
 (0x6D65746177696B69,0x687474703A2F2F73756E69722E6F72672F617070732F6D6574612E706C3F2431,'','',0,0),
 (0x6D65746177696B697065646961,0x687474703A2F2F6D6574612E77696B696D656469612E6F72672F77696B692F2431,'','',0,0),
 (0x6D6F696E6D6F696E,0x687474703A2F2F7075726C2E6E65742F77696B692F6D6F696E2F2431,'','',0,0),
 (0x6D6F7A696C6C6177696B69,0x687474703A2F2F77696B692E6D6F7A696C6C612E6F72672F696E6465782E7068702F2431,'','',0,0),
 (0x6D77,0x687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F2431,'','',0,0),
 (0x6F656973,0x687474703A2F2F7777772E72657365617263682E6174742E636F6D2F6367692D62696E2F6163636573732E6367692F61732F6E6A61732F73657175656E6365732F656973412E6367693F416E756D3D2431,'','',0,0),
 (0x6F70656E6661637473,0x687474703A2F2F6F70656E66616374732E6265726C696F732E64652F696E6465782E7068746D6C3F7469746C653D2431,'','',0,0),
 (0x6F70656E77696B69,0x687474703A2F2F6F70656E77696B692E636F6D2F3F2431,'','',0,0),
 (0x706D6567,0x687474703A2F2F7777772E62657274696C6F772E636F6D2F706D65672F24312E706870,'','',0,0),
 (0x707072,0x687474703A2F2F63322E636F6D2F6367692F77696B693F2431,'','',0,0),
 (0x707974686F6E696E666F,0x687474703A2F2F77696B692E707974686F6E2E6F72672F6D6F696E2F2431,'','',0,0),
 (0x726663,0x687474703A2F2F7777772E7266632D656469746F722E6F72672F7266632F72666324312E747874,'','',0,0),
 (0x73323377696B69,0x687474703A2F2F69732D726F6F742E64652F77696B692F696E6465782E7068702F2431,'','',0,0),
 (0x73656174746C6577696B69,0x687474703A2F2F73656174746C652E77696B69612E636F6D2F77696B692F2431,'','',0,0),
 (0x73656174746C65776972656C657373,0x687474703A2F2F73656174746C65776972656C6573732E6E65742F3F2431,'','',0,0),
 (0x73656E736569736C696272617279,0x687474703A2F2F73656E736569732E786D702E6E65742F3F2431,'','',0,0),
 (0x736F75726365666F726765,0x687474703A2F2F736F75726365666F7267652E6E65742F2431,'','',0,0),
 (0x73717565616B,0x687474703A2F2F77696B692E73717565616B2E6F72672F73717565616B2F2431,'','',0,0),
 (0x7375736E696E67,0x687474703A2F2F7777772E7375736E696E672E6E752F2431,'','',0,0),
 (0x73766777696B69,0x687474703A2F2F77696B692E7376672E6F72672F2431,'','',0,0),
 (0x74617669,0x687474703A2F2F746176692E736F75726365666F7267652E6E65742F2431,'','',0,0),
 (0x74656A6F,0x687474703A2F2F7777772E74656A6F2E6F72672F76696B696F2F2431,'','',0,0),
 (0x7468656F7065646961,0x687474703A2F2F7777772E7468656F70656469612E636F6D2F2431,'','',0,0),
 (0x746D6277,0x687474703A2F2F7777772E746D62772E6E65742F77696B692F2431,'','',0,0),
 (0x746D6E6574,0x687474703A2F2F7777772E746563686E6F6D616E69666573746F732E6E65742F3F2431,'','',0,0),
 (0x746D77696B69,0x687474703A2F2F7777772E45617379546F7069634D6170732E636F6D2F3F706167653D2431,'','',0,0),
 (0x7477696B69,0x687474703A2F2F7477696B692E6F72672F6367692D62696E2F766965772F2431,'','',0,0),
 (0x756561,0x687474703A2F2F7777772E74656A6F2E6F72672F7565612F2431,'','',0,0),
 (0x756E7265616C,0x687474703A2F2F77696B692E6265796F6E64756E7265616C2E636F6D2F77696B692F2431,'','',0,0),
 (0x7573656D6F64,0x687474703A2F2F7777772E7573656D6F642E636F6D2F6367692D62696E2F77696B692E706C3F2431,'','',0,0),
 (0x76696E69736D6F,0x687474703A2F2F76696E69736D6F2E636F6D2F656E2F2431,'','',0,0),
 (0x776562736569747A77696B69,0x687474703A2F2F776562736569747A2E666C7578656E742E636F6D2F77696B692F2431,'','',0,0),
 (0x776879,0x687474703A2F2F636C75626C65742E636F6D2F632F632F7768793F2431,'','',0,0),
 (0x77696B69,0x687474703A2F2F63322E636F6D2F6367692F77696B693F2431,'','',0,0),
 (0x77696B6961,0x687474703A2F2F7777772E77696B69612E636F6D2F77696B692F2431,'','',0,0),
 (0x77696B69626F6F6B73,0x687474703A2F2F656E2E77696B69626F6F6B732E6F72672F77696B692F2431,'','',1,0),
 (0x77696B69636974696573,0x687474703A2F2F7777772E77696B69612E636F6D2F77696B692F2431,'','',0,0),
 (0x77696B696631,0x687474703A2F2F7777772E77696B6966312E6F72672F2431,'','',0,0),
 (0x77696B69686F77,0x687474703A2F2F7777772E77696B69686F772E636F6D2F2431,'','',0,0),
 (0x77696B696D65646961,0x687474703A2F2F77696B696D65646961666F756E646174696F6E2E6F72672F77696B692F2431,'','',0,0),
 (0x77696B696E657773,0x687474703A2F2F656E2E77696B696E6577732E6F72672F77696B692F2431,'','',1,0),
 (0x77696B696E666F,0x687474703A2F2F7777772E77696B696E666F2E6F72672F696E6465782E7068702F2431,'','',0,0),
 (0x77696B697065646961,0x687474703A2F2F656E2E77696B6970656469612E6F72672F77696B692F2431,'','',1,0),
 (0x77696B6971756F7465,0x687474703A2F2F656E2E77696B6971756F74652E6F72672F77696B692F2431,'','',1,0),
 (0x77696B69736F75726365,0x687474703A2F2F77696B69736F757263652E6F72672F77696B692F2431,'','',1,0),
 (0x77696B6973706563696573,0x687474703A2F2F737065636965732E77696B696D656469612E6F72672F77696B692F2431,'','',1,0),
 (0x77696B6974726176656C,0x687474703A2F2F77696B6974726176656C2E6F72672F656E2F2431,'','',0,0),
 (0x77696B6976657273697479,0x687474703A2F2F656E2E77696B69766572736974792E6F72672F77696B692F2431,'','',1,0),
 (0x77696B74,0x687474703A2F2F656E2E77696B74696F6E6172792E6F72672F77696B692F2431,'','',1,0),
 (0x77696B74696F6E617279,0x687474703A2F2F656E2E77696B74696F6E6172792E6F72672F77696B692F2431,'','',1,0),
 (0x776C7567,0x687474703A2F2F7777772E776C75672E6F72672E6E7A2F2431,'','',0,0),
 (0x7A77696B69,0x687474703A2F2F7A77696B692E6F72672F2431,'','',0,0),
 (0x7A7A7A2077696B69,0x687474703A2F2F77696B692E7A7A7A2E65652F696E6465782E7068702F2431,'','',0,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `ipblocks`
--

/*!40000 ALTER TABLE `ipblocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ipblocks` ENABLE KEYS */;


--
-- Definition of table `iwlinks`
--

DROP TABLE IF EXISTS `iwlinks`;
CREATE TABLE `iwlinks` (
  `iwl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `iwl_prefix` varbinary(20) NOT NULL DEFAULT '',
  `iwl_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `iwl_from` (`iwl_from`,`iwl_prefix`,`iwl_title`),
  UNIQUE KEY `iwl_prefix_title_from` (`iwl_prefix`,`iwl_title`,`iwl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `iwlinks`
--

/*!40000 ALTER TABLE `iwlinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `iwlinks` ENABLE KEYS */;


--
-- Definition of table `job`
--

DROP TABLE IF EXISTS `job`;
CREATE TABLE `job` (
  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_cmd` varbinary(60) NOT NULL DEFAULT '',
  `job_namespace` int(11) NOT NULL,
  `job_title` varbinary(255) NOT NULL,
  `job_params` blob NOT NULL,
  PRIMARY KEY (`job_id`),
  KEY `job_cmd` (`job_cmd`,`job_namespace`,`job_title`,`job_params`(128))
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `job`
--

/*!40000 ALTER TABLE `job` DISABLE KEYS */;
/*!40000 ALTER TABLE `job` ENABLE KEYS */;


--
-- Definition of table `l10n_cache`
--

DROP TABLE IF EXISTS `l10n_cache`;
CREATE TABLE `l10n_cache` (
  `lc_lang` varbinary(32) NOT NULL,
  `lc_key` varbinary(255) NOT NULL,
  `lc_value` mediumblob NOT NULL,
  KEY `lc_lang_key` (`lc_lang`,`lc_key`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `l10n_cache`
--

/*!40000 ALTER TABLE `l10n_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `l10n_cache` ENABLE KEYS */;


--
-- Definition of table `langlinks`
--

DROP TABLE IF EXISTS `langlinks`;
CREATE TABLE `langlinks` (
  `ll_from` int(10) unsigned NOT NULL DEFAULT '0',
  `ll_lang` varbinary(20) NOT NULL DEFAULT '',
  `ll_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `ll_from` (`ll_from`,`ll_lang`),
  KEY `ll_lang` (`ll_lang`,`ll_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `langlinks`
--

/*!40000 ALTER TABLE `langlinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `langlinks` ENABLE KEYS */;


--
-- Definition of table `log_search`
--

DROP TABLE IF EXISTS `log_search`;
CREATE TABLE `log_search` (
  `ls_field` varbinary(32) NOT NULL,
  `ls_value` varbinary(255) NOT NULL,
  `ls_log_id` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `ls_field_val` (`ls_field`,`ls_value`,`ls_log_id`),
  KEY `ls_log_id` (`ls_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `log_search`
--

/*!40000 ALTER TABLE `log_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_search` ENABLE KEYS */;


--
-- Definition of table `logging`
--

DROP TABLE IF EXISTS `logging`;
CREATE TABLE `logging` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varbinary(32) NOT NULL DEFAULT '',
  `log_action` varbinary(32) NOT NULL DEFAULT '',
  `log_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  `log_user` int(10) unsigned NOT NULL DEFAULT '0',
  `log_user_text` varbinary(255) NOT NULL DEFAULT '',
  `log_namespace` int(11) NOT NULL DEFAULT '0',
  `log_title` varbinary(255) NOT NULL DEFAULT '',
  `log_page` int(10) unsigned DEFAULT NULL,
  `log_comment` varbinary(255) NOT NULL DEFAULT '',
  `log_params` blob NOT NULL,
  `log_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `type_time` (`log_type`,`log_timestamp`),
  KEY `user_time` (`log_user`,`log_timestamp`),
  KEY `page_time` (`log_namespace`,`log_title`,`log_timestamp`),
  KEY `times` (`log_timestamp`),
  KEY `log_user_type_time` (`log_user`,`log_type`,`log_timestamp`),
  KEY `log_page_id_time` (`log_page`,`log_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `math_html` blob,
  `math_mathml` blob,
  UNIQUE KEY `math_inputhash` (`math_inputhash`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `math`
--

/*!40000 ALTER TABLE `math` DISABLE KEYS */;
/*!40000 ALTER TABLE `math` ENABLE KEYS */;


--
-- Definition of table `module_deps`
--

DROP TABLE IF EXISTS `module_deps`;
CREATE TABLE `module_deps` (
  `md_module` varbinary(255) NOT NULL,
  `md_skin` varbinary(32) NOT NULL,
  `md_deps` mediumblob NOT NULL,
  UNIQUE KEY `md_module_skin` (`md_module`,`md_skin`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `module_deps`
--

/*!40000 ALTER TABLE `module_deps` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_deps` ENABLE KEYS */;


--
-- Definition of table `msg_resource`
--

DROP TABLE IF EXISTS `msg_resource`;
CREATE TABLE `msg_resource` (
  `mr_resource` varbinary(255) NOT NULL,
  `mr_lang` varbinary(32) NOT NULL,
  `mr_blob` mediumblob NOT NULL,
  `mr_timestamp` binary(14) NOT NULL,
  UNIQUE KEY `mr_resource_lang` (`mr_resource`,`mr_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `msg_resource`
--

/*!40000 ALTER TABLE `msg_resource` DISABLE KEYS */;
/*!40000 ALTER TABLE `msg_resource` ENABLE KEYS */;


--
-- Definition of table `msg_resource_links`
--

DROP TABLE IF EXISTS `msg_resource_links`;
CREATE TABLE `msg_resource_links` (
  `mrl_resource` varbinary(255) NOT NULL,
  `mrl_message` varbinary(255) NOT NULL,
  UNIQUE KEY `mrl_message_resource` (`mrl_message`,`mrl_resource`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `msg_resource_links`
--

/*!40000 ALTER TABLE `msg_resource_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `msg_resource_links` ENABLE KEYS */;


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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `objectcache`
--

/*!40000 ALTER TABLE `objectcache` DISABLE KEYS */;
INSERT INTO `objectcache` (`keyname`,`value`,`exptime`) VALUES 
 (0x656D7074795F6D773131373A7063616368653A6964686173683A312D30212A21302121656E212A,0xA5566D6FE24610BECFFC8AC51F5AE924B04D48B82CC6D5294A934821A13ADAFB505568B1075865BDB6BCEB102EBAFFDE99C57086A4559B44289667679E79765E7DCFC31EF726A23450DE57B6A8ACC77B03FE6CF829F7B2293C596F6850E7F48C7B511147F3780CA9145FE583642B61D81C403353250918B3A894DA30A98D154A41DA8DFC79DC8A7CB42AE28B5C9B4A596657C022C156252C46DECADA82FB7E065674D788981174372F973EBDF9D7A00A8E8616B4351E4B943066E4212528B550CC123956821A793A5FE44AE56B8F59512EC18EBCD95C09FDE0C5BFE3BD7E36ECAA922944BE88D9222F9122FECF8495B966F8AB8CD44BC78CDC32932FEC5A94D075DC5BD1AA1747A6107A4F2095D64042D65EFCE7FE325BCE52A7F0D42D56C52F565A05A3B1907A36114BF84964C55038AB1121B8F71A66142271A7EE5DE211ABC59C5D81B5C40D235A5A48BD982CE91A7F453E518AD901B36CDD59814895D4E031998EBCDA7EB6B73F4664354EE4E32D5B51A5E248C9F84586D6EB75D72587EEF8234163A12BA128450BB9AC4A17D099D97A786BC20EC0D80E8C2969DCC5312748F00D247FFDFCDB1B19FD2878C4F8370E0649104F7354CC99902A13DA9D51E9F97B961DA1755EE904DE4D0D15411860E48BF27B142F1F33DB6A45ED4E87B5EE603D99A042869556429197B635C16799530F6377E83C0596202DCBD9891F06EEAF35C98DEDC013164B8AFD93A80A958CFC069C057E2F381F84A73D36DF5830AD29648512161892AD32ECDD7FD0BB7C2A401BF908AC70E3872D30122EEBB5F3809CB73A9D1DF32FE211C8F94E3F1189EB59BB620FB061E8D66E66D93A0C07BC70675CA638A3563CEC04ED8FEDA0DD06DDFEC8E80616D3832D9015AC178461F0290CC293FEE02464E4CD4DBC3E8EBF5BA19715F6EEADD40FC61B0A1EF0E7EF7816E2D905DE709997129A0701CD4C6AE4DDE03CE71E4D00461380DECF08F508CD19D5316BCA7B0DF94DDA3819E0C14D86884D6DE27B5917CFDE439F06791F8FFED7A41D4A1E22D7D373FEAEFEAF61FAC17F84A10EAD6DCE4EF8BB3BCA4151646891DDE0D54A3A7F2D95D8105FEA793E9CF36058DB5CE3CA78E588D275975FD18A2B3707D26B9CBD3716B206FE27148FF3B4522F32BB5DB6D779DEA48311CFBE8A526FE3D794D62C9C34E4CF12BD0A447F762E6C9E287804B5CF1CF7EA7774C6BD1E3DB108DC5EA86F77BC59B6B5A9AB6C0E656D167A5B28B7D29A32E4B328F3CC6DAC4691CF76454EC1A016CF170B2C0647EA6CB0752074B2CACB2312FBF534FCBE4BC9A4CC0B28ED4177512CA7F717D7D3F12D01A0137AF410F543F3FBE503261B094F7225934D43F12478A1F8D97DB6407A5FEC637B4221A566DA8682D6E9361973175922D7DCFFB518A955888B4379B915D574FF80D2383517C8B07BD6EDEF0284030427D4148B7A376E0E27D1F6B36BA787B35252B5DD9194FA9F9A15636EEE553A164B991C14E27437DD5E850E30CE7F03,'2011-08-11 13:47:31'),
 (0x656D7074795F6D773131373A7063616368653A69646F7074696F6E733A31,0x458FC10E82400C44FFA51F60B6B002768FC6A3E1A2DE5768B0892C86AE8986F0EFB218E3AD9937ED4C6BDA11EC7D73E393F40CB4A549A922E82F3CAA0C019C2E1AE0A6D8D834A359D8DF9F144B90194453A1C1DC9639AE6AF6F31D5E0F19DFE084AAC21A935899D810A297A0F5BD3DFA4E1A705732BFC5B3725B3FE292AFE03CE534C9172E59E1D95F79BCB16F25749AEE622248C0AD44E526AEB58532B77EF2541EEF3E74E0E6F903,'2011-08-11 13:47:31');
/*!40000 ALTER TABLE `objectcache` ENABLE KEYS */;


--
-- Definition of table `oldimage`
--

DROP TABLE IF EXISTS `oldimage`;
CREATE TABLE `oldimage` (
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
  `oi_minor_mime` varbinary(100) NOT NULL DEFAULT 'unknown',
  `oi_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `oi_sha1` varbinary(32) NOT NULL DEFAULT '',
  KEY `oi_usertext_timestamp` (`oi_user_text`,`oi_timestamp`),
  KEY `oi_name_timestamp` (`oi_name`,`oi_timestamp`),
  KEY `oi_name_archive_name` (`oi_name`,`oi_archive_name`(14)),
  KEY `oi_sha1` (`oi_sha1`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=binary;

--
-- Dumping data for table `page`
--

/*!40000 ALTER TABLE `page` DISABLE KEYS */;
INSERT INTO `page` (`page_id`,`page_namespace`,`page_title`,`page_restrictions`,`page_counter`,`page_is_redirect`,`page_is_new`,`page_random`,`page_touched`,`page_latest`,`page_len`) VALUES 
 (1,0,0x4D61696E5F50616765,'',0,0,1,0.512769789783,0x3230313130383130313334373330,1,438);
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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `pl_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `pl_from` (`pl_from`,`pl_namespace`,`pl_title`),
  UNIQUE KEY `pl_namespace` (`pl_namespace`,`pl_title`,`pl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `qc_title` varbinary(255) NOT NULL DEFAULT '',
  KEY `qc_type` (`qc_type`,`qc_value`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `qcc_title` varbinary(255) NOT NULL DEFAULT '',
  `qcc_namespacetwo` int(11) NOT NULL DEFAULT '0',
  `qcc_titletwo` varbinary(255) NOT NULL DEFAULT '',
  KEY `qcc_type` (`qcc_type`,`qcc_value`),
  KEY `qcc_title` (`qcc_type`,`qcc_namespace`,`qcc_title`),
  KEY `qcc_titletwo` (`qcc_type`,`qcc_namespacetwo`,`qcc_titletwo`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=binary;

--
-- Dumping data for table `recentchanges`
--

/*!40000 ALTER TABLE `recentchanges` DISABLE KEYS */;
INSERT INTO `recentchanges` (`rc_id`,`rc_timestamp`,`rc_cur_time`,`rc_user`,`rc_user_text`,`rc_namespace`,`rc_title`,`rc_comment`,`rc_minor`,`rc_bot`,`rc_new`,`rc_cur_id`,`rc_this_oldid`,`rc_last_oldid`,`rc_type`,`rc_moved_to_ns`,`rc_moved_to_title`,`rc_patrolled`,`rc_ip`,`rc_old_len`,`rc_new_len`,`rc_deleted`,`rc_logid`,`rc_log_type`,`rc_log_action`,`rc_params`) VALUES 
 (1,0x3230313130383130313334373330,0x3230313130383130313334373330,0,0x4D6564696157696B692064656661756C74,0,0x4D61696E5F50616765,'',0,0,1,1,1,0,1,0,'',0,0x3132372E302E302E31,0,438,0,0,NULL,'','');
/*!40000 ALTER TABLE `recentchanges` ENABLE KEYS */;


--
-- Definition of table `redirect`
--

DROP TABLE IF EXISTS `redirect`;
CREATE TABLE `redirect` (
  `rd_from` int(10) unsigned NOT NULL DEFAULT '0',
  `rd_namespace` int(11) NOT NULL DEFAULT '0',
  `rd_title` varbinary(255) NOT NULL DEFAULT '',
  `rd_interwiki` varbinary(32) DEFAULT NULL,
  `rd_fragment` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`rd_from`),
  KEY `rd_ns_title` (`rd_namespace`,`rd_title`,`rd_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=binary MAX_ROWS=10000000 AVG_ROW_LENGTH=1024;

--
-- Dumping data for table `revision`
--

/*!40000 ALTER TABLE `revision` DISABLE KEYS */;
INSERT INTO `revision` (`rev_id`,`rev_page`,`rev_text_id`,`rev_comment`,`rev_user`,`rev_user_text`,`rev_timestamp`,`rev_minor_edit`,`rev_deleted`,`rev_len`,`rev_parent_id`) VALUES 
 (1,1,1,'',0,0x4D6564696157696B692064656661756C74,0x3230313130383130313334373330,0,0,438,0);
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
INSERT INTO `searchindex` (`si_page`,`si_title`,`si_text`) VALUES 
 (1,'main page','  mediawiki hasu800 been successfully installed.  consult theu800 user user\'su800 guide foru800 information onu800 using theu800 wiki software. getting started getting started getting started configuration settings list mediawiki faqu800 mediawiki release mailing list ');
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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `site_stats`
--

/*!40000 ALTER TABLE `site_stats` DISABLE KEYS */;
INSERT INTO `site_stats` (`ss_row_id`,`ss_total_views`,`ss_total_edits`,`ss_good_articles`,`ss_total_pages`,`ss_users`,`ss_active_users`,`ss_admins`,`ss_images`) VALUES 
 (1,0,1,0,1,1,-1,0,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `tl_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `tl_from` (`tl_from`,`tl_namespace`,`tl_title`),
  UNIQUE KEY `tl_namespace` (`tl_namespace`,`tl_title`,`tl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=binary MAX_ROWS=10000000 AVG_ROW_LENGTH=10240;

--
-- Dumping data for table `text`
--

/*!40000 ALTER TABLE `text` DISABLE KEYS */;
INSERT INTO `text` (`old_id`,`old_text`,`old_flags`) VALUES 
 (1,0x2727274D6564696157696B6920686173206265656E207375636365737366756C6C7920696E7374616C6C65642E2727270A0A436F6E73756C7420746865205B687474703A2F2F6D6574612E77696B696D656469612E6F72672F77696B692F48656C703A436F6E74656E7473205573657227732047756964655D20666F7220696E666F726D6174696F6E206F6E207573696E67207468652077696B6920736F6674776172652E0A0A3D3D2047657474696E672073746172746564203D3D0A2A205B687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F4D616E75616C3A436F6E66696775726174696F6E5F73657474696E677320436F6E66696775726174696F6E2073657474696E6773206C6973745D0A2A205B687474703A2F2F7777772E6D6564696177696B692E6F72672F77696B692F4D616E75616C3A464151204D6564696157696B69204641515D0A2A205B68747470733A2F2F6C697374732E77696B696D656469612E6F72672F6D61696C6D616E2F6C697374696E666F2F6D6564696177696B692D616E6E6F756E6365204D6564696157696B692072656C65617365206D61696C696E67206C6973745D,0x7574662D38);
/*!40000 ALTER TABLE `text` ENABLE KEYS */;


--
-- Definition of table `trackbacks`
--

DROP TABLE IF EXISTS `trackbacks`;
CREATE TABLE `trackbacks` (
  `tb_id` int(11) NOT NULL AUTO_INCREMENT,
  `tb_page` int(11) DEFAULT NULL,
  `tb_title` varbinary(255) NOT NULL,
  `tb_url` blob NOT NULL,
  `tb_ex` blob,
  `tb_name` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`tb_id`),
  KEY `tb_page` (`tb_page`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `tc_contents` blob,
  `tc_time` binary(14) NOT NULL,
  UNIQUE KEY `tc_url_idx` (`tc_url`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `ul_key` varbinary(255) NOT NULL,
  `ul_value` blob,
  PRIMARY KEY (`ul_key`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=binary;

--
-- Dumping data for table `user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`user_id`,`user_name`,`user_real_name`,`user_password`,`user_newpassword`,`user_newpass_time`,`user_email`,`user_options`,`user_touched`,`user_token`,`user_email_authenticated`,`user_email_token`,`user_email_token_expires`,`user_registration`,`user_editcount`) VALUES 
 (1,0x57696B695379736F70,'',0x3A423A30333238356130613A6434333137303664656466643566313634313564323937386463643830646133,'',NULL,0x6B61692E6B7565686E407765622E6465,'',0x3230313130383130313334373335,0x6338613334353061613037383333323462303662636437336264613533373531,NULL,0x0000000000000000000000000000000000000000000000000000000000000000,NULL,0x3230313130383130313334373330,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `user_newtalk`
--

/*!40000 ALTER TABLE `user_newtalk` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_newtalk` ENABLE KEYS */;


--
-- Definition of table `user_properties`
--

DROP TABLE IF EXISTS `user_properties`;
CREATE TABLE `user_properties` (
  `up_user` int(11) NOT NULL,
  `up_property` varbinary(32) NOT NULL,
  `up_value` blob,
  UNIQUE KEY `user_properties_user_property` (`up_user`,`up_property`),
  KEY `user_properties_property` (`up_property`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

--
-- Dumping data for table `user_properties`
--

/*!40000 ALTER TABLE `user_properties` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_properties` ENABLE KEYS */;


--
-- Definition of table `valid_tag`
--

DROP TABLE IF EXISTS `valid_tag`;
CREATE TABLE `valid_tag` (
  `vt_tag` varbinary(255) NOT NULL,
  PRIMARY KEY (`vt_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
  `wl_title` varbinary(255) NOT NULL DEFAULT '',
  `wl_notificationtimestamp` varbinary(14) DEFAULT NULL,
  UNIQUE KEY `wl_user` (`wl_user`,`wl_namespace`,`wl_title`),
  KEY `namespace_title` (`wl_namespace`,`wl_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

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
