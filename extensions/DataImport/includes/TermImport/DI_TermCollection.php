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

//todo:document this

class DITermCollection {
	
	private $terms = array();
	private $errorMsgs = array();
	
	public function addTerm($term){
		$this->terms[] = $term;
	}
	
	public function getTerms(){
		return $this->terms;
	}
	
	public function addErrorMsg($msg){
		$this->errorMsgs[] = $msg;
	}
	
	public function getErrorMsgs(){
		return $this->errorMsgs;
	}
	
}


class DITerm {

	private $articleName = '';
	private $props = array();
	
	private $callbacks = array();
	private $isAnnonymousCallbackTerm = false;
	
	private $propsForAnnotations;
	private $propsForTemplates;
	
	public static $tagWhitelist;

	public function setArticleName($articleName){
		$articleName = strip_tags($articleName);
		$this->articleName = $articleName; 
	}
	
	public function getArticleName(){
		return $this->articleName; 
	}
	
	public function addProperty($prop, $value){
		if(is_array($value)){
			
			foreach($value as $val){
				$this->addProperty($prop, $val);
			}
				
		} else {
		
			$value = trim($value);
			
			if (strlen($value) > 0) {
				if(!array_key_exists($prop, $this->props)){
					$this->props[$prop] = array();
				}
				$this->props[$prop][] = $value;
			}
		}
	}
	
	public function getProperties($sAnnotations=true){
		return $this->initializeReturnValues($sAnnotations);
	}
	
	private function initializeReturnValues($sAnnotations){
		//todo: document this
		
		$tagWhitelist = self::getTagWhitelist();
		
		if($sAnnotations){
			if(is_null($this->propsForAnnotations)){
				$this->propsForAnnotations = array();
				foreach($this->props as $prop => $values){
					foreach($values as $value){
						$value = str_replace(
							array('[', ']', '{', '}', '|'),
							array('&#91;', '&#93;', '&#123;', '&#125;', '&#124;'),
							$value);
						
						$this->propsForAnnotations[$prop][] = 
							strip_tags($value, $tagWhitelist);	
					}
				}
			}	
			return $this->propsForAnnotations; 
		} else {
			if(is_null($this->propsForTemplates)){
				$this->propsForTemplates = array();
				foreach($this->props as $prop => $value){
					foreach($values as $value){
						$this->propsForTemplates[$prop][] = 
							$completeValue .= strip_tags($value, $tagWhitelist.'<pre>');;	
					}
				}
			}	
			return $this->propsForTemplates;
		}
	}
	
	public function getPropertyValue($propertyName, $asAnnotations=true){
		
		$props = $this->initializeReturnValues($asAnnotations);
		
		if(array_key_exists($propertyName, $props)){
			return $props[$propertyName];
		}
		
		return false;
	}
	
	public function getCallbacks(){
		return $this->callbacks;
	}
	
	public function addCallback(DITermImportCallback $callback){
		$this->callbacks[] = $callback;
	}
	
	public function isAnnonymousCallbackTerm(){
		return $this->isAnnonymousCallbackTerm;
	}
	
	public function setAnnonymousCallbackTerm($anonymousCallbackTerm){
		$this->isAnnonymousCallbackTerm = $anonymousCallbackTerm;
	}
	
	public static function getTagWhitelist(){
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

