<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Interwiki-Article-Import-module in the Data-Import-Extension.
*
*   The DataImport-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The DataImport-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
  * @ingroup DIInterWikiArticleImport
  * 
 * This file contatins the class IAIArticleImporter for importing articles
 * from other Mediawikis.
 * 
 * @author Thomas Schweitzer
 * Date: 30.10.2009
 * 
 */

/**
 * This group contains all parts of the Data Import extension.that deal with the Interwiki Article Import
 * @defgroup DIInterWikiArticleImport
 * @ingroup DITermImport
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the IAI module. It is not a valid entry point.\n" );
}

 //--- Includes ---
global $iaigIP, $IP;
require_once("$IP/includes/HttpFunctions.php");

/**
 * This is the main class for importing articles (with templates and images)
 * from other Mediawikis by using the MediaWiki-API (see http://www.mediawiki.org/wiki/API).
 * 
 * @author Thomas Schweitzer
 * 
 */
class IAIArticleImporter  {
	
	//--- Constants ---
	/**
	 * Max. number of titles that can be downloaded with one call to the 
	 * Mediawiki API. (see http://www.mediawiki.org/wiki/API:Query#Specifying_titles)
	 *
	 */
	const TITLE_LIMIT = 50;	
	
	/**
	 * Timeout for HTTP requests in seconds
	 */
	const HTTP_TIMEOUT = 60;
		
	//--- Private fields ---
	
	/**
	 * Basic URL of the wiki from which articles can be imported e.g. 
	 * http://en.wikipedia.org/w/ . This is the URL to the API of the wiki. 
	 * The GET parameters are appended e.g. 
	 * "$mWikiBase"."api.php?action=query&titles=abc&export&exportnowrap"
	 *
	 * @var string
	 */
	private $mWikiBase;
	
	/**
	 * This is the orignal callback function of the importer (class WikiImporter).
	 * The callback is replaced for reporting purposes.
	 *
	 * @var function
	 */
	private $mImportCallback;
	
	/**
	 * A list of all pages that were imported, indexed by namespace and augmented
	 * with revision ID.
	 *
	 * @var array(string)
	 */
	private $mImportedPages = array();
	
	/**
	 * The list of actually imported articles. This is used
	 * to find the articles that could not be imported i.e. because they don't
	 * exist in the source wiki. (see $mMissingArticles)
	 *
	 * @var array(string)
	 * 
	 */
	private $mImportedArticles = array();
	
	/**
	 * List of articles that were imported in one iteration. This is used internally
	 * to resolve dependencies.
	 * 
	 * @var array(string)
	 */
	private $mLastImportedArticles = array();
	
	/**
	 * List of imported images.
	 *
	 * @var array(string)
	 */
	private $mImportedImages = array();
	
	/**
	 * List of skipped images.
	 *
	 * @var array(string)
	 */
	private $mSkippedImages = array();
	
	/**
	 * This is the list of articles that were specified for import but could not
	 * be downloaded for some reason.
	 *
	 * @var array(string)
	 */
	private $mMissingArticles = array();

	/**
	 * A list of all pages that were skipped during import.
	 *
	 * @var array(string)
	 */
	private $mSkippedPages = array();
	
	/**
	 * Start time of import
	 *
	 * @var string
	 */
	private $mImportStarted = "";
	
	/**
	 * Constructor for  IAIArticleImporter
	 *
	 * @param string $wikiBase
	 * 		Basic URL of the wiki from which articles can be imported.
	 */		
	function __construct($wikiBase) {
		$this->mWikiBase = $wikiBase;
	}
	

	//--- getter/setter ---
	public function getWikiBase()           {return $this->mWikiBase;}

	public function setWikiBase($wikiBase)  {$this->mWikiBase = $wikiBase;}
	
	//--- Public methods ---
	
	/**
	 * This method asks the source wiki for a list of article names, starting
	 * at $from. The articles are only searched in the namespace $namespace.
	 * $num article names are returned.
	 *
	 * @param string $from
	 * 		Articles that begin with this string or the next ones in the alphabetic
	 * 		index.
	 * @param int $namespace
	 * 		Number of the namespace from which the article names are retrieved
	 * @param int $num
	 * 		Number of articles to return
	 * 
	 * @return array(string)
	 * 		Array of article names
	 * @throws
	 * 		IAIException
	 * 
	 */
	public function getArticles($from, $namespace, $num) {
		$apiURL = $this->mWikiBase.
		          'api.php?action=query&list=allpages&apfrom='.
				  urlencode($from).
				  "&aplimit=$num&format=xml&apnamespace=$namespace";
		$xml = Http::get($apiURL, self::HTTP_TIMEOUT);
		if ($xml === false) {
			throw new IAIException(IAIException::HTTP_ERROR, $apiURL);
		}
		
		$articles = array();
		// Get all article names from the xml
		$domDocument = new DOMDocument();
		$success = $domDocument->loadXML($xml);
		$domXPath = new DOMXPath($domDocument);
		$nodes = $domXPath->query('//query/allpages/p/@title');
		foreach ($nodes as $node) {
			$articles[] = $node->nodeValue;
		}
		
		return $articles;
		
	}
		
	/**
	 * Imports the given articles from the wiki that was specified in the 
	 * constructor. The articles can be imported with depending templates and
	 * images. 
	 * A page that reports the results and failures can be created if 
	 * you call startReport() before and createReport() after this method. 
	 *
	 * @param array(string) $articles
	 * 		An array of article names that are to be imported
	 * @param bool $importTemplates
	 * 		If <true> all depending templates of the imported articles are 
	 * 		imported as well. Templates are imported in an iterative process so
	 * 		that even nested templates are considered. 
	 * @param bool $importImages
	 * 		Images that are referenced in the imported articles are downloaded
	 * 		and installed.
	 * @param bool $skipExistingArticles
	 *		If <true>, articles that already exist in the destination wiki are
	 * 		skipped. They are not overwritten with the latest version of the
	 * 		source wiki.
	 * @throws
	 * 		IAIException
	 * 
	 */
	public function importArticles($articles, $importTemplates = true, 
								   $importImages = true, $skipExistingArticles = true) {
								   	
		// Import all specified articles
		$this->transferArticles($articles, $skipExistingArticles);
			
		// Find all missing articles for report
		$articlesToImport = $articles;
		foreach ($articlesToImport as $k => $a) {
			$articlesToImport[$k] = str_replace('_', ' ', $a);
		}
		$skipped = array();											 
		foreach ($this->mSkippedPages as $ns => $sp) {
			foreach ($sp as $k => $pr) {
				$skipped[] = str_replace('_', ' ', $pr[0]);
			}
		}
		$this->mMissingArticles = array_diff($articlesToImport, 
											 $this->mImportedArticles, $skipped);
		
		// resolve template dependencies of all articles that were previously
		// imported
		if ($importTemplates) {
			$this->importTemplates($this->mLastImportedArticles);
		}
			
		// import images
		if ($importImages) {
			// Import the images of all imported pages
			$this->importImagesForArticle($this->mImportedArticles);
		}
	}
	
	/**
	 * This method imports all templates required for the articles given in
	 * $forArticles.
	 * A page that reports the results and failures can be created if 
	 * you call startReport() before and createReport() after this method. 
	 * 
	 * @param array(string) $forArticles
	 * 		The templates of these articles are imported.
	 * @param bool $reevaluate
	 * 		If <true> all template dependencies for all articles are reevaluated
	 * 		i.e. all articles are saved again to find new dependencies.
	 * @throws
	 * 		IAIException
	 *  
	 */
	public function importTemplates($forArticles, $reevaluate = true) {

		$this->mLastImportedArticles = array();
		// First examine all articles in one step
		$templates = $this->getMissingTemplates($forArticles);
		if (!empty($templates)) {
			$this->transferArticles($templates);
		} else if (!$reevaluate) {
			// no templates missing
			return;
		}
		$reevaluate = false;
		
		// now treat all articles separately
		do {
			foreach ($forArticles as $index => $a) {
				// Save the article
				$t = Title::newFromText($a);
				$a = $t->getDBkey();
				$prevMissingTemplate = null;
				do {
					$article = new Article($t);
					echo "Saving article: {$t->getFullText()} \n";
					// The article has to be saved because all of its templates have
					// to be evaluated in order to find missing templates.
					$article->doEdit($article->getContent(), "Find missing templates.",
									 EDIT_UPDATE | EDIT_MINOR | EDIT_SUPPRESS_RC);
					// Still templates missing?
					$missingTemplates = $this->getMissingTemplates(array($a));

					// Check if the set of missing templates has changed. Otherwise
					// this could lead to an endless loop.
					if (!empty($missingTemplates) && !is_null($prevMissingTemplate)) {
						$diff = array_diff($missingTemplates, $prevMissingTemplate);
						if (count($diff) == 0) {
							// Nothing changed. The same templates are missing
							// => stop finding templates for this article.
							break;
						}
					}

					if (!empty($missingTemplates)) {
						echo "\t".count($missingTemplates)." templates missing.\n";
						$this->transferArticles($missingTemplates);
					}
					$prevMissingTemplate = $missingTemplates;
				} while (!empty($missingTemplates));
			}
			$forArticles = $this->mLastImportedArticles;
			$this->mLastImportedArticles = array();
		} while (!empty($forArticles));
		
	}
	
	/**
	 * This method imports all images required for the articles given in
	 * $forArticles.
	 * A page that reports the results and failures can be created if 
	 * you call startReport() before and createReport() after this method. 
	 * 
	 *
	 * @param array(string) $forArticles
	 * 		The images of these articles are imported.
	 * 
	 * @throws
	 * 		IAIException
	 * 
	 */
	public function importImagesForArticle($forArticles) {
		
		$images = $this->getMissingImages($forArticles);
		$this->importImages($images);
	}
	
	
	/**
	 * This method imports all images given in $images.
	 * A page that reports the results and failures can be created if 
	 * you call startReport() before and createReport() after this method. 
	 * 
	 * @param array(string) $images
	 * 		Names of the images that will be imported from the source wiki. 
	 * 		If no namespace is given it will be appended automatically.
	 * @param bool $createReport (default: false)
	 * 		If <true>, a report for this operation is created.
	 * 
	 * @throws
	 * 		IAIException(IAIException::HTTP_ERROR) 
	 * 			if the HTTP request fails
	 *   
	 */
	public function importImages($images) {
		// Add namespace if necessary
		global $wgContLang, $iaigTempDir;
		$imgNs = $wgContLang->getNsText(NS_IMAGE).":";
		
		foreach ($images as $k => $i) {
			if (strpos($i, $imgNs) === false) {
				$images[$k] = $imgNs.$i;
			}
		}
		$imageURLs = $this->getImageURLs($images);

		foreach ($imageURLs as $img) {
			$base = urldecode(wfBaseName($img));
			
			$title = Title::makeTitleSafe( NS_FILE, $base );
			if( !is_object( $title ) ) {
				echo( "{$base} could not be imported; a valid title cannot be produced\n" );
				continue;
			}
		
			# Check existence
			$image = wfLocalFile( $title );
//TODO replace $options['overwrite'] with a field in this class		
			if( $image->exists() ) {
//				if( isset( $options['overwrite'] ) ) {
//					echo( "{$base} exists, overwriting..." );
//				} else {
					echo( "{$base} exists, skipping\n" );
					$this->mSkippedImages[] = $base;
					continue;
//				}
			} else {
				echo( "Importing {$base}..." );
			}

			// Read image from source wiki
			$contents = Http::get($img, self::HTTP_TIMEOUT);
			if ($contents === false) {
				// HTTP-Timeout
				$this->mSkippedImages[] = $base;
				echo( "HTTP timeout for {$base}; skipping\n" );
				continue;
				
//				throw new IAIException(IAIException::HTTP_ERROR, $img);
			}
			
			
			$handle = fopen ($iaigTempDir.$base, "wb");
			fwrite($handle, $contents);
			fclose($handle);
			
			
			$this->mImportedImages[] = $base;
			
			$archive = $image->publish( $iaigTempDir.$base );
			if( WikiError::isError( $archive ) || !$archive->isGood() ) {
				echo( "failed.\n" );
				continue;
			} else {
				echo "success.\n";
			}
			$image->recordUpload($archive->value, "Imported with IAI Article Importer", "No license information" );
			unlink($iaigTempDir.$base);
		}
	
	}

	/**
	 * This function is called from the wiki importer when a <page>-tag is reached
	 * in the imported XML. Do not call from outside.
	 *
	 * @param string $page 
	 * 		Name of the page that is about to be processed
	 */
	public function reportPage( $page ) {
		echo "Page: $page \n";
	}
	
	/**
	 * This function can be called before articles, templates or images are 
	 * imported. It prepares the creation of a report.
	 *
	 */
	public function startReport() {
		$this->mImportStarted = date("Y-m-d H:i:s");
	}
	
	/**
	 * This method creates a report for the pages, templates and images imported 
	 * by this importer. Call this method after the import-methods of this class.
	 * 
	 * @param bool $createResultPage
	 * 		If <true> a new page is created in the wiki that contains the report.
	 * 		Otherwise the complete report is returned a string.
	 * 
	 * @return string
	 * 		If $createResultPage is <true> the name of the page containing the
	 * 		report is returned otherwise the complete report.
	 */
	public function createReport($createResultPage) {
		
		global $wgContLang;
		$report = "";
		
		/**
		 * Show statistics
		 */
		
		$now = date("Y-m-d H:i:s");
		
		$report .= "==Statistics==\n\n";
		$report .= "*Import started: {$this->mImportStarted}\n";
		$report .= "*Import ended: $now\n";
		$report .= ";Imported articles:\n";
		$num = count($this->mImportedPages);
		$report .= "*Number of namespaces of articles: $num\n";

		// Count number of imported articles.
		$numArticles = 0;
		foreach ($this->mImportedPages as $pages) {
			$numArticles += count($pages);
		}
		$report .= "*Number of imported articles: $numArticles\n";

		$report .= ";Skipped articles:\n";
		$num = count($this->mSkippedPages);
		$report .= "*Number of namespaces of skipped articles: $num\n";

		
		// Count number of skipped articles.
		$numArticles = 0;
		foreach ($this->mSkippedPages as $pages) {
			$numArticles += count($pages);
		}
		$report .= "*Number of skipped articles: $numArticles\n";
		
		$report .= ";Missing articles:\n";
		$numArticles = count($this->mMissingArticles);
		$report .= "* Number of missing articles: $numArticles\n";
		
		$report .= ";Imported images:\n";
		$num = count($this->mImportedImages);
		$report .= "*Number imported images: $num\n";
		$num = count($this->mSkippedImages);
		$report .= "*Number skipped images: $num\n";
		
		
		/**
		 * Show information about imported articles
		 */
		$report .= "==Imported articles==\n\n";
		
		// Iterate over all namespaces of imported pages
		foreach ($this->mImportedPages as $ns => $pages) {

			$namespace = ($ns == 0) ? "Main" : $wgContLang->getNsText($ns);
			
			$report .= "\n===$namespace===\n\n";
			$numArticles = count($pages);
			$report .= "Number of imported articles in namespace $namespace: $numArticles\n\n";
			foreach ($pages as $p) {
				$report .= "* [[{$p[0]}]] (Revision: {$p[1]})\n";
			}
		}
		
		/**
		 * Show information about skipped articles
		 */
		
		$report .= "==Skipped articles==\n\n";
		
		
		// Iterate over all namespaces of skipped pages
		foreach ($this->mSkippedPages as $ns => $pages) {

			$namespace = ($ns == 0) ? "Main" : $wgContLang->getNsText($ns);
			
			$report .= "\n===$namespace===\n\n";
			$numArticles = count($pages);
			$report .= "Number of skipped articles in namespace $namespace: $numArticles\n\n";
			foreach ($pages as $p) {
				$report .= "* [[{$p[0]}]] (Revision: {$p[1]})\n";
			}
		}
		
		/**
		 * Show information about missing articles
		 */
		
		$report .= "==Missing articles==\n\n";
		$report .= "The following articles were specified for import but could ".
		           "not be found in the source wiki or could not be downloaded ".
		           "because of some error.\n\n";		
		// Iterate over all missing pages
		$numArticles = count($this->mMissingArticles);
		$report .= "Number of missing articles: $numArticles\n\n";
		foreach ($this->mMissingArticles as $article) {
			$report .= "* $article\n";
		}
		
		/**
		 * Show information about imported images
		 */
		$report .= "==Images==\n\n";
		$report .= "===Imported images===\n\n";
		$namespace = $wgContLang->getNsText(NS_IMAGE);
		
		$pos = array("left", "center", "right");
		$i = 0;
		foreach ($this->mImportedImages as $img) {
			$report .= "[[$namespace:$img|thumb|200px|".
			           $pos[$i++%3].
			           "|$img]]\n";
		}
		
		$report .= "===Skipped images===\n\n";
		$i = 0;
		foreach ($this->mSkippedImages as $img) {
			$report .= "[[$namespace:$img|thumb|200px|".
			           $pos[$i++%3].
			           "|$img]]\n";
		}
		
		
		$pageName = $report;
		if ($createResultPage) {
			global $iaigContLang;
			$iaiNs = $iaigContLang->getNamespaces();
			$iaiNs = $iaiNs[IAI_NS_IAI];
			
			$pageName = "$iaiNs:Import $now";
			$title = Title::newFromText($pageName);
			$article = new Article($title);
			$article->doEdit($report, "Created report for import.");
			
		}
		
		return $pageName;
	}
		
	

	/**
	 * This function is called from the wiki importer when a <revision>-tag is reached
	 * in the imported XML. The revision contains the actual text of the article.
	 * The original method of the importer is called as it creates/updates the
	 * actual article.
	 * This callback is used for reporting the imported articles.
	 *
	 * @param Revision $rev
	 * 		The article's revision
	 */
	public function handleRevision( $rev ) {
		echo "\tRevision:".$rev->getId();
		$imported = call_user_func($this->mImportCallback, $rev);
		$t = $rev->getTitle();
		$ns = $t->getNamespace();
		$name = $t->getFullText();
		$this->mImportedArticles[] = str_replace('_', ' ', $name);
		$this->mLastImportedArticles[] = $name;
		
		if ($imported) {
			// Page was imported
			if (!array_key_exists($ns, $this->mImportedPages)) {
				$this->mImportedPages[$ns] = array();
			}
			// Store the name and revision ID of the imported article with the
			// namespace as key
			$this->mImportedPages[$ns][] = array($name, $rev->getId()); 
		} else {
			// Page was skipped
			if (!array_key_exists($ns, $this->mSkippedPages)) {
				$this->mSkippedPages[$ns] = array();
			}
			// Store the name and revision ID of the skipped article with the
			// namespace as key
			$this->mSkippedPages[$ns][] = array($name, $rev->getId()); 
		}
		echo $imported ? " imported\n" : " exists\n";
	}
	
	//--- Private methods ---
	
	/**
	 * This method tries to transfer the given articles from the source wiki
	 * to this wiki. 
	 *
	 * @param array<string> $articles
	 * 		An array of article names that are to be imported
	 * @param bool $skipExistingArticles
	 *		If <true>, articles that already exist in the destination wiki are
	 * 		skipped. They are not overwritten with the latest version of the
	 * 		source wiki.
	 * 
	 * @throws
	 * 
	 */
	private function transferArticles($articles, $skipExistingArticles = true) {

		if ($skipExistingArticles) {
			// Remove all existing articles from $articles.
			foreach ($articles as $k => $a) {
				$t = Title::newFromText($a);
				if ($t && $t->exists()) {
					// article exists => remove it from $articles
					unset($articles[$k]);
					$rev = Revision::newFromTitle($t);
					$this->mSkippedPages[$t->getNamespace()][] = array($a, $rev->getId());
				}
			}
		}
		
		if (empty($articles)) {
			return;
		}
		
		$titleParams = $this->makeTitlesForURL($articles);
		
		// Import all titles 
		foreach ($titleParams as $tp) {
			$xml = $this->downloadArticles($tp);
			$this->createArticlesFromXML($xml);
		}
	}
	
	/**
	 * Titles for the MediaWiki-API are passed as list of title names separated
	 * by '|'. The number of titles per API call is restricted (see TITLE_LIMIT).
	 * This method converts the names of all titles given in $articles in several
	 * strings suitable as URL-parameter.
	 *
	 * @param array(string) $articles
	 * 		The names of all titles
	 * @return array(string)
	 * 		Lists of titles that can be used in the title-parameter of the 
	 * 		Mediawiki-API.
	 */
	private function makeTitlesForURL($articles) {
		$numTitles = 0;
		$titleParams = array();
		$tp = "";
		foreach ($articles as $a) {
			$a = str_replace(' ', '_', $a);
			$a = urlencode($a);
			if ($numTitles == self::TITLE_LIMIT) {
				// The number of titles per API call is limited.
				$numTitles = 0;
				$titleParams[] = $tp;
			}
			if ($numTitles == 0) {
				$tp = $a;
			} else {
				$tp .= "|".$a;
			}
			++$numTitles;
		}
		$titleParams[] = $tp;
		return $titleParams;
	}
	
	/**
	 * Uses the Mediawiki API to download the given articles.
	 *
	 * @param string $titles
	 * 		Name of titles separated by '|'. The number of titles must not 
	 * 		exceed the maximum defined in TITLE_LIMIT.
	 * @return string
	 * 		The XML representation of the downloaded articles.
	 * @throws
	 * 		IAIException(IAIException::HTTP_ERROR) 
	 * 			if the HTTP request fails
	 */
	private function downloadArticles($titles) {
		$apiURL = $this->mWikiBase.
		          'api.php?action=query&titles='.$titles.
				  '&export&exportnowrap';
		echo "Downloading articles. URL:\n".$apiURL."\n\n";
		$xml = Http::get($apiURL, self::HTTP_TIMEOUT);
		if ($xml === false) {
			throw new IAIException(IAIException::HTTP_ERROR, $apiURL);
		}
		
		return $xml;
	}

	/**
	 * Uses the Mediawiki API to download the templates used in the given articles.
	 *
	 * @param string $titles
	 * 		Name of titles separated by '|'. The number of titles must not 
	 * 		exceed the maximum defined in TITLE_LIMIT.
	 * @return string
	 * 		The XML representation of the downloaded templates.
	 * @throws
	 * 		IAIException(IAIException::HTTP_ERROR) 
	 * 			if the HTTP request fails
	 */
	private function downloadTemplates($titles) {
		$apiURL = $this->mWikiBase.
		          'api.php?action=query&titles='.$titles.
				  '&generator=templates&export&exportnowrap';
		echo "Downloading templates. URL:\n".$apiURL."\n\n";
		$xml = Http::get($apiURL, self::HTTP_TIMEOUT);
		if ($xml === false) {
			throw new IAIException(IAIException::HTTP_ERROR, $apiURL);
		}
		
		return $xml;
	}
	
	/**
	 * Creates or updates articles in this wiki based on the XML passed in $xml.
	 * It is given in the format delivered by the export of Mediawiki.
	 *
	 * @param string $xml
	 * 		XML representation of wiki articles.
	 */
	private function createArticlesFromXML($xml) {
		$source = new ImportStringSource($xml);
		$importer = new WikiImporter( $source );
		$importer->setDebug(true);

		$importer->setPageCallback( array( &$this, 'reportPage' ) );
		$this->mImportCallback = $importer->setRevisionCallback(
										array( &$this, 'handleRevision' ) );	
		$importer->doImport();
	}
	
	/**
	 * This method finds the templates that are currently missing in the articles
	 * given in $forArticles. The MW-database table "templatelinks" is used for
	 * finding these templates.
	 *
	 * @param array(string) $forArticles
	 * 		Names of the articles that are examined for missing templates.
	 * 
	 * @return array(string)
	 * 		Names of the missing templates.
	 */
	private function getMissingTemplates($forArticles) {
		global $wgContLang;
		$tmpl = $wgContLang->getNsText(NS_TEMPLATE);
		
		$articleSet = array();
		foreach ($forArticles as $a) {
			$t = Title::newFromText($a);
			$articleSet[] = $t->getDBkey();			
		}
		
		$dbr = wfGetDB( DB_SLAVE );
		
		$articleSet = '"'.$dbr->strencode(implode('","', $articleSet)).'"';
				$sql = <<<SQL
SELECT DISTINCT tl_title
FROM templatelinks
WHERE
	tl_from IN(SELECT page_id FROM page p WHERE p.page_title IN($articleSet))
AND
	tl_title NOT IN (SELECT page_title FROM page WHERE page_namespace=10);
SQL;
		
		$templates = array();
		$res = $dbr->query($sql, __METHOD__ );
		if( $res !== false ) {
			foreach( $res as $row ) {
				$templates[] = $tmpl.":".$row->tl_title;
			}
		}
		$dbr->freeResult( $res );

		return $templates;
		
	}

	/**
	 * This method finds the images that are currently missing in the articles
	 * given in $forArticles. The MW-database tables "imagelinks", "image" and 
	 * "page" are used for finding these images.
	 *
	 * @param array(string) $forArticles
	 * 		Names of the articles that are examined for missing images.
	 * 
	 * @return array(string)
	 * 		Names of the missing images.
	 */
	private function getMissingImages($forArticles) {
		global $wgContLang;
		$imgNs = $wgContLang->getNsText(NS_IMAGE);

		$articleSet = array();
		foreach ($forArticles as $a) {
			$t = Title::newFromText($a);
			$articleSet[] = $t->getDBkey();			
		}
		
		$dbr = wfGetDB( DB_SLAVE );
		$articleSet = '"'.$dbr->strencode(implode('","', $articleSet)).'"';
		$sql = <<<SQL
SELECT DISTINCT il_to
FROM imagelinks
WHERE
	il_from IN(SELECT page_id FROM page p WHERE p.page_title IN($articleSet))
AND
	il_to NOT IN (SELECT img_name FROM image i);
SQL;
		
		$images = array();
		$res = $dbr->query($sql, __METHOD__ );
		if( $res !== false ) {
			foreach( $res as $row ) {
				$images[] = $imgNs.':'.$row->il_to;
			}
		}
		$dbr->freeResult( $res );

		return $images;
		
	}
	
	/**
	 * This method retrieves the URLs of all given $images in the source wiki.
	 *
	 * @param array(string) $images
	 * 		Names of images
	 * @return array(string) / bool
	 * 		URLs of the images in the source wiki
	 */
	private function getImageURLs($images) {
		
		$imgURLs = array();
		if (empty($images)) {
			return $imgURLs;
		}
		
		$titleParams = $this->makeTitlesForURL($images);
		$domDocument = new DOMDocument();
		
		// Get all image properties
		foreach ($titleParams as $tp) {
			$xml = $this->getImageProperties($tp);
			$success = $domDocument->loadXML($xml);
			$domXPath = new DOMXPath($domDocument);
			$nodes = $domXPath->query('//page/imageinfo/ii/@url');
			foreach ($nodes AS $node) {
				$imgURLs[] = $node->nodeValue;
			}
		}
		
		return $imgURLs;
		
	}
	
	/**
	 * This method uses the Mediawiki-API to retrieve properties of the images
	 * given in $title.
	 *
	 * @param string $titles
	 * 		Names of images separated with "|" and URL encoded
	 * @return string
	 * 		XML description of the given images
	 * @throws
	 * 		IAIException(IAIException::HTTP_ERROR) 
	 * 			if the HTTP request fails
	 */
	private function getImageProperties($titles) {
		$apiURL = $this->mWikiBase.
		          'api.php?action=query&titles='.$titles.
				  '&prop=imageinfo&format=xml&iiprop=url';
		echo "Downloading image properties. URL:\n".$apiURL."\n\n";
		$xml = Http::get($apiURL, self::HTTP_TIMEOUT);
		if ($xml === false) {
			throw new IAIException(IAIException::HTTP_ERROR, $apiURL);
		}
		return $xml;
	}
	
}
