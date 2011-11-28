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
  * @ingroup DAPCP
  *
  * @author Dian
 */

/**
 * This group contains all parts of the DataAPI
 * @defgroup DataAPI
 * @ingroup SMWHalo
 */

/**
 * This group contains all parts of the DataAPI that deal with the Page Crud Plus component.
 * @defgroup DAPCP
 * @ingroup DataAPI
 */

/**
  * The class represent a page in a wiki system. It has most of the attributes
 * as described in the MediaWiki API.
 * If the page ID is equal to '-1', the page is not existing.
 * 
 * 
 *
 * @author  Dian
 * @version 0.1
 */
class PCPPage{

	/**
	 * The page title.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * The ID of the page.
	 *
	 * @var string
	 */
	public $pageid = '';

	/**
	 * The namespace of the page.
	 *
	 * @var string
	 */
	public $namespace = '';

	/**
	 * New page (or section) content.
	 *
	 * @var string
	 */
	public $text = '';

	/**
	 * Edit comment.
	 *
	 * @var unknown_type
	 */
	public $summary ='';

	/**
	 * Timestamp of the last revision, used to detect edit conflicts. Leave unset to ignore conflicts
	 *
	 * @var unknown_type
	 */
	public $basetimestamp ='';

	/**
	 * The last revision ID of the page.
	 *
	 * @var string
	 */
	public $lastrevid= '';

	/**
	 * The revision ID of the page that is loaded.
	 * Can differ from the lastrevid if an older revision is loaded.
	 * @see $lastrevid
	 *
	 * @var string
	 */
	public $usedrevid ='';

	/**
	 * The class constructor.
	 *
	 * @param string $title
	 */
	public function PCPPage($title=NULL){
		$this->title = $title;
	}

	/**
	 * Converts the object to an XML node.<br/>
	 * <p><i>Example: &lt;page title="title" pageid="4611" ns ="namespace" bt="basetimestamp" lrid="3" urid="2"&gt;<br/>
	 * &lt;text&gt;The text on the page.&lt;/text&gt;<br/>
	 * &lt;summary&gt;The summary of the last change made.&lt;/summary&gt;
	 * &lt;/page&gt;
	 * </i></p>
	 *
	 *
	 * @return string The XML node.
	 */
	public function toXML(){
		$__xmlPage = '';

		$__xmlPage.= "<page ".
		'title="'.$this->title.'" '.
		'pageid="'.$this->pageid.'" '.
		'ns="'.$this->namespace.'" '.
		'bt="'.$this->basetimestamp.'" '.
		'lrid="'.$this->lastrevid.'" '.
		'urid="'.$this->usedrevid.'" '.
		">".
		"<text><![CDATA[".$this->text."]]></text>".
		"<summary><![CDATA[".$this->summary."]]></summary>".
		"</page>"
		;

		return $__xmlPage;
	}
	
/**
	 * Converts the attributes of the object in keys of a hashmap.
	 *
	 *
	 * @return array The hashmap.
	 */
	public function toHashmap(){
		$__hmPage = array();

		$__hmPage= array(
		"title" => $this->title,
		"pageid" => $this->pageid,
		"ns" => $this->namespace,
		"bt" => $this->basetimestamp,
		"lrid" => $this->lastrevid,
		"urid" => $this->usedrevid,
		"text" => $this->text,
		"summary" => $this->summary,
		);

		return $__hmPage;
	}

	/**
	 * Converts a given XML node into a Page object.
	 *
	 * @param string $xmlString The XML node representing the page.
	 */
	public function fromXML($xmlString){

	}
}
