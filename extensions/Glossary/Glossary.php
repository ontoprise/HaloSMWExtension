<?php
/*
 * Copyright (C) 2007  BarkerJr
 * Modified by Thomas Schweitzer for using SMW's tooltips.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/*
 *
 * Use definition-lists in the Glossary page.  Place one definition on each line.  For example:
 * ;CS-S:CounterStrike Source, a game by Valve
 * ;HTML:HyperText Markup Language
 */
$wgExtensionCredits['parserhook'][] = array(
  'name' => 'Glossary',
  'description' => 'Provides tooltips from the [[glossary]] defined for all instances of the given term',
  'version' => '2008.05.27',
  'author' => 'BarkerJr, Sorin Sbarnea'
);

$wgExtensionFunctions[] = 'glossarySetup';
function glossarySetup() {
  global $wgOut, $wgScriptPath;
  $wgOut->addHTML("<script type='text/javascript' src='$wgScriptPath/extensions/SemanticMediaWiki/skins/SMW_tooltip.js'></script>");
}

$wgHooks['ParserBeforeTidy'][] = 'glossaryParser';


function glossaryParser(&$parser, &$text) {
  $rev = Revision::newFromTitle(Title::makeTitle(null, 'Glossary'));
  if ($rev) {
    $content = $rev->getText();
    if ($content != "") {
      $changed = false;
      $doc = new DOMDocument();
@     $doc->loadHTML('<meta http-equiv="content-type" content="charset=utf-8"/>' . $text);

      // makes glossary definitions to be on only one row, not two
      $content = str_replace("\n:",":",$content);

      foreach (explode("\n", $content) as $entry) {
        $terms = explode(':', $entry, 2);
        if (count($terms) == 2) {
          if (substr($terms[0], 0, 1) == ';') {
            $term = trim(substr($terms[0], 1));
            $definition = trim($terms[1]);
            if (glossaryParseThisNode($doc, $doc->documentElement, $term, $definition)) {
/*
              $span = $doc->createElement('span');
              $span->setAttribute('id', $term);
              $span->setAttribute('style', 'display:none');
              $span->appendChild($doc->createElement('b', $term));
              $span->appendChild($doc->createTextNode(": $definition"));
              $doc->documentElement->appendChild($span);
*/
              $changed = true;
            }
          }
        }
      }
      if ($changed) {
        $text = $doc->saveHTML();
      }
    }
  }
  return true;
}

function glossaryParseThisNode($doc, $node, $term, $definition) {
  $tooltipsIncluded = false;
  $changed = false;
  if ($node->nodeType == XML_TEXT_NODE) {
    $texts = preg_split('/\b('.preg_quote($term).'s?)\b/iu', $node->textContent, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (count($texts) > 1) {
      if (!$tooltipsIncluded) {
      	SMWOutputs::requireHeadItem(SMW_HEADER_TOOLTIP);
      }
      $container = $doc->createElement('span');
      for ($x = 0; $x < count($texts); $x++) {
        if ($x % 2) {
          $span = $doc->createElement('span');
          $span->setAttribute('class', 'smwglossaryhighlight');
          
          $ttai = $doc->createElement('span', $texts[$x]);
          $ttai->setAttribute('class', 'smwttinline');
          $span->appendChild($ttai);
                   
          $ttcont = $doc->createElement('span', $definition);
          $ttcont->setAttribute('class', 'smwttcontent');
          $ttcont->setAttribute('style', 'display: none;');
          $ttai->appendChild($ttcont);
          
          $container->appendChild($span);
        } else {
          $container->appendChild($doc->createTextNode($texts[$x]));
        }
      }
      $node->parentNode->replaceChild($container, $node);
      $changed = true;
    }
  } elseif ($node->hasChildNodes()) {
    // We have to do this because foreach gets confused by changing data
    $nodes = $node->childNodes;
    $previousLength = $nodes->length;
    for ($x = 0; $x < $nodes->length; $x++) {
      if ($nodes->length <> $previousLength) {
        $x += $nodes->length - $previousLength;
      }
      $previousLength = $nodes->length;
      $child = $nodes->item($x);
      if (glossaryParseThisNode($doc, $child, $term, $definition)) {
        $changed = true;
      }
    }
  }
  return $changed;
}
?>