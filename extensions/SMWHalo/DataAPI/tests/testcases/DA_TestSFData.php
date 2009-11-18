<?php

$params = array('http' => array('method' => 'GET'));
$ctx = stream_context_create($params);
$uri = "http://localhost/mediawiki/api.php?action=sfdata";


// title does not exist
/**
$response = stream_get_contents(
	fopen($uri."&title=TestMissingTitle&format=xml",
		 'rb', true, $ctx));
echo("\n".$response);

// title exists, no form available
$response = stream_get_contents(
	fopen($uri."&title=FormTestNoForm&format=xml",
		 'rb', true, $ctx));
echo("\n".$response);

//get article data
$response = stream_get_contents(
	fopen($uri."&title=FormTestArticle&format=xml",
		 'rb', true, $ctx));
echo("\n".$response);

//get list of forms
$response = stream_get_contents(
	fopen($uri."&sflist=root&format=xml",
		 'rb', true, $ctx));
echo("\n".$response);

//get form data
$response = stream_get_contents(
	fopen($uri."&title=Form:FormTest&format=xml",
		 'rb', true, $ctx));
echo("\n".$response);


//create new article
//get an existing article as basis for the new one
$response = stream_get_contents(
	fopen($uri."&title=FormTestArticle&format=xml",
		 'rb', true, $ctx));

$response = str_replace("FormTestArticle","FormTestArticle2",$response);
echo("\n\n".$response."\n\n");
*/

$response = stream_get_contents(
	fopen("http://localhost/mediawiki/api.php?action=wspcp&method=login&un=WikiSysop&pwd=m8nix&format=xml",
		 'rb', true, $ctx));

echo($response);
	
$response = new SimpleXMLElement($response);
$response = $response->xpath("//login/@logintoken");
$lt = $response[0];

//<page title="A_complex_project" ns="0" rid="10459">
$response = '<?xml version="1.0" encoding="utf-8"?><api><sfdata><page title="A_complex_project" ns="0"><Project tmpl_name="Project"><field0 cur_value="A complex project" field_name="Title" /><field1 cur_value="This project is simple project%2c involving a bunch of people and a lot of milestones and tasks." field_name="Description" /><field2 cur_value="1974/09/14" field_name="Start date" /><field3 cur_value="2009/07/10" field_name="End date" /><field4 cur_value="httpcomplex-project.eu" field_name="Homepage" /><field5 cur_value="Green" field_name="Status" /><field6 cur_value="Fred%2c Dian%2c Daniel%2c Joe Mystery%2c Regina" field_name="Members" /><field7 cur_value="Accenture Evri bla" field_name="Sponsors" /></Project></page></sfdata></api>';


$uri = "http://localhost/mediawiki/api.php";
$dataArray = array("action" => "sfdata");
$dataArray["xmldata"] = $response;
$dataArray["format"] = "xml";
$dataArray["mt"] = "u";
$dataArray["un"] = "WikiSysop";
$dataArray["uid"] = 1;
$dataArray["lt"] = $lt;

$ctx = getPostContext($dataArray);

$response = stream_get_contents(
	fopen($uri,'rb', true, $ctx));
echo("\n".$response);

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
	$data = getPostData($dataArray);
	
	$params = array('http' => array(
    	'method' => 'POST',
    	'content-length' =>strlen($data),
		'content' => $data
	));
	return stream_context_create($params);
}