<?php
/**
 * This file contains a static class for accessing functions to generate and execute
 * notify me semantic queries and to serialise their results.
 *
 * @author dch
 */

global $smwgIP, $smwgConnectorIP ;
require_once($smwgIP . '/includes/storage/SMW_Store.php');
require_once( $smwgConnectorIP . '/includes/SC_Storage.php' );

class SCArticleUtils {
	static function parseTemplate( $text, &$offset ) {
		++ $offset;
		$start = $offset;
		$curly_brackets = 1;
		$square_brackets = 0;
		$len = strlen($text);
		$group = array();
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos($text, '<nowiki>', $offset);
			if($idx_nowiki !== FALSE) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos($text, '<noinclude>', $offset);
			if($idx_noinclude !== FALSE && $idx_noinclude < $min) {
				$min = $idx_noinclude;
				$type = 2;
			}
			for(; $offset < $min && $curly_brackets > 0; ++$offset) {
				$c = $text[$offset];
				if($c == '[') ++$square_brackets;
				else if($c == ']') --$square_brackets;
				else if($square_brackets == 0) {
					if($c == '{') ++$curly_brackets;
					else if($c == '}') --$curly_brackets;
					else if($curly_brackets == 2 && $c == '|') {
						$group[] = trim(substr($text, $start + 1, $offset - $start - 1));
						$start = $offset;
					}
				}
			}
			if($curly_brackets == 0) break;
			if($type !== FALSE) {
				$offset = $min;
				if($type == 1) {
					$offset = stripos($text, '</nowiki>', $offset);
				}else if($type == 2) {
					$offset = stripos($text, '</noinclude>', $offset);
				}
			}
		} while ($offset < $len);
		$group[] = trim(substr($text, $start + 1, $offset - $start - 3));
		
		$result = array('name' => Title::newFromText($group[0])->getText(), 'fields' => array());
		foreach($group as $k => $tdata) {
			if($k > 0) {
				$f = explode('=', $tdata, 2);
				$result['fields'][trim($f[0])] = trim($f[1]);
			}
		}
		return $result;
	}
	static function parseToTemplates( $text ) {
		$result = array();
		$start = 0;
		$offset = 0;
		$len = strlen($text);
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos($text, '<nowiki>', $offset);
			if($idx_nowiki !== FALSE) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos($text, '<noinclude>', $offset);
			if($idx_noinclude !== FALSE && $idx_noinclude < $min) {
				$min = $idx_noinclude;
				$type = 2;
			}
			$idx_triplebracket = strpos($text, '{{{', $offset);
			$idx_doublebracket = strpos($text, '{{', $offset);
			if($idx_doublebracket !== FALSE && $idx_doublebracket < $min && $idx_doublebracket !== $idx_triplebracket) {
				$min = $idx_doublebracket;
				$type = 3;
				$result[] = substr($text, $start, $min - $start);
				if(substr($text, $min + 2, 1) == '#') {
					$is_parserfunc = true;
					$pfstart = $min;
				}
				$template = SCArticleUtils::parseTemplate($text, $min);
				$start = $min;
				if($is_parserfunc) {
					$result[] = substr($text, $pfstart, $min - $pfstart);
				} else {
					$result[] = $template;
				}
			}
			if($type !== FALSE) {
				$offset = $min;
				if($type == 1) {
					$offset = stripos($text, '</nowiki>', $offset);
				}else if($type == 2) {
					$offset = stripos($text, '</noinclude>', $offset);
				}
			}
		} while ($type !== FALSE);
		$result[] = substr($text, $start, $len - $start);
		
		return $result;
	}
	static function applyMappedTemplates( $title, &$text ) {
		$fname = 'SCProcessor::applyMappedTemplates (SMW)';
		wfProfileIn($fname);

		$pid = $title->getArticleID();
		$sStore = SCStorage::getDatabase();

		$target_forms = $sStore->getEnabledForms($pid);
		$target_forms = array_flip($target_forms);
		if(!$target_forms) $target_forms = array();
		ksort($target_forms);
		$current_form = array_pop($target_forms);
		
		$mapped_tfs = $sStore->getMappingTFs($current_form, $target_forms);
		if(count($mapped_tfs) == 0) {
			wfProfileOut($fname);
			return false;
		}
		$tfs = SCArticleUtils::parseToTemplates($text);
		
		$addon = '';
		foreach($mapped_tfs as $template => $fields) {
			if(!is_array($fields)) continue;
			$addon .= "{{" . $template . "\n";
			foreach($fields as $f => $src) {
				$tf = explode('.', $src);
				foreach($tfs as $t) {
					if(!is_array($t)) continue;
					if($t['name'] == trim($tf[0])) {
						if(isset($t['fields'][trim($tf[1])])) {
							$addon .= "|$f=" . $t['fields'][trim($tf[1])] . "\n";
						}
						// always apply mapping to first matched template
						break;
					}
				}
			}
			$addon .= "}}";
		}
		
		$text .= $addon;
		wfProfileOut($fname);
		return true;
	}

	/**
	 * Used to updates data after changes of templates, but also at each saving of an article.
	 */
	static public function onLinksUpdateConstructed($links_update) {
		if (isset($links_update->mParserOutput)) {
			$output = $links_update->mParserOutput;
		} else { // MediaWiki <= 1.13 compatibility
			$output = SMWParseData::$mPrevOutput;
			if (!isset($output)) {
				smwfGetStore()->clearData($links_update->mTitle, SMWFactbox::isNewArticle());
				return true;
			}
		}

		$title = $links_update->mTitle;
		$article = new Article($title);
		$text = $article->getContent();
		if( SCArticleUtils::applyMappedTemplates($title, $text) ){
			// just re-parse page properties with mapped data
			global $wgParser;
			$options = new ParserOptions;
			$options->setTidy( true );
			$options->enableLimitReport();
			$output = $wgParser->parse( $text, $title, $options, true, true );
		}
		
		SMWParseData::storeData($output, $links_update->mTitle, true);
		return true;
	}
}
