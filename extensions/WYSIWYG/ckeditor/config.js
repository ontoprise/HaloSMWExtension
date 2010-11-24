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

    // custom toolbar for SMW
    var smwToolbar = ['MWSpecialTags', 'MWTemplate' ];
    // if Advanced Annotation is missing, SMWHalo seems not to be installed.
    if (('SMW_HALO_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
        smwToolbar.push('SMWqi');
        CKEDITOR.plugins.addExternal( 'smw_qi', CKEDITOR.basePath + 'plugins/smwqueryinterface/' );
        smwToolbar.push('SMWtoolbar');
        CKEDITOR.plugins.addExternal( 'smw_toolbar', CKEDITOR.basePath + 'plugins/smwtoolbar/' );
        extraPlugins += ",smw_qi,smwtoolbar";
    }
    // DataImport extension
    if (('SMW_DI_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
        smwToolbar.push('SMWwebservice');
        CKEDITOR.plugins.addExternal( 'smw_webservice', CKEDITOR.basePath + 'plugins/smwwebservice/' );
        extraPlugins += ",smw_webservice";
    }
    // SemanticRule extension
    if (('SEMANTIC_RULES_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
        CKEDITOR.plugins.addExternal( 'smw_rule', CKEDITOR.basePath + 'plugins/smwrule/' );
        extraPlugins += ",smw_rule";
    }

    config.toolbar_Wiki = [
        ['Source'],
        ['PasteText','PasteFromWord','-','Print','SpellChecker','Scayt'],
        ['Undo','Redo','-','Find','Replace','-','Subscript','Superscript'],
        ['Link','Unlink'],
        ['TextColor','BGColor'],
        ['Maximize', 'ShowBlocks'],
        ['SelectAll','RemoveFormat', '-'],
        smwToolbar,
        ['About'],
        '/',
        ['Styles','Format','Font','FontSize'],
        ['Bold','Italic','Underline','Strike'],
        ['NumberedList','BulletedList', '-', 'Outdent','Indent', 'Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Image', 'Table', 'HorizontalRule', 'SpecialChar']
    ];
    config.extraPlugins = extraPlugins;
    config.height = '26em';

    config.WikiSignature = '--~~~~';

};
