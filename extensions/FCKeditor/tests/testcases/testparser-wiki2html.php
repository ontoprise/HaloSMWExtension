<?php
class TestParserWiki2Html extends PHPUnit_Framework_TestCase {

    function setUp() {
        // calling the FCKparser a second time makes trouble with skins, therefore
        // set to monobook, and some default mechanism work.
        global $wgDefaultSkin, $wgValidSkinNames;
        $wgDefaultSkin = "monobook";
        $wgValidSkinNames = array(
            "ontoskin3" => "OntoSkin3",
            "simple" => "Simple",
            "monobook" => "MonoBook",
            "ontoskin2" => "OntoSkin2",
        );
        
        $this->title = Title::newFromText("__dummy__");
        $this->options = new FCKeditorParserOptions();
        $this->options->setTidy(true);
        $this->parser = new FCKeditorParser();
        $this->parser->setOutputType(OT_HTML);
    }

    function tearDown() {
    }

    function testPage1() {
        
        $text = 'This is the capital of [[Also known as::Germany|Deutschland]]. We are living in the year {{CURRENTYEAR}} AC.

{{#language:ar}}

[[Category:City]]';

        $expected = '<p>This is the capital of <span class="fck_mw_property" property="Also known as::Germany">Deutschland</span>. We are living in the year <span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="CURRENTYEAR" _fck_mw_tagtype="v"/> AC.
</p><p><span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="#language" _fck_mw_tagtype="p">ar</span>
</p><p><span class="fck_mw_category">City</span> </p>';
        
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);
    }

    function testPage2() {
        $text = 'This is empty [[Prop::{{{1}}}| ]]. Param2 comes here: {{{2}}} which is good. Also there is no template.';
        $expected = '<p>This is empty <span class="fck_mw_property" property="Prop::{{{1}}}">&nbsp;</span>. Param2 comes here: <span class="fck_mw_template">{{{2}}}</span> which is good. Also there is no template.
</p>';
        $res = $this->parsePage($text);
//        var_dump($res, $expected);
        $this->assertEquals($expected, $res);
    }

    function testPage3() {
        $text = 'This link [[Document:Richmedia.doc|Richmedia.doc]] is created when you upload an document with the [http://smwformum.ontoprise.com Richmedia extension].';
        $expected = '<p>This link <a title="Richmedia.doc" _fck_mw_type="Document" _fck_mw_filename="Richmedia.doc" _fcksavedurl="Document:Richmedia.doc" href="Richmedia.doc">Richmedia.doc</a> is created when you upload an document with the <a href="http://smwformum.ontoprise.com">Richmedia extension</a>.
</p>';
        $res = $this->parsePage($text);
        $this->assertEquals($expected, $res);
    }

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
<th style="background: rgb(255, 150, 2) none repeat scroll 0% 0%; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous; color: white;" colspan="2"> <span style="font-size: 80%; float: right;"><span class="fck_mw_askquery">{{#ask:[[{{PAGENAME}}]]fckLR|?Description=descriptionfckLR|?start date=startfckLR|?end date=endfckLR|?location=locationfckLR|searchlabel=iCalfckLR|format=icalendar}}</span></span> <span class="fck_mw_template">{{#if:{{{Title}}}|[[Title::{{{Title}}}]]|{{PAGENAME}}}}</span>
</th></tr>
<span class="fck_mw_template">{{#ifeq:{{{Description|}}}|||{{Tablerow|Label=Description:|Value=[[Description::{{{Description|}}}]]}}}}</span>

<span class="fck_mw_template">{{#ifeq:{{{Homepage|}}}|||{{Tablerow|Label=Homepage: |Value=[[homepage::{{{Homepage}}}|{{{Homepage label|{{{Homepage}}}}}}]]}}}}</span>

<span class="fck_mw_template">{{#ifeq:{{{Attendee|}}}|||{{Tablelongrow|Value={{#arraymap:{{{Attendee|}}}|,|x|[[Attendee::x]]}}}}}}</span>

<span class="fck_mw_template">{{#ifeq:{{{Related to|}}}|||{{Tablesection|Label=Related items|Color=#ffcd87}}}}</span>
</table>
<p><span class="fck_mw_category">Event</span> </p>';
        
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
&lt;uri name="<a href="http://api.opencalais.com/enlighten/?wsdl">http://api.opencalais.com/enlighten/?wsdl</a>" /&gt;
&lt;protocol&gt;SOAP&lt;/protocol&gt;
&lt;method name="Enlighten" /&gt;
&lt;parameter name="content"  optional="false"  path="/parameters/content" /&gt;
&lt;result name="result" &gt;
&lt;part name="company"  path="//EnlightenResult" xpath="//rdf:Description[./rdf:type/@rdf:resource=\'<a href="http://s.opencalais.com/1/type/em/e/Company\'">http://s.opencalais.com/1/type/em/e/Company\'</a>]/c:name"/&gt;
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
    
    private function parsePage($text) {
        return $this->parser->parse($text, $this->title, $this->options)->getText();        
    }
}
?>