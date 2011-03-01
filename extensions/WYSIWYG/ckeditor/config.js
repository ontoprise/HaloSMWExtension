/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

if (!String.prototype.InArray) {
	String.prototype.InArray = function(arr) {
		for(var i=0;i<arr.length;i++) {
            if (arr[i] == this)
                return true;
        }
		return false;
	}
}

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    
    CKEDITOR.plugins.addExternal( 'mediawiki', CKEDITOR.basePath + 'plugins/mediawiki/' );
    CKEDITOR.plugins.addExternal( 'mwtemplate', CKEDITOR.basePath + 'plugins/mwtemplate/' );
    
    // Remove the link plugin because it's replaced with the mediawiki plugin
    //CKEDITOR.config.plugins = CKEDITOR.config.plugins.replace( /(?:^|,)link(?=,|$)/, '' );
	var extraPlugins = "mediawiki,mwtemplate";

	config.toolbar = 'Wiki';
    // var origToolbar = CKEDITOR.config.toolbar_Full

    // custom toolbar for MW
    var mwToolbar = ['MWSpecialTags', 'MWTemplate'];
    // SMWHalo extension
    var smwQiButton;
    var smwStbButton;
    if (('SMW_HALO_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
        smwQiButton = 'SMWqi';
        CKEDITOR.plugins.addExternal( 'smw_qi', CKEDITOR.basePath + 'plugins/smwqueryinterface/' );
        smwStbButton = 'SMWtoolbar';
        CKEDITOR.plugins.addExternal( 'smw_toolbar', CKEDITOR.basePath + 'plugins/smwtoolbar/' );
        extraPlugins += ",smw_qi,smwtoolbar";
    }
    // DataImport extension
    var smwDiButton;
    if (('SMW_DI_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
        smwDiButton = 'SMWwebservice';
        CKEDITOR.plugins.addExternal( 'smw_webservice', CKEDITOR.basePath + 'plugins/smwwebservice/' );
        extraPlugins += ",smw_webservice";
    }
    // SemanticRule extension
    if (('SEMANTIC_RULES_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
        CKEDITOR.plugins.addExternal( 'smw_rule', CKEDITOR.basePath + 'plugins/smwrule/' );
        extraPlugins += ",smw_rule";
    }
    // Richmedia extension
    var smwRmButton;
    if (('SMW_RM_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
        smwRmButton = 'SMWrichmedia';
        CKEDITOR.plugins.addExternal( 'smw_richmedia', CKEDITOR.basePath + 'plugins/smwrichmedia/' );
        extraPlugins += ",smw_richmedia";
    }

    config.toolbar_Wiki = [
        ['Source', '-', 'Print','SpellChecker','Scayt'],
        ['PasteText','PasteFromWord', '-','Find','Replace'],
        ['SelectAll','RemoveFormat'],
        ['Subscript','Superscript'],
        ['Link','Unlink'],
        ['Undo','Redo'],
        ['Image', 'Table', 'HorizontalRule', 'SpecialChar'],
        ['MWSpecialTags', 'MWTemplate', smwQiButton, smwDiButton, smwRmButton ],
        [ smwStbButton ],
        ['About'],
        '/',
        ['Styles','Format','Font','FontSize'],
        ['Bold','Italic','Underline','Strike'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['NumberedList','BulletedList', '-', 'Outdent','Indent', 'Blockquote'],
        ['TextColor','BGColor'],
        ['Maximize', 'ShowBlocks'],
    ];
    config.extraPlugins = extraPlugins;
    config.height = '26em';
    config.resize_dir = 'vertical';
    config.language = window.parent.wgUserLanguage || 'en';

    config.WikiSignature = '--~~~~';

    // remove format: address
    config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre;div';
    // use fontsizes only that do not harm the skin
    config.fontSize_sizes = 'smaller;larger;xx-small;x-small;small;medium;large;x-large;xx-large';

};
