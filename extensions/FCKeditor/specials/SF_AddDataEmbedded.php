<?php
/**
 * Displays a pre-defined form for adding data.
 *
 * @author Yaron Koren
 */
if (!defined('MEDIAWIKI')) die();

global $wgSpcecialPages;
$wgSpecialPages['AddDataEmbedded'] = array('SFAddDataEmbedded');


class SFAddDataEmbedded extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	function __construct() {
		SpecialPage::SpecialPage('AddDataEmbedded');
		wfLoadExtensionMessages('SemanticForms');
	}

	function execute($query) {
		global $wgRequest;

		$this->setHeaders();
		$form_name = $wgRequest->getVal('form');
		$target_name = $wgRequest->getVal('target');

		// if query string did not contain these variables, try the URL
		if (! $form_name && ! $target_name) {
			$queryparts = explode('/', $query, 2);
			$form_name = isset($queryparts[0]) ? $queryparts[0] : '';
			$target_name = isset($queryparts[1]) ? $queryparts[1] : '';
		}

		$alt_forms = $wgRequest->getArray('alt_form');

		self::printAddForm($form_name, $target_name, $alt_forms);
	}

	static function printAltFormsList($alt_forms, $target_name) {
		$text = "";
		$ad = SpecialPage::getPage('AddData');
		$i = 0;
		foreach ($alt_forms as $alt_form) {
			if ($i++ > 0) { $text .= ", "; }
			$text .= '<a href="' . $ad->getTitle()->getFullURL() . "/" . $alt_form . "/" . $target_name . '">' . str_replace('_', ' ', $alt_form) . "</a>";
		}
		return $text;
	}



static function printAddForm($form_name, $target_name, $alt_forms) {
	global $wgOut, $wgRequest, $wgScriptPath, $sfgScriptPath, $sfgFormPrinter, $sfgYUIBase, $wgFCKEditorDir;

        // within the popup of the FCK, don't load another FCK for text fields
        $original_wgFCKEditorDir = $wgFCKEditorDir;
        $wgFCKEditorDir = null;

	wfLoadExtensionMessages('SemanticForms');

	// initialize some variables
	$page_title = NULL;
	$target_title = NULL;
	$page_name_formula = NULL;

	// get contents of form and target page - if there's only one,
	// it might be a target with only alternate forms
	if ($form_name == '') {
		$wgOut->addHTML( "<p class='error'>" . wfMsg('sf_adddata_badurl') . '</p>');
		return;
	} elseif ($target_name == '') {
		// parse the form to see if it has a 'page name' value set
		$form_title = Title::makeTitleSafe(SF_NS_FORM, $form_name);
		$form_article = new Article($form_title);
		$form_definition = $form_article->getContent();
		$form_definition = StringUtils::delimiterReplace('<noinclude>', '</noinclude>', '', $form_definition);
		$matches;
		if (preg_match('/{{{info.*page name=([^\|}]*)/m', $form_definition, $matches)) {
			$page_name_formula = str_replace('_', ' ', $matches[1]);
		} elseif (count($alt_forms) == 0) {
			$wgOut->addWikiText( "<p class='error'>" . wfMsg('sf_adddata_badurl') . '</p>');
			return;
		}
	}

	$form_title = Title::makeTitleSafe(SF_NS_FORM, $form_name);

	if ($target_name != '') {
		$target_title = Title::newFromText($target_name);
		$s = wfMsg('sf_adddata_title', $form_title->getText(), $target_title->getPrefixedText());
		$wgOut->setPageTitle($s);
	}

	// target_title should be null - we shouldn't be adding a page that
	// already exists
	if ($target_title && $target_title->exists()) {
		$wgOut->addWikiText( "<p class='error'>" . wfMsg('articleexists') . '</p>');
		return;
	} elseif ($target_name != '') {
		$page_title = str_replace('_', ' ', $target_name);
	}

	if (! $form_title || ! $form_title->exists()) {
		if ($form_name == '')
			$text = '<p class="error">' . wfMsg('sf_adddata_badurl') . "</p>\n";
		else {
			if (count($alt_forms) > 0) {
				$text .= '<div class="infoMessage">' . wfMsg('sf_adddata_altformsonly') . ' ';
				$text .= self::printAltFormsList($alt_forms, $form_name);
				$text .= "</div>\n";
			} else
				$text = '<p class="error">' . wfMsg('sf_addpage_badform', SFUtils::linkText(SF_NS_FORM, $form_name)) . ".</p>\n";
		}
	} elseif ($target_name == '' && $page_name_formula == '') {
		$text = '<p class="error">' . wfMsg('sf_adddata_badurl') . "</p>\n";
	} else {
		$form_article = new Article($form_title);
		$form_definition = $form_article->getContent();

		$form_submitted = false;
		// get 'preload' query value, if it exists
                if ($wgRequest->getCheck('preload')) {
                    $page_is_source = true;
                    $page_contents = SFFormUtils::getPreloadedText($wgRequest->getVal('preload'));
		} else {
                    // let other extensions preload the page, if they want
                    wfRunHooks('sfEditFormPreloadText', array(&$page_contents, $target_title, $form_title));
                    $page_is_source = ($page_contents != null);
		}
		/*op-patch|BL|2009-09-16|CollapsingForms|SaveFormnameGlobally|start*/
		global $smwgRMActFormName;
		$smwgRMActFormName = $form_name;
		/*op-patch|BL|2009-09-16|end*/
		list ($form_text, $javascript_text, $data_text, $form_page_title, $generated_page_name) =
			$sfgFormPrinter->formHTML($form_definition, $form_submitted, $page_is_source, $page_contents, $page_title, $page_name_formula);
		// override the default title for this page if
		// a title was specified in the form
		if ($form_page_title != NULL) {
			if ($target_name == '') {
				$wgOut->setPageTitle($form_page_title);
			} else {
				$wgOut->setPageTitle("$form_page_title: {$target_title->getPrefixedText()}");
			}
		}
		$text = "";
		if (count($alt_forms) > 0) {
			$text .= '<div class="infoMessage">' . wfMsg('sf_adddata_altforms') . ' ';
			$text .= self::printAltFormsList($alt_forms, $target_name);
			$text .= "</div>\n";
		}
                // remove preview and diff buttons and checkboxes for minor edit
                // and watch this page
                SFEditDataEmbedded::removeButtonsAndLinksFromPage($form_text);

		$text .=<<<END
                <form name="createbox" onsubmit="return validate_all_and_save();" action="" method="post" class="createbox">

END;
                $text .= $form_text;
                // add extra javascript that reads form data and fills the
                // template popup in the FCK
                $javascript_text.= SFEditDataEmbedded::getFckTemplatePopupJavascript();
                // move /*]]>*/ to the end
                $javascript_text = str_replace('/*]]>*/', '', $javascript_text)."\n/*]]>*/";

        }
	SFUtils::addJavascriptAndCSS();
	if (! empty($javascript_text))
		$wgOut->addScript('		<script type="text/javascript">' . "\n" . $javascript_text . '</script>' . "\n");
	$wgOut->addHTML($text);
}

}

?>