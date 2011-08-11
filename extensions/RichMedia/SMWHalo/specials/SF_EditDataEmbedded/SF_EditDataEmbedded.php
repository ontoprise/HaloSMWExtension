<?php
/**
 * Displays a pre-defined form for editing a page's data.
 * @file
 * @ingroup SMWHaloSpecials
 * 
 * @author Yaron Koren
 */

/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

global $wgSpcecialPages;
$wgSpecialPages['EditDataEmbedded'] = array('SFEditDataEmbedded');

class SFEditDataEmbedded extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	function __construct() {
		UnlistedSpecialPage::UnlistedSpecialPage('EditDataEmbedded');
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
			$form_name = $queryparts[0];
			$target_name = $queryparts[1];
		}

		self::printEditForm($form_name, $target_name);
	}

    /**
     * Print the edit form. Mostly copied from the SemanticForms extension
     * SF_FormEdit.php but simplyfied to the requirements. No page is supposed
     * to be modified. When submiting the form, javascript is added that reads
     * the actual data for the form and adds them for the template call. The
     * page never does a redirect but calls itself again. Buttons for preview
     * and show changes as well as checkboxes are removed as they are not needed.
     *
     * @param string form name
     * @param string target name (i.e. page name)
     * @param string content whih is page content (optional, default null)
     */
    static function printEditForm($form_name, $target_name, $content = null) {
	global $wgOut, $wgRequest, $wgScriptPath, $sfgScriptPath, $sfgFormPrinter, $sfgYUIBase;

        // within the popup of the FCK, don't load another FCK for text fields
        global $wgFCKEditorDir;
        $original_wgFCKEditorDir = $wgFCKEditorDir;
        $wgFCKEditorDir = null;

	wfLoadExtensionMessages('SemanticForms');

	$javascript_text = "";
	// get contents of form definition file
	$form_title = Title::makeTitleSafe(SF_NS_FORM, $form_name);
	// get contents of target page
	$target_title = Title::newFromText($target_name);

        // typical handling to check, if we know that we have a form and a target
	if (! $form_title || ! $form_title->exists() ) {
		if ($form_name == '')
			$text = '<p class="error">' . wfMsg('sf_editdata_badurl') . "</p>\n";
		else
			$text = '<p class="error">Error: No form page was found at ' . SFUtils::linkText(SF_NS_FORM, $form_name) . ".</p>\n";
	} elseif (! $target_title || ! $target_title->exists() ) {
		if ($target_name == '')
			$text = '<p class="error">' . wfMsg('sf_editdata_badurl') . "</p>\n";
		else
			$text = '<p class="error">Error: No page was found at ' . SFUtils::linkText(null, $target_name) . ".</p>\n";
	} else {
		$s = wfMsg('sf_editdata_title', $form_title->getText(), $target_title->getPrefixedText());
		$wgOut->setPageTitle($s);
		$form_article = new Article($form_title);
		$form_definition = $form_article->getContent();
		$page_title = str_replace('_', ' ', $target_name);

		// if user already made some action, ignore the edited page
		// and just get data from the query string
		if ($wgRequest->getVal('query') == 'true') {
			$edit_content = null;
			$is_text_source = false;
		} elseif ($content != null) {
			$edit_content = $content;
			$is_text_source = true;
		} else {
			$target_article = new Article($target_title);
			$edit_content = $target_article->getContent();
			$is_text_source = true;
		}
                // the form is never submited (we need the Javascript at onsubmit only)
                // therefore set the submit to false, this will dispay the form always
                // like we will see it when calling it for the first time
                $form_submitted = false;
		list ($form_text, $javascript_text, $data_text, $form_page_title) =
			$sfgFormPrinter->formHTML($form_definition, $form_submitted, $is_text_source, $edit_content, $page_title);
		// override the default title for this page if
		// a title was specified in the form
		if ($form_page_title != NULL) {
                    $wgOut->setPageTitle("$form_page_title: {$target_title->getPrefixedText()}");
		}
                // remove preview and diff buttons and checkboxes for minor edit
                // and watch this page
                self::removeButtonsAndLinksFromPage($form_text);

		$text =<<<END
                <form name="createbox" onsubmit="return validate_all_and_save();" action="" method="post" class="createbox">
        	<input type="hidden" name="query" value="true" />

END;
                $text .= $form_text;
                // add extra javascript that reads form data and fills the
                // template popup in the FCK
                $javascript_text.= self::getFckTemplatePopupJavascript();
        }
        // add javascript and css to the page header
	SFUtils::addJavascriptAndCSS();
	$wgOut->addScript('		<script type="text/javascript">' . "\n" . $javascript_text . '</script>' . "\n");
	$wgOut->addHTML($text);
        // set the $wgFCKEditorDir again to the original (maybe not needed)
        $wgFCKEditorDir = $original_wgFCKEditorDir;
    }

    /**
     * remove unneccessary html buttons and links from the page html which
     * are not needed in the overlay. It should not be possible to navigate
     * the wiki insidethe iframe of the FCK popup window.
     *
     * @param &string html
     */
    static function removeButtonsAndLinksFromPage(&$html) {
        // remove the checkboxes "minor edit" and "Watch page"
        // look for the <p> before and </p> after this section
        self::sliceHtmlPieces($html, 'id="wpMinoredit"', '<p>', '</p>');
        // remove wpPreview button
        self::sliceHtmlPieces($html, 'id="wpPreview"', '<input ', '/>');
        // remove wpDiff button
        self::sliceHtmlPieces($html, 'id="wpDiff"', '<input ', '/>');
        // remove cancel link
        self::sliceHtmlPieces($html, "class='editHelp'", '<span ', '</span>');
    }
    static function sliceHtmlPieces(&$html, $entry, $start, $end) {
        $pStart = strpos($html, $entry);
        if ($pStart === false) return;
        $pStart = strrpos(substr($html, 0, $pStart), $start);
        $pEnd = strpos($html, $end, $pStart);
        $before = substr($html, 0, $pStart);
        $behind = substr($html, $pEnd + strlen($end));
        $html = $before . $behind;
    }

    /**
     * additional javascript so that some parameter value are extraced from the
     * form and added to the template call string in the parent frame.
     *
     * @return string javascript
     */
    static function getFckTemplatePopupJavascript() {
        return <<<ENDJS
            validate_all_and_save = function() {
                if (validate_all()) {
                    var newData = getValuesFromPage();
                    setNewTemplateData(newData);
                    return true;
                }
                return false;
            }

            /**
             * take all values from the form regarding the specific template call
             * An assoc array is build with the data of the form for the template
             *
             * @return array(string, string) paramname, paramvalue
             */
            getValuesFromPage = function() {
                var newData = [];
                // get the current template name from the globa variable in the popup
                // and remove the Template: namespace
                var tName = window.parent.sTemplateName.substr(window.parent.sTemplateName.indexOf(':') + 1);
                tName = tName.replace(/ /g, '_');
                // iterate over all input elements to get template parameters
                var inputs = document.getElementsByTagName('input');
                for (var i = 0; i < inputs.length; i++) {
                    // if input field is a radiobutton or checkbox and not checked skip it
                    if (inputs[i].type &&
                        (inputs[i].type == "radio" || inputs[i].type == "checkbox") &&
                        !inputs[i].checked)
                        continue;
                    var inputName = inputs[i].name;
                    // check input name which must look like template_name[field_name]
                    if (inputName && inputName.indexOf(tName) != -1) {
                        // do we have a date? (i.e. template_name[field_name][day])
                        if (inputName.match(/\]\[(day|month|year)\]$/))
                            newData = addDateToResult(newData, inputName, inputs[i].value);
                        else
                            // normal value
                            newData.push( [ inputName.substr(tName.length + 1, inputName.length - tName.length -2),
                                            (inputs[i].value) ? inputs[i].value : '' ]);
                    }
                }
                // do the same for textareas, handling is pretty much the same
                inputs = document.getElementsByTagName('textarea');
                for (var i = 0; i < inputs.length; i++) {
                    var inputName = inputs[i].name;
                    if (inputName && inputName.indexOf(tName) != -1) {
                        newData.push( [ inputName.substr(tName.length + 1, inputName.length - tName.length -2),
                                      (inputs[i].innerHTML) ? inputs[i].innerHTML : '' ]);
                    }
                }
                // and now for selections
                inputs = document.getElementsByTagName('select');
                for (var i = 0; i < inputs.length; i++) {
                    var inputName = inputs[i].name;
                    if (inputName && inputName.indexOf(tName) != -1) {
                        if (inputName.match(/\]\[(day|month|year)\]$/))
                            newData = addDateToResult(newData, inputName, inputs[i].value);
                        else
                            // normal value
                            newData.push( [ inputName.substr(tName.length + 1, inputName.length - tName.length -2),
                                            (inputs[i].innerHTML) ? inputs[i].innerHTML : '' ]);
                    }
                }

                // flatten the date arrays in case there are any
                for (i = 0; i < newData.length; i++) {
                    if (typeof newData[i][1] == "object")
                        newData[i][1] = newData[i][1].join(' ');
                }
                return newData;
            }

            /**
             * takes the array with the collected template data and merge these
             * with the existing template data of the article. New parameters that
             * are set by the form but where not yet existing in the page, will
             * be added. All others existing parameters will be replaced by the
             * data from the form. Params that exist in the template call but are
             * not set by the form remain unchanged in the page.
             *
             * @param array(string, string) paramname, paramvalue
             */
            setNewTemplateData = function(newData) {
                var newStr = '';
                var orig = window.parent.document.getElementById('xTemplateRaw').value;
                var tail = orig.substr(orig.lastIndexOf('}}'));
                var oldData = orig.split('|');
                // safe template name to new string.
                newStr += oldData.shift();
                // iterate over all old data (parameters are seperated by |) and
                // replace them with the new data if the key with the param name exists
                while (param = oldData.shift() ) {
                    var keyVal = param.split('=');
                    var key = keyVal[0].replace(/^\s*/, '').replace(/\s*$/, '');
                    var replaced = 0;
                    for (var i = 0; i < newData.length; i++) {
                        if (key == newData[i][0]) {
                            newStr += '|' + key + '=' + newData[i][1] + '\\n';
                            newData.splice(i, 1);
                            replaced = 1;
                            break;
                        }
                    }
                    // the current param was not found in the param list
                    // so the parameter is not set by the form. Then copy the
                    // existing parameter name and value
                    if (! replaced)
                        newStr += '|' + param;
                }
                // if the last value was replaced the }} are missing already
                // otherwise remove them
                if (newStr.match(/\}\}\s*$/))
                    newStr = newStr.substr(0, newStr.lastIndexOf('}}'));
                // if there are still parameter left in the newData array
                // then they were not yet in the template call and will be added
                for (var i = 0; i < newData.length; i++)
                    newStr += '|' + newData[i][0] + '=' + newData[i][1] + '\\n';
                // now add the missing }} again.
                newStr += tail;
                window.parent.document.getElementById('xTemplateRaw').value = newStr;
            }

            /**
             * Add date string to result array
             *
             * @param array(string, string) result array
             * @param string input field name
             * @param string input field value
             * @return array(string, string) result array modified
             */
             addDateToResult = function(newData, name, value) {
                var s = name.indexOf('[') + 1;
                var l = name.indexOf(']') - s;
                var fieldname = name.substr(s , l);
                s = name.lastIndexOf('[') + 1;
                l = name.lastIndexOf(']') - s;
                var type = name.substr(s, l);
                // check if we already have that field name in the result
                for (i = 0; i < newData.length; i++) {
                    if (newData[i][0] == fieldname) {
                        switch (type) {
                            case 'day':
                                newData[i][1][0] = value;
                                return newData;
                            case 'month':
                                newData[i][1][1] = getNameOfMonth(value);
                                return newData;
                            case 'year':
                                newData[i][1][2] = value;
                                return newData;
                        }
                    }
                }
                var date = [];
                date.push( (type == 'day') ? value : '');
                date.push( (type == 'month') ? getNameOfMonth(value) : '');
                date.push( (type == 'year') ? value : '');
                newData.push([fieldname, date]);
                return newData;
            }

            /**
             * Get name of month (english)
             *
             * @param int number of month
             * @return string month
             */
            getNameOfMonth = function(val) {
                if (! val.match(/^\d+$/)) return val;
                months = [ 'January', 'February', 'March', 'April',
                           'May', 'June', 'July', 'August',
                           'September', 'October', 'November', 'December'
                         ];
                var key = parseInt(val) - 1;
                return (key < 12) ? months[key] : '';
            }
ENDJS;
    }
}
