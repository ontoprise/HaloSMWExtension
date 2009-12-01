<?php
$url = "http://localhost/mediawiki/api.php";
$userName = "WikiSysop";
$pw = "root";

$lt = pcpLogin($url, $userName, $pw);
echo "\n".$lt."\n";

pcpDeleteArticle($url, "Template:Project", $userName, $pw, $lt);
//pcpCreateArticle($url, "Template:Project", $userName, $pw, $lt, getProjectTemplateText());
//
//pcpDeleteArticle($url, "Form:Project", $userName, $pw, $lt);
//pcpCreateArticle($url, "Form:Project", $userName, $pw, $lt, getProjectFormText());
//
//pcpDeleteArticle($url, "Template:ShowAttachedContent", $userName, $pw, $lt);
//pcpCreateArticle($url, "Template:ShowAttachedContent", $userName, $pw, $lt, getShowAttachedContentTemplateText());
//
//pcpDeleteArticle($url, "Template:RMList", $userName, $pw, $lt);
//pcpCreateArticle($url, "Template:RMList", $userName, $pw, $lt, getRMListTemplateText());
//
//pcpDeleteArticle($url, urlencode("A complex project"), $userName, $pw, $lt);
//
//pcpCreateArticle($url, urlencode("A_complex_project"), $userName, $pw, $lt, getAComplexProjectText());
//pcpUpdateArticle($url, urlencode("A_complex_project"), $userName, $pw, $lt, getAComplexProjectText());
//
//sfGetForm($url, urlencode("A_complex_project"));
//$xmldata = getAComplexProjectSFData();
//sfUpdateForm($url, $userName, $lt, $xmldata);

function pcpLogin($url, $userName, $pw){
	$params = array('http' => array('method' => 'GET'));
	$ctx = stream_context_create($params);
	$response = stream_get_contents(
		fopen($url."?action=wspcp&method=login&un=".$userName."&pwd=".$pw."&format=xml",
		'rb', true, $ctx));
	$response = new SimpleXMLElement($response);
	$response = $response->xpath("//login/@logintoken");
	$lt = $response[0];
	return $lt;
}

function pcpDeleteArticle($url, $title, $userName, $pw, $lt){
	$dataArray = array("action" => "wspcp");
	$dataArray["method"] = "deletePage";
	$dataArray["title"] = $title;
	$dataArray["un"] = $userName;
	$dataArray["pwd"] = $pw;
	$dataArray["lt"] = $lt;
	$dataArray["format"] = "xml";

	$ctx = getPostContext($dataArray);
	
	$response = stream_get_contents(
		fopen($url,'rb', true, $ctx));
	echo("\n".$response);
}

function pcpCreateArticle($url, $title, $userName, $pw, $lt, $text){
	$dataArray = array("action" => "wspcp");
	$dataArray["method"] = "createPage";
	$dataArray["title"] = $title;
	$dataArray["un"] = $userName;
	$dataArray["pwd"] = $pw;
	$dataArray["lt"] = $lt;
	$dataArray["text"] = $text;
	$dataArray["summary"] = "Created via the DataAPI";
	$dataArray["format"] = "xml";

	$ctx = getPostContext($dataArray);
	
	$response = stream_get_contents(
		fopen($url,'rb', true, $ctx));
	echo("\n".$response);
}

function pcpUpdateArticle($url, $title, $userName, $pw, $lt, $text){
	$dataArray = array("action" => "wspcp");
	$dataArray["method"] = "updatePage";
	$dataArray["title"] = $title;
	$dataArray["un"] = $userName;
	$dataArray["pwd"] = $pw;
	$dataArray["lt"] = $lt;
	$dataArray["text"] = $text;
	$dataArray["summary"] = "Created via the DataAPI";
	$dataArray["format"] = "xml";

	$ctx = getPostContext($dataArray);
	
	$response = stream_get_contents(
		fopen($url,'rb', true, $ctx));
	echo("\n".$response);
}

function sfGetForm($url, $title){
	$params = array('http' => array('method' => 'GET'));
	$ctx = stream_context_create($params);
	
	$response = stream_get_contents(
		fopen($url."?action=sfdata&format=xml&title=".$title,'rb', true, $ctx));
	echo("\n".$response);
	//file_put_contents("d:\zz-zz.txt", $response);
}

function sfUpdateForm($url, $userName, $lt, $xmldata){
	$dataArray = array("action" => "sfdata");
	$dataArray["mt"] = "update";
	$dataArray["un"] = $userName;
	$dataArray["lt"] = $lt;
	$dataArray["xmldata"] = $xmldata;
	$dataArray["format"] = "xml";

	$ctx = getPostContext($dataArray);
	
	$response = stream_get_contents(
		fopen($url,'rb', true, $ctx));
	echo("\n".$response);
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
	$data = getPostData($dataArray);
	
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
This template is used for collecting data about some project. When inserted in an project\'s page, it creates a decorative table with much helpful information. It also takes care of annotating the given data semantically, so that users can easily find it or query it in other articles. Do not try to read this page\'s source code for learning table syntax – there are far easier ways of creating tables in MediaWiki.
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

function getAComplexProjectText(){
	return '
{{Project
|Title=A complex project
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
}}
	';	
}

function getAComplexProjectSFData(){
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
						cur_value="HOHAHI2">
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
						cur_value="False">
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