<?php

//////////////NOT IN USE. Left for backwards compatibility with SemanticForms//////////////////
/**
 * Options for FCKeditor
 * [start with FCKeditor]
 */
define('RTE_VISIBLE', 1);
/**
 * Options for FCKeditor
 * [show toggle link]
 */
define('RTE_TOGGLE_LINK', 2);
/**
 * Options for FCKeditor
 * [show popup link]
 */
define('RTE_POPUP', 4);

/////////////////////////////////////////////////////////////////////////////////////////////////

class CKeditor_MediaWiki {

  private $excludedNamespaces;

  public function __construct() {
    $this->registerResourceLoaderModules();
  }

  /**
   * Gets the namespaces where FCKeditor should be disabled
   * First check is done against user preferences, second is done against the global variable $wgFCKEditorExcludedNamespaces
   */
  private function getExcludedNamespaces() {
    global $wgUser, $wgDefaultUserOptions, $wgFCKEditorExcludedNamespaces;

    if (is_null($this->excludedNamespaces)) {
      $this->excludedNamespaces = array();
      $namespaces = self::buildNamespaceOptions(MWNamespace::getCanonicalNamespaces());
      foreach ($namespaces as $key => $value) {
        $optionValue = 'cke_ns_' . $value;
        $default = array_key_exists($optionValue, $wgDefaultUserOptions) ? $wgDefaultUserOptions[$optionValue] : '';
        if ($wgUser->getOption($optionValue, $default)) {
          $this->excludedNamespaces[] = constant($value);
        }
      }
      /*
        If this site's LocalSettings.php defines Namespaces that shouldn't use the FCKEditor (in the #wgFCKexcludedNamespaces array), those excluded
        namespaces should be combined with those excluded in the user's preferences.
       */
      if (!empty($wgFCKEditorExcludedNamespaces) && is_array($wgFCKEditorExcludedNamespaces)) {
        $this->excludedNamespaces = array_merge($wgFCKEditorExcludedNamespaces, $this->excludedNamespaces);
      }
    }

    return $this->excludedNamespaces;
  }

  public static function onLanguageGetMagic(&$magicWords, $langCode) {
    $magicWords['NORICHEDITOR'] = array(0, '__NORICHEDITOR__');

    return true;
  }

  public static function onParserBeforeInternalParse(&$parser, &$text, &$strip_state) {
    MagicWord::get('NORICHEDITOR')->matchAndRemove($text);

    return true;
  }

  /**
   * @param $pageEditor EditPage instance
   * @param $out OutputPage instance
   * @return true
   */
  public static function onEditPageBeforeConflictDiff($pageEditor, $out) {
    global $wgRequest;

    /*
      Show WikiText instead of HTML when there is a conflict
      http://dev.fckeditor.net/ticket/1385
     */
    $pageEditor->textbox2 = $wgRequest->getVal('wpTextbox1');
    $pageEditor->textbox1 = $pageEditor->getWikiContent();

    return true;
  }

  public static function onParserBeforeStrip(&$parser, &$text, &$stripState) {
    $text = $parser->strip($text, $stripState);
    return true;
  }

  public static function onSanitizerAfterFixTagAttributes($text, $element, &$attribs) {
    $text = preg_match_all("/Fckmw\d+fckmw/", $text, $matches);

    if (!empty($matches[0][0])) {
      global $leaveRawTemplates;
      if (!isset($leaveRawTemplates)) {
        $leaveRawTemplates = array();
      }
      $leaveRawTemplates = array_merge($leaveRawTemplates, $matches[0]);
      $attribs = array_merge($attribs, $matches[0]);
    }

    return true;
  }

  // we need to move our hook onBeforePageDisplay at the end of the list so that
  // style sheets are already inserted into the out object.
  public static function onOutputPageParserOutput(&$out, $parseroutput) {
    global $wgHooks;
    $noHooks = count($wgHooks['BeforePageDisplay']);
    if ($wgHooks['BeforePageDisplay'][$noHooks - 1] != 'CKeditor_MediaWiki::onBeforePageDisplay') {
      $BeforePageDisplay = array();
      for ($i = 0; $i < $noHooks; $i++) {
        if ($wgHooks['BeforePageDisplay'][$i] == 'CKeditor_MediaWiki::onBeforePageDisplay')
          continue;
        $BeforePageDisplay[] = $wgHooks['BeforePageDisplay'][$i];
      }
      $wgHooks['BeforePageDisplay'] = $BeforePageDisplay;
      $wgHooks['BeforePageDisplay'][] = 'CKeditor_MediaWiki::onBeforePageDisplay';
      return true;
    }
    return true;
  }

  // take content of css files and put this as inline text into the page, instead
  // of using the link elements to fetch css files separate from the server.
  // The latter causes IE to hang when more than 31 style sheets are processed this way.
  public static function onBeforePageDisplay(&$out, &$text) {
    global $wgRequest, $wgScriptPath;

    //var_dump($out->styles);
    $action = $wgRequest->getText('action');
    if (!in_array($action, array('edit', 'submit'))) {
      return $out;
    }

    $inlineStyles = array();
    foreach ($out->styles as $key => $val) {
      if (count($out->styles[$key]) > 0) {
        if (isset($out->styles[$key]['condition']) ||
                isset($out->styles[$key]['dir']) ||
                strpos($key, '?') !== false ||
                strpos($key, 'jquery.fancybox') !== false)
          continue;
        $count = 1;
        $cssFile = dirname(__FILE__) . '/../../' . str_replace($wgScriptPath, '', $key, $count);
        $cssFile = str_replace('//', '/', $cssFile);
        if (isset($out->styles[$key]['media']) &&
                file_exists($cssFile)) {
          $cssCont = file_get_contents($cssFile);
          if ($cssCont !== false) {
            if (!isset($inlineStyles[$out->styles[$key]['media']]))
              $inlineStyles[$out->styles[$key]['media']] = '';
            $inlineStyles[$out->styles[$key]['media']] .= $cssCont . "\n";
            unset($out->styles[$key]);
          }
        }
      }
    }
    foreach ($inlineStyles as $media => $css) {
      $out->addInlineStyle($css);
    }

    $out->addModules('ext.wysiwyg.core');
    return $out;
  }

  public function onCustomEditor($article, $user) {
    global $wgRequest, $mediaWiki;

    $action = $mediaWiki->getVal('Action');

    $internal = $wgRequest->getVal('internaledit');
    $external = $wgRequest->getVal('externaledit');
    $section = $wgRequest->getVal('section');
    $oldid = $wgRequest->getVal('oldid');
    if (!$mediaWiki->getVal('UseExternalEditor')
            || $action == 'submit'
            || $internal
            || $section
            || $oldid
            || (!$user->getOption('externaleditor') && !$external )) {
      $editor = new CKeditorEditPage($article);
      $editor->submit();
    } elseif ($mediaWiki->getVal('UseExternalEditor') && ( $external || $user->getOption('externaleditor') )) {
      $mode = $wgRequest->getVal('mode');
      $extedit = new ExternalEdit($article, $mode);
      $extedit->edit();
    }

    return false;
  }

  public function onEditPageBeforePreviewText(&$editPage, $previewOnOpen) {
    global $wgUser, $wgRequest;

    if ($wgUser->getOption('showtoolbar') && !$previewOnOpen) {
      $this->oldTextBox1 = $editPage->textbox1;
      $editPage->importFormData($wgRequest);
      //bugfix #16730: load WYSIWYG on preview
      if ($wgUser->getOption('cke_show') != 'wikitexteditor') {
        $wgRequest->setVal('mode', 'wysiwyg');
      }
    }

    return true;
  }

  public function onEditPagePreviewTextEnd(&$editPage, $previewOnOpen) {
    global $wgUser;

    if ($wgUser->getOption('showtoolbar') && !$wgUser->getOption('riched_disable') && !$previewOnOpen) {
      $editPage->textbox1 = $this->oldTextBox1;
    }

    return true;
  }

  public function onParserAfterTidy(&$parser, &$text) {
    global $wgUseTeX, $wgUser, $wgTitle, $wgFCKEditorIsCompatible;

    MagicWord::get('NORICHEDITOR')->matchAndRemove($text);

    # Don't initialize for users that have chosen to disable the toolbar, rich editor or that do not have a FCKeditor-compatible browser
    if (!$wgUser->getOption('showtoolbar') || $wgUser->getOption('riched_disable') || !$wgFCKEditorIsCompatible) {
      return true;
    }

    # Are we editing a page that's in an excluded namespace? If so, bail out.
    if (is_object($wgTitle) && in_array($wgTitle->getNamespace(), $this->getExcludedNamespaces())) {
      return true;
    }

    if ($wgUseTeX) {
      // it may add much overload on page with huge amount of math content...
      $text = preg_replace('/<img class="tex" alt="([^"]*)"/m', '<img _fckfakelement="true" _fck_mw_math="$1"', $text);
      $text = preg_replace("/<img class='tex' src=\"([^\"]*)\" alt=\"([^\"]*)\"/m", '<img src="$1" _fckfakelement="true" _fck_mw_math="$2"', $text);
    }

    return true;
  }

  /**
   * Adds some new JS global variables
   * @param $vars Array: array of JS global variables
   * @return true
   */
  public static function onMakeGlobalVariablesScript($vars) {
    global $wgFCKEditorDir, $wgFCKEditorExtDir, $wgFCKEditorToolbarSet, $wgFCKEditorHeight,
    $wgGroupPermissions;

    $vars['WYSIWYG_EDITOR_VERSION'] = WYSIWYG_EDITOR_VERSION;
    $vars['wgFCKEditorDir'] = $wgFCKEditorDir;
    $vars['wgFCKEditorExtDir'] = $wgFCKEditorExtDir;
    $vars['wgFCKEditorToolbarSet'] = $wgFCKEditorToolbarSet;
    $vars['wgFCKEditorHeight'] = $wgFCKEditorHeight;
    $ckParser = new CKeditorParser();
    $vars['wgCKeditorMagicWords'] = array(
        'wikitags' => $ckParser->getSpecialTags(),
        'magicwords' => $ckParser->getMagicWords(),
        'datevars' => $ckParser->getDateTimeVariables(),
        'wikivars' => $ckParser->getWikiVariables(),
        'parserhooks' => $ckParser->getFunctionHooks()
    );

    if (defined('SF_VERSION'))
      $vars['wgCKeditorMagicWords']['sftags'] = $ckParser->getSfSpecialTags();
    $instExt = array();
    if (defined('SMW_DI_VERSION'))
      $instExt[] = 'SMW_DI_VERSION';
    if (defined('SMW_HALO_VERSION'))
      $instExt[] = 'SMW_HALO_VERSION';
    if (defined('SMW_RM_VERSION'))
      $instExt[] = 'SMW_RM_VERSION';
    if (defined('SEMANTIC_RULES_VERSION'))
      $instExt[] = 'SEMANTIC_RULES_VERSION';
    $vars['wgCKeditorUseBuildin4Extensions'] = $instExt;

    $vars['wgGroupPermissions'] = $wgGroupPermissions;

    return true;
  }

  /**
   * Adds new toggles into Special:Preferences
   * @param $user User object
   * @param $preferences Preferences object
   * @return true
   */
  public static function onGetPreferences($user, &$preferences) {
    global $wgDefaultUserOptions;
    wfLoadExtensionMessages('CKeditor');

    $preferences['cke_show'] = array(
        'type' => 'radio',
        'section' => 'editing/fckeditor',
        'options' => array(
            wfMsgHtml('edit-in-richeditor') => 'richeditor',
            wfMsgHtml('edit-in-wikitexteditor') => 'wikitexteditor',
            wfMsgHtml('tog-riched_toggle_remember_state') => 'rememberlast'
        )
    );

    $preferences['riched_use_toggle'] = array(
        'type' => 'toggle',
        'section' => 'editing/fckeditor',
        'label-message' => 'tog-riched_use_toggle',
    );

    if (defined('SMW_HALO_VERSION')) {
      $preferences['riched_load_semantic_toolbar'] = array(
          'type' => 'toggle',
          'section' => 'editing/fckeditor',
          'label-message' => 'load-stb-on-startup',
      );
    }

    // Show default options in Special:Preferences
    if (!array_key_exists('cke_show', $user->mOptions) && !empty($wgDefaultUserOptions['cke_show']))
      $user->setOption('cke_show', $wgDefaultUserOptions['cke_show']);
    if (!array_key_exists('riched_use_toggle', $user->mOptions) && !empty($wgDefaultUserOptions['riched_use_toggle']))
      $user->setOption('riched_use_toggle', $wgDefaultUserOptions['riched_use_toggle']);


    //the name of multiselect also goes into the selected value and it looks like there is a length limit so keep it short
    $preferences['cke_ns_'] = array(
        'type' => 'multiselect',
        'section' => 'editing/fckeditor-disable-namespaces',
        'options' => self::buildNamespaceOptions(MWNamespace::getCanonicalNamespaces())
    );

    return true;
  }

  /**
   * Build option array for multiselect control
   * @param array $canonicalNamespaces array of MW namespaces
   * @return array array of namespace options e.g. "MediaWiki_talk" => "NS_MEDIAWIKI_TALK"
   */
  private static function buildNamespaceOptions($canonicalNamespaces) {
    $result = array();
    foreach ($canonicalNamespaces as $key => $namespace) {
      if (empty($namespace)) {
        $namespace = 'Main';
      }
      $constName = strtoupper('ns_' . $namespace);
      if (!defined($constName)) {
        define($constName, $key);
      }
      $result[$namespace] = $constName;
    }

    return $result;
  }

  /**
   * Register all SMW modules with the MediaWiki Resource Loader.
   */
  private function registerResourceLoaderModules() {
    global $wgResourceModules, $wysiwygIP, $wysiwygScriptPath;

    $moduleTemplate = array(
        'localBasePath' => $wysiwygIP,
        'remoteBasePath' => $wysiwygScriptPath,
        'group' => 'ext.wysiwyg'
    );

    $wgResourceModules['ext.wysiwyg.core'] = $moduleTemplate + array(
        'messages' => array(
            'wysiwyg-qi-edit-query',
            'wysiwyg-qi-insert-query',
            'wysiwyg-qi-insert-new-query',
            'wysiwyg-rename',
            'wysiwyg-title-empty',
            'wysiwyg-title-invalid',
            'wysiwyg-title-exists',
            'wysiwyg-show-richtexteditor',
            'wysiwyg-show-wikitexteditor',
            'wysiwyg-save-and-continue',
            'wysiwyg-save-failed',
            'wysiwyg-save-failed-unknown-error',
            'wysiwyg-save-error',
            'wysiwyg-save-successful',
            'wysiwyg-move-failed',
            'wysiwyg-move-failed-unknown-error',
            'wysiwyg-move-error',
            'wysiwyg-move-successful',
            'wysiwyg-last-save',
            'wysiwyg-never',
            'wysiwyg-no-changes',
            'wysiwyg-save-before-rename',
            'wysiwyg-save-before-exit'
        ),
        'styles' => array(
            'ckeditor/_source/skins/kama/editor.css',
            'ckeditor/_source/skins/kama/dialog.css',
            'ckeditor/_source/skins/kama/templates.css'
        ),
        'scripts' => array(
            'scripts/setBasePath.js',
            'ckeditor/_source/core/ckeditor_base.js',
            'ckeditor/_source/core/event.js',
            'ckeditor/_source/core/editor_basic.js',
            'ckeditor/_source/core/env.js',
            'ckeditor/_source/core/ckeditor_basic.js',
            'ckeditor/_source/core/dom.js',
            'ckeditor/_source/core/tools.js',
            'ckeditor/_source/core/dtd.js',
            'ckeditor/_source/core/dom/event.js',
            'ckeditor/_source/core/dom/domobject.js',
            'ckeditor/_source/core/dom/window.js',
            'ckeditor/_source/core/dom/document.js',
            'ckeditor/_source/core/dom/node.js',
            'ckeditor/_source/core/dom/nodelist.js',
            'ckeditor/_source/core/dom/element.js',
            'ckeditor/_source/core/command.js',
            'ckeditor/_source/core/config.js',
            'ckeditor/_source/core/focusmanager.js',
            'ckeditor/_source/core/lang.js',
            'ckeditor/_source/core/scriptloader.js',
            'ckeditor/_source/core/resourcemanager.js',
            'ckeditor/_source/core/plugins.js',
            'ckeditor/_source/core/skins.js',
            'ckeditor/_source/core/themes.js',
            'ckeditor/_source/core/ui.js',
            'ckeditor/_source/core/editor.js',
            'ckeditor/_source/core/htmlparser.js',
            'ckeditor/_source/core/htmlparser/comment.js',
            'ckeditor/_source/core/htmlparser/text.js',
            'ckeditor/_source/core/htmlparser/cdata.js',
            'ckeditor/_source/core/htmlparser/fragment.js',
            'ckeditor/_source/core/htmlparser/element.js',
            'ckeditor/_source/core/htmlparser/filter.js',
            'ckeditor/_source/core/htmlparser/basicwriter.js',
            'ckeditor/_source/core/loader.js',
            'ckeditor/_source/core/ckeditor.js',
            'ckeditor/_source/core/dom/comment.js',
            'ckeditor/_source/core/dom/elementpath.js',
            'ckeditor/_source/core/dom/text.js',
            'ckeditor/_source/core/dom/documentfragment.js',
            'ckeditor/_source/core/dom/walker.js',
            'ckeditor/_source/core/dom/range.js',
            'ckeditor/_source/core/dom/rangelist.js',
            'ckeditor/_source/core/_bootstrap.js',
            'ckeditor/_source/skins/kama/skin.js',
            'ckeditor/_source/lang/en.js',
            'ckeditor/_source/adapters/jquery.js',
            'ckeditor/_source/plugins/about/plugin.js',
            'ckeditor/_source/plugins/ajax/plugin.js',
            'ckeditor/_source/plugins/autogrow/plugin.js',
            'ckeditor/_source/plugins/a11yhelp/plugin.js',
            'ckeditor/_source/plugins/basicstyles/plugin.js',
            'ckeditor/_source/plugins/bidi/plugin.js',
            'ckeditor/_source/plugins/blockquote/plugin.js',
            'ckeditor/_source/plugins/button/plugin.js',
            'ckeditor/_source/plugins/clipboard/plugin.js',
            'ckeditor/_source/plugins/colorbutton/plugin.js',
            'ckeditor/_source/plugins/colordialog/plugin.js',
            'ckeditor/_source/plugins/contextmenu/plugin.js',
            'ckeditor/_source/plugins/dialogadvtab/plugin.js',
            'ckeditor/_source/plugins/div/plugin.js',
//					'ckeditor/_source/plugins/elementspath/plugin.js',
            'ckeditor/_source/plugins/enterkey/plugin.js',
            'ckeditor/_source/plugins/entities/plugin.js',
            'ckeditor/_source/plugins/filebrowser/plugin.js',
            'ckeditor/_source/plugins/find/plugin.js',
            'ckeditor/_source/plugins/flash/plugin.js',
            'ckeditor/_source/plugins/font/plugin.js',
            'ckeditor/_source/plugins/format/plugin.js',
//					'ckeditor/_source/plugins/forms/plugin.js',
            'ckeditor/_source/plugins/horizontalrule/plugin.js',
            'ckeditor/_source/plugins/htmldataprocessor/plugin.js',
            'ckeditor/_source/plugins/iframe/plugin.js',
            'ckeditor/_source/plugins/iframedialog/plugin.js',
//					'ckeditor/_source/plugins/image/plugin.js',
            'ckeditor/_source/plugins/indent/plugin.js',
            'ckeditor/_source/plugins/justify/plugin.js',
            'ckeditor/_source/plugins/keystrokes/plugin.js',
            'ckeditor/_source/plugins/link/plugin.js',
            'ckeditor/_source/plugins/list/plugin.js',
            'ckeditor/_source/plugins/liststyle/plugin.js',
            'ckeditor/_source/plugins/maximize/plugin.js',
            'ckeditor/_source/plugins/newpage/plugin.js',
            'ckeditor/_source/plugins/pagebreak/plugin.js',
            'ckeditor/_source/plugins/pastefromword/plugin.js',
            'ckeditor/_source/plugins/pastetext/plugin.js',
            'ckeditor/_source/plugins/popup/plugin.js',
            'ckeditor/_source/plugins/preview/plugin.js',
            'ckeditor/_source/plugins/print/plugin.js',
            'ckeditor/_source/plugins/removeformat/plugin.js',
//					'ckeditor/_source/plugins/resize/plugin.js',
            'ckeditor/_source/plugins/save/plugin.js',
            'ckeditor/_source/plugins/scayt/plugin.js',
            'ckeditor/_source/plugins/smiley/plugin.js',
            'ckeditor/_source/plugins/showblocks/plugin.js',
            'ckeditor/_source/plugins/showborders/plugin.js',
            'ckeditor/_source/plugins/sourcearea/plugin.js',
            'ckeditor/_source/plugins/stylescombo/plugin.js',
            'ckeditor/_source/plugins/table/plugin.js',
            'ckeditor/_source/plugins/tabletools/plugin.js',
            'ckeditor/_source/plugins/specialchar/plugin.js',
            'ckeditor/_source/plugins/tab/plugin.js',
            'ckeditor/_source/plugins/templates/plugin.js',
            'ckeditor/_source/plugins/toolbar/plugin.js',
            'ckeditor/_source/plugins/undo/plugin.js',
            'ckeditor/_source/plugins/wysiwygarea/plugin.js',
            'ckeditor/_source/plugins/wsc/plugin.js',
            'ckeditor/_source/plugins/xml/plugin.js',
            'ckeditor/_source/plugins/styles/plugin.js',
            'ckeditor/_source/plugins/styles/styles/default.js',
            'ckeditor/_source/plugins/dialog/plugin.js',
            'ckeditor/_source/plugins/domiterator/plugin.js',
            'ckeditor/_source/plugins/panelbutton/plugin.js',
            'ckeditor/_source/plugins/floatpanel/plugin.js',
            'ckeditor/_source/plugins/menu/plugin.js',
            'ckeditor/_source/plugins/editingblock/plugin.js',
            'ckeditor/_source/plugins/selection/plugin.js',
            'ckeditor/_source/plugins/fakeobjects/plugin.js',
            'ckeditor/_source/plugins/richcombo/plugin.js',
            'ckeditor/_source/plugins/htmlwriter/plugin.js',
            'ckeditor/_source/plugins/menubutton/plugin.js',
            'ckeditor/_source/plugins/dialogui/plugin.js',
            'ckeditor/_source/plugins/panel/plugin.js',
            'ckeditor/_source/plugins/listblock/plugin.js',
            'ckeditor/_source/themes/default/theme.js',
            'ckeditor/_source/plugins/mediawiki/plugin.js',
            'ckeditor/_source/plugins/mwtemplate/plugin.js',
            'ckeditor/_source/plugins/smwtoolbar/plugin.js',
            'ckeditor/_source/plugins/smwqueryinterface/plugin.js',
            'ckeditor/_source/plugins/smwrichmedia/plugin.js',
            'ckeditor/_source/plugins/smwrule/plugin.js',
            'ckeditor/_source/plugins/smwwebservice/plugin.js',
            'ckeditor/_source/plugins/saveAndExit/plugin.js',
            'ckeditor/_source/plugins/mediawiki.api/plugin.js',
            'ckeditor/config.js',
            'scripts/jquery.jscroll.js',
            'scripts/init.js'
        )
    );
  }

  /**
   * Add FCK script
   *
   * @param $form EditPage object
   * @return true
   */
//  public function onEditPageShowEditFormInitial($form) {
//    global $wgOut, $wgTitle, $wgScriptPath, $wgContLang, $wgUser;
//    global $wgStylePath, $wgStyleVersion, $wgDefaultSkin, $wgExtensionFunctions, $wgHooks, $wgDefaultUserOptions;
//    global $wgFCKEditorIsCompatible, $wgRequest, $wgFCKEditorDir;
//
//    if (defined('SMW_HALO_VERSION') && !isset($this->loadSTBonStartup)) {
//      $this->loadSTBonStartup = 0;
//      if ($wgUser->getOption('riched_load_semantic_toolbar', $wgDefaultUserOptions['riched_load_semantic_toolbar'])) {
//        $this->loadSTBonStartup = 1;
//      }
//    }
//    if ($wgRequest->getVal('mode') != 'wysiwyg') {
//      return true;
//    }
//
//    # Don't initialize if we have disabled the toolbar or have a non-compatible browser
//    if (!$wgUser->getOption('showtoolbar') || !$wgFCKEditorIsCompatible) {
//      return true;
//    }
//
//    # Don't do anything if we're in an excluded namespace
//    if (in_array($wgTitle->getNamespace(), $this->getExcludedNamespaces())) {
//      return true;
//    }
//
//    # Make sure that there's no __NORICHEDITOR__ in the text either
//    if (false !== strpos($form->textbox1, '__NORICHEDITOR__')) {
//      return true;
//    }
//    if (!isset($this->showFCKEditor)) {
//      $this->showFCKEditor = 0;
//      $default_cke_show = array_key_exists('cke_show', $wgDefaultUserOptions) ? $wgDefaultUserOptions['cke_show'] : '';
//      //show toggle if configured
//      if ($wgUser->getOption('riched_use_toggle', $wgDefaultUserOptions['riched_use_toggle'])) {
//        $this->showFCKEditor += RTE_TOGGLE_LINK;
//      }
//      $cke_show = $wgUser->getOption('cke_show', $default_cke_show);
//      if ($cke_show == 'richeditor') {
//        $this->showFCKEditor += RTE_VISIBLE;
//      }
//      //use "remember last toggle state" option only if toggle is visible
//      else if ($cke_show == 'rememberlast') {
//        if ($this->showFCKEditor & RTE_TOGGLE_LINK) {
//          if (!array_key_exists('showMyFCKeditor', $_SESSION) || $_SESSION['showMyFCKeditor'] == RTE_VISIBLE) {
//            $this->showFCKEditor += RTE_VISIBLE;
//          }
//        }//if "remember last toggle state" is set and toggle is not visible then show rich editor
//        else {
//          $this->showFCKEditor += RTE_VISIBLE;
//        }
//      }
//    }
//
//    $printsheet = htmlspecialchars("$wgStylePath/common/wikiprintable.css?$wgStyleVersion");
//
//    // CSS trick,  we need to get user CSS stylesheets somehow... it must be done in a different way!
//    $skin = $wgUser->getSkin();
//    $skin->loggedin = $wgUser->isLoggedIn();
//    $skin->mTitle = & $wgTitle;
//    $skin->initPage($wgOut);
//    $skin->userpage = $wgUser->getUserPage()->getPrefixedText();
//    $skin->setupUserCss($wgOut);
//
//    if (!empty($skin->usercss) && preg_match_all('/@import "([^"]+)";/', $skin->usercss, $matches)) {
//      $userStyles = $matches[1];
//    }
//    return true;
//  }

  public static function onGetLocalURL($title, $url, $query) {
    if (!strpos($query, 'mode=')) {
      $url = str_replace('action=edit', 'action=edit&mode=wysiwyg', $url);
    }
    return true;
  }

}
