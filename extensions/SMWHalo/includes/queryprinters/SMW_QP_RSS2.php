<?php

/**
 * Print links to RSS feeds for query results.
 */

/**
 * Printer for creating a link to RSS feeds. This RSS2 query result printer is based
 * on the RSS query result printer by Denny Vrandecic and Markus Krötzsch
 
 * @author Ingo Steinbauer
 */
class SMWRSS2QueryPrinter extends SMWResultPrinter {
	protected $m_title = '';
	protected $m_description = '';
	
	protected $m_editor = '';
	protected $m_image = '';
	protected $m_copyright = '';
	protected $m_categories = array();
	protected $m_ttl = '';
	protected $m_link_to = '';
	
	protected $m_description_template = '';

	protected function readParameters( $params, $outputmode ) {
		parent::readParameters( $params, $outputmode );
		
		global $wgSitename;
		if ( array_key_exists( 'title', $this->m_params ) ) {
			$this->m_title = trim( $this->m_params['title'] );
		} elseif ( array_key_exists( 'rsstitle', $this->m_params ) ) { // for backward compatibiliy
			$this->m_title = trim( $this->m_params['rsstitle'] );
		}
		if ( $this->m_title == '' ) {
			$this->m_title = $wgSitename;
		}
		
		
		if ( array_key_exists( 'description', $this->m_params ) ) {
			$this->m_description = trim( $this->m_params['description'] );
		} elseif ( array_key_exists( 'rssdescription', $this->m_params ) ) { // for backward compatibiliy
			$this->m_description = trim( $this->m_params['rssdescription'] );
		}
		if ( $this->m_description == '' ) {
			smwfLoadExtensionMessages( 'SemanticMediaWiki' );
			$this->m_description = wfMsg( 'smw_rss_description', $wgSitename );
		}
		
		if ( array_key_exists( 'editor', $this->m_params ) ) {
			$this->m_editor= trim( $this->m_params['editor'] );
		}
		
		if ( array_key_exists( 'copyright', $this->m_params ) ) {
			$this->m_copyright= trim( $this->m_params['copyright'] );
		}
		
		if ( array_key_exists( 'image', $this->m_params ) ) {
			$this->m_image = trim( $this->m_params['image'] );
		}
		
		if ( array_key_exists('ttl', $this->m_params ) ) {
			$this->m_ttl = trim( $this->m_params['ttl'] );
		}
		
		if ( !array_key_exists('link to', $this->m_params)){
			global $wgTitle;
			$this->m_link_to = $wgTitle->getFullURL(); 
		} else {
			$this->m_link_to = trim( $this->m_params['link to'] );
		}
		
		if ( array_key_exists( 'categories', $this->m_params ) ) {
			$categories = explode(';', $this->m_params['categories'] );
			foreach($categories as $c){
				$this->m_categories[] = trim($c);
			}
		}
		
		if ( array_key_exists('description template', $this->m_params ) ) {
			$this->m_description_template = trim( $this->m_params['description template'] );
		}
	}

	public function getMimeType( $res ) {
		return 'application/rss+xml';
	}

	public function getQueryMode( $context ) {
		return ( $context == SMWQueryProcessor::SPECIAL_PAGE ) ? SMWQuery::MODE_INSTANCES:SMWQuery::MODE_NONE;
	}

	public function getName() {
		//todo: use language file
		if($this->mFormat == 'rss2'){
			return 'RSS2 export';
		} else {
			return 'Atom export';
		}
	}

	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		global $smwgIQRunningNumber, $wgSitename, $wgServer, $smwgRSSEnabled, $wgRequest;
		
		$result = '';
		if ( $outputmode == SMW_OUTPUT_FILE ) { // make RSS feed
			
			//echo('<pre>'.print_r(self::, true).'</pre>');
			
			if ( !$smwgRSSEnabled ) return '';
			
			if($this->mFormat == 'rss2'){
				$result .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
				$result .= "<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
				$result .= "\t<channel>\n";
				$result .= "\t\t<title>".smwfXMLContentEncode($this->m_title)."</title>\n";
				$result .= "\t\t<link>".smwfXMLContentEncode($this->m_link_to)."</link>\n";
				$result .= "\t\t<description>".smwfXMLContentEncode( $this->m_description)."</description>\n";
				$result .= "\t\t<generator>http://smwforum.ontoprise.com</generator>\n";
				$result .= "\t\t<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
				
				global $wgRequest; 
				$result .= "\t\t<atom:link rel=\"self\" type=\"text/html\" href=\"".$wgRequest->getFullRequestURL()."\"/>\n";
				
				if($this->m_editor !== ''){
					$result .= "\t\t<managingEditor>".smwfXMLContentEncode( $this->m_editor)."</managingEditor>\n";
				}
				
				if($this->m_copyright !== ''){
					$result .= "\t\t<copyright>".smwfXMLContentEncode( $this->m_copyright)."</copyright>\n";
				}
				
				if($this->m_ttl !== ''){
					$result .= "\t\t<ttl>".smwfXMLContentEncode( $this->m_ttl)."</ttl>\n";
				}
				
				if($this->m_image !== ''){
					$file = Title::newFromText($this->m_image);
					if($file->exists()){
						$file = wfLocalFile($file);
						if($file->exists()){
							$result .= "\t\t<image>".smwfXMLContentEncode($file->getFullURL())."</image>\n";		
						}
					}
				}
				
				foreach($this->m_categories as $category){
					$result .= "\t\t<category>".smwfXMLContentEncode( $category)."</category>\n";
				}
			} else { //atom feed
				$result .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
				$result .= "<feed  xmlns=\"http://www.w3.org/2005/Atom\">\n";
				$result .= "\t\t<title type=\"text\">".smwfXMLContentEncode($this->m_title)."</title>\n";
				$result .= "\t\t<link rel=\"alternate\" type=\"text/html\" href=\"".$this->m_link_to."\"/>\n";
				$result .= "\t\t<subtitle>".smwfXMLContentEncode( $this->m_description)."</subtitle>\n";
				$result .= "\t\t<generator uri=\"http://smwforum.ontoprise.com\">SMW plus</generator>\n";
				$result .= "\t\t<updated>".date('c')."</updated>\n";

				global $wgRequest; 
				$result .= "\t\t<link rel=\"self\" type=\"text/html\" href=\"".$wgRequest->getFullRequestURL()."\"/>\n";
				$result .= "\t\t<id>".smwfXMLContentEncode($wgRequest->getFullRequestURL())."</id>\n";
				
				if($this->m_editor !== ''){
					$result .= "\t\t<author><name>".smwfXMLContentEncode( $this->m_editor)."</name></author>\n";
				}
				
				if($this->m_copyright !== ''){
					$result .= "\t\t<rights>".smwfXMLContentEncode( $this->m_copyright)."</rights>\n";
				}
				
				if($this->m_image !== ''){
					$file = Title::newFromText($this->m_image);
					if($file->exists()){
						$file = wfLocalFile($file);
						if($file->exists()){
							$result .= "\t\t<logo uri=\"".$file->getFullURL()."\"/>\n";		
						}
					}
				}
				
				foreach($this->m_categories as $category){
					global $wgServer;
					$result .= "\t\t<category scheme=\"".$wgServer."\" term=\"".$category."\"/>\n";
				}
			}
			
			//add items
			while ( $row = $res->getNext() ) {
				$item = new SMWRSS2Item($row, $this->m_description_template);
				if($this->mFormat == 'rss2'){
					$result .= $item->getRSS2Text();
				} else {
					$result .= $item->getAtomText();
				}
			}
			
			if($this->mFormat == 'rss2'){
				$result .= "\t</channel>\n";
				$result .= '</rss>';
			} else { //atom
				$result .= '</feed>';
			}
		
		} else { // just make link to feed
			
			if ( $this->getSearchLabel( $outputmode ) ) {
				$label = $this->getSearchLabel( $outputmode );
			} else {
				smwfLoadExtensionMessages( 'SemanticMediaWiki' );
				$label = wfMsgForContent( 'smw_rss_link' );
			}
			$link = $res->getQueryLink( $label );
			
			if($this->mFormat == "rss2"){
				$link->setParameter( 'format=rss2');
			} else {//atom
				$link->setParameter( 'format=atom' );
			}
			if ( $this->m_title !== '' ) {
				$link->setParameter( 'title='.$this->m_title);
			}
			if ( $this->m_description !== '' ) {
				$link->setParameter('description='.$this->m_description);
			}			
			if ( array_key_exists( 'limit', $this->m_params ) ) {
				$link->setParameter('limit='.$this->m_params['limit']);
			} else { // use a reasonable deafult limit (10 is suggested by RSS)
				$link->setParameter('limit=20');
			}

			foreach ( $res->getPrintRequests() as $printout ) { // overwrite given "sort" parameter with printout of label "date"
				if ( ( $printout->getMode() == SMWPrintRequest::PRINT_PROP ) && ( strtolower( $printout->getLabel() ) == "date" ) && ( $printout->getTypeID() == "_dat" ) ) {
					$link->setParameter( $printout->getData()->getWikiValue(), 'sort' );
				}
			}
			
			if($this->m_editor !== '' ) {
				$link->setParameter('editor='.$this->m_editor);
			}
			
			if($this->m_image !== '' ) {
				$link->setParameter('image='.$this->m_image);
			}
			
			if($this->m_copyright !== '' ) {
				$link->setParameter('copyright='.$this->m_copyright);
			}
			
			if(count($this->m_categories) > 0){
				$link->setParameter('categories='.implode(';', $this->m_categories));
			}
			
			if($this->m_ttl !== '' ) {
				$link->setParameter('ttl='.$this->m_ttl);
			}
			
			if($this->m_description_template !== '' ) {
				$link->setParameter('description template='.$this->m_description_template );
			}

			if($this->m_link_to !== '' ) {
				$link->setParameter('link to='.$this->m_link_to);
			}
			
			$result .= $link->getText( $outputmode, $this->mLinker );
			$this->isHTML = ( $outputmode == SMW_OUTPUT_HTML ); // yes, our code can be viewed as HTML if requested, no more parsing needed
			SMWOutputs::requireHeadItem( 'rss' . $smwgIQRunningNumber, '<link rel="alternate" type="application/rss+xml" title="' . $this->m_title . '" href="' . $link->getURL() . '" />' );
			
		}

		return $result;
	}

	public function getParameters() {
		//todo:use language file
		
		$params = array_merge( parent::getParameters(), $this->exportFormatParameters() );
		
		$params['title'] = new Parameter( 'title' );
		$params['title']->setMessage( 'smw_paramdesc_rsstitle' );
		
		$params['description'] = new Parameter( 'title' );
		$params['description']->setMessage( 'smw_paramdesc_rssdescription' );
		
		$params['description'] = new Parameter( 'editor' );
		//E-mail adress of the person responsible for editorial content.
		$params['description']->setMessage( 'smw_paramdesc_rssdescription' );
		
		$params['description'] = new Parameter( 'copyright' );
		//Copyright notice for content in the feed.
		$params['description']->setMessage( 'smw_paramdesc_rssdescription' );
		
		$params['description'] = new Parameter( 'categories' );
		//A semicolon separated list of tags for this feed.
		$params['description']->setMessage( 'smw_paramdesc_rssdescription' );
		
		$params['description'] = new Parameter( 'description template' );
		//Name of a template which will be used to generate item descriptions. 
		$params['description']->setMessage( 'smw_paramdesc_rssdescription' );
		
		return $params;
	}
}


/**
 * Represents a single entry, or item, in an RSS feed. Useful since those items are iterated more
 * than once when serialising RSS.
 */
class SMWRSS2Item {

	private $title;
	private $fieldValues = array();
	
	public function __construct($queryResultFields, $descriptionTemplate) {
		
		$title = $queryResultFields[0]->getNextDataValue(); // get the object
		if($title instanceof SMWWikiPageValue){
			$this->title = $title->getTitle();
		}
		
		
		foreach($queryResultFields as $field ) {
			$fieldLabel = strtolower($field->getPrintRequest()->getLabel());
			
			while(( $dV = $field->getNextDataValue())!== false ) {
				$this->fieldValues[$fieldLabel][] = $dV->getShortWikiText();
			}
		}
		
		//add values from article object if necessary
		if($this->title instanceof Title && $this->title->exists()){
			$article = new Article($this->title);
		
			//add author if necessary
			if(!array_key_exists('author', $this->fieldValues) && !array_key_exists('creatorr', $this->fieldValues)){
				$this->fieldValues['author'] = array($article->getUserText());
			}
		
			//add publication date if necessary
			if(!array_key_exists('publication date', $this->fieldValues)){
				$this->fieldValues['publication date'] = array(date( "c", strtotime( $article->getTimestamp() ) ));
			}
			
			//add title if necessary
			if(!array_key_exists('title', $this->fieldValues)){
				$this->fieldValues['title'] = array($this->title->getFullText());
			}
			
			//add link if necessary
			if(!array_key_exists('link', $this->fieldValues)){
				$this->fieldValues['link'] = array($this->title->getFullURL());
			}
			
			//add guid if necessary
			if(!array_key_exists('id', $this->fieldValues)){
				$this->fieldValues['id'] = array($this->title->getFullURL());
			}
			
			//display templates  are also used if a property with the label description exists
			if(strlen($descriptionTemplate) > 0){
				$description = '{{'.$descriptionTemplate;
				foreach($this->fieldValues as $label => $values){
					$description .= '| '.$label.'=';
					$delimiter = ';'; //todo: choose delimiter based on the 'Use delimiter' annotation
					$description .= implode($delimiter, $values);
					$description .= "\n";
				}
				$description .= '}}';
				
				$this->fieldValues['description'] = array($description);
			}
			
			//add complete page as description  if necessary
			global $smwgRSSWithPages;
			if(!array_key_exists('description', $this->fieldValues) && $smwgRSSWithPages){
				$this->fieldValues['description'] = array('{{'.$this->title->getFullText().'}}');
			}

			//parse description if necessary
			if(array_key_exists('description', $this->fieldValues)){
				$this->fieldValues['description'] = array($this->renderText($this->fieldValues['description'][0]));
			}
		}
		
		//convert date format
		if(array_key_exists('publication date', $this->fieldValues)){
			$unixTS = strtotime($this->fieldValues['publication date'][0]);
			$this->fieldValues['publication date'][0] = date('r', $unixTS);
		}
		
		//do xml encoding
		foreach($this->fieldValues as $label => $values){
			foreach($values as $key => $value){
				$this->fieldValues[$label][$key] = smwfXMLContentEncode($value); 
			}
		}
	}
	
	public function getRSS2Text(){
		$result = "\t\t<item>\n";

		$result .= $this->getTagText('title', 'title');
		$result .= $this->getTagText('author', 'dc:creator', true);
		$result .= $this->getTagText('creator', 'dc:creator', true);
		$result .= $this->getTagText('publication date', 'pubDate');
		$result .= $this->getTagText('categories', 'category', true);
		$result .= $this->getTagText('link', 'link');
		$result .= $this->getTagText('id', 'guid');
 		$result .= $this->getTagText('description', 'description');
		
		// todo: support these fields
 		// source
		// enclosure
		
 		$result .= "\t\t</item>\n";
 		
 		return $result;
	}
	
	public function getAtomText(){
		$result = "\t\t<entry>\n";

		if(array_key_exists('title', $this->fieldValues)){
			$result .= "\t\t\t".'<title type="text">'.$this->fieldValues['title'][0].'</title>'."\n";
		}
		
		if(array_key_exists('author', $this->fieldValues)){
			foreach($this->fieldValues['author'] as $author){
				$result .= "\t\t\t".'<author><name>'.$author.'</name></author>'."\n";
			}
		}
		
		if(array_key_exists('creator', $this->fieldValues)){
			foreach($this->fieldValues['creator'] as $author){
				$result .= "\t\t\t".'<author><name>'.$author.'</name></author>'."\n";
			}
		}
		
		if(array_key_exists('publication date', $this->fieldValues)){
			$unixTS = strtotime($this->fieldValues['publication date'][0]);
			$this->fieldValues['publication date'][0] = date('c', $unixTS);
			$result .= "\t\t\t".'<published>'.$this->fieldValues['publication date'][0].'</published>'."\n";
			$result .= "\t\t\t".'<updated>'.$this->fieldValues['publication date'][0].'</updated>'."\n";
		}
		
		if(array_key_exists('categories', $this->fieldValues)){
			foreach($this->fieldValues['categories'] as $category){
				global $wgServer;
				$result .= "\t\t\t".'<category scheme="'.$wgServer.'" term="'.$category.'"/>'."\n";
			}
		}
		
		if(array_key_exists('id', $this->fieldValues)){
			$result .= "\t\t\t".'<id>'.$this->fieldValues['id'][0].'</id>'."\n";
		}
		
		if(array_key_exists('description', $this->fieldValues)){
			$result .= "\t\t\t".'<content type="html">'.$this->fieldValues['description'][0].'</content>'."\n";
		}
		
		if(array_key_exists('link', $this->fieldValues)){
			$result .= "\t\t\t".'<link rel="alternate" type="text/html" href="'.$this->fieldValues['link'][0].'"/>'."\n";
		}
		
		// todo: support these fields
 		// source
		// enclosure
		
 		$result .= "\t\t</entry>\n";
 		
 		return $result;
	}
	
	private function getTagText($label, $tag, $multiple = false){
		$result = '';
		
		if(array_key_exists($label, $this->fieldValues) && strlen($this->fieldValues[$label][0]) > 0){
			$values = array();
			
			if($multiple){
				foreach($this->fieldValues[$label] as $value){
					$values[] = $value;
				}
			} else {
				$values[] = $this->fieldValues[$label][0];
			}

			$result .= "\t\t\t<".$tag.">";
			$result .= implode("</".$tag.">\n"."\t\t\t<".$tag.">", $values);		
			$result .= "</".$tag.">\n";
		}
		
		return $result;
	}

	private function renderText($text) {
		global $wgServer, $wgParser;
		
		$options = new ParserOptions();
		$options->setEditSection( false );
		$wgParser->startExternalParse($this->title, $options, Parser::OT_HTML);
		$output = $wgParser->parse($text, $this->title, $options);
		$content = $output->getText();		
		
		// Make absolute URLs out of the local ones:
		/// find a better way to do this
		$content = str_replace( '<a href="/', '<a href="' . $wgServer . '/', $content );
		
		return $content;
	}
}
