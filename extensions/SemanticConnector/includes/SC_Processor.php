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

class SCProcessor {
	static public function renderAddEditPage($form_name, $page_name) {
		$fname = 'SCProcessor::renderAddEditPage (SMW)';
		wfProfileIn($fname);
		$form_name = Title::newFromText($form_name)->getText();
		$page_name = Title::newFromText($page_name)->getText();

		global $wgScriptPath, $wgOut;
		global $smwgConnectorScriptPath, $smwgScriptPath;
		smwfConnectorGetJSLanguageScripts($pathlng, $userpathlng);
		$wgOut->addLink( array( 'rel' => 'stylesheet', 'type' => 'text/css',
			'href' => $smwgConnectorScriptPath . '/scripts/extjs/resources/css/ext-all.css' ) );
		$wgOut->addLink( array( 'rel' => 'stylesheet', 'type' => 'text/css',
			'href' => $smwgConnectorScriptPath . '/scripts/extjs/ux/css/MultiSelect.css' ) );

		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/adapter/ext/ext-base.js"></script>');
		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/ext-all.js"></script>');
		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/ux/MultiSelect.js"></script>');
		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/ItemSelector.js"></script>');
		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/edit_form.js"></script>');

		$ed = SpecialPage::getPage('EditData');
		if($ed == null) $ed = SpecialPage::getPage('FormEdit'); // SF2 removed special page EditData
		$wgOut->addScript('<script type="text/javascript">
		SemanticConnector.form = { 
			page_name: "' . $page_name . '", 
			form_name: "' . $form_name . '", 
			ajaxUrl: "' . $wgScriptPath . '/index.php",
			switchFormPrefix: "' . $ed->getTitle()->getFullURL() . '/",
			switchFormSuffix: "/' . str_replace('"', '\"', $page_name) . '",
		};
		</script>');

		$validForms = SCProcessor::getValidForms($page_name);
		$enabledForms = SCProcessor::getEnabledForms($page_name);
		$mappedForms = array();
		foreach(array_intersect($enabledForms, $validForms) as $f) {
			if($f == $form_name) continue;
			$mappedForms[] = '["' . str_replace('"', '\"', $f) . '", "' . str_replace('"', '\"', $f) . '"]';
		}
		$relatedForms = array();
		foreach(array_diff($validForms, $enabledForms) as $f) {
			if($f == $form_name) continue;
			$relatedForms[] = '["' . str_replace('"', '\"', $f) . '", "' . str_replace('"', '\"', $f) . '"]';
		}

		$wgOut->addScript('<script type="text/javascript">
			SemanticConnector.edit.imagePath = "' . $smwgConnectorScriptPath . '/skins";
			SemanticConnector.edit.currentForm = "' . str_replace('"', '\"', $form_name) . '";
			SemanticConnector.edit.possible = [' . implode(',', array_diff($relatedForms, $mappedForms)) . '];
			SemanticConnector.edit.applicable = [' . implode(',', $mappedForms) . '];
			</script>');

		$wgOut->addHTML('<div id="valid_forms" style=""></div>');

		wfProfileOut($fname);
	}
	static $validForms = array();
	static public function getValidForms($page_name) {
		$fname = 'SCProcessor::getValidForms (SMW)';
		wfProfileIn($fname);

		if(!isset(SCProcessor::$validForms[$page_name])) {
			$template_forms = array();
			foreach(SCProcessor::getAllFTFs() as $form => $tfs) {
				foreach($tfs as $template => $fs) {
					$template_forms[$template][] = $form;
				}
			}
			$valid_forms = array();
				
			// get all templates in page content
			$title = Title::newFromText($page_name);
			$article = new Article($title);
			$content = $article->getContent();
			$content = StringUtils::delimiterReplace('<noinclude>', '</noinclude>', '', $content);
			$content = strtr($content, array('<includeonly>' => '', '</includeonly>' => ''));
			// just remove field values
			$content = StringUtils::delimiterReplace('<nowiki>', '</nowiki>', '', $content);
				
			preg_match_all("/(\\{?\\{\\{)([^#\\|\\}\n]+)/", $content, $matches, PREG_SET_ORDER);
			foreach($matches as $m) {
				if($m[1] == '{{{') continue;
				$template = Title::newFromText(trim($m[2]))->getText();
				if (isset($template_forms[$template])) {
					foreach($template_forms[$template] as $form) {
						$valid_forms[] = $form;
					}
				}
			}
			$valid_forms = SCProcessor::getPossibleForms(array_unique($valid_forms));
			SCProcessor::$validForms[$page_name] = $valid_forms;
		}
		wfProfileOut($fname);
		return SCProcessor::$validForms[$page_name];
	}
	// look into form
	static public function getPossibleForms($form_names, $depth = 0) {
		$fname = 'SCProcessor::getPossibleForms (SMW)';
		wfProfileIn($fname);

		$sStore = SCStorage::getDatabase();
		$valid_forms = $form_names;

		$valid_forms = array_unique($valid_forms);
		if(count($valid_forms) > 0) {
			$diff = $valid_forms;
			do {
				$new_forms = array_unique($sStore->getMappingForms($diff));
				$diff = array_diff($new_forms, $valid_forms);
				$valid_forms = array_unique($valid_forms + $diff);
			} while(count($diff) > 0 && (--$depth) >= 0);
		}
		wfProfileOut($fname);
		return $valid_forms;
	}
	static public function getActivedForm($page_name){
		$fname = 'SCProcessor::getActivedForm (SMW)';
		wfProfileIn($fname);

		$fs = SCProcessor::getEnabledForms($page_name);
		
		$form_name = array_pop($fs);
		
		wfProfileOut($fname);
		return $form_name === NULL ? NULL : array($form_name);
	}
	static $enabledForms = array();
	static public function getEnabledForms($page_name) {
		$fname = 'SCProcessor::getEnabledForms (SMW)';
		wfProfileIn($fname);
		if(!isset(SCProcessor::$enabledForms[$page_name])) {
			$form_names = array();
			$pid = Title::newFromText($page_name)->getArticleID();
			$sStore = SCStorage::getDatabase();
			$form_names = $sStore->getEnabledForms($pid);
			$form_names = array_flip($form_names);
			if(!$form_names) $form_names = array();
			ksort($form_names);
		
			$form_names = array_intersect($form_names, SCProcessor::getValidForms($page_name));
			SCProcessor::$enabledForms[$page_name] = $form_names;
		}
		wfProfileOut($fname);
		return SCProcessor::$enabledForms[$page_name];
	}
	static function getDefaultValues($form_name, $page_title) {
		$fname = 'SCProcessor::getDefaultValues (SMW)';
		wfProfileIn($fname);
		
		$result = array();
		$form_title = Title::makeTitleSafe(SF_NS_FORM, $form_name);
		$form_article = new Article($form_title);
		$form_def = $form_article->getContent();

		$form_def = StringUtils::delimiterReplace('<noinclude>', '</noinclude>', '', $form_def);
		$form_def = strtr($form_def, array('<includeonly>' => '', '</includeonly>' => ''));

		global $sfgDisableWikiTextParsing;
		if (! $sfgDisableWikiTextParsing) {
			$form_def = "__NOEDITSECTION__" . strtr($form_def, array('{{{' => '<nowiki>{{{', '}}}' => '}}}</nowiki>'));
		}

		// turn form definition file into an array of sections, one for each
		// template definition (plus the first section)
		$form_def_sections = array();
		$start_position = 0;
		$section_start = 0;

		$form_def = str_replace(array('&#123;', '&#124;', '&#125;'), array('{', '|', '}'), $form_def);
		$form_def = str_replace('standard input|free text', 'field|<freetext>', $form_def);
		while ($brackets_loc = strpos($form_def, "{{{", $start_position)) {
			$brackets_end_loc = strpos($form_def, "}}}", $brackets_loc);
			$bracketed_string = substr($form_def, $brackets_loc + 3, $brackets_end_loc - ($brackets_loc + 3));
			$tag_components = explode('|', $bracketed_string);
			$tag_title = trim($tag_components[0]);
			if ($tag_title == 'for template' || $tag_title == 'end template') {
				// create a section for everything up to here
				$section = substr($form_def, $section_start, $brackets_loc - $section_start);
				$form_def_sections[] = $section;
				$section_start = $brackets_loc;
	        }
			$start_position = $brackets_loc + 1;
		} // end while
		$form_def_sections[] = trim(substr($form_def, $section_start));

		$template_name = "";
		for ($section_num = 0; $section_num < count($form_def_sections); $section_num++) {
			$start_position = 0;
			$template_text = "";
			// the append is there to ensure that the original array doesn't get
			// modified; is it necessary?
			$section = " " . $form_def_sections[$section_num];

			while ($brackets_loc = strpos($section, '{{{', $start_position)) {
				$brackets_end_loc = strpos($section, "}}}", $brackets_loc);
				$bracketed_string = substr($section, $brackets_loc + 3, $brackets_end_loc - ($brackets_loc + 3));
				$tag_components = explode('|', $bracketed_string);
				$tag_title = trim($tag_components[0]);
				// =====================================================
				// for template processing
				// =====================================================
				if ($tag_title == 'for template') {
					$template_name = trim($tag_components[1]);
				// =====================================================
		        // field processing
		        // =====================================================  
		        } elseif ($tag_title == 'field') {
		        	$field_name = trim($tag_components[1]);
		        	// cycle through the other components
		        	$input_type = null;
		        	$default_value = "";
		        	for ($i = 2; $i < count($tag_components); $i++) {
		        		$component = trim($tag_components[$i]);
		        		$sub_components = explode('=', $component);
		        		if (count($sub_components) != 2) continue;
		        		if ($sub_components[0] == 'input type') {
		        			$input_type = $sub_components[1];
		        		} elseif ($sub_components[0] == 'default') {
		        			$default_value = $sub_components[1];
		        		}
		        	}
		        	if ($default_value == 'always now' || $default_value == 'now') {
		        		// get current time, for the time zone specified in the wiki
		        		global $wgLocaltimezone;
		        		putenv('TZ=' . $wgLocaltimezone);
		        		$cur_time = time();
		        		$year = date("Y", $cur_time);
		        		$month = date("n", $cur_time);
		        		$day = date("j", $cur_time);
		        		global $wgAmericanDates, $sfg24HourTime;
		        		if ($wgAmericanDates == true) {
		        			$month_names = SFFormUtils::getMonthNames();
		        			$month_name = $month_names[$month - 1];
		        			$default_value = "$month_name $day, $year";
		        		} else {
		        			$default_value = "$year/$month/$day";
		        		}
		        		if ($input_type ==  'datetime' || $input_type == 'datetime with timezone') {
		        			if ($sfg24HourTime) {
		        				$hour = str_pad(intval(substr(date("G", $cur_time),0,2)),2,'0',STR_PAD_LEFT);
		        			} else {
		        				$hour = str_pad(intval(substr(date("g", $cur_time),0,2)),2,'0',STR_PAD_LEFT);
		        			}
		        			$minute = str_pad(intval(substr(date("i", $cur_time),0,2)),2,'0',STR_PAD_LEFT);
		        			$second = str_pad(intval(substr(date("s", $cur_time),0,2)),2,'0',STR_PAD_LEFT);
		        			if ($sfg24HourTime) {
		        				$default_value .= " $hour:$minute:$second";
		        			} else {
		        				$ampm = date("A", $cur_time);
		        				$default_value .= " $hour:$minute:$second $ampm";
		        			}
		        		}
		        		if ($input_type == 'datetime with timezone') {
		        			$timezone = date("T", $cur_time);
		        			$default_value .= " $timezone";
		        		}
		        	} elseif ($default_value == 'always current user' || $default_value == 'current user') {
		        		if ($input_type == 'text' || $input_type == '') {
		        			$default_value = $wgUser->getName();
		        		}
		        	} elseif ($default_value == 'revision id') {
		        		if ($input_type == 'text' || $input_type == '') {
		        			$revision = Revision::newFromTitle( $page_title );
		        			if($revision !== NULL) $default_value = $revision->getId();
		        		}
		        	}
		        	if($default_value != '') $result[$template_name][$field_name] = $default_value;
				}
				$start_position = $brackets_end_loc;
			} // end while
		}

		wfProfileOut($fname);
		return $result;
	}
	static public function getTemplateField($form_name) {
		$fname = 'SCProcessor::getTemplateField (SMW)';
		wfProfileIn($fname);

		$result = array();
		$form_title = Title::makeTitleSafe(SF_NS_FORM, $form_name);
		$form_article = new Article($form_title);
		$form_def = $form_article->getContent();

		$form_def = StringUtils::delimiterReplace('<noinclude>', '</noinclude>', '', $form_def);
		$form_def = strtr($form_def, array('<includeonly>' => '', '</includeonly>' => ''));

		global $sfgDisableWikiTextParsing;
		if (! $sfgDisableWikiTextParsing) {
			$form_def = "__NOEDITSECTION__" . strtr($form_def, array('{{{' => '<nowiki>{{{', '}}}' => '}}}</nowiki>'));
		}

		// turn form definition file into an array of sections, one for each
		// template definition (plus the first section)
		$form_def_sections = array();
		$start_position = 0;
		$section_start = 0;

		$form_def = str_replace(array('&#123;', '&#124;', '&#125;'), array('{', '|', '}'), $form_def);
		$form_def = str_replace('standard input|free text', 'field|<freetext>', $form_def);
		while ($brackets_loc = strpos($form_def, "{{{", $start_position)) {
			$brackets_end_loc = strpos($form_def, "}}}", $brackets_loc);
			$bracketed_string = substr($form_def, $brackets_loc + 3, $brackets_end_loc - ($brackets_loc + 3));
			$tag_components = explode('|', $bracketed_string);
			$tag_title = trim($tag_components[0]);
			if ($tag_title == 'for template' || $tag_title == 'end template') {
				// create a section for everything up to here
				$section = substr($form_def, $section_start, $brackets_loc - $section_start);
				$form_def_sections[] = $section;
				$section_start = $brackets_loc;
	        }
			$start_position = $brackets_loc + 1;
		} // end while
		$form_def_sections[] = trim(substr($form_def, $section_start));

		$template_name = "";
		for ($section_num = 0; $section_num < count($form_def_sections); $section_num++) {
			$start_position = 0;
			$template_text = "";
			// the append is there to ensure that the original array doesn't get
			// modified; is it necessary?
			$section = " " . $form_def_sections[$section_num];

			while ($brackets_loc = strpos($section, '{{{', $start_position)) {
				$brackets_end_loc = strpos($section, "}}}", $brackets_loc);
				$bracketed_string = substr($section, $brackets_loc + 3, $brackets_end_loc - ($brackets_loc + 3));
				$tag_components = explode('|', $bracketed_string);
				$tag_title = trim($tag_components[0]);
				// =====================================================
				// for template processing
				// =====================================================
				if ($tag_title == 'for template') {
					$template_name = trim($tag_components[1]);
					//					$tif = new SFTemplateInForm();
					//					$tif->template_name = $template_name;
					$res = array();
					//					foreach($tif->getAllFields() as $field) {
					foreach(SCProcessor::getAllFields($template_name) as $field) {
						$res[] = $field->field_name;
					}
					$result[$template_name] = $res;
				}
				$start_position = $brackets_end_loc;
			} // end while
		}

		wfProfileOut($fname);
		return $result;
	}

	// copied from sf_formclasses.inc
	static function getAllFields($template_name) {
		$template_fields = array();
		$field_names_array = array();

		// Get the fields of the template, both semantic and otherwise, by parsing
		// the text of the template.
		// The way this works is that fields are found and then stored in an
		// array based on their location in the template text, so that they
		// can be returned in the order in which they appear in the template, even
		// though they were found in a different order.
		// Some fields can be found more than once (especially if they're part
		// of an "#if" statement), so they're only recorded the first time they're
		// found. Also, every field gets replaced with a string of x's after
		// being found, so it doesn't interfere with future parsing.
		$template_title = Title::makeTitleSafe(NS_TEMPLATE, $template_name);
		$template_article = null;
		if(isset($template_title)) $template_article = new Article($template_title);
		if(isset($template_article)) {
			$template_text = $template_article->getContent();
			// ignore 'noinclude' sections and 'includeonly' tags
			$template_text = StringUtils::delimiterReplace('<noinclude>', '</noinclude>', '', $template_text);
			$template_text = strtr($template_text, array('<includeonly>' => '', '</includeonly>' => ''));

			// first, look for "arraymap" parser function calls that map a
			// property onto a list
			if (preg_match_all('/{{#arraymap:{{{([^|}]*:?[^|}]*)[^\[]*\[\[([^:=]*:?[^:=]*)(:[:=])/mis', $template_text, $matches)) {
				// this is a two-dimensional array; we need the last three of the four
				// sub-arrays; we also have to remove redundant values
				foreach ($matches[1] as $i => $field_name) {
					$semantic_property = $matches[2][$i];
					$full_field_text = $matches[0][$i];
					if (! in_array($field_name, $field_names_array)) {
						$template_field = SFTemplateField::create($field_name, ucfirst($field_name));
						$template_field->setSemanticProperty($semantic_property);
						$template_field->is_list = true;
						$cur_pos = stripos($template_text, $full_field_text);
						$template_fields[$cur_pos] = $template_field;
						$field_names_array[] = $field_name;
						$replacement = str_repeat("x", strlen($full_field_text));
						$template_text = str_replace($full_field_text, $replacement, $template_text);
					}
				}
			}

			// second, look for normal property calls
			if (preg_match_all('/\[\[([^:=]*:*?[^:=]*)(:[:=]){{{([^\]\|}]*).*?\]\]/mis', $template_text, $matches)) {
				// this is a two-dimensional array; we need the last three of the four
				// sub-arrays; we also have to remove redundant values
				foreach ($matches[1] as $i => $semantic_property) {
					$field_name = $matches[3][$i];
					$full_field_text = $matches[0][$i];
					if (! in_array($field_name, $field_names_array)) {
						$template_field = SFTemplateField::create($field_name, ucfirst($field_name));
						$template_field->setSemanticProperty($semantic_property);
						$cur_pos = stripos($template_text, $full_field_text);
						$template_fields[$cur_pos] = $template_field;
						$field_names_array[] = $field_name;
						$replacement = str_repeat("x", strlen($full_field_text));
						$template_text = str_replace($full_field_text, $replacement, $template_text);
					}
				}
			}

			// finally, get any non-semantic fields defined
			if (preg_match_all('/{{{([^|}]*)/mis', $template_text, $matches)) {
				// bug fixed by ning
				$cur_pos = 0;
				foreach ($matches[1] as $i => $field_name) {
					$full_field_text = $matches[0][$i];
					if (($full_field_text != '') && (! in_array($field_name, $field_names_array))) {
						$cur_pos = stripos($template_text, $full_field_text, $cur_pos);
						$template_fields[$cur_pos] = SFTemplateField::create($field_name, ucfirst($field_name));
						$field_names_array[] = $field_name;
						$cur_pos += strlen($full_field_text);
					}
				}
			}
		}
		ksort($template_fields);
		return $template_fields;
	}

	// should think of site global cache, which will save much time
	static $allFTFs = array();
	static public function getAllFTFs() {
		$fname = 'SCProcessor::getAllFTFs (SMW)';
		wfProfileIn($fname);

		if(count(SCProcessor::$allFTFs) == 0) {
			$sStore = SCStorage::getDatabase();
			foreach($sStore->getAllForms() as $form_name) {
				SCProcessor::$allFTFs[$form_name] = SCProcessor::getTemplateField($form_name);
			}
		}
		wfProfileOut($fname);
		return SCProcessor::$allFTFs;
	}

	static public function getPageMappedFormData($page_name, $form_name) {
		$fname = 'SCProcessor::getPageMappedFormData (SMW)';
		wfProfileIn($fname);

		$ret = array();

		$page_title = Title::newFromText( $page_name );
		$revision = Revision::newFromTitle( $page_title );
		if ( $revision === NULL ) {
			wfProfileOut($fname);
			return $ret;
		}

		$sStore = SCStorage::getDatabase();
		$pid = $page_title->getArticleID();
		$current_form = $sStore->getCurrentForm($pid);
		$form_name = Title::makeTitleSafe(SF_NS_FORM, $form_name)->getText();

		$content = $revision->getText();
		$templates = SCArticleUtils::parseToTemplates($content);
		$freetext = '';
		foreach($templates as $template) {
			if(is_string($template)) $freetext .= $template;
		}
		if($current_form == null || $current_form == $form_name) {
			foreach($templates as $template) {
				if(!is_array($template)) continue;
				$fields = $template['fields'];
				$template = str_replace('_', ' ', $template['name']);
				foreach($fields as $f => $v) {
					$f = str_replace('_', ' ', $f); // May cause error!!! Semantic Forms always replace ' ' to '_'
					$ret[$template . '.' . $f] = $v;
				}
			}
			
			$ret['{free text}'] = $freetext;
			
			wfProfileOut($fname);
			return $ret;
		}

		foreach($templates as $template) {
			if(!is_array($template)) continue;
			$fields = $template['fields'];
			$template = str_replace('_', ' ', $template['name']);
			foreach($fields as $f => $v) {
				$ftf = explode('+', $f, 3);
				if(str_replace('_', ' ', $ftf[0]) === $form_name)
				$ret[str_replace('_', ' ', $ftf[1]) . '.' . $ftf[2]] = $v;
			}
		}

		$mapped = false;
		$searchFroms = array($current_form);
		$forms = array($current_form);
		$path = array($current_form=>$current_form);
		do{
			$mappedForms = array();
			foreach($searchFroms as $form) {
				$fs = $sStore->lookupMappedForm( $form );
				$fs = array_diff($fs, $forms);
				foreach($fs as $f) {
					if(!in_array($f, $forms))
					$path[$f] = $path[$form] . '|' . $f;
					if($f === $form_name) {
						$mapped = true;
						break;
					}
				}
				if($mapped) break;
				$mappedForms += $fs;
				$forms += $fs;
			}
			$searchFroms = array_unique($mappedForms);
		} while(!$mapped && count($searchFroms) > 0);

		if(!$mapped) {
			wfProfileOut($fname);
			return $ret;
		}

		$forms = explode('|', $path[$form_name]);
		$mapping = array();
		for($i = 1; $i < count($forms); ++$i) {
			$ftfs = $sStore->getMappingData( $forms[$i-1], $forms[$i] );
			foreach($ftfs as $ftf) {
				if($i == 1) {
					$mapping[$ftf['map']] = $ftf['src'];
				} else if($mapping[$ftf['src']] !== false){
					$mapping[$ftf['map']] = $mapping[$ftf['src']];
				}
			}
		}

		foreach($mapping as $map=>$src) {
			$ftf = explode('.', $map, 3);
			if($ftf[0] == $form_name) {
				$sftf = explode('.', $src, 3);
				foreach($templates as $t) {
					if(!is_array($t)) continue;
					if($t['name'] == $sftf[1]) {
						if(isset($t['fields'][$sftf[2]])) {
							$ret[$ftf[1] . '.' . $ftf[2]] = $t['fields'][$sftf[2]];
						}
						// always apply mapping to first matched template
						break;
					}
				}
			}
		}

		$ret['{free text}'] = $freetext;

		wfProfileOut($fname);
		return $ret;
	}

	static public function getMappingData($form_name) {
		$fname = 'SCProcessor::getMappingData (SMW)';
		wfProfileIn($fname);
		$sStore = SCStorage::getDatabase();
		$form_name = Title::makeTitleSafe(SF_NS_FORM, $form_name)->getText();
		$data = '
{
	source:{
		form:"' . str_replace('"', '\"', $form_name). '",
		templates: [
		';
		foreach(SCProcessor::getTemplateField($form_name) as $template => $fields) {
			$data .= '{id:"' . str_replace('"', '\"', $template) . '", fields:[';
			foreach($fields as $field) {
				$data .= '{id:"' . str_replace('"', '\"', $field) . '"},';
			}
			$data .= ']}, ';
		}
		$data .= '
		],
		mappedData: [';
		foreach($sStore->getMappingData($form_name) as $m) {
			$data .= '{
				src:"' . str_replace('"', '\"', $m['src']) . '", 
				map:"' . str_replace('"', '\"', $m['map']) . '"
			},';
		}
		$data .= '
		]
	},
	mapping:{
		forms: [';
		foreach(SCProcessor::getAllFTFs() as $form => $tfs) {
			$data .= '{id:"' . str_replace('"', '\"', $form) . '", templates:[';
			foreach($tfs as $template => $fields) {
				$data .= '{id:"' . str_replace('"', '\"', $template) . '", fields:[';
				foreach($fields as $field) {
					$data .= '{id:"' . str_replace('"', '\"', $field) . '"},';
				}
				$data .= ']}, ';
			}
			$data .= ']}, ';
		}
		$data .= '
		]
	}
}';
		wfProfileOut($fname);
		return $data;
	}
	static public function saveMappingData($form_name, $mapping) {
		$fname = 'SCProcessor::saveMappingData (SMW)';
		wfProfileIn($fname);
		$form_name = Title::makeTitleSafe(SF_NS_FORM, $form_name)->getText();

		$sStore = SCStorage::getDatabase();
		$sStore->removeOldMappingData($form_name);
		foreach($mapping as $map) {
			$m = explode('|', $map);
			if(count($m)!=2) continue;
			$sStore->saveMappingDataSameAs($m[0], $m[1]);
		}
		wfProfileOut($fname);

		return '{success:true, msg:"Your schema mapping is saved successfully."}';
	}
	static public function saveEnabledForms($page_name, $current_form, $enabled_forms = NULL) {
		$fname = 'SCProcessor::saveEnabledForms (SMW)';
		wfProfileIn($fname);

		$current_form = Title::newFromText($current_form)->getText();
		$title = Title::newFromText($page_name);
		if(!$title->exists()) {
			return '{success:false, msg:"The page does not exist."}';
		}
		$pid = $title->getArticleID();
		$sStore = SCStorage::getDatabase();
		if(!is_array($enabled_forms)) $enabled_forms = array();
		$sStore->saveEnabledForms($pid, $current_form, $enabled_forms);

		wfProfileOut($fname);
		return '{success:true, msg:"Your schema mapping is saved successfully."}';
	}
	static public function toMappedFormContent($text, $page_title, $form_title) {
		$fname = 'SCProcessor::toMappedFormContent (SMW)';
		wfProfileIn($fname);
		$pid = $page_title->getArticleID();
		$sStore = SCStorage::getDatabase();
//		$form = $sStore->getCurrentForm($pid);

		if( class_exists( "SFFormLinker" ) ) {
			$activeForms = SFFormLinker::getDefaultFormsForPage( $page_title );
		} else {
			$activeForms = SFLinkUtils::getFormsForArticle(new Article($page_title));
		}
		if($activeForms == NULL || count($activeForms)==0) {
			wfProfileOut($fname);
			return $text;
		}
		$form = $activeForms[0];
		$mapped_form_name = $form_title->getText();
		if($mapped_form_name == $form) {
			wfProfileOut($fname);
			return $text;
		}
		
		// apply default values to active form
		$defaultValues = SCProcessor::getDefaultValues($mapped_form_name, $page_title);
		$tmplNames = SCProcessor::getTemplateField($mapped_form_name);
		$templates = SCArticleUtils::parseToTemplates($text);
		$tmplData = array();
		$tmplMap = array(); # template place in old text to template in new
		$tmplFlag = array(); # a template cannot place in multiple
		$unmappedText = "";
		foreach($templates as $template) {
			if(!is_array($template)) continue;
			$fields = $template['fields'];
			$template = $template['name'];
			foreach($fields as $f => $v) {
				if(strpos($f, '+') !== FALSE) {
					$mapped = false;
					// try to get mapping data which has been edit by other forms
					$f = str_replace('_', ' ', $f); // May cause error!!! Semantic Forms always replace ' ' to '_'
					
					$ftf = explode('+', $f, 3);
					if(isset($tmplNames[$ftf[1]])) {
						$tmplData[$ftf[1]][$ftf[2]] = $v;
						if(!isset($tmplFlag[$ftf[1]])) {
							$tmplMap[$template][] = $ftf[1];
							$tmplFlag[$ftf[1]] = true;
						}
						$mapped = true;
					} else {
						foreach($sStore->getMappingItem(implode('.', $ftf), $mapped_form_name) as $m) {
							$ftf1 = explode('.', $m['map']);
							$tmplData[$ftf1[1]][$ftf1[2]] = $v;
							if(!isset($tmplFlag[$ftf1[1]])) {
								$tmplMap[$template][] = $ftf1[1];
								$tmplFlag[$ftf1[1]] = true;
							}
							$mapped = true;
						}
					}
					if(!$mapped) {
						// template not in use, just keep the form and template history info
						$unmappedText .= "| $f = $v \n";
					}
				} else {
					if(isset($tmplNames[$template])) {
						$tmplData[$template][$f] = $v;
						if(!isset($tmplFlag[$template])) {
							$tmplMap[$template][] = $template;
							$tmplFlag[$template] = true;
						}
					} else {
						$mapped = false;
						foreach($sStore->getMappingItem("$form.$template.$f", $mapped_form_name) as $m) {
							$ftf1 = explode('.', $m['map']);
							$tmplData[$ftf1[1]][$ftf1[2]] = $v;
							if(!isset($tmplFlag[$ftf1[1]])) {
								$tmplMap[$template][] = $ftf1[1];
								$tmplFlag[$ftf1[1]] = true;
							}
							$mapped = true;
						}
						
						// for plain fields, add form and template history info
						if(!$mapped) {
							// template not in use, just keep the form and template history info
							$unmappedText .= "| $form+$template+$f = $v \n";
						}
					}
				}
			}
		}
		if(count($tmplMap) == 0) {
			$newText = $text;
		} else {
			foreach($defaultValues as $t => $fv) {
				if(!isset($tmplData[$t])) continue;
				foreach($fv as $f => $v) {
					if(!isset($tmplData[$t][$f])) $tmplData[$t][$f] = $v;
				}
			}
			$addedUnmappedText = false;
			foreach($templates as $template) {
				if(is_string($template)) {
					$newText .= $template;
					continue;
				} else if(!is_array($template)) continue;
				
				$fields = $template['fields'];
				$template = $template['name'];
				
				if(isset($tmplMap[$template])) {
					foreach($tmplMap[$template] as $t) {
						$newText .= "{{"."$t \n";
						foreach($tmplData[$t] as $f => $v) {
							$newText .= "| $f = $v \n";
						}
						if(!$addedUnmappedText) {
							// add unmapped text to first template of new wiki text
							$newText .= $unmappedText;
							$addedUnmappedText = true;
						}
						$newText .= "}}";
					}
				}
			}
		}
		
		wfProfileOut($fname);
		return $newText;
	}
	static public function resetPageForms($page_name) {
		$fname = 'SCProcessor::resetPageForms (SMW)';
		wfProfileIn($fname);
		$pid = Title::newFromText($page_name)->getArticleID();
		if($pid != 0) {
			$sStore = SCStorage::getDatabase();
			$sStore->resetPageForms($pid);
		}
		wfProfileOut($fname);
	}
}
