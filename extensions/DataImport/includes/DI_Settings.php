<?php

/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup DataImport
 * This file contains the settings that configure the Data Import Extension
 * 
 * @author Ingo Steinbauer
 */

/*
 * Change the following values in order to rename
 * fix attributes names for some Data Access Modules
 */
define('DI_TI_DAM_FEED_SUBJECT', 'Has subject');
define('DI_TI_DAM_FEED_STEMS_FROM', 'Stems from feed');
define('DI_TI_DAM_FEED_CONTENT', 'Has content');
define('DI_TI_DAM_FEED_TAG', 'Has tag');
define('DI_TI_DAM_FEED_AUTHOR', 'Has author');
define('DI_TI_DAM_FEED_CONTRIBUTOR', 'Has contributor');
define('DI_TI_DAM_FEED_COPYRIGHT', 'Has copyright');
define('DI_TI_DAM_FEED_DATE', 'Has publication date');
define('DI_TI_DAM_FEED_LOCAL_DATE', 'Has local publication date');
define('DI_TI_DAM_FEED_PERMA_LINK', 'Has permalink');
define('DI_TI_DAM_FEED_URL', 'Has URL');
define('DI_TI_DAM_FEED_ENCLOSURES', 'Enclosures');
define('DI_TI_DAM_FEED_LATITUDE', 'Has latitude');
define('DI_TI_DAM_FEED_LONGITUDE', 'Has longiitude');
define('DI_TI_DAM_FEED_SOURCE', 'Has sourc');
define('DI_TI_DAM_FEED_ID', 'Has id');

/*
 * Identity checks by the 'Append Some' conflict strategy
 * will be done based on the following term attributes 
 */
global $ditigAttributesForIdentityCheck;
$ditigAttributesForIdentityCheck[] = array();
$ditigAttributesForIdentityCheck['DALReadFeed'] = array(
	DI_TI_DAM_FEED_ID);

	
/*
 * Values of the following term attributes will be appended
 * by the 'Append Some' conflict strategy if existing instances
 * are updated 
 */	
global $ditigAttributesForAppending;
$ditigAttributesForAppending[] = array();
$ditigAttributesForAppending['DALReadFeed'] = array(
	DI_TI_DAM_FEED_STEMS_FROM);
	
/*
 * if the 'Append Some' Conflict Policy detects that an article with the 
 * samw title but which is not identical already exists, then it adds a 
 * suffix to the article name and tries to create this one. This is repeated
 * until an already existing article which is identical or an article name which
 * does not yet exist is found.
 * 
 * Normally a counter is used as suffix, e.g. 'articlename - 1'. This is bad if
 * one of your feeds always uses the same subject for all feed items. In this
 * case a huge number of articles has to be checked before the CP finds an
 * article name that does not yet exist because the CP always starts with the
 * suffix 1. In this case set the variable to true. Then the current date is used as
 * suffix.
 * 
 *  If you do not have such a feed, then setting this variable to true is not
 *  a good idea, since after the first article name check the CP will never have
 *  another hit and the CP may miss that some articles are identical.  
 */
global $ditigUseDateAsSuffixOnConflicts;
$ditigUseDateAsSuffixOnConflicts = false;

/*
* Pages in the namespace WebService list the articles that use a the web service.
* This value limits the number of displayed articles.
*/
global $diwsgArticleLimit;
$diwsgArticleLimit = 25;