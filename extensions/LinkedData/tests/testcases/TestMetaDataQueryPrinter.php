<?php
/**
 * @file
 * @ingroup LinkedData_Tests
 */

/**
 * Test suite for the meta-data query printer.
 * Start the triple store with these options before running the test:
 * msgbroker=none client=MyStore driver=ontobroker-quad wsport=8090 console reasoner=owl restfulws
 * 
 * @author thsc
 *
 */
class TestMetaDataQueryPrinterSuite extends PHPUnit_Framework_TestSuite
{
	
	public static $mOrderOfArticleCreation;
	public static $mArticles;
	
	protected static $mBaseURI = 'http://www.example.org/smw-lde/';
	protected $mGraph1 = "http://www.example.org/smw-lde/smwGraphs/ToyotaGraph";
	protected $mGraph2 = "http://www.example.org/smw-lde/smwGraphs/VolkswagenGraph";
	protected $mGraph3 = "http://www.example.org/smw-lde/smwGraphs/PersonGraph";
	protected $mProvGraph;
	protected $mDSIGraph;
    
	// this file is located in the TSC resources directory
	protected $mFilePath = "file://resources/lod_wiki_tests/OntologyBrowserSparql/";
	protected $mGraph1N3 = "ToyotaGraph.n3";
	protected $mGraph2N3 = "VolkswagenGraph.n3";
	protected $mGraph3N3 = "PersonGraph.n3";
	protected $mProvGraphN3 = "ProvenanceGraph.n3";
	protected $mDSIGraphN3 = "DataSourceInformationGraph.n3";
	
	public static function suite() {
		
		$suite = new TestMetaDataQueryPrinterSuite();
		$suite->addTestSuite('TestMetaDataQueryPrinter');
		return $suite;
	}
	
	protected function setUp() {
				
		// Setup the content of the triple store
		$this->mProvGraph = self::$mBaseURI."smwGraphs/ProvenanceGraph";
		$this->mDSIGraph = self::$mBaseURI."smwGraphs/DataSourceInformationGraph";

		$this->dropGraphs();

		$tsa = new LODTripleStoreAccess();
		$tsa->createGraph($this->mGraph1);
		$tsa->createGraph($this->mGraph2);
		$tsa->createGraph($this->mGraph3);
		$tsa->createGraph($this->mProvGraph);
		$tsa->createGraph($this->mDSIGraph);
		$tsa->loadFileIntoGraph("{$this->mFilePath}ToyotaGraph.n3", $this->mGraph1, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}VolkswagenGraph.n3", $this->mGraph2, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}PersonGraph.n3", $this->mGraph3, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}ProvenanceGraph.n3", $this->mProvGraph, "n3");
		$tsa->loadFileIntoGraph("{$this->mFilePath}DataSourceInformationGraph.n3", $this->mDSIGraph, "n3");
		$tsa->flushCommands();
		
		// Create articles
    	$this->initArticleContent();
    	$this->createArticles();
    	
	}
	
	protected function tearDown() {
    	$this->removeArticles();
    	$this->dropGraphs();
	}

	protected function dropGraphs() {
		$tsa = new LODTripleStoreAccess();
		$tsa->dropGraph($this->mGraph1);
		$tsa->dropGraph($this->mGraph2);
		$tsa->dropGraph($this->mGraph3);
		$tsa->dropGraph($this->mProvGraph);
		$tsa->createGraph($this->mDSIGraph);
		$tsa->flushCommands();
		
	}
//--- Private functions --------------------------------------------------------
    
	private function createArticles() {
    	global $wgUser;
    	$wgUser = User::newFromName("WikiSysop");
    	
    	$file = __FILE__;
    	try {
	    	foreach (self::$mOrderOfArticleCreation as $title) {
				self::createArticle($title, self::$mArticles[$title]);
	    	}
    	} catch (Exception $e) {
			$this->assertTrue(false, "Unexpected exception while testing ".basename($file)."::createArticles():".$e->getMessage());
		}
    	
    }

	private function createArticle($title, $content) {
	
		$title = Title::newFromText($title);
		$article = new Article($title);
		// Set the article's content
		$success = $article->doEdit($content, 'Created for test case', 
		                            $article->exists() ? EDIT_UPDATE : EDIT_NEW);
		if (!$success) {
			echo "Creating article ".$title->getFullText()." failed\n";
		}
	}
     
	private function removeArticles() {
				
		foreach (self::$mOrderOfArticleCreation as $a) {
			global $wgTitle;
		    $wgTitle = $t = Title::newFromText($a);
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
		
	}
        
    private function initArticleContent() {
		self::$mOrderOfArticleCreation = array(
			'TestMetaDataQueryPrinter',
			'MediaWiki:XSLForMetaData',
			'MediaWiki:XSLForMetaDataWithoutPre'
		);
		
		self::$mArticles = array(
//------------------------------------------------------------------------------		
			'TestMetaDataQueryPrinter' =>
<<<LODMD

{{#sparql:
 SELECT ?P ?O
 WHERE {
     GRAPH ?G {
         a:Prius-II ?P ?O .
     }
 }
 | format=table
 | merge=false
 | metadata=(SWP2_AUTHORITY;IMPORT_GRAPH_CREATED)
 | metadataformat=table
 | dataspace=Toyota
}}
LODMD
,
//------------------------------------------------------------------------------		
			'MediaWiki:XSLForMetaData' =>
<<<LODMD
This style sheet transforms the meta-data of a query result into HTML in form of a table.
 
<pre>
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="iso-8859-1" indent="yes" />
	
	<xsl:template match="/metadata">
		<span class = "lodMdTableTitle">Meta-data of this data value:</span>
		<table class="lodMdTable">
			<th>Property</th>
			<th>Value</th>
			<xsl:for-each select="child::property">
				<tr>
					<td>
						<xsl:value-of select="@name" />
					</td>
					<td>
						<xsl:value-of select="." />
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
</pre>
LODMD
,

//------------------------------------------------------------------------------		
			'MediaWiki:XSLForMetaDataWithoutPre' =>
<<<LODMD
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="iso-8859-1" indent="yes" />
	
	<xsl:template match="/metadata">
		<span class = "lodMdTableTitle">Meta-data of this data value:</span>
		<table class="lodMdTable">
			<th>Property</th>
			<th>Value</th>
			<xsl:for-each select="child::property">
				<tr>
					<td>
						<xsl:value-of select="@name" />
					</td>
					<td>
						<xsl:value-of select="." />
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
LODMD

		);
	}
	
}



/**
 * This class tests the meta-data query printer.
 * See feature: http://dmwiki.ontoprise.com:8888/dmwiki/index.php/Query_printer_for_metadata
 * 
 * @author thsc
 *
 */
class TestMetaDataQueryPrinter extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
		
    function setUp() {
    }

    function tearDown() {
    }

    /**
     * Checks if the meta data for a query is delivered for the triples of a 
     * query result on the level of SPARQL XML.
     */
	function testGetMetaDataForResultsInXML() {
		
		$querystring = <<<SPARQL
			SELECT ?P ?O
			 WHERE {
			     GRAPH ?G {
			         a:Prius-II ?P ?O .
			     }
			 }		
SPARQL;

		$con = TSConnection::getConnector();
		$con->connect();
		$response = $con->query($querystring, "metadata=(SWP2_AUTHORITY)");
		
		// Search for the meta data of triples
		$expect = '<uri _meta_swp2_authority="http://www.example.org/smw-lde/smwDatasources/DataSource1">http://mywiki/category#Automobile</uri>';
		$this->assertTrue(strpos($response, $expect) >= 0, "Authority for Automobile not found.");

		$expect = '<uri _meta_swp2_authority="http://www.example.org/smw-lde/smwDatasources/DataSource1">http://mywiki/category#Hybrid</uri>';
		$this->assertTrue(strpos($response, $expect) >= 0, "Authority for Hybrid not found.");
		
		$expect = '<literal _meta_swp2_authority="http://www.example.org/smw-lde/smwDatasources/DataSource1" datatype="http://www.w3.org/2001/XMLSchema#int">113</literal>';
		$this->assertTrue(strpos($response, $expect) >= 0, "Authority for property HasPower not found.");
		
	}
	
	
	/**
	 * The meta data query printer replaces some data type classes of SMW 
	 * in the hook "smwInitDatatypes".
	 * This function checks the hooks are set correctly and if the correct
	 * classes for the data types are instantiated.
	 */
	function testSmwInitDatatypesHooks() {
		global 	$wgHooks;
		
		// Verify that the last hook is the hook for the meta-data query printer.
		$this->assertTrue(isset($wgHooks['smwInitDatatypes']));
		
		$this->assertEquals($wgHooks['smwInitDatatypes'][count($wgHooks['smwInitDatatypes'])-1],
							'LODMetaDataQueryPrinter::onSmwInitDatatypesHooks');
		
		// Verify that the correct data type instance is created.
		
		$typesAndClasses = array(
			"_str" => "LODStringValue",
			"_ema" => "LODURIValue",
			"_uri" => "LODURIValue",
			"_anu" => "LODURIValue",
			"_tel" => "LODURIValue",
			"_wpg" => "LODWikiPageValue",
			"_wpp" => "LODWikiPageValue",
			"_wpc" => "LODWikiPageValue",
			"_wpf" => "LODWikiPageValue",
			"_num" => "LODNumberValue",
			"_tem" => "LODTemperatureValue",
			"_dat" => "LODTimeValue",
			"_boo" => "LODBoolValue",
			"_rec" => "LODRecordValue",
		);
		
		foreach ($typesAndClasses as $tid => $class) {
			$typeValue = SMWDataValueFactory::newTypeIDValue($tid);
			$this->assertTrue($typeValue instanceof $class, 
				"Missing derived data value type of class <$class> for type ID <$tid>.");
		}
		
	}
	
	/**
	 * Verifies that the hook for processing a query result is installed. It 
	 * augments the query results with a meta-data printer.
	 */
	function testProcessQueryResultsHook() {
		global $wgHooks;

		// Verify that hooks for 'ProcessQueryResults' exist.
		$this->assertTrue(isset($wgHooks['ProcessQueryResults']));
		
		// Verify that the meta-data query printer is hooked in 'ProcessQueryResults'
		$this->assertContains("LODMetaDataQueryPrinter::onProcessQueryResults",
							  $wgHooks['ProcessQueryResults']);
	}

	/**
	 * Verifies that the table meta-data printer is attached to the data values of a
	 * query result.
	 */
	function testTableMetaDataPrinterAttached() {
		self::checkMetaDataPrinterAttached("table", "LODMDPTable");
	}
	
	/**
	 * Verifies that the XSLT meta-data printer is attached to the data values of a
	 * query result.
	 */
	function testXSLTMetaDataPrinterAttached() {
		self::checkMetaDataPrinterAttached("xslt", "LODMDPXslt");
	}

	/**
	 * Verifies that the error meta-data printer is attached to the data values of a
	 * query result.
	 */
	function testErrorMetaDataPrinterAttached() {
		self::checkMetaDataPrinterAttached("non-existingMDP", "LODMDPError");
	}
	
	/**
	 * Tests if the HTML for the meta-data is created correctly by the table meta-
	 * data printer. 
	 */
	function testMDPTableContent() {
		$res = self::getQueryResult("(SWP2_AUTHORITY;IMPORT_GRAPH_CREATED)", "table");
		
		$expected = <<<HTML
<span class="lodMetadata">113
  <span class="lodMetadataContent" style="display:none">
    <span class = "lodMdTableTitle">Meta-data for this data value</span>
    <table class="lodMdTable">
      <tr>
        <th>Property</th>
        <th>Value</th>
      </tr>
      <tr>
        <td>Import date</td>
        <td>2010-05-19T08:33:19</td>
      </tr>
      <tr>
        <td>Data source</td>
        <td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
  <span class="lodRatingKey" style="display:none">***</span>
</span>
HTML;
		self::checkMetaDataOfValue("113", $res, $expected);
		
		
		$expected = <<<HTML
<span class="lodMetadata">Category:Hybrid
  <span class="lodMetadataContent" style="display:none">
    <span class = "lodMdTableTitle">Meta-data for this data value</span>
    <table class="lodMdTable">
      <tr>
        <th>Property</th>
        <th>Value</th>
      </tr>
      <tr>
        <td>Import date</td>
        <td>2010-05-19T08:33:19</td>
      </tr>
      <tr>
        <td>Data source</td>
        <td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
  <span class="lodRatingKey" style="display:none">***</span>
</span>
HTML;
		self::checkMetaDataOfValue("Category:Hybrid", $res, $expected);
		
		
		$expected = <<<HTML
<span class="lodMetadata">Category:Automobile
  <span class="lodMetadataContent" style="display:none">
    <span class = "lodMdTableTitle">Meta-data for this data value</span>
    <table class="lodMdTable">
      <tr>
        <th>Property</th>
        <th>Value</th>
      </tr>
      <tr>
        <td>Import date</td>
        <td>2010-05-19T08:33:19</td>
      </tr>
      <tr>
        <td>Data source</td>
        <td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
  <span class="lodRatingKey" style="display:none">***</span>
</span>
HTML;
		self::checkMetaDataOfValue("Category:Automobile", $res, $expected);

//-------------------------
/*
		// Retrieve all available meta-data
		$res = self::getQueryResult("*", "table");
		
		$expected = <<<HTML
<span class="lodMetadata">113
  <span class="lodMetadataContent">
    <span class = "lodMdTableTitle">Meta-data for this data value</span>
    <table class="lodMdTable">
      <tr>
        <th>Property</th>
        <th>Value</th>
      </tr>
      <tr>
        <td>import_graph_created</td>
        <td>2010-05-19T08:33:19</td>
      </tr>
      <tr>
        <td>swp2_authority</td>
        <td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
</span>
HTML;
		self::checkMetaDataOfValue("113", $res, $expected);
		
		
		$expected = <<<HTML
<span class="lodMetadata">Category:Hybrid
  <span class="lodMetadataContent">
    <span class = "lodMdTableTitle">Meta-data for this data value</span>
    <table class="lodMdTable">
      <tr>
        <th>Property</th>
        <th>Value</th>
      </tr>
      <tr>
        <td>import_graph_created</td>
        <td>2010-05-19T08:33:19</td>
      </tr>
      <tr>
        <td>swp2_authority</td>
        <td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
</span>
HTML;
		self::checkMetaDataOfValue("Category:Hybrid", $res, $expected);
		
		
		$expected = <<<HTML
<span class="lodMetadata">Category:Automobile
  <span class="lodMetadataContent">
    <span class = "lodMdTableTitle">Meta-data for this data value</span>
    <table class="lodMdTable">
      <tr>
        <th>Property</th>
        <th>Value</th>
      </tr>
      <tr>
        <td>import_graph_created</td>
        <td>2010-05-19T08:33:19</td>
      </tr>
      <tr>
        <td>swp2_authority</td>
        <td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
</span>
HTML;
		self::checkMetaDataOfValue("Category:Automobile", $res, $expected);
*/		
	}
	
	/**
	 * The XSL for transforming the meta-data to HTML is stored in an article.
	 * The name of the article is given in the query. This function checks if
	 * the XSLT printer retrieves the correct XSL.
	 */
	function testMDPXsltRetrieveXSL() {
		$expected = <<<XSL
<?xmlversion="1.0"encoding="UTF-8"?>
<xsl:stylesheetxmlns:xsl="http://www.w3.org/1999/XSL/Transform"version="1.0">
  <xsl:outputmethod="html"encoding="iso-8859-1"indent="yes"/>
  <xsl:templatematch="/metadata">
    <spanclass="lodMdTableTitle">Meta-dataofthisdatavalue:</span>
    <tableclass="lodMdTable">
      <th>Property</th>
      <th>Value</th>
      <xsl:for-eachselect="child::property">
        <tr>
          <td>
            <xsl:value-ofselect="@name"/>
          </td>
          <td>
            <xsl:value-ofselect="."/>
          </td>
        </tr>
      </xsl:for-each>
    </table>
  </xsl:template>
</xsl:stylesheet>
XSL;
		
		$this->checkXSL("MediaWiki:XSLForMetaData", $expected);
	}

	/**
	 * The XSL for transforming the meta-data to HTML is stored in an article.
	 * Check the error which is returned when the page does not exist.
	 */
	function testMDPXsltRetrieveXSLFromNonExistingPage() {
		$expected = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html" encoding="iso-8859-1" indent="yes" />
	<xsl:template match="/metadata">
		The article "MediaWiki:ThisPageDoesNotExist" that was specified in the query parameter &lt;tt&gt;metadatastylesheet&lt;/tt&gt; does not exist!
	</xsl:template>
</xsl:stylesheet>
XSL;
		
		$this->checkXSL("MediaWiki:ThisPageDoesNotExist", $expected);
	}	
	
	/**
	 * The XSL for transforming the meta-data to HTML is stored in an article.
	 * Check the error which is returned when the XSL is not embedded in <pre>
	 * tags.
	 */
	function testMDPXsltRetrieveXSLWithourPre() {
		$expected = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html" encoding="iso-8859-1" indent="yes" />
	<xsl:template match="/metadata">
		The XSL in the article "MediaWiki:XSLForMetaDataWithoutPre" must be embedded in &lt;pre&gt;-tags!
	</xsl:template>
</xsl:stylesheet>
XSL;
		
		$this->checkXSL("MediaWiki:XSLForMetaDataWithoutPre", $expected);
	}	
	
	
	
	/**
	 * The XSL for transforming the meta-data to HTML is stored in an article.
	 * This function checks if the XSLT printer retrieves the correct XSL given
	 * in the article with the name $article and compares it to the expected 
	 * XSL in $expected.
	 * @param string $article
	 * 		Name of the article that contains the XSL
	 * @param string $expected
	 * 		Expected XSL
	 */
	function checkXSL($article, $expected) {
		$queryString = <<<SPARQL
SELECT ?P ?O
WHERE {
    GRAPH ?G {
        a:Prius-II ?P ?O .
    }
}		
SPARQL;
		$params = array("metadata" => "(SWP2_AUTHORITY;IMPORT_GRAPH_CREATED)",
						"metadataformat" => "xslt",
						"dataspace" => "Toyota",
						"metadatastylesheet" => $article,
						"enablerating" => "true");
	
		// Create and execute the query
		$query  = SMWSPARQLQueryProcessor::createQuery($queryString, $params);
		$store = new SMWTripleStore();
		$res = $store->getQueryResult( $query );
		
		// Create an XSLT meta-data printer and retrieve its XSL
		$xsltMDP = new LODMDPXslt($query, $res);
		$xsl = $xsltMDP->getXSL();
		$xsl = $xsl->saveXML();
		
		$xsl = preg_replace("/\s*/", "", $xsl);
		$expected = preg_replace("/\s*/", "", $expected);
		
		$this->assertEquals($expected, $xsl, 
			"The XSL was not correctly retrieved from article '$article'.");
		
	}
	
	/**
	 * Checks the XSLT meta-data printer
	 */
	function testMDPXSLTContent() {
		$res = self::getQueryResult("(SWP2_AUTHORITY;IMPORT_GRAPH_CREATED)", 
				"xslt", array("metadatastylesheet" => "MediaWiki:XSLForMetaData"));
		
		$expected = <<<HTML
<span class="lodMetadata">113
  <span class="lodMetadataContent" style="display:none">
    <span class="lodMdTableTitle">Meta-data of this data value:
    </span>
    <table class="lodMdTable">
      <th>Property</th>
      <th>Value</th>
      <tr><td>Import date</td><td>2010-05-19T08:33:19</td>
      </tr>
      <tr><td>Data source</td><td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
  <span class="lodRatingKey" style="display:none">***</span>
</span>
HTML;
		self::checkMetaDataOfValue("113", $res, $expected);
		
		$expected = <<<HTML
<span class="lodMetadata">Category:Hybrid
  <span class="lodMetadataContent" style="display:none">
    <span class = "lodMdTableTitle">Meta-data of this data value:</span>
    <table class="lodMdTable">
      <th>Property</th>
      <th>Value</th>
      <tr>
        <td>Import date</td>
        <td>2010-05-19T08:33:19</td>
      </tr>
      <tr>
        <td>Data source</td>
        <td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
  <span class="lodRatingKey" style="display:none">***</span>
</span>
HTML;
		self::checkMetaDataOfValue("Category:Hybrid", $res, $expected);
		
		
		$expected = <<<HTML
<span class="lodMetadata">Category:Automobile
  <span class="lodMetadataContent" style="display:none">
    <span class = "lodMdTableTitle">Meta-data of this data value:</span>
    <table class="lodMdTable">
      <th>Property</th>
      <th>Value</th>
      <tr>
        <td>Import date</td>
        <td>2010-05-19T08:33:19</td>
      </tr>
      <tr>
        <td>Data source</td>
        <td>http://www.example.org/smw-lde/smwDatasources/DataSource1</td>
      </tr>
    </table>
  </span>
  <span class="lodRatingKey" style="display:none">***</span>
</span>
HTML;
		self::checkMetaDataOfValue("Category:Automobile", $res, $expected);
		
	}
	

//--- Auxiliary functions ---

	
	/**
	 * Checks if the meta-data in a data value in a result is as expected.
	 * @param string $value
	 * 		Representation of a value in a result
	 * @param SMWHaloQueryResult $result
	 * 		This result should contain the data value
	 * @param string $expected
	 * 		Expected meta-data representation
	 * 		
	 */
	private function checkMetaDataOfValue($value, $result, $expected) {
		$dataValue = self::getDataValue($value, $result);
		$this->assertNotNull($dataValue, 
			"Expected to find the data value '$value' in the query result.");
		$wikiText = $dataValue->getShortWikiText();
		// replace whitespaces in the result and the expected result
		$wikiText = preg_replace("/\s*/", "", $wikiText);
		$wikiText = preg_replace('/<spanclass="lodRatingKey"style="display:none">.*?<\/span>/', 
								 '<spanclass="lodRatingKey"style="display:none">***</span>', 
								 $wikiText);
		$expected = preg_replace("/\s*/", "", $expected);
		
		$this->assertEquals($expected, $wikiText, 
			"The wiki text generated by the table meta-data printer does not match the expected.");
		
	}

	/**
	 * Verifies that a meta-data printer is attached to the data values of a
	 * query result.
	 * @param string $mdpID
	 * 		ID of the meta-data printer e.g. "table"
	 * @param string $mdpClassName
	 * 		Expected class of the meta-data printer
	 */
	private function checkMetaDataPrinterAttached($mdpID, $mdpClassName) {
		
		$res = self::getQueryResult("(SWP2_AUTHORITY;IMPORT_GRAPH_CREATED)", $mdpID);
		
		$this->assertTrue($res instanceof SMWHaloQueryResult, 
				"The query result is not an instance of SMWHaloQueryResult.");

		// We expect that all data values of the result are augmented with
		// a meta-data printer for tables.
		
		$rowCount = 0;
		// Iterate all rows
		while ($row = $res->getNext()) {
			++$rowCount;
			// Iterate all cells in a row
			foreach ($row as $cell) {
				// Iterate all values in a cell
				while ($value = $cell->getNextObject()) {
					$ro = new ReflectionObject($value);
					$class = get_class($value);
					$this->assertTrue($ro->hasMethod("setMetaDataPrinter"), 
									  "Meta-data printer missing in data value of type <$class>.");
					$mdp = $value->getMetaDataPrinter();
					$this->assertFalse(empty($mdp), "Meta-data printer of a result is empty.");
					// Expected meta-data printer is of class $mdpClassName
					$this->assertTrue($mdp instanceof $mdpClassName, "Meta-data printer is expected to be of class $mdpClassName.");
				}
			}
		}
		
		// Three rows of data are expected
		$this->assertEquals(3, $rowCount, "Unexpected number of result rows for the query.");
	}


	/**
	 * Returns the result of a standard query.
	 * 
	 * @param string $metaData
	 * 		The meta-data that is queried for each result e.g.
	 * 		(SWP2_AUTHORITY;IMPORT_GRAPH_CREATED).
	 * @param string $metaDataPrinter
	 * 		The name of the meta-data printer e.g. table
	 * @param array(string => string)
	 * 		Additional parameters that are added to the query parameters
	 */
	private function getQueryResult($metaData, $metaDataPrinter, $params = array()) {
			
		$queryString = <<<SPARQL
SELECT ?P ?O
WHERE {
    GRAPH ?G {
        a:Prius-II ?P ?O .
    }
}		
SPARQL;
		$params = array_merge($params,
						array(
						"metadata" => "$metaData",
						"metadataformat" => $metaDataPrinter,
						"dataspace" => "Toyota",
						"enablerating" => "true"));
		
	
		$query  = SMWSPARQLQueryProcessor::createQuery($queryString, $params);
		$store = new SMWTripleStore();
		$res = $store->getQueryResult( $query );
		
		return $res;
			
	}
	
	/**
	 * Finds the first value in the result with the representation $valueRep.
	 * 
	 * @param string $valueRep
	 * 		Representation of a requested value
	 * @param SMWHaloQueryResult $result
	 * 		The result which should contain the requested value
	 * @return Ambigous <NULL, SMWDataValue>
	 * 		null or the requested value
	 */
	private function getDataValue($valueRep, SMWHaloQueryResult $result) {
		$dataValue = null;
		
		// Iterate all rows
		while ($row = $result->getNext()) {
			// Iterate all cells in a row
			foreach ($row as $cell) {
				// Iterate all values in a cell
				while ($value = $cell->getNextObject()) {
					$dv = $value->getShortWikiText();
					preg_match("/<span.*?>(.*?)<span/", $dv, $matches);
					$dv = @$matches[1];
					if ($dv == $valueRep) {
						$dataValue = $value;
						break;
					}
				}
			}
		}
		$result->resetResultArray();
		
		return $dataValue;
		
	}
	
}
	