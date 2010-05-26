<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * 
 * @file
 * @ingroup SMWHaloArticlepages
 * 
 * @defgroup SMWHaloArticlepages SMWHalo Articlepages
 * @ingroup SMWHalo
 *  
 * @author Kai K�hn
 * 
 * Extended handling for category description pages. 
 * 
 * The original category page is augmented by additional information:
 * - relations of a category
 * - attributes of a category
 * - pages that have a domain hint but which are neither relation nor attribute
 *
 */

if( !defined( 'MEDIAWIKI' ) )
	die( 1 );

global $IP, $wgHooks;
require_once( "$IP/includes/CategoryPage.php");
//$wgHooks['CategoryPageView'][] = 'smwfSemanticCategoryPage';

/**
 * Hook for category page
 */
//function smwfSemanticCategoryPage(& $categoryPage) {
//	$newCategoryPage = new SMWCategoryPage($categoryPage->getTitle());
//	if ( NS_CATEGORY == $newCategoryPage->mTitle->getNamespace() ) {
//			$newCategoryPage->openShowCategory();
//	}
//
//	# If the article we've just shown is in the "Image" namespace,
//	# follow it with the history list and link list for the image
//	# it describes.
//	if ( NS_CATEGORY == $newCategoryPage->mTitle->getNamespace() ) {
//			$newCategoryPage->closeShowCategory();
//	}
//	return false;
//}
/**
 * Extends the original CategoryPage. 
 */
class SMWCategoryPage extends CategoryPage {
	
	/**
	 * Overwrites the original method and installs the extended SMWCategoryViewer.
	 */
	function closeShowCategory() {
		global $wgOut, $wgRequest;
		$from = $wgRequest->getVal( 'from' );
		$until = $wgRequest->getVal( 'until' );

		$viewer = new SMWCategoryViewer( $this->mTitle, $from, $until );
		$wgOut->addHTML( $viewer->getHTML() );
	}
}

/**
 * Extends CategoryViewer and provides the additional information.
 */
class SMWCategoryViewer extends CategoryViewer {

	function __construct( $title, $from = '', $until = '' ) {
		parent::__construct($title, $from, $until);
	}
	
	/**
	 * Format the category data list.
	 *
	 * @param string $from -- return only sort keys from this item on
	 * @param string $until -- don't return keys after this point.
	 * @return string HTML output
	 * @private
	 */
	function getHTML() {
		global $wgOut, $wgCategoryMagicGallery, $wgCategoryPagingLimit;
		wfProfileIn( __METHOD__ );

		$this->showGallery = $wgCategoryMagicGallery && !$wgOut->mNoGallery;

		$this->clearCategoryState();
		$this->doCategoryQuery();
		$this->finaliseCategoryState();

		$r = $this->getCategoryTop() .
			$this->getSubcategorySection() .
			$this->getSemanticSearchLinks() .
			$this->getPagesSection() .
			$this->getImageSection() .
			$this->getProperties().
			$this->getCategoryBottom();

		wfProfileOut( __METHOD__ );
		return $r;
	}
	
	function getSemanticSearchLinks() {
		global $wgContLang;
		$html = "<h2>".wfMsg('smw_category_queries')."</h2>\n";
		$query = htmlentities('[['.$wgContLang->getNsText(NS_CATEGORY).':'.$this->title->getText().']]');
		$link = $this->getSkin()->makeKnownLinkObj(Title::newFromText('Ask', NS_SPECIAL), 
			wfMsg('smw_category_askforallinstances', $this->title->getText()), 'query='.$query.'&order=ASC');
		return $html.$link;
	}
	
	/**
	 * Formats the properties of this category i.e. the relations and attributes
	 * whose domain is this category.
	 * 
	 * @return string HTML for rendering the properties of this category. 
	 */
	private function getProperties() {

		$options = new SMWRequestOptions();
		$options->limit = $this->limit + 1;
		$options->sort = true;
		$reverse = false;
		if ($this->from != '') {
			$options->boundary = $this->from;
			$options->ascending = true;
			$options->include_boundary = true;
		} elseif ($this->until != '') {
			$options->boundary = $this->until;
			$options->ascending = false;
			$options->include_boundary = false;
			$reverse = true;
		}

		// List relations
		$r = '<a name="SMWResults"></a> <div id="mw-pages">';
			$r .= '<h2>' . wfMsg('smw_category_schemainfo',$this->title->getText()) . "</h2>\n";
		$r .= "</div>";
		
		$pl = $this->getShortRelationList($options, true);
		$pl .= $this->getShortRelationList($options, false);
		
		if (empty($pl)) {
			return "";
		}
		return $r.$pl;
		
	}
	
	/**
	 * Format a list of the category's properties.
	 * 
	 * Lists the following information:
	 * - Relations of this category
	 * - Attributes of this category
	 * - Articles that have a domain hint, but are neither relation nor attribute.
	 *   This is probably an error in the user's model.
	 * 
	 * Redirects:
	 * If a normal article has a domain hint and is redirected from a relation or
	 * attribute it is listed with the corresponding kind of property. But this 
	 * also indicates an error in the user's model.
	 * 
	 * @param SMWRequestOptions $options Search options for the database query
	 * @param boolean $domain If <true> the properties whose domain is this 
	 *             category are listed. Otherwise those whose range is this 
	 *             category.
	 * 
	 */
	private function getShortRelationList($options, $domain) {
		global $smwgHaloContLang;
		
		$ti = htmlspecialchars( $this->title->getText() );
			
		// retrieve all properties of this category
	
		$properties = $domain ? smwfGetSemanticStore()->getPropertiesWithDomain($this->title) :
									smwfGetSemanticStore()->getPropertiesWithRange($this->title);
		                      
		$r = $this->getPropertyList(SMW_NS_PROPERTY, $properties, $domain);   
		//$r .= $this->getPropertyList(SMW_NS_ATTRIBUTE, $properties, $domain);
		$r .= $this->getPropertyList(-1, $properties, $domain);                
		                               
		return $r;           
	}
	
	/**
	 * Returns a property list with a specific namespace as HTML table.
	 * @param int $ns ID of the namespace of the properties of interest 
	 *            (SMW_NS_PROPERTY, SMW_NS_ATTRIBUTE, -1 (=any namespace))
	 * @param array(Title) $properties All title object whose domain is the
	 *                     category.
	 * @param boolean $domain If <true> the properties whose domain is this 
	 *             category are listed. Otherwise those whose range is this 
	 *             category.
	 * @return string HTML with the table of properties
	 */
	private function getPropertyList($ns, $properties, $domain) {
		global $wgContLang;
		global $smwgHaloContLang;
		
		$props = array();
		$store = smwfGetStore();
		$sspa = $smwgHaloContLang->getSpecialSchemaPropertyArray();
		
		$relationDV = SMWPropertyValue::makeProperty($sspa[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT]);
		$hastypeDV = SMWPropertyValue::makeProperty("_TYPE");          
		                              
		foreach ($properties as $prop) {
			if (!$prop) {
				// $prop may be undefined
				continue;
			}
			$propFound = false;
			if ($prop->getNamespace() == $ns) {
				// Property with namespace of interest found
				$props[] = $prop;
				$propFound = true;
			} else if ($prop->getNamespace() != SMW_NS_PROPERTY) {
				// The property is neither a relation nor an attribute. It is 
				// probably redirected from one of those or it is wrongly annotated
				// with a domain hint.				    
				$titleName = $prop->getText();
				$redirects = array();
				$redirects[] = $prop;
				$nsFound = false;
				
				// Collect all redirects in an array.
				while (($rdSource = $this->getRedirectFrom($titleName)) != null) {
					$redirects[] = $rdSource;
					if ($rdSource->getNamespace() == $ns) {
						$nsFound = true;
						break;
					}
					$titleName = $rdSource->getText(); 
				}
				
				if ($nsFound === true || $ns == -1) {
					$props[] = $redirects;
					$propFound = true;
				}
			}
			if ($propFound) {
				// Find the range of the property
				$range = null;
				$type = $store->getPropertyValues($prop, $hastypeDV);
				if (count($type) > 0) {
					$type = $type[0];
					$xsd = $type->getXSDValue();
					if ($xsd != '_wpg') {
						$range = $type;
					}
				}
					
				if ($range == null) {
					$range = $store->getPropertyValues($prop, $relationDV);
					$rangePageContainers = array();
					foreach($range as $c) {
						$h = $c->getDVs();
						 
						$domainCatValue = reset($h);
						$rangeCatValue = next($h);
						if ($rangeCatValue != NULL) $rangePageContainers[] = $rangeCatValue;
					}
					$range = $rangePageContainers;
				}
				 
				$props[] = $range;
			}
			
		}
				
		$ac = count($props);
		if ($ac == 0) {
			// No properties => return
			return "";
		}

		$r="";
		$r = '<a name="SMWResults"></a> <div id="mw-pages">';
	    if ($ns == SMW_NS_PROPERTY) {
			if ($domain) {
				$r .= '<h4>' . wfMsg('smw_category_properties',$this->title->getText()) . "</h4>\n";
			} else {
				$r .= '<h4>' . wfMsg('smw_category_properties_range',$this->title->getText()) . "</h4>\n";
			}
		} else if (count($props) > 0) {
			// Pages with a domain, that are neither relation nor attribute
			if ($domain) {
				$r .= '<h4>' . wfMsg('smw_category_nrna',$this->title->getText()) . "</h4>\n";
				$r .= wfMsg('smw_category_nrna_expl'). "\n";
			} else {
				$r .= '<h4>' . wfMsg('smw_category_nrna_range',$this->title->getText()) . "</h4>\n";
				$r .= wfMsg('smw_category_nrna_range_expl'). "\n";
			}
		}
		
		$r .= "</div>";
		
		$r .= '<table style="width: 100%;" class="smw-category-schema-table smwtable">';
		if ($ns == SMW_NS_PROPERTY) {
			$r .= '<tr><th>Property</th><th>Range/Type</th></tr>';
		} 
		$prevchar = 'None';
		for ($index = 0; $index < $ac; $index +=2 ) {
			
			// Property name
			if (is_array($props[$index])) {
				// Handle list of redirects
				$redirects = $props[$index];
				$r .= '<tr><td>';
				$rc = count($redirects);
				
				for ($i = 0; $i < $rc; $i++) {
					if ($i == 1) {
						$r .= ' <span class="smw-cat-redirected-from">(redirected from: ';
					}
					$rd = $redirects[$i];
					$pt = $rd->getPrefixedText();
					$searchlink = SMWInfolink::newBrowsingLink('+',$pt);
					$link = $this->getSkin()
					           ->makeKnownLinkObj($rd, 
				                                  $wgContLang->convert($rd->getText()));
				    $link = preg_replace("/(.*?)(href=\".*?)\"(.*)/","$1$2?redirect=no\"$3",$link);
				    $r .= $link;
					$r .= $searchlink->getHTML($this->getSkin()). " ";
				}
				if ($rc > 1) {
					$r .= ')</span>';
				}
				$r .= '</td><td>';
			} else {
				$searchlink = SMWInfolink::newBrowsingLink('+',$props[$index]->getPrefixedText());
				$r .= '<tr><td>' . $this->getSkin()->makeKnownLinkObj( $props[$index], 
				  $wgContLang->convert( $props[$index]->getText() ) ) . 
				  '&nbsp;' . $searchlink->getHTML($this->getSkin()) .
				  '</td><td>';
			}
			// Show the range
			if (is_array($props[$index+1])) {
				$range = $props[$index+1];
				if (count($range) > 0) { ///FIXME this check is just for compatibility reasons and as catch for obscure and buggy code; the class of $range[0] should not vary between different possibilities.
					if ($range[0] instanceof SMWWikiPageValue) {
						$r .= $this->getSkin()->makeKnownLinkObj($range[0]->getTitle(), 
				                                                 $wgContLang->convert($range[0]->getTitle()->getText()));
					} elseif ($range[0] instanceof SMWDataValue) {
						$r .= $range[0]->getShortHTMLText();
					} else {
						$r .= $range[0];
					}
				}
			} else if ($props[$index+1] instanceof SMWTypesValue) {
				$t = $props[$index+1];
				$t = $t->getTypeLabels();
				$r .= $t[0];
			}
			$r .= "</td></tr>\n";
		}
		$r .= '</table>';
		
		return $r;
		
	}
	
	/**
	 * Tries to find the title that is redirected to <$targetTitle>.
	 * 
	 * @param Title $targetTitle This title is the target of a redirect.
	 * @return Title This title is redirected to <$targetTitle> or <null>, if no 
	 *               title is found.
	 */
	private function getRedirectFrom($targetTitle) {

		$result = null;
		$db =& wfGetDB( DB_SLAVE ); 
		
		// Find out from where title is redirected
		$sql = 'rd_title=' . $db->addQuotes($targetTitle);
		$res = $db->select( $db->tableName('redirect'),
							'rd_namespace,rd_from',
							$sql, 'SMW_CategoryPage::getPropertyList');
							
		// reqrite results as array
		if($db->numRows( $res ) > 0) {
			if ($row = $db->fetchObject($res)) {
				$result = Title::newFromID($row->rd_from);
			}
		}
		$db->freeResult($res);
		
		return $result;
	}
	
}



