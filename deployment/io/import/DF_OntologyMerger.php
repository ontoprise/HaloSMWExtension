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
 * derived from
 *   MediaWiki page data importer
 *   Copyright (C) 2003,2005 Brion Vibber <brion@pobox.com>
 *   http://www.mediawiki.org/
 *
 * Checks if an ontology can be properly imported and provides means to merge
 * it with existing versions.
 */

/**
 * @file
 * @ingroup DFIO
 *
 * The ontology merger component is used when a wiki page
 * page comes originally from at least two bundles. It handles
 * the merging and extraction process.
 *
 *
 * @author Kai Kühn
 *
 */
class OntologyMerger {

	/**
	 * Semantic patterns
	 *
	 * @var regular expressions (string)
	 */
	private static $PROPERTY_LINK_PATTERN;
	private static  $CATEGORY_LINK_PATTERN;


	public function __construct() {
		global $dfgLang;
		self::$PROPERTY_LINK_PATTERN = '/\[\[                 # Beginning of the link
                                    (?:([^:][^]]*):[=:])+ # Property name (or a list of those)
                                    ([^\[\]]*)            # content: anything but [, |, ]
                                    \]\]                  # End of link
                                    /xu';


		self::$CATEGORY_LINK_PATTERN = '/\[\[                 # Beginning of the link
                                    (?:\s*'.$dfgLang->getLanguageString('category').'\s*:)+  # Property name (or a list of those)
                                    ([^\[\]]*)            # content: anything but [, |, ]
                                    \]\]                  # End of link
                                    /ixu';
	}

	public function containsAnyBundle($wikiText) {

		$pattern = '/<!--\s*BEGIN ontology: /';
		$begin = preg_match($pattern, $wikiText, $matches) > 0;
		$pattern = '/<!--\s*END ontology: /';
		$end = preg_match($pattern, $wikiText, $matches) > 0;
		return $begin && $end;
	}
	
	public function getAllBundles($wikiText) {
		$pattern = '/<!--\s*BEGIN ontology:\s*(([^-]|-[^-]|--[^>])+)\s*-->/i';
		preg_match_all($pattern, $wikiText, $matches);
		array_walk($matches[1], array($this, 'trim'));
		return $matches[1];
	}
	
	private function trim(& $t, $i) {
		$t = trim($t);
	}

	public function containsBundle($bundleID, $wikiText) {
		$bundleID = preg_quote($bundleID);
        $bundleID = str_replace("/","\\/", $bundleID);
		$pattern = '/<!--\s*BEGIN ontology:\s*'.$bundleID.'\s*-->([^<]*)<!--\s*END ontology:\s*'.$bundleID.'\s*-->/i';
		return preg_match($pattern, $wikiText, $matches) > 0;
	}

	public function removeBundle($bundleID, $wikiText) {
		$bundleID = preg_quote($bundleID);
		$bundleID = str_replace("/","\\/", $bundleID);
		$pattern = '/<!--\s*BEGIN ontology:\s*'.$bundleID.'\s*-->([^<]*)<!--\s*END ontology:\s*'.$bundleID.'\s*-->/i';
		return preg_replace($pattern, "", $wikiText);
	}

	public function addBundle($bundleID, $wikiText, $annotations = "") {
		$wikiText .= "\n\n<!-- BEGIN ontology: $bundleID -->";
		$wikiText .= "\n$annotations";
		$wikiText .= "\n<!-- END ontology: $bundleID -->";
		return $wikiText;
	}
	
    public function getBundleContent($bundleID, $wikiText) {
        $bundleID = preg_quote($bundleID);
        $bundleID = str_replace("/","\\/", $bundleID);
        $pattern = '/<!--\s*BEGIN ontology:\s*'.$bundleID.'\s*-->([^<]*)<!--\s*END ontology:\s*'.$bundleID.'\s*-->/i';
        $res = preg_match($pattern, $wikiText, $match);
        if ($res == 0) return NULL;
        return $match[1];
    }

	public function getSemanticData($bundleID, $wikiText) {
		if (!$this->containsBundle($bundleID, $wikiText)) {
			return NULL;
		}
		$bundleID = preg_quote($bundleID);
        $bundleID = str_replace("/","\\/", $bundleID);
		$pattern = '/<!--\s*BEGIN ontology:\s*'.$bundleID.'\s*-->([^<]*)<!--\s*END ontology:\s*'.$bundleID.'\s*-->/i';
		preg_match_all($pattern, $wikiText, $matches);

		if (isset($matches[1])) {
			$annotations = reset($matches[1]); // should be only this
			$datavalues = $this->getAnnotations($annotations);
			$categories = $this->getCategories($annotations);
			return array($datavalues, $categories);
		}
		return NULL;
	}

	private function getAnnotations($text) {
		$propertyMatches = array();
			
		preg_match_all(self::$PROPERTY_LINK_PATTERN, $text, $propertyMatches);
		$value = '';
		$caption = false;

		$i = 0;
		foreach($propertyMatches[2] as $value) {

			$parts = explode( '|', $value );
			if ( array_key_exists( 0, $parts ) ) {
				$value = $parts[0];
			}
			if ( array_key_exists( 1, $parts ) ) {
				$caption = $parts[1];
			}
			$propertyName = trim($propertyMatches[1][$i]);
			$i++;
			$annotations[] = array($propertyName, $value);
		}


		return $annotations;


	}

	private function getCategories($text) {
		$categoryMatches = array();
		preg_match_all(self::$CATEGORY_LINK_PATTERN, $text, $categoryMatches);
		$value = '';
		$caption = false;
		$categories = array();
		foreach($categoryMatches[1] as $value) {

			$parts = explode( '|',$value);
			if ( array_key_exists( 0, $parts ) ) {
				$value = trim($parts[0]);
			}
			if ( array_key_exists( 1, $parts ) ) {
				$caption = trim($parts[1]);
			}
			$categories[] = $value;
		}
		return $categories;
	}


}
