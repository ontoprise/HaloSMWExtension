<?php
/*
 * Copyright (C) Vulcan Inc.
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


class TestTab extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = false;
	
//	public static function setUpBeforeClass() {
//		$page_title = Title::newFromText( UltrapediaTests::$TEST_PAGE_NAME );
//		$article = new Article($page_title);
//		$content = '{{#tab: 
//|options=resizable:0;movable:0
//
//|tab1.body=ask tab
//{{#ask: [[Category:Science fiction films]]
//[[budget::<10000000]] 
//|?budget
//|?gross
//|mainlabel=movie
//|format=table}}
//
//|tab2.body=ajaxask tab
//{{#ajaxask: [[Category:Science fiction films]]
//[[budget::>10000001]][[budget::<15000000]] 
//|?budget
//|?gross
//|mainlabel=movie|format=table}}
//
//|tab2.body=[[Main Page|internal tab]]
//
//|}}';
//		global $wgTitle;
//		$wgTitle = $page_title;
//		$article->doEdit($content, 'setup before php unit');
//    }
//
//    public static function tearDownAfterClass() {
//		$page_title = Title::newFromText( UltrapediaTests::$TEST_PAGE_NAME );
//		$article = new Article($page_title);
//		global $wgTitle;
//		$wgTitle = $page_title;
//		$article->doDelete('tear down after php unit');
//    }
	
    function testTabWidget() {
    	$page_title = Title::newFromText( UltrapediaTests::$TEST_PAGE_NAME );
		$article = new Article($page_title);
		$text = $article->getContent();
		global $wgTitle;
		$wgTitle = $page_title;

		global $wgParser;
		$options = new ParserOptions;
		$options->setTidy( true );
		$options->enableLimitReport();
		$output = $wgParser->parse( $text, $page_title, $options );
		
		$ps = explode('<!--', $output->getText(), 2);
		$ret = $ps[0];
		$acceptable = '<div id="tabs0"><div id="tabs0_4" class="x-hide-display"><table class="smwtable" id="querytable1">
        <tr>
                <th>UP</th>
                <th><a href="/mediawiki/index.php/Property:UP_number" title="Property:UP number">UP number</a></th>
        </tr>
        <tr>
                <td><a href="/mediawiki/index.php/Ultrapedia_1" title="Ultrapedia 1">Ultrapedia 1</a></td>
                <td><span class="smwsortkey">1</span>1</td>
        </tr>
        <tr>
                <td><a href="/mediawiki/index.php/Ultrapedia_2" title="Ultrapedia 2">Ultrapedia 2</a></td>
                <td><span class="smwsortkey">1</span>1</td>
        </tr>
</table></div><div id="tabs0_3" class="x-hide-display"><div id="AjaxAsk2"><img src="/mediawiki/extensions/Ultrapedia/ajax-loader.gif"></div></div></div>';
		$ret = preg_replace('/\s*\n\s*/', '', $ret);
		$acceptable = preg_replace('/\s*\n\s*/', '', $acceptable);
		
		$this->assertEquals($ret, $acceptable);
		
		$ret = $output->mHeadItems;
		$acceptable = array(
'tab_css' => '<link rel="stylesheet" type="text/css" href="/mediawiki/extensions/Ultrapedia/scripts/extjs/resources/css/ext-all.css" />',
'tab_js1' => '<script type="text/javascript" src="/mediawiki/extensions/Ultrapedia/scripts/extjs/adapter/ext/ext-base.js"></script>',
'tab_js2' => '<script type="text/javascript" src="/mediawiki/extensions/Ultrapedia/scripts/extjs/ext-all.js"></script>',
'tab_js3' => '<script type="text/javascript" src="/mediawiki/extensions/Ultrapedia/scripts/tabwidgets.js"></script>',
'ajaxask-header' => '<script type="text/javascript" src="/mediawiki/extensions/Ultrapedia/scripts/ajaxasks.js"></script>',
'AjaxAsk2' => '<script type="text/javascript">/*<![CDATA[*/
                        AjaxAsk.queries.push({id:"AjaxAsk2",qno:2,query:"[[Category:UP tests]][[UP number::>10]] | ?UP number | mainlabel=UP | format=table"});
                        /*]]>*/</script>',
'uptabwidget0' => '<script type="text/javascript">
                        UltraPedia.tabWidgets.push({
                                id:"tabs0",items:[{title: "ask tab",contentEl:"tabs0_4"},{title: "ajaxask tab",contentEl:"tabs0_3"},{title: "internal tab",autoLoad: {url: "", params: "action=ajax&rs=smwf_up_Access&&rsargs[]=internalLoad&rsargs[]=2,UP tab"}}]
                        });
                        </script>',
		);
		foreach($acceptable as $k => $v) {
			$acc = preg_replace('/\s*\n\s*/', "\n", trim($v));
			$v = preg_replace('/\s*\n\s*/', "\n", trim($ret[$k]));

			$this->assertEquals($v, $acc);
		}
	}
}
?>
