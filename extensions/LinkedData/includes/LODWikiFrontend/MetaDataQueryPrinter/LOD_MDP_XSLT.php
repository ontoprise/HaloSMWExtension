<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the class LODMetaDataFormatter.
 * 
 * @author Thomas Schweitzer
 * Date: 21.09.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---

/**
 * This meta-data printer shows an error message if the requested printer was
 * not found.
 * 
 * @author Thomas Schweitzer
 * 
 */
class LODMDPXslt extends LODMetaDataPrinter {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	
	// array(string => DOMDocument): Stores the stylesheets for the transformation
	private static $mStylesheets = array();  
	
	// string: The name of the XSL that is used by this printer. It is the key
	//   in the array $mStylesheets.
	private $mXSLName;
	
	//
	private $mXmlDocument;
	
	//--- Constructor ---
	
	/**
	 * Constructor for LODMDPTable
	 *
	 * @param SMWQuery $query
	 * 		The meta-data of the result of this query will be processed. 
	 * @param SMWQueryResult $queryResult
	 * 		This is the result of $query. 
	 */		
	function __construct(SMWQuery $query, SMWQueryResult $queryResult) {
		parent::__construct($query, $queryResult);
		
		// The query contains the parameter metadatastylesheet which points to
		// an article that contains the style for transforming the XML of the
		// meta-data into HTML
		$this->storeXSL($query);
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	
	/**
	 * This function takes the wiki text of the data value $value and augments 
	 * it with the HTML of the value's meta-data.
	 * 
	 * @param SMWDataValue $value
	 * 		The meta-data of this value will be formatted by the printer.
	 * @param string $wikiText
	 * 		The wiki text to augment
	 * @return string
	 * 		The augmented wiki text
	 */
	public function attachMetaDataToWikiText(SMWDataValue $value, $wikiText) {
		$md = $value->getMetadataMap();
		if (empty($md)) {
			$metaDataHTML = wfMsg('lod_mdp_no_metadata');
		} else {
			$xml  = $this->generateXML($md);
			$xsl  = $this->getXSL();
			$metaDataHTML = $this->transformXML($xml, $xsl);
		}
		return '<span class="lodMetadata">' 
				. $wikiText 
				. '<span class="lodMetadataContent">' 
				.  $metaDataHTML
				. '</span></span>';
		
	}
	
	/**
	 * This meta-data printer makes use of the jQuery qTip extension for showing
	 * tool-tips with meta-data. It adds a script that enables these tool-tips.
	 */
	public function addJavaScripts() {
		self::addJS("LOD_MetaDataQTip.js");
	}
	
	/**
	 * Adds the style sheets for the table in the tool-tip.
	 */
	public function addStyleSheets() {
		self::addCSS('metadata.css');
	}

	/**
	 * Returns the style for the transformation.
	 * 
	 * @return string
	 * 		The XSL for the transformation of the meta-data XML to HTML
	 */
	public function getXSL() {
		return self::$mStylesheets[$this->mXSLName];
	}
	
	//--- Private methods ---
	
	private function generateXML($metaData) {
		$id = uniqid (rand());
		$count = 0;
		
		$metadataTags = "<metadata id=\"".$id."_meta_".$count."\">";
		// read metadata
		foreach($metaData as $mdProperty => $mdValues) {
			foreach($mdValues as $mdValue) {
				$metadataTags .= "<property name=\""
					.htmlspecialchars($this->translateMDProperty($mdProperty))
					."\">"
					.htmlspecialchars($mdValue)
					."</property>";
			}
		}
		$metadataTags .= "</metadata>";
		
		return $metadataTags;
	}
	
	/**
	 * Transforms the xml containing meta-data with the given style into HTML.
	 * @param string $xml
	 * 		The meta-data in XML
	 * @param DOMDocument $xslDoc
	 * 		The style for the transformation
	 * @return string
	 * 		The generated HTML
	 */
	private function transformXML($xml, $xslDoc) {
		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML(trim($xml));

		$proc = new XSLTProcessor();
		$proc->importStylesheet($xslDoc);
		$html = $proc->transformToXML($xmlDoc);

		return $html;
	}
	
	/**
	 * The query should contain the parameter "metadatastylesheet" that points
	 * to an article in the wiki. This article contains the stylesheet embedded
	 * in <pre> tags. This content is retrieved and stored with the value of 
	 * "metadatastylesheet" as key in the static array $this->mStylesheets.
	 * @param SMWQuery $query
	 * 		The query with the parameter "metadatastylesheet".
	 */
	private function storeXSL(SMWQuery $query) {
		
		// Get the parameter "metadatastylesheet" from the query
		$xslName = @$query->params['metadatastylesheet'];
		
		if (!isset($xslName)) {
			$this->mXSLName = 'ErrorMissingParamMetaDataStylesheet';
			if (array_key_exists($this->mXSLName, self::$mStylesheets)) {
				return;
			}
			self::$mStylesheets[$this->mXSLName] = 
					$this->getErrorXSL('lod_mdp_xsl_missing_meta_data_stylesheet');
			return;
		}

		// retrieve the content of the article that contains the XSL
		$xsl = $this->getContentOfArticle($xslName);
		if (is_null($xsl)) {
			$this->mXSLName = 'ErrorArticleDoesNotExist';
			if (array_key_exists($this->mXSLName, self::$mStylesheets)) {
				return;
			}
			self::$mStylesheets[$this->mXSLName] = 
					$this->getErrorXSL('lod_mdp_xsl_missing_article', $xslName);
			return;
		}
		
		$this->mXSLName = $xslName;
		 
		// Parse the XSL that is embedded in <pre> tags.
		preg_match("/<pre>(.*)<\/pre>/s", $xsl, $matches);
		
		$xsl = @$matches[1];
		if (!isset($xsl)) {
			$articleName = $this->mXSLName;
			$this->mXSLName = 'ErrorEmbedXSLinPre';
			if (array_key_exists($this->mXSLName, self::$mStylesheets)) {
				return;
			}
			self::$mStylesheets[$this->mXSLName] = 
					$this->getErrorXSL('lod_mdp_xsl_missing_pre', $articleName);
			return;
		}
		
		$xslDoc = new DOMDocument();
		$xslDoc->loadXML(trim($xsl));

		self::$mStylesheets[$this->mXSLName] = $xslDoc;
	}
	
	/**
	 * Retrieves the content (wiki text) of the article with the name $articleName.
	 * @param string $articleName
	 * 		Name of the article whose wiki text is retrieved.
	 * 
	 * @return string 
	 * 		Returns the wiki text of the specified article or <null> if the 
	 * 		article does not exist.
	 */
	private function getContentOfArticle($articleName) {
		$titleObj = Title::newFromText($articleName);
		$article = new Article($titleObj);

		return ($article->exists()) 
			? $article->getContent()
			: null;

	}
	
	/**
	 * Creates an XSL for errors.
	 * 
	 * @param string $msgID
	 * 		A message ID for wfMsg().
	 * @param string $articleName
	 * 		The name of the article that contains the XSL.
	 */
	private function getErrorXSL($msgID, $articleName = null) {
		$msg = wfMsg($msgID, $articleName);
		$msg = htmlentities($msg);
		$errorXSL = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="iso-8859-1" indent="yes" />
	<xsl:template match="/metadata">
		$msg
	</xsl:template>
</xsl:stylesheet>
XSL;
		$xslDoc = new DOMDocument();
		$xslDoc->loadXML(trim($errorXSL));
		
		return $xslDoc;
	}
	
}