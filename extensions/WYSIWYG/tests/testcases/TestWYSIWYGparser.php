<?php
/*
 * Copyright (C) ontoprise GmbH
 *
 * Vulcan Inc. (Seattle, WA) and ontoprise GmbH (Karlsruhe, Germany)
 * expressly waive any right to enforce any Intellectual Property
 * Rights in or to any enhancements made to this program.
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
 *
 * @file
 * @ingroup WYSIWYGTests
 *
 * description...

 */
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the WYSIWYG extension. It is not a valid entry point.\n" );
}

class TestWYSIWYGparser extends PHPUnit_Framework_TestCase {

    function setUp() {
        // calling the CKparser a second time makes trouble with skins, therefore
        // set to monobook, and some default mechanism work.
        global $wgDefaultSkin, $wgValidSkinNames;
        $wgDefaultSkin = "ontoskin3";
        $wgValidSkinNames = array(
            "ontoskin3" => "OntoSkin3",
            "simple" => "Simple",
            "monobook" => "MonoBook",
            "vector" => "Vector",
        );

        $this->title = Title::newFromText("__dummy__");
        $this->options = new CKeditorParserOptions();
        $this->options->setTidy(true);
        $this->parser = new CKeditorParser();
        $this->parser->setOutputType(OT_HTML);
        global $wgTitle;
        $wgTitle = $this->title;
    }

    function tearDown() {
    }

    function testPage1() {

        $text = 'This is the capital of [[Also known as::Germany|Deutschland]]. We are living in the year {{CURRENTYEAR}} AC.

{{#language:ar}}

[[Category:City]]';

        $expected = '<p>This is the capital of <span class="fck_mw_property" property="Also known as::Germany">Deutschland</span>. We are living in the year <span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="CURRENTYEAR" _fck_mw_tagtype="v">_</span> AC.</p><p><span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="#language" _fck_mw_tagtype="p">ar_</span></p><br/><span _fcknotitle="true" class="fck_mw_category" sort="City">City</span>';

        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);
    }

    function testPage2() {
        $text = 'This is empty [[Prop::{{{1}}}| ]]. Param2 comes here: {{{2}}} which is good. Also there is no template.';
        $expected = '<p>This is empty <span class="fck_mw_property" property="Prop::{{{1}}}">&nbsp;</span>. Param2 comes here: <span class="fck_mw_template">{{{2}}}</span> which is good. Also there is no template.</p>';
        $res = $this->parsePage($text);
//        var_dump($res, $expected);
        $this->assertEquals($expected, $res);
    }

    /* because of the not installed RichMedia extension this test doen't make sense
    function testPage3() {
        $text = 'This link [[Document:Richmedia.doc|Richmedia.doc]] is created when you upload an document with the [http://smwforum.ontoprise.com Richmedia extension].';
        $expected = '<p>This link <a title="Richmedia.doc" _fck_mw_type="Document" _fck_mw_filename="Richmedia.doc" _fcksavedurl="Document:Richmedia.doc" href="Richmedia.doc">Richmedia.doc</a> is created when you upload an document with the <a href="http://smwforum.ontoprise.com">Richmedia extension</a>.
</p>';
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);
    }
     * 
     */

    function testPage4() {
        $text = '{| cellspacing="0" cellpadding="5" style="border: 1px solid rgb(170, 170, 170); margin: 0pt 0pt 0.5em 1em; background: rgb(255, 255, 255) none repeat scroll 0% 0%; position: relative; border-collapse: collapse; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous; float: right; clear: right; width: 35%;"
|-
! style="background: rgb(255, 150, 2) none repeat scroll 0% 0%; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous; color: white;" colspan="2" | <span style="font-size: 80%; float: right;">{{#ask:[[{{PAGENAME}}]]
|?Description=description
|?start date=start
|?end date=end
|?location=location
|searchlabel=iCal
|format=icalendar}}</span> {{#if:{{{Title}}}|[[Title::{{{Title}}}]]|{{PAGENAME}}}}
|-
{{#ifeq:{{{Description|}}}|||{{Tablerow|Label=Description:|Value=[[Description::{{{Description|}}}]]}}}}
|-
{{#ifeq:{{{Homepage|}}}|||{{Tablerow|Label=Homepage: |Value=[[homepage::{{{Homepage}}}|{{{Homepage label|{{{Homepage}}}}}}]]}}}}
|-
{{#ifeq:{{{Attendee|}}}|||{{Tablelongrow|Value={{#arraymap:{{{Attendee|}}}|,|x|[[Attendee::x]]}}}}}}
|-
{{#ifeq:{{{Related to|}}}|||{{Tablesection|Label=Related items|Color=#ffcd87}}}}
|}

[[Category:Event]]
';
        $expected = '<table cellspacing="0" cellpadding="5" style="border: 1px solid rgb(170, 170, 170); margin: 0pt 0pt 0.5em 1em; background: rgb(255, 255, 255) none repeat scroll 0% 0%; position: relative; border-collapse: collapse; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous; float: right; clear: right; width: 35%;">

<tr>
<th style="background: rgb(255, 150, 2) none repeat scroll 0% 0%; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous; color: white;" colspan="2"> <span style="font-size: 80%; float: right;"><span class="fck_smw_query">{{#ask:[[{{PAGENAME}}]]fckLR|?Description=descriptionfckLR|?start date=startfckLR|?end date=endfckLR|?location=locationfckLR|searchlabel=iCalfckLR|format=icalendar}}</span></span> <span class="fck_mw_template">{{#if:{{{Title}}}|[[Title::{{{Title}}}]]|{{PAGENAME}}}}</span>
</th></tr>
<span class="fck_mw_template">{{#ifeq:{{{Description|}}}|||{{Tablerow|Label=Description:|Value=[[Description::{{{Description|}}}]]}}}}</span>

<span class="fck_mw_template">{{#ifeq:{{{Homepage|}}}|||{{Tablerow|Label=Homepage: |Value=[[homepage::{{{Homepage}}}|{{{Homepage label|{{{Homepage}}}}}}]]}}}}</span>

<span class="fck_mw_template">{{#ifeq:{{{Attendee|}}}|||{{Tablelongrow|Value={{#arraymap:{{{Attendee|}}}|,|x|[[Attendee::x]]}}}}}}</span>

<span class="fck_mw_template">{{#ifeq:{{{Related to|}}}|||{{Tablesection|Label=Related items|Color=#ffcd87}}}}</span>
</table>
<br/><span _fcknotitle="true" class="fck_mw_category" sort="Event">Event</span> <br/>';

        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);
    }

    function testPage5() {
        $text = '<WebService>
<uri name="http://api.opencalais.com/enlighten/?wsdl" />
<protocol>SOAP</protocol>
<method name="Enlighten" />
<parameter name="content"  optional="false"  path="/parameters/content" />
<result name="result" >
<part name="company"  path="//EnlightenResult" xpath="//rdf:Description[./rdf:type/@rdf:resource=\'http://s.opencalais.com/1/type/em/e/Company\']/c:name"/>
</result>
<displayPolicy>
<once/>
</displayPolicy>
<spanOfLife value="0" expiresAfterUpdate="true" />
</WebService>';

        $expected = '<p>&lt;WebService&gt;
&lt;uri name="http://api.opencalais.com/enlighten/?wsdl" /&gt;
&lt;protocol&gt;SOAP&lt;/protocol&gt;
&lt;method name="Enlighten" /&gt;
&lt;parameter name="content"  optional="false"  path="/parameters/content" /&gt;
&lt;result name="result" &gt;
&lt;part name="company"  path="//EnlightenResult" xpath="//rdf:Description[./rdf:type/@rdf:resource=\'http://s.opencalais.com/1/type/em/e/Company\']/c:name"/&gt;
&lt;/result&gt;
&lt;displayPolicy&gt;
&lt;once/&gt;
&lt;/displayPolicy&gt;
&lt;spanOfLife value="0" expiresAfterUpdate="true" /&gt;
&lt;/WebService&gt;
</p>';
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);
    }

    function testPage6() {
        $text= '<noinclude>
{{Template
|PAGENAME=Template:Test4
|rationale=Some test
|defines category=Category:
}}
<pre>
{{Test4
|Name=
|vorname=
}}
</pre>
Edit the page to see the template text.
</noinclude><includeonly>
{| class="wikitable"
! Name
| [[name::{{{Name|}}}]]
|-
! vorname
| [[vorname_df::{{{vorname|}}}]]
|-
!
| {{#ask:[[Belongs to comment::{{SUBJECTPAGENAME}}]]|format=list}}
|}
</includeonly>';
        $expected = '<p><span class="fck_mw_noinclude" _fck_mw_customtag="true" _fck_mw_tagname="noinclude">fckLR{{TemplatefckLR|PAGENAME=Template:Test4fckLR|rationale=Some testfckLR|defines category=Category:fckLR}}fckLR&lt;pre&gt;fckLR{{Test4fckLR|Name=fckLR|vorname=fckLR}}fckLR&lt;/pre&gt;fckLREdit the page to see the template text.fckLR</span><span class="fck_mw_includeonly" _fck_mw_customtag="true" _fck_mw_tagname="includeonly">fckLR{| class=&quot;wikitable&quot;fckLR! NamefckLR| [[name::{{{Name|}}}]]fckLR|-fckLR! vornamefckLR| [[vorname_df::{{{vorname|}}}]]fckLR|-fckLR!fckLR| {{#ask:[[Belongs to comment::{{SUBJECTPAGENAME}}]]|format=list}}fckLR|}fckLR</span>
</p>';
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);

    }

    function testPage7() {
        $text= 'Super

[[Image:SMWplus151 enhancedretrieval.png|My dashboard menu of {{SMW+}}]]

and

[[Image:SMWplus151 enhancedretrieval.png|My dashboard menu]]';
        $expected = '<p>Super
</p><p><img src="ef=" _fck_mw_filename="SMWplus151 enhancedretrieval.png" alt="My dashboard menu of {{SMW+}}" />
</p><p>and
</p><p><img src="ef=" _fck_mw_filename="SMWplus151 enhancedretrieval.png" alt="My dashboard menu" />
</p>';
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);

    }

    function testPage8() {
        $text= 'Sometimes we also have a link like [[{{PAGENAME}}|hier]] or [[{{{1}}}]] or [[{{{param1}}}-{{{param2}}}]] that goes some where.';
        $expected = '<p>Sometimes we also have a link like <a href="{{PAGENAME}}">hier</a> or <a _fcknotitle="true" href="{{{1}}}">{{{1}}}</a> or <a _fcknotitle="true" href="{{{param1}}}-{{{param2}}}">{{{param1}}}-{{{param2}}}</a> that goes some where.
</p>';
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);

    }

    function testPage9() {
        $text= 'include this into an article and save:

{| class="wikitable"
|-
! field
| [[Vendor::{{{field|}}}]]
|}
';
        $expected = '<p>include this into an article and save:
</p>
<table class="wikitable">

<tr>
<th> field
</th><td> <span class="fck_mw_property" property="Vendor">{{{field|}}}</span>
</td></tr></table>
';
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);
    }
    function testPage10() {
        $text= 'include this into an article and save:

{| class="wikitable"
|-
! field
| [[Vendor::foo|{{{field|}}}]]
|}
';
        $expected = '<p>include this into an article and save:
</p>
<table class="wikitable">

<tr>
<th> field
</th><td> <span class="fck_mw_property" property="Vendor::foo">{{{field|}}}</span>
</td></tr></table>
';
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);
    }

    private function parsePage($text) {
        return $this->parser->parse($text, $this->title, $this->options)->getText();
    }
}
