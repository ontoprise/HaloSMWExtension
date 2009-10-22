/*
 * FCKeditor Extension for MediaWiki specific settings.
 */

// When using the modified image dialog you must set this variable. It must
// correspond to $wgScriptPath in LocalSettings.php.
FCKConfig.mwScriptPath = '' ;     

// Setup the editor toolbar.
FCKConfig.ToolbarSets['WikiEnhanced'] = [
	['Cut','Copy','Paste', /*'PasteText','PasteWord',*/ '-', 'Undo','Redo', '-', 'SelectAll','RemoveFormat'],
        [/*'Print',*/ 'Rule', '-','TextColor', 'BGColor'],
        ['Find','Replace'],
	['MW_Special',/* 'MW_Ref',*/ 'MW_Math'],
        ['MW_Edit', 'Fullscreen'],
	'/',
        ['Bold','Italic','Underline'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
        ['OrderedList','UnorderedList', 'Blockquote'],
        ['SpecialChar','Table','Image'],
        ['MW_MediaUpload', 'SMW_UseWebService'],
        ['SMW_Annotate', 'ToolbarSwitch'],
        '/',
	['FontFormat', '-', 'Source'],
        ['StrikeThrough','Subscript','Superscript'],
	['Link','Unlink','Anchor'],
	['MW_Template','SMW_QueryInterface'],
        ['About']

] ;

FCKConfig.ToolbarSets['Wiki'] = [
	['Bold','Italic','Underline'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
        ['UnorderedList', 'SpecialChar', 'Subscript','Superscript', '-','Blockquote', 'Anchor', 'Rule'],
	['Cut','Copy','Paste'],
	['Fullscreen', 'About'],
	'/',
	['FontFormat', '-','Source'],
        ['OrderedList', 'TextColor','BGColor'],
        ['Link','Unlink', '-', 'Table', 'Image'],
        ['Undo','Redo'],
	['ToolbarSwitch']
] ;

FCKConfig.ToolbarSets['WikiSource'] = [
	['Source', '-', 'SelectAll'],
        ['SMW_QueryInterface', 'SMW_UseWebService'],
        ['SMW_Annotate', 'Fullscreen', 'About']
] ;


// Load the extension plugins.
FCKConfig.PluginsPath = FCKConfig.EditorPath + '../plugins/' ;
FCKConfig.Plugins.Add( 'mediawiki', 'en,de' ) ;
FCKConfig.Plugins.Add( 'mediaupload' ) ;
FCKConfig.Plugins.Add( 'mwedit' ) ;
FCKConfig.Plugins.Add( 'fullscreen' ) ;
FCKConfig.Plugins.Add( 'toolbarswitch' ) ;

FCKConfig.ForcePasteAsPlainText = true ;
FCKConfig.FontFormats	= 'p;h1;h2;h3;h4;h5;h6;pre' ;

FCKConfig.AutoDetectLanguage	= false ;
FCKConfig.DefaultLanguage	= window.parent.wgUserLanguage || 'en' ;

// FCKConfig.DisableObjectResizing = true ;

FCKConfig.EditorAreaStyles = '\
.FCK__SMWask, .FCK__MWTemplate, .FCK__MWRef, .FCK__MWSpecial, .FCK__MWReferences, .FCK__MWNowiki, .FCK__MWIncludeonly, .FCK__MWNoinclude, .FCK__MWOnlyinclude, .FCK__MWGallery \
{ \
	border: 1px dotted #00F; \
	background-position: center center; \
	background-repeat: no-repeat; \
	vertical-align: middle; \
} \
.FCK__MWTemplate \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/tb_icon_template.gif); \
	width: 16px; \
	height: 16px; \
} \
.FCK__SMWask \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/tb_icon_ask.gif); \
	width: 16px; \
	height: 16px; \
} \
.FCK__MWRef \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/icon_ref.gif); \
	width: 18px; \
	height: 15px; \
} \
.FCK__MWSpecial \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/icon_special.gif); \
	width: 66px; \
	height: 15px; \
} \
.FCK__MWNowiki \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/icon_nowiki.gif); \
	width: 66px; \
	height: 15px; \
} \
.FCK__MWIncludeonly \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/icon_includeonly.gif); \
	width: 66px; \
	height: 15px; \
} \
.FCK__MWNoinclude \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/icon_noinclude.gif); \
	width: 66px; \
	height: 15px; \
} \
.FCK__MWGallery \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/icon_gallery.gif); \
	width: 66px; \
	height: 15px; \
} \
.FCK__MWOnlyinclude \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/icon_onlyinclude.gif); \
	width: 66px; \
	height: 15px; \
} \
.fck_mw_property \
{ \
    background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/tb_icon_property.gif); \
    background-repeat: no-repeat; \
    background-color: #ffcd87; \
    padding-left: 16px; \
} \
.fck_mw_category \
{ \
    background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/tb_icon_category.gif); \
    background-repeat: no-repeat; \
    background-color: #94b0f3; \
    padding-left: 16px; \
} \
.FCK__MWWebservice \
{ \
	background-image: url(' + FCKConfig.PluginsPath + 'mediawiki/images/tb_icon_webservice.gif); \
	width: 16px; \
	height: 16px; \
} \
' ;
