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
 * @ingroup LinkedData
 */
/**
 * This file defines the class LODRating.
 * 
 * @author Thomas Schweitzer
 * Date: 11.10.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class describes a rating of a triple.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODRating {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	private $mAuthor;    		//string: Name of the user who created this rating
	private $mCreationTime;		//string: Date of this rating
	private $mComment;			//string: The comment for this rating
	private $mValue;			//string: Value of a rating e.g. true or false
	
	/**
	 * Constructor for LODRating
	 *
	 * @param string $value
	 * 		The value of a rating. This is normally "true" or "false" as the
	 * 		rated triple can be correct or incorrect. 
	 * @param string $comment
	 * 		A comment that describes why the author thinks that his rating is 
	 * 		justified. 
	 * @param string $author
	 * 		The name of the wiki user who enters the rating. If <null>, the 
	 * 		name of the current user is assumed. Default is <null>.
	 * @param string $creationTime
	 * 		Time of creation of this rating in the format ISO 8601 e.g. 
	 * 		2010-10-12T06:07:11Z . If <null>, the current time is set.
	 * @throws TSCException
	 * 		... if the format of $creationTime does not match ISO 8601
	 */
	function __construct($value, $comment, $author = null, $creationTime = null) {
		$this->mValue = $value;
		$this->mComment = $comment;
		if (is_null($author)) {
			global $wgUser;
			$this->mAuthor = $wgUser->getName();
		} else {
			$this->mAuthor = $author;
		}
		if (!is_null($creationTime)) {
			// Verify that the format of the time is ISO 8601
			if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.*\d*)?Z?$/', $creationTime)) {
				throw new TSCException(TSCException::INTERNAL_ERROR,
										"Wrong time format. Expected ISO 8601 e.g. 2010-10-12T06:07:11Z");
			}
		}
		$this->mCreationTime = is_null($creationTime) ? wfTimestamp(TS_ISO_8601)
													  : $creationTime;
	}
	

	//--- getter/setter ---
	public function getAuthor()			{return $this->mAuthor;}
	public function getCreationTime()	{return $this->mCreationTime;}
	public function getComment()		{return $this->mComment;}
	public function getValue()			{return $this->mValue;}
	
	
	//--- Public methods ---
	

	//--- Private methods ---
}
