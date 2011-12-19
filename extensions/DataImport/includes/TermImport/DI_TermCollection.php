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
 * @ingroup DITermImport
 * 
 * @author Ingo Steinbauer
 */

/*
 * A collection of terms that have been
 * provided by a DAM and that will be imported
 * by the Term Import Framework
 */
class DITermCollection {
	
	private $terms = array();
	private $errorMsgs = array();
	
	/*
	 * Add a new term
	 */
	public function addTerm($term){
		$this->terms[] = $term;
	}
	
	/*
	 * Get all terms in this collection
	 */
	public function getTerms(){
		return $this->terms;
	}
	
	/*
	 * Enables a DAM to add errors to the collection.
	 * The errors later will be added to the import log by
	 * the Term Import Bot.
	 */
	public function addErrorMsg($msg){
		$this->errorMsgs[] = $msg;
	}
	
	/*
	 * Get all errors
	 */
	public function getErrorMsgs(){
		return $this->errorMsgs;
	}
	
}

/*
 * This class reprents a term that was provided
 * by a DAM and that will be imported by the TIF
 */
class DITerm {

	private $articleName = '';
	private $attributes = array();
	
	private $callbacks = array();
	private $isAnnonymousCallbackTerm = false;
	
	private $attributesForAnnotations;
	private $attributesForTemplates;
	
	public static $tagWhitelist;

	/*
	 * Set the article name for this term
	 */
	public function setArticleName($articleName){
		$articleName = strip_tags($articleName);
		$this->articleName = $articleName; 
	}
	
	/*
	 * Get the desired article name for this term
	 */
	public function getArticleName(){
		return $this->articleName; 
	}
	
	/*
	 * add a new attribute name / value pair to this term
	 */
	public function addAttribute($attribute, $value){
		if(is_array($value)){
			
			foreach($value as $val){
				$this->addAttribute($attribute, $val);
			}
				
		} else {
			$value = trim($value);
			
			if (strlen($value) > 0) {
				if(!array_key_exists($attribute, $this->attributes)){
					$this->attributes[$attribute] = array();
				}
				$this->attributes[$attribute][] = $value;
			}
		}
	}
	
	/*
	 * Get all attributes of this term. The attributes will differ depending on
	 * ehether they will be used as annotation or template parameter values.
	 */
	public function getAttributes($sAnnotations=true){
		return $this->initializeReturnValues($sAnnotations);
	}
	
	private function initializeReturnValues($sAnnotations){
		//todo: document this
		
		$tagWhitelist = self::getTagWhitelist();
		
		if($sAnnotations){
			if(is_null($this->attributesForAnnotations)){
				$this->attributesForAnnotations = array();
				foreach($this->attributes as $attribute => $values){
					foreach($values as $value){
						$value = str_replace(
							array('[', ']', '{', '}', '|'),
							array('&#91;', '&#93;', '&#123;', '&#125;', '&#124;'),
							$value);
						
						$this->attributesForAnnotations[$attribute][] = 
							strip_tags($value, $tagWhitelist);	
					}
				}
			}	
			return $this->attributesForAnnotations; 
		} else {
			if(is_null($this->attributesForTemplates)){
				$this->attributesForTemplates = array();
				foreach($this->attributes as $attribute => $values){
					foreach($values as $value){
						$this->attributesForTemplates[$attribute][] = 
							strip_tags($value, $tagWhitelist.'<pre>');;	
					}
				}
			}	
			return $this->attributesForTemplates;
		}
	}
	
	/*
	 * Returns the values of a certain attribute. Value may differ on whether it
	 * will be used as an annotation or template parameter value
	 */
	public function getAttributeValue($attributeName, $asAnnotations=true){
		$attributes = $this->initializeReturnValues($asAnnotations);
		
		if(array_key_exists($attributeName, $attributes)){
			return $attributes[$attributeName];
		}
		
		return false;
	}
	
	/*
	 * Get all callbacks that have been associated with
	 * this term by a DAM
	 */
	public function getCallbacks(){
		return $this->callbacks;
	}
	
	/*
	 * Enables a DAM to tell the Term Import Bot to call a callback method
	 * of the DAM, when the Term Import Bot reaches this term in his queue.
	 */
	public function addCallback(DITermImportCallback $callback){
		$this->callbacks[] = $callback;
	}
	
	/*
	 * Does this term also contain content for new articles or
	 * is it only used to tell the Term Import Bot to call some 
	 * callback methods of the DAm, when he reaches this term.
	 */
	public function isAnnonymousCallbackTerm(){
		return $this->isAnnonymousCallbackTerm;
	}
	
	/*
	 * Enables a DAM to make this an annonymous callback term, that does not
	 * provide content for a new article.
	 */
	public function setAnnonymousCallbackTerm($anonymousCallbackTerm){
		$this->isAnnonymousCallbackTerm = $anonymousCallbackTerm;
	}
	
	private static function getTagWhitelist(){
		if(is_null(self::$tagWhitelist)){
			//copied from Sanitizer (MW 1.17)
			//removed 'pre' since pre seems to be not allowed in annotation values
			$tags = array( # Tags that must be closed
				'b', 'del', 'i', 'ins', 'u', 'font', 'big', 'small', 'sub', 'sup', 'h1',
				'h2', 'h3', 'h4', 'h5', 'h6', 'cite', 'code', 'em', 's',
				'strike', 'strong', 'tt', 'var', 'div', 'center',
				'blockquote', 'ol', 'ul', 'dl', 'table', 'caption',
				'ruby', 'rt' , 'rb' , 'rp', 'p', 'span', 'abbr', 'dfn',
				'kbd', 'samp'
			);
			$tags = array_merge($tags, array(
				'br', 'hr', 'li', 'dt', 'dd'
			));
			$tags = array_merge($tags, array( # Elements that cannot have close tags
				'br', 'hr'
			));
			$tags = array_merge($tags, array( # Tags that can be nested--??
				'table', 'tr', 'td', 'th', 'div', 'blockquote', 'ol', 'ul',
				'dl', 'font', 'big', 'small', 'sub', 'sup', 'span'
			));
			$tags = array_merge($tags, array( # Can only appear inside table, we will close them
				'td', 'th', 'tr',
			));
			$tags = array_merge($tags, array( # Tags used by list
				'ul','ol',
			));
			$tags = array_merge($tags, array( # Tags that can appear in a list
				'li',
			));

			global $wgAllowImageTag;
			if ( $wgAllowImageTag ) {
				$tags = array_merge($tags, array('img'));
			}
			self::$tagWhitelist = '<'.implode('><', $tags).'>';
		}
		return self::$tagWhitelist;
	}
	
	/*
	 * Enables the Term Import Bot to create a term with a
	 * safer article name, when creating an article with the
	 * original article name failed.
	 */
	public function getSaferArticleName(){
		//based on Title.php (MW 1.17)
		$articleName = $this->articleName;
		
		$rxTc = Title::getTitleInvalidRegex();
		
		$articleName = preg_replace( '/\xE2\x80[\x8E\x8F\xAA-\xAE]/S', '', $articleName );
		
		$articleName = preg_replace( '/[ _\xA0\x{1680}\x{180E}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}]+/u', '_', $articleName );
		
		$articleName = trim($articleName, '_');

		$articleName = str_replace(UTF8_REPLACEMENT, '', $articleName);
		
		$articleName = preg_replace($rxTc, '', $articleName );

		while(strpos($articleName, '/') === 0){
			$articleName = substr($articleName, 1);
		}
		
		$articleName = str_replace('~~~', '', $articleName);
		
		$articleName = substr($articleName, 0, 254);

		$this->articleName = $articleName;
		
		return $articleName;
	}
}

class DITermImportCallback{
	
	private $methodName;
	private $params;
	
	public function __construct($methodName, $params){
		$this->methodName = $methodName;
		$this->params = $params;
	}
	
	public function getMethodName(){
		return $this->methodName;
	}
	
	public function getParams(){
		return $this->params;
	}
}

