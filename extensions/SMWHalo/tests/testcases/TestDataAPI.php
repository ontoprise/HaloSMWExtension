<?php
/**
 * @file
 * @ingroup SMWHaloTests 
 * 
 * @author Kai Kühn
 *
 */
class TestDataAPI extends PHPUnit_Framework_TestCase {

	private $url = "http://localhost/mediawiki/api.php";
	private $userName = "WikiSysop";
	private $pw = "root";
	private static $isInitialized = false;	
	
	function setUp() {
		if(!$self->isInitialized){
			$this->initialize();
			$self->isInitialized = true;
		}
	}

	function tearDown() {

	}

	function initialize(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "Template:Forms/Generate form field including PAGENAME", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:Forms/Generate form field including PAGENAME", 
			$this->userName, $this->pw, $lt, $uid, $this->getProjectFormHelpTemplateText());
		
		$this->pcpDeleteArticle($this->url, "Form:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Form:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectFormText());

		$this->pcpDeleteArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectTemplateText());

		$this->pcpDeleteArticle($this->url, "Category:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Category:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectCategoryText());

		$this->pcpDeleteArticle($this->url, "Template:Category", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:Category", $this->userName, $this->pw, $lt, $uid, $this->getCategoryTemplateText());

		$this->pcpDeleteArticle($this->url, "Template:RMList", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:RMList", $this->userName, $this->pw, $lt, $uid, $this->getRMListTemplateText());

		$this->pcpDeleteArticle($this->url, "Template:ShowAttachedContent", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:ShowAttachedContent", $this->userName, $this->pw, $lt, $uid, $this->getShowAttachedContentTemplateText());
	}

	function testPCPLogin(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->assertEquals(strlen($lt) > 0, true);
	}
	
	function testSFGetJSON(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid, $this->getAComplexProjectText("pcp_create", "True"));
		$response = $this->sfGetForm($this->url, "A_complex_project", false, "json");
		
		$this->assertEquals(strpos($response, '"tmpl_name":"Project"') !== false, true);
		$this->assertEquals(strpos($response, '"tmpl_name":"ShowAttachedContent"') !== false, true);
		$this->assertEquals(strpos($response, '"tmpl_name":"RMList"') !== false, true);

		$this->pcpDeleteArticle($this->url, "Form:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Form:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectFormText());
		$response = $this->sfGetForm($this->url, "Form:Project", true, "json");

		$this->assertEquals(strpos($response, '"tmpl_name":"Project"') !== false, true);
		$this->assertEquals(strpos($response, '"tmpl_name":"ShowAttachedContent"') !== false, true);
		$this->assertEquals(strpos($response, '"tmpl_name":"RMList"') !== false, true);
	}
	
	function testSFCreateJSON(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid);
		$this->sfCreateForm($this->url, $this->userName, $lt, $uid, $this->getAComplexProjectSFJSONData("sf-create-json"), "json");
		$response = $this->sfGetForm($this->url, "A_complex_project", false, "json");
		
		$this->assertEquals(strpos($response, '"cur_value":"sf-create-json"') !== false, true);
	}

	function testSFUpdateJSON(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid, $this->getAComplexProjectText("pcp_create", "True"));
		$this->sfUpdateForm($this->url, $this->userName, $lt, $uid, $this->getAComplexProjectSFJSONData("sf-update-json"), "json");
		$response = $this->sfGetForm($this->url, "A_complex_project");

		$this->assertEquals(strpos($response, 'cur_value="sf-update-json"') !== false, true);
	}

	function testPCPDeleteAndRead(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid);
		//$this->pcpCreateArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectTemplateText());
		$res = $this->pcpReadArticle($this->url, "Template:Project");

		$this->assertEquals(strlen($res) == 0, true);
	}

	function testPCPCreateAndRead(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectTemplateText());
		$res = $this->pcpReadArticle($this->url, "Template:Project");
		$this->assertEquals(strpos($res, "{{Tablelongrow|Align=left|Value=") !== false, true);
	}

	function testPCPMove(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpDeleteArticle($this->url, "Template:Project2", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectTemplateText());
		$this->pcpMoveArticle($this->url, "Template:Project", "Template:Project2", $this->userName, $this->pw, $lt, $uid);
		$res = $this->pcpReadArticle($this->url, "Template:Project2");
		$this->assertEquals(strpos($res, "{{Tablelongrow|Align=left|Value=") !== false, true);

		//cleanup
		$this->pcpDeleteArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpDeleteArticle($this->url, "Template:Project2", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectTemplateText());
	}

	function testPCPUpdate(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectTemplateText());
		$this->pcpUpdateArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid, "this is the update");
		$res = $this->pcpReadArticle($this->url, "Template:Project");
		$this->assertEquals(strpos($res, "this is the update") !== false, true);

		//cleanup
		$this->pcpDeleteArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Template:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectTemplateText());
	}

	function testPOMGetElements(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$res = $this->pomGetElements($this->url, "Template:Project");

		$this->assertEquals(strpos($res, "Teamwork section&#10;|rationale=This templa") !== false, true);
	}

	function testPOMGetTemplates(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid,
		$this->getAComplexProjectText("A complex project", "True"));

		$response = $this->pomGetTemplates($this->url, "A_complex_project");
		$this->assertEquals(strpos($response, '<title leading_space="" trimmed="Project"') !== false, true);

		$response = $this->pomGetTemplates($this->url, "A_complex_project", "RMList");
		$this->assertEquals(strpos($response, 'leading_space="" trimmed="RMList" trailing_space=') !== false, true);
	}

	function testSFGet(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid, $this->getAComplexProjectText("pcp_create", "True"));
		$response = $this->sfGetForm($this->url, "A_complex_project");

		$this->assertEquals(strpos($response, 'tmpl_name="Project"') !== false, true);
		$this->assertEquals(strpos($response, 'tmpl_name="ShowAttachedContent"') !== false, true);
		$this->assertEquals(strpos($response, 'tmpl_name="RMList"') !== false, true);

		$this->pcpDeleteArticle($this->url, "Form:Project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "Form:Project", $this->userName, $this->pw, $lt, $uid, $this->getProjectFormText());
		$response = $this->sfGetForm($this->url, "Form:Project", true);

		$this->assertEquals(strpos($response, 'tmpl_name="Project"') !== false, true);
		$this->assertEquals(strpos($response, 'tmpl_name="ShowAttachedContent"') !== false, true);
		$this->assertEquals(strpos($response, 'tmpl_name="RMList"') !== false, true);
	}

	function testSFCreate(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid);
		$this->sfCreateForm($this->url, $this->userName, $lt, $uid, $this->getAComplexProjectSFData("sf-create", "True"));
		$response = $this->sfGetForm($this->url, "A_complex_project");

		$this->assertEquals(strpos($response, 'cur_value="sf-create"') !== false, true);
	}

	function testSFUpdate(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$this->pcpDeleteArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid);
		$this->pcpCreateArticle($this->url, "A_complex_project", $this->userName, $this->pw, $lt, $uid, $this->getAComplexProjectText("pcp_create", "True"));
		$this->sfUpdateForm($this->url, $this->userName, $lt, $uid, $this->getAComplexProjectSFData("sf-update", "True"));
		$response = $this->sfGetForm($this->url, "A_complex_project");

		$this->assertEquals(strpos($response, 'cur_value="sf-update"') !== false, true);
	}

	function testSFFormsList(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$response = $this->sfGetFormsList($this->url, "root");
		$this->assertEquals(strpos($response, '<Project') !== false, true);

		$response = $this->sfGetFormsList($this->url, "root", "Pro");
		$this->assertEquals(strpos($response, '<Project') !== false, true);

		$response = $this->sfGetFormsList($this->url, "Project");
		$this->assertEquals(strpos($response, '<Project') !== false, true);

		$response = $this->sfGetFormsList($this->url, "Project", "Proj");
		$this->assertEquals(strpos($response, '<Project') !== false, true);
	}

	function testSFPageList(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$response = $this->sfGetPageList($this->url, "root");
		$this->assertEquals(strpos($response, '<Project><A_complex_project') !== false, true);

		$response = $this->sfGetPageList($this->url, "root", "Proj");
		$this->assertEquals(strpos($response, '<Project><A_complex_project') !== false, true);

		$response = $this->sfGetPageList($this->url, "Project");
		$this->assertEquals(strpos($response, '<Project><A_complex_project') !== false, true);

		$response = $this->sfGetPageList($this->url, "Project", "Proj");
		$this->assertEquals(strpos($response, '<Project><A_complex_project') !== false, true);
	}

	function testSFCatTree(){
		$lt = $this->pcpLogin($this->url, $this->userName, $this->pw);
		$uid = $lt[1];
		$lt = $lt[0];

		$response = $this->sfGetCatTree($this->url, "root");
		$this->assertEquals(strpos($response, '<Project catOptions="Form:Project" />') !== false, true);

		$response = $this->sfGetCatTree($this->url, "root", "2");
		$this->assertEquals(strpos($response, '<Project catOptions="Form:Project" />') !== false, true);

		$response = $this->sfGetCatTree($this->url, "Project");
		$this->assertEquals(strpos($response, '<Project catOptions="Form:Project" />') !== false, true);

		$response = $this->sfGetCatTree($this->url, "Project", "2");
		$this->assertEquals(strpos($response, '<Project catOptions="Form:Project" />') !== false, true);
	}

	function pcpLogin($url, $userName, $pw){
		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);
		$response = stream_get_contents(
		fopen($this->url."?action=wspcp&method=login&un=".$this->userName."&pwd=".$this->pw."&format=xml",
		'rb', true, $ctx));

		$response = new SimpleXMLElement($response);
		$lt = $response->xpath("//login/@logintoken");
		$lt = $lt[0];
		$uid = $response->xpath("//login/@userid");
		$uid = $uid[0];
		return array($lt, $uid);
	}


	function sfGetFormsList($url, $title, $substr = ""){
		if(strlen($substr) > 0){
			$substr = "&substr=".$substr;
		}

		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);

		$response = stream_get_contents(
		fopen($this->url."?action=sfdata&sflist=".$title.$substr."&format=xml",
			'rb', true, $ctx));
		//echo("\n\n".$response);
		return $response;
	}

	function sfGetCatTree($url, $category, $level = ""){
		if(strlen($level) > 0){
			$level = "&catlevel=".$level;
		}

		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);

		$response = stream_get_contents(
		fopen($this->url."?action=sfdata&cattree=".$category.$level."&format=xml",
			'rb', true, $ctx));
		//echo("\n\n".$response);
		return $response;
	}

	function sfGetPageList($url, $title, $substr = ""){
		if(strlen($substr) > 0){
			$substr = "&substr=".$substr;
		}

		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);

		$response = stream_get_contents(
		fopen($this->url."?action=sfdata&pagelist=".$title.$substr."&format=xml",
			'rb', true, $ctx));
		//echo("\n\n".$response);
		return $response;
	}

	function pcpReadArticle($url, $title){
		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);
		$response = stream_get_contents(
		fopen($this->url."?action=wspcp&method=readPage&format=xml&title=".$title,
		'rb', true, $ctx));

		//echo("\n\n".$response);

		$response = new SimpleXMLElement($response);
		$res = $response->xpath("//readPage/@text");
		$res = $res[0];

		return $res;
	}


	function pcpDeleteArticle($url, $title, $userName, $pw, $lt, $uid){
		$dataArray = array("action" => "wspcp");
		$dataArray["method"] = "deletePage";
		$dataArray["title"] = $title;
		$dataArray["un"] = $this->userName;
		$dataArray["pwd"] = $this->pw;
		$dataArray["lt"] = $lt;
		$dataArray["uid"] = $uid;
		$dataArray["format"] = "xml";
		$dataArray["summary"] = "Deleted via the DataAPI";

		$ctx = $this->getPostContext($dataArray);

		$response = stream_get_contents(
		fopen($this->url,'rb', true, $ctx));
		//echo("\n\n".$response);
	}

	function pcpMoveArticle($url, $fromTitle, $toTitle, $userName, $pw, $lt, $uid){
		$dataArray = array("action" => "wspcp");
		$dataArray["method"] = "movePage";
		$dataArray["title"] = $fromTitle;
		$dataArray["fromTitle"] = $fromTitle;
		$dataArray["toTitle"] = $toTitle;
		$dataArray["un"] = $this->userName;
		$dataArray["pwd"] = $this->pw;
		$dataArray["lt"] = $lt;
		$dataArray["uid"] = $uid;
		$dataArray["format"] = "xml";
		$dataArray["summary"] = "Moved via the DataAPI";

		$ctx = $this->getPostContext($dataArray);

		$response = stream_get_contents(
		fopen($this->url,'rb', true, $ctx));
		//echo("\n\n".$response);
	}

	function pcpUpdateArticle($url, $title, $userName, $pw, $lt, $uid, $text){
		$dataArray = array("action" => "wspcp");
		$dataArray["method"] = "updatePage";
		$dataArray["title"] = $title;
		$dataArray["un"] = $this->userName;
		$dataArray["pwd"] = $this->pw;
		$dataArray["lt"] = $lt;
		$dataArray["uid"] = $uid;
		$dataArray["text"] = $text;
		$dataArray["summary"] = "Created via the DataAPI";
		$dataArray["format"] = "xml";

		$ctx = $this->getPostContext($dataArray);

		$response = stream_get_contents(
		fopen($this->url,'rb', true, $ctx));
		//echo("\n\n".$response);
	}

	function pcpCreateArticle($url, $title, $userName, $pw, $lt, $uid, $text){
		$dataArray = array("action" => "wspcp");
		$dataArray["method"] = "createPage";
		$dataArray["title"] = $title;
		$dataArray["un"] = $this->userName;
		$dataArray["pwd"] = $this->pw;
		$dataArray["lt"] = $lt;
		$dataArray["uid"] = $uid;
		$dataArray["text"] = $text;
		$dataArray["summary"] = "Created via the DataAPI";
		$dataArray["format"] = "xml";

		$ctx = $this->getPostContext($dataArray);

		$response = stream_get_contents(
		fopen($this->url,'rb', true, $ctx));
		//echo("\n\n".$response);
	}

	function pomGetTemplates($url, $title, $name = ""){
		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);

		$method = "getTemplates";
		if(strlen($name) > 0){
			$name = "&ttitle=".$name;
			$method = "getTemplateByTitle";
		}

		$response = stream_get_contents(
		fopen($this->url."?action=wspom&format=xml&method=".$method."&title=".$title.$name,'rb', true, $ctx));
		//echo("\n\n".$response);

		return $response;
	}

	function pomGetElements($url, $title){
		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);

		$response = stream_get_contents(
		fopen($this->url."?action=wspom&format=xml&method=getElements&title=".$title,'rb', true, $ctx));
		//echo("\n\n".$response);

		return $response;
	}

	function sfGetForm($url, $title, $direct = false, $format = "xml"){
		$params = array('http' => array('method' => 'GET'));
		$ctx = stream_context_create($params);
		if($direct){
			$direct = "sfname";
		} else {
			$direct = "title";
		}
		$response = stream_get_contents(
		fopen($this->url."?action=sfdata&format=".$format."&".$direct."=".$title,'rb', true, $ctx));
		//echo("\n\n".$response);
		return $response;
	}

	function sfUpdateForm($url, $userName, $lt, $uid, $xmldata, $format = "xml"){
		$dataArray = array("action" => "sfdata");
		$dataArray["mt"] = "update";
		$dataArray["un"] = $this->userName;
		$dataArray["lt"] = $lt;
		$dataArray["uid"] = $uid;
		if($format == "xml"){
			$dataArray["xmldata"] = $xmldata;
		} else {
			$dataArray["jsondata"] = $xmldata;
		}
		$dataArray["format"] = "xml";

		$ctx = $this->getPostContext($dataArray);

		$response = stream_get_contents(
		fopen($this->url,'rb', true, $ctx));
		//echo("\n\n".$response);
	}

	function sfCreateForm($url, $userName, $lt, $uid, $xmldata, $format = "xml"){
		$dataArray = array("action" => "sfdata");
		$dataArray["mt"] = "create";
		$dataArray["un"] = $this->userName;
		$dataArray["lt"] = $lt;
		$dataArray["uid"] = $uid;
		if($format == "xml"){
			$dataArray["xmldata"] = $xmldata;
		} else {
			$dataArray["jsondata"] = $xmldata;
		}
		$dataArray["format"] = "xml";

		$ctx = $this->getPostContext($dataArray);

		$response = stream_get_contents(
		fopen($this->url,'rb', true, $ctx));
		echo("\n\n".$response);
	}

	function getPostData($dataArray){
		$data = "";
		foreach ($dataArray as $key => $value){
			if(!$first){
				$data .= "&";
			}
			$data .= urlencode($key)."=".urlencode($value);
			$first = false;
		}
		return $data;
	}

	function getPostContext($dataArray){
		$data = $this->getPostData($dataArray);

		$params = array('http' => array(
    	'method' => 'POST',
    	'content-length' =>strlen($data),
		'content' => $data
		));
		return stream_context_create($params);
	}

	function getProjectTemplateText(){
		return
	'<noinclude>{{Template
|PAGENAME=Template:Project
|Has author=WikiSysop
|part of=Teamwork section
|rationale=This template is used for formatting and annotating projects.
|long description=__NOTITLE__
This template is used for collecting data about some project. When inserted in an project\'s page, it creates a decorative table with much helpful information. It also takes care of annotating the given data semantically, so that users can easily find it or query it in other articles. Do not try to read this page\'s source code for learning table syntax â€“ there are far easier ways of creating tables in MediaWiki.
|parameters=To use this template, insert the following at the beginning of your user page. 
<pre>
{{Project
 |  Title=formal title of project
 |  Homepage=URL of the homepage (with http://)
 |  Homepage label=Optional label if homepage URL is too long
 |  Start date=October 20 2008
 |  End date=October 24 2008
 |  Members=Kai, Ingo, Daniel
 |  Sponsors=Ontoprise, Vulcan (comma delimited list)
 |  Status=(for example a phase or completion status)
 |  Description=Description of project 
}}
</pre>
The order of the fields is not relevant. The template should be given as the first thing on a page.
|Calls template=Template:Tablelongrow, Template:Tablerow, Template:Tablesection
}}
{{Copyright of article
|own work=True
|copyright holder=ontoprise GmbH
}}
</noinclude><includeonly>{| cellspacing="0" cellpadding="5" style="border: 1px solid rgb(170, 170, 170); margin: 0pt 0pt 0.5em 1em; background: rgb(255, 255, 255) none repeat scroll 0% 0%; position: relative; border-collapse: collapse; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; float: right; clear: right; width: 35%;"
|-
! style="background: rgb(51, 102, 204) none repeat scroll 0% 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; color: white;" colspan="2" | {{#if:{{{Title}}}|[[Title::{{{Title}}}]]|{{PAGENAME}}}}
|-
{{#ifeq:{{{Description|}}}|||{{Tablerow|Label=Description:|Value=[[Description::{{{Description|}}}]]}}}}
|-
{{#ifeq:{{{Description|}}}|||{{Tablesection|Label=Details|Color=#99ccff}}}}
|-
{{#ifeq:{{{Start date|}}}|||{{Tablerow|Label=Start Date: |Value=[[start date::{{{Start date}}}]]}}}}
|-
{{#ifeq:{{{End date|}}}|||{{Tablerow|Label=End Date: |Value=[[end date::{{{End date}}}]]}}}}
|-
{{#ifeq:{{{Homepage|}}}|||{{Tablerow|Label=Homepage: |Value=[[homepage::{{{Homepage}}}|{{{Homepage label|{{{Homepage}}}}}}]]}}}}
|-
{{#ifeq:{{{Status|}}}|||{{Tablerow|Label=Status: |Value=[[Status::{{{Status}}}]]}}}}
|-
{{#ifeq:{{{Members|}}}|||{{Tablesection|Label=Team members|Color=#99ccff}}}}
|-
{{#ifeq:{{{Members|}}}|||{{Tablelongrow|Value={{#arraymap:{{{Members|}}}|,|x|[[Project member::x]]}}}}}}
|-
{{Tablelongrow|Align=left|Value={{ #ifeq: {{#ask: [[Category:Phonebook]][[People for::{{SUBJECTPAGENAME}}]] }} || {{create phonebook}} | <small>For all people involved see: {{#ask: [[Category:Phonebook]][[People for::{{SUBJECTPAGENAME}}]]}}</small> }} }}
|-
{{#ifeq:{{{Sponsors|}}}|||{{Tablesection|Label=Sponsoring Organizations|Color=#99ccff}}}}
|-
{{#ifeq:{{{Sponsors|}}}|||{{Tablelongrow|Value={{#arraymap:{{{Sponsors|}}}|,|x|[[Sponsored by::x]]}}}}}}
|}

{{ActiveTasksInfoBox|color=#99ccff}} 

{| cellspacing="0" cellpadding="5" style="border: 1px solid rgb(170, 170, 170); margin: 0pt 0pt 0.5em 1em; background: rgb(255, 255, 255) none repeat scroll 0% 0%; position: relative; border-collapse: collapse; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; float: right; clear: right; width: 35%;"
|-
{{Tablesection|Label=Upcoming Events <div style="font-size: 80%; float: right;">{{create event}}</div>|Color=#99ccff}}
|-
{{Tablelongrow|Align=left|Value={{#ask: [[Category:Event]][[Related to::{{SUBJECTPAGENAME}}]][[End date::+]][[End date::>{{TODAY for ask}}]]|?Start date=Start: |sort=Start date|order=asc|limit=5|format=ul }} }}
|-
{{Tablelongrow|Align=left|Value={{ #ifeq: {{#ask: [[Category:Calendar]][[Related to::{{SUBJECTPAGENAME}}]] }} || {{create calendar}} for this project| <small>For all events see: {{#ask: [[Category:Calendar]][[Related to::{{SUBJECTPAGENAME}}]]}}</small> }} }}
|}

[[Category:Project]]</includeonly><noinclude>
</noinclude>
	';
	}

	function getProjectFormText(){
		return '<noinclude>{{Form
|PAGENAME=Form:Project
|Has author=WikiSysop
|part of=Teamwork section
|rationale=This form is used for adding or editing projects.
|long description=__NOTITLE__
This is the Project form.
To add a page with this form, enter the page name below;
if a page with that name already exists, you will be sent to a form to edit that page.
|Form thumbnail=Image:Project created with form.png
|Form needs context=false
|Populates template=Template:Project
}}
{{Copyright of article
|own work=True
|copyright holder=ontoprise
|year=2009
}}
</noinclude><includeonly>{{{info|WYSIWYG||page name=<Project[Title]> <unique number;start=1>}}}

{{{for template|Project|label=Enter project data}}}
{| class="formtable" width="100%"
|-
| width="20%" |
| width="80%" |
|-
! valign="top" | Title*:
| valign="top" | {{Forms/Generate form field including PAGENAME|fieldname=Title|params=mandatory{{!}}size=59}}
|-
! valign="top" | Description:
| valign="top" | {{{field|Description|rows=5|cols=45}}}
|-
! valign="top" | Start date:
| valign="top" | {{{field|Start date|input type=date|default=now}}}
|-
! valign="top" | End date:
| valign="top" | {{{field|End date|input type=date}}}
|-
! valign="top" | Website <nowiki>(include \'http://\')</nowiki>:
| valign="top" | {{{field|Homepage|size=59}}}
|-
! valign="top" | Status:
| valign="top" | {{{field|Status|size=59}}}
|-
!
| valign="top" | <small>As an example the values for status are preset to Red, Yellow and Green. You may customize this list by changing the allowed values in the [[property:status|status property]]</small>
|-
! valign="top" | Members:
| valign="top" | {{{field|Members|input type=haloACtextarea|rows=1|cols=45|constraints=ask: [[Category:Person]]|list}}}
|-
!
| valign="top" | <small>Enter a comma delimited list of project members. (Auto-completes on pages in the category Person.)</small>
|-
! valign="top" | Sponsors:
| valign="top" | {{{field|Sponsors|input type=haloACtext|constraints=ask: [[Category:Organization]]|list|size=59}}}
|-
!
| valign="top" | <small>Enter a comma delimited list of organizations. (Auto-completes on pages in the category Organization.)</small>
|}
{{{end template}}}

{{{for template|ShowAttachedContent|label=Show attached content|collapsible}}}
{| class="formtable" width="100%" 
|-
| width="20%" |
| width="80%" |
|-
! valign="top" | Show attached wiki articles:
| valign="top" | {{{field|show|input type=radiobutton|mandatory|default=True|values=True,False}}} 
|-
!
| valign="top" | <small>Select "True" if you want to show the articles attached to this project article.</small>
|}
{{{end template}}}

----

Enter your rich text here:

{{{standard input|free text|rows=35|cols=120}}}
----

{{{for template|RMList|label=Show attached files}}}
{| class="formtable" width="100%"
|-
| width="20%" |
| width="80%" |
|-
! valign="top" | Show list of attached media files:
| valign="top" | {{{field|show|input type=radiobutton|mandatory|default=True|values=True,False}}} 
|-
!
| valign="top" | <small>Select "True" if you want to show the list of media files attached to this organization article.</small>
|}
{{{end template}}}

<small>Fields marked with "*" are mandatory.</small> 

{{{standard input|minor edit}}} {{{standard input|watch}}}

{{{standard input|save}}} {{{standard input|preview}}} {{{standard input|changes}}} {{{standard input|cancel}}}</includeonly><noinclude>
</noinclude>
	';
	}

	function getShowAttachedContentTemplateText(){
		return '
	<noinclude>{{Template
|PAGENAME=Template:ShowAttachedContent
|Has author=WikiSysop
|part of=Teamwork section,
|rationale=Displays the attached content to this article.
|long description=__NOTITLE__
|parameters=<pre>
{{ShowAttachedContent
|show=True}}
</pre>
|Calls template=Template:Subsections, 
}}
{{Copyright of article
|own work=True
|copyright holder=ontoprise
|year=2009
}}
</noinclude><includeonly>{{#ifeq:{{{show|}}}|True|{{Subsections}}|}}</includeonly><noinclude>
</noinclude>
	';
	}

	function getRMListTemplateText(){
		return '
	<noinclude>{{Template
|PAGENAME=Template:RMList
|Has author=WikiSysop
|part of=SMWplus Basic Ontology Section,
|rationale=This template lists all media which has been uploaded and attached to a particular article.
|long description=__NOTITLE__
|parameters=<pre>
{{RMList
|show=True}}
</pre>
|Calls template=Template:RMListOuterTable
|hints_for_templates_and_forms=To attach uploaded media to an article, the property 
}}
{{Copyright of article
|own work=True
|copyright holder=ontoprise GmbH
|year=2009
|hints_for_templates_and_forms=To attach uploaded media to an article, the property 
}}
</noinclude><includeonly><div style="font-color:blue;float:left;width:100%">
{{#ifeq:{{{show|}}}|True|{{RMListOuterTable}}|}}
</div></includeonly><noinclude>
</noinclude>
	';
	}

	function getAComplexProjectText($title){
		return '{{Project
|Title='.$title.';
|Description=This project is complex project, involving a bunch of people and a lot of milestones and tasks.
|Start date=2008/06/10
|End date=2009/07/10
|Homepage=http://complex-project.eu
|Status=Green
|Members=Fred, Dian, Daniel, Joe Mystery, Regina
|Sponsors=Accenture, Evri
}}
{{ShowAttachedContent
|show=True
}}
{{RMList
|show=False
}}';
	}

	function getAComplexProjectSFData($title, $show){
		return '<?xml version="1.0"?>
<api>
	<sfdata>
		<page title="A_complex_project" ns="0">
			<Project>
				<template0 tmpl_name="Project">
					<field1 num="" input_type="text" is_mandatory="1" is_hidden=""
						is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list=""
						cur_value="'.$title.'">
						<template_field field_name="Title" label="Title"
							semantic_property="Title" field_type="String" is_list=""
							input_type="">
							<possible_values />
						</template_field>
						<possible_values />
					</field1>
					<field2 num="" input_type="" is_mandatory="" is_hidden=""
						is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list=""
						cur_value="This project is complex project, involving a bunch of people and a lot of milestones and tasks.">
						<template_field field_name="Description" label="Description"
							semantic_property="Description" field_type="Text" is_list=""
							input_type="">
							<possible_values />
						</template_field>
						<possible_values />
					</field2>
					<field3 num="" input_type="date" is_mandatory="" is_hidden=""
						is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list=""
						cur_value="2008/06/10">
						<template_field field_name="Start date" label="Start date"
							semantic_property="start date" field_type="Date" is_list=""
							input_type="">
							<possible_values />
						</template_field>
						<possible_values />
					</field3>
					<field4 num="" input_type="date" is_mandatory="" is_hidden=""
						is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list=""
						cur_value="2009/07/10">
						<template_field field_name="End date" label="End date"
							semantic_property="end date" field_type="Date" is_list=""
							input_type="">
							<possible_values />
						</template_field>
						<possible_values />
					</field4>
					<field5 num="" input_type="" is_mandatory="" is_hidden=""
						is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list=""
						cur_value="http://complex-project.eu">
						<template_field field_name="Homepage" label="Homepage"
							semantic_property="homepage" field_type="URL" is_list=""
							input_type="">
							<possible_values />
						</template_field>
						<possible_values />
					</field5>
					<field6 num="" input_type="" is_mandatory="" is_hidden=""
						is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list=""
						cur_value="Green">
						<template_field field_name="Status" label="Status"
							semantic_property="Status" field_type="enumeration" is_list=""
							input_type="">
							<possible_values value0="Green" value1="Yellow"
								value2="Red" />
						</template_field>
						<possible_values />
					</field6>
					<field7 num="" input_type="haloACtextarea" is_mandatory=""
						is_hidden="" is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list="1"
						cur_value="Fred, Dian, Daniel, Joe Mystery, Regina">
						<template_field field_name="Members" label="Members"
							semantic_property="Project member" field_type="Page" is_list="1"
							input_type="">
							<possible_values />
						</template_field>
						<possible_values />
					</field7>
					<field8 num="" input_type="haloACtext" is_mandatory=""
						is_hidden="" is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list="1"
						cur_value="Accenture, Evri">
						<template_field field_name="Sponsors" label="Sponsors"
							semantic_property="Sponsored by" field_type="Page" is_list="1"
							input_type="">
							<possible_values />
						</template_field>
						<possible_values />
					</field8>
				</template0>
				<template1 tmpl_name="ShowAttachedContent">
					<field1 num="" input_type="radiobutton" is_mandatory="1"
						is_hidden="" is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list=""
						cur_value="True">
						<template_field field_name="show" label="Show"
							semantic_property="" field_type="" is_list="" input_type="">
							<possible_values />
						</template_field>
						<possible_values>
							<value>True</value>
							<value>False</value>
						</possible_values>
					</field1>
				</template1>
				<template2 tmpl_name="RMList">
					<field1 num="" input_type="radiobutton" is_mandatory="1"
						is_hidden="" is_restricted="" is_uploadable="" field_args=""
						autocomplete_source="" autocomplete_field_type="" no_autocomplete=""
						part_of_multiple="" input_name="" is_disabled="" is_list=""
						cur_value="'.$show.'">
						<template_field field_name="show" label="Show"
							semantic_property="" field_type="" is_list="" input_type="">
							<possible_values />
						</template_field>
						<possible_values>
							<value>True</value>
							<value>False</value>
						</possible_values>
					</field1>
				</template2>
			</Project>
		</page>
	</sfdata>
</api>';
	}

	function getProjectCategoryText(){
		return '{{Category
|PAGENAME=Category:Project
|Has author=WikiSysop
|part of=Teamwork section,
|Has default form=Form:Project
|rationale=This category is used for tagging projects.
|long description=__NOTITLE__
}}
{{Copyright of article
|own work=True
|copyright holder=ontoprise GmbH
}}
	';
	}

	function getCategoryTemplateText(){
		return '<noinclude>{{Template
|PAGENAME=Template:Category
|Has author=User:WikiSysop
|part of=SMWplus Basic Ontology Section
|rationale=This template is used to display a category.
|long description=__NOTITLE__
You should use this template to provide your wiki-community with a proper documentation of the categories used in the wiki.
|Calls template=Template:Header for wiki parts
}}
{{Copyright of article
|own work=True
|copyright holder=Ontoprise GmbH
|year=2009
}}
</noinclude><includeonly>__NOTOC__ {{Header for wiki parts
|Category=Category
|Icon=Image:Smw_plus_category_icon_100x100.gif
|Pagetitle={{PAGENAME}}
|Short description={{#ifeq:{{{rationale|}}}|||[[rationale::{{{rationale|}}}]]}}
|author={{#ifeq:{{{Has author|}}}|||{{#arraymap:{{{Has author|}}}|,|x|[[has author::x]]|,}}}}
|Wiki section={{#ifeq:{{{part of|}}}|||{{#arraymap:{{{part of|}}}|,|x|[[part of::x]]|,}}}}
}} {{{long description|}}} 

=== Default form  ===

This category is populated by the form: 
{{#ifeq:{{FULLPAGENAME}}|Category:Category<!--
-->|*{{#ifeq:{{{Has default form|}}}||not specified|[[Page has default form::{{{Has default form|}}}]]}}<!--
-->|*{{#ifeq:{{{Has default form|}}}||not specified|[[Has default form::{{{Has default form|}}}]]}}
}}[[Page has default form::Form:Add and edit Category| ]]

=== Defined in templates  ===

This category is used for category annotations in the templates: 

{{#ifeq:{{#ask:[[Category:Template]][[Defines category::{{FULLPAGENAME}}]]|default=}}||* none (you can create a new template using this category by clicking here: <span class="plainlinks">[{{SERVER}}{{SCRIPTPATH}}/index.php/Special:CreateTemplate?category={{urlencode:{{PAGENAME}}}} create template]</span>)|<ul>{{#ask:[[Category:Template]][[Defines category::{{FULLPAGENAME}}]]|format=template|template=Show defining template|link=none}}</ul>
}} <!-- not needed currently, too fine-grained
<nowiki>
=== Rules  ===

==== Read by rules  ====

This category is read by the following rules: {{#sparql:[[Category:Rule]][[Is read by rule::{{FULLPAGENAME}}]]|?=|format=ul|default=<ul><li>no rule reading this</ul>}} 

==== Modified by rules  ====

This category is modified by the following rules: {{#sparql:[[Category:Rule]][[Is written by rule::{{FULLPAGENAME}}]]|?=|format=ul|default=<ul><li>no rule modifying this</ul>}}
</nowiki>
--><!-- category annotations --><!--
-->{{#ifeq:{{FULLPAGENAME}}|Category:Category|[[Category:Wiki part| ]]|}}[[Is category::true| ]]<!--
--></includeonly><noinclude>
</noinclude>
	';
	}
	
	private function getAComplexProjectSFJSONData($title){
		return '{"sfdata":{"page":{"title":"A_complex_project","ns":0,"Project":{"template0":{"tmpl_name":"Project","field1":{"num":null,"template_field":{"field_name":"Title","label":"Title","semantic_property":"Title","field_type":"String","possible_values":[],"is_list":null,"input_type":null},"input_type":"text","is_mandatory":true,"is_hidden":false,"is_restricted":null,"possible_values":{"_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":false,"cur_value":"'.$title.'"},"field2":{"num":null,"template_field":{"field_name":"Description","label":"Description","semantic_property":"Description","field_type":"Text","possible_values":[],"is_list":null,"input_type":null},"input_type":null,"is_mandatory":false,"is_hidden":false,"is_restricted":null,"possible_values":{"_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":false,"cur_value":"This project is complex project, involving a bunch of people and a lot of milestones and tasks."},"field3":{"num":null,"template_field":{"field_name":"Start date","label":"Start date","semantic_property":"start date","field_type":"Date","possible_values":[],"is_list":null,"input_type":null},"input_type":"date","is_mandatory":false,"is_hidden":false,"is_restricted":null,"possible_values":{"_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":false,"cur_value":"2008\/06\/10"},"field4":{"num":null,"template_field":{"field_name":"End date","label":"End date","semantic_property":"end date","field_type":"Date","possible_values":[],"is_list":null,"input_type":null},"input_type":"date","is_mandatory":false,"is_hidden":false,"is_restricted":null,"possible_values":{"_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":false,"cur_value":"2009\/07\/10"},"field5":{"num":null,"template_field":{"field_name":"Homepage","label":"Homepage","semantic_property":"homepage","field_type":"URL","possible_values":[],"is_list":null,"input_type":null},"input_type":null,"is_mandatory":false,"is_hidden":false,"is_restricted":null,"possible_values":{"_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":false,"cur_value":"http:\/\/complex-project.eu"},"field6":{"num":null,"template_field":{"field_name":"Status","label":"Status","semantic_property":"Status","field_type":"enumeration","possible_values":{"value0":"Green","value1":"Yellow","value2":"Red"},"is_list":null,"input_type":null},"input_type":null,"is_mandatory":false,"is_hidden":false,"is_restricted":null,"possible_values":{"_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":false,"cur_value":"Green"},"field7":{"num":null,"template_field":{"field_name":"Members","label":"Members","semantic_property":"Project member","field_type":"Page","possible_values":[],"is_list":true,"input_type":null},"input_type":"haloACtextarea","is_mandatory":false,"is_hidden":false,"is_restricted":null,"possible_values":{"_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":true,"cur_value":"Fred, Dian, Daniel, Joe Mystery, Regina"},"field8":{"num":null,"template_field":{"field_name":"Sponsors","label":"Sponsors","semantic_property":"Sponsored by","field_type":"Page","possible_values":[],"is_list":true,"input_type":null},"input_type":"haloACtext","is_mandatory":false,"is_hidden":false,"is_restricted":null,"possible_values":{"_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":true,"cur_value":"Accenture, Evri"}},"template1":{"tmpl_name":"ShowAttachedContent","field1":{"num":null,"template_field":{"field_name":"show","label":"Show","semantic_property":null,"field_type":null,"possible_values":[],"is_list":null,"input_type":null},"input_type":"radiobutton","is_mandatory":true,"is_hidden":false,"is_restricted":null,"possible_values":{"0":"True","1":"False","_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":false,"cur_value":"True"}},"template2":{"tmpl_name":"RMList","field1":{"num":null,"template_field":{"field_name":"show","label":"Show","semantic_property":null,"field_type":null,"possible_values":[],"is_list":null,"input_type":null},"input_type":"radiobutton","is_mandatory":true,"is_hidden":false,"is_restricted":null,"possible_values":{"0":"True","1":"False","_element":"value"},"is_uploadable":false,"field_args":null,"autocomplete_source":null,"autocomplete_field_type":null,"no_autocomplete":null,"part_of_multiple":false,"input_name":null,"is_disabled":false,"is_list":false,"cur_value":"False"}}}}}}';
	}
	
	function getProjectFormHelpTemplateText(){
		return '<noinclude></noinclude><includeonly><nowiki>{{{field|</nowiki>{{{fieldname}}}<nowiki>|input type=text|default=</nowiki>{{#ifeq: {{PAGENAME}}|Semantic Forms permissions test||{{PAGENAME}} }}<nowiki>|</nowiki>{{{params|}}}<nowiki>}}}</nowiki></includeonly><noinclude>
</noinclude>';		
	}
}

