<?php

global $wgOMIP, $wgAutoloadClasses;

// POM Type
$wgAutoloadClasses['WikiObjectModelFactory']    =  $wgOMIP . '/includes/models/WOMFactory.php';
$wgAutoloadClasses['WikiObjectModel']           =  $wgOMIP . '/includes/models/WikiObjectModel.php';
$wgAutoloadClasses['WikiObjectModelCollection'] =  $wgOMIP . '/includes/models/WikiObjectModelCollection.php';

$wgAutoloadClasses['WOMCategoryModel']         =  $wgOMIP . '/includes/models/WOM_OM_Category.php';
$wgAutoloadClasses['WOMPageModel']             =  $wgOMIP . '/includes/models/WOM_OM_Page.php';
$wgAutoloadClasses['WOMTemplateModel']         =  $wgOMIP . '/includes/models/WOM_OM_Template.php';
$wgAutoloadClasses['WOMParserFunctionModel']   =  $wgOMIP . '/includes/models/WOM_OM_ParserFunction.php';
$wgAutoloadClasses['WOMParameterModel']        =  $wgOMIP . '/includes/models/WOM_OM_Parameter.php';
$wgAutoloadClasses['WOMParamValueModel']       =  $wgOMIP . '/includes/models/WOM_OM_ParamValue.php';
$wgAutoloadClasses['WOMTemplateFieldModel']    =  $wgOMIP . '/includes/models/WOM_OM_TmplField.php';
$wgAutoloadClasses['WOMPropertyModel']         =  $wgOMIP . '/includes/models/WOM_OM_Property.php';
$wgAutoloadClasses['WOMTextModel']             =  $wgOMIP . '/includes/models/WOM_OM_Text.php';
$wgAutoloadClasses['WOMLinkModel']             =  $wgOMIP . '/includes/models/WOM_OM_Link.php';
$wgAutoloadClasses['WOMSectionModel']          =  $wgOMIP . '/includes/models/WOM_OM_Section.php';
$wgAutoloadClasses['WOMSentenceModel']         =  $wgOMIP . '/includes/models/WOM_OM_Sentence.php';
$wgAutoloadClasses['WOMListItemModel']         =  $wgOMIP . '/includes/models/WOM_OM_ListItem.php';
$wgAutoloadClasses['WOMTableModel']            =  $wgOMIP . '/includes/models/WOM_OM_Table.php';
$wgAutoloadClasses['WOMTableCellModel']        =  $wgOMIP . '/includes/models/WOM_OM_TblCell.php';
$wgAutoloadClasses['WOMMagicWordModel']        =  $wgOMIP . '/includes/models/WOM_OM_MagicWord.php';
$wgAutoloadClasses['WOMHTMLTagModel']          =  $wgOMIP . '/includes/models/WOM_OM_HTMLTag.php';

// Definitions
define( 'WOM_TYPE_PAGE'           , 'page' );
define( 'WOM_TYPE_CATEGORY'       , 'category' );
define( 'WOM_TYPE_SECTION'        , 'section' );
define( 'WOM_TYPE_PROPERTY'       , 'property' );
define( 'WOM_TYPE_LINK'           , 'link' );
define( 'WOM_TYPE_TEXT'           , 'text' );
define( 'WOM_TYPE_TEMPLATE'       , 'template' );
define( 'WOM_TYPE_PARSERFUNCTION' , 'parser_function' );
define( 'WOM_TYPE_PARAMETER'      , 'parameter' );
define( 'WOM_TYPE_PARAM_VALUE'    , 'value' );
define( 'WOM_TYPE_TMPL_FIELD'     , 'template_field' );
define( 'WOM_TYPE_SENTENCE'       , 'sentence' );
define( 'WOM_TYPE_LISTITEM'       , 'list_item' );
define( 'WOM_TYPE_TABLE'          , 'table' );
define( 'WOM_TYPE_TBL_CELL'       , 'table_cell' );
define( 'WOM_TYPE_MAGICWORD'      , 'magicword' );
define( 'WOM_TYPE_HTMLTAG'        , 'html' );

// POM Parser
$wgAutoloadClasses['WikiObjectModelParser']     =  $wgOMIP . '/includes/parsers/WikiObjectModelParser.php';

$wgAutoloadClasses['WOMCategoryParser']        =  $wgOMIP . '/includes/parsers/WOMCategoryParser.php';
$wgAutoloadClasses['WOMLinkParser']            =  $wgOMIP . '/includes/parsers/WOMLinkParser.php';
$wgAutoloadClasses['WOMPropertyParser']        =  $wgOMIP . '/includes/parsers/WOMPropertyParser.php';
$wgAutoloadClasses['WOMSectionParser']         =  $wgOMIP . '/includes/parsers/WOMSectionParser.php';
$wgAutoloadClasses['WOMTemplateParser']        =  $wgOMIP . '/includes/parsers/WOMTemplateParser.php';
$wgAutoloadClasses['WOMParserFunctionParser']  =  $wgOMIP . '/includes/parsers/WOMParserFunctionParser.php';
$wgAutoloadClasses['WOMParameterParser']       =  $wgOMIP . '/includes/parsers/WOMParameterParser.php';
$wgAutoloadClasses['WOMParamValueParser']      =  $wgOMIP . '/includes/parsers/WOMParamValueParser.php';
$wgAutoloadClasses['WOMListItemParser']        =  $wgOMIP . '/includes/parsers/WOMListItemParser.php';
$wgAutoloadClasses['WOMTableParser']           =  $wgOMIP . '/includes/parsers/WOMTableParser.php';
$wgAutoloadClasses['WOMTableCellParser']       =  $wgOMIP . '/includes/parsers/WOMTblCellParser.php';
$wgAutoloadClasses['WOMMagicWordParser']       =  $wgOMIP . '/includes/parsers/WOMMagicWordParser.php';
$wgAutoloadClasses['WOMHTMLTagParser']         =  $wgOMIP . '/includes/parsers/WOMHTMLTagParser.php';

// Definitions
define( 'WOM_PARSER_ID_CATEGORY'       , 'category' );
define( 'WOM_PARSER_ID_SECTION'        , 'section' );
define( 'WOM_PARSER_ID_PROPERTY'       , 'property' );
define( 'WOM_PARSER_ID_LINK'           , 'link' );
define( 'WOM_PARSER_ID_TEXT'           , 'text' );
define( 'WOM_PARSER_ID_TEMPLATE'       , 'template' );
define( 'WOM_PARSER_ID_PARSERFUNCTION' , 'parser_function' );
define( 'WOM_PARSER_ID_PARAMETER'      , 'parameter' );
define( 'WOM_PARSER_ID_PARAM_VALUE'    , 'value' );
define( 'WOM_PARSER_ID_LISTITEM'       , 'list_item' );
define( 'WOM_PARSER_ID_TABLE'          , 'table' );
define( 'WOM_PARSER_ID_TABLECELL'      , 'tbl_cell' );
define( 'WOM_PARSER_ID_MAGICWORD'      , 'magicword' );
define( 'WOM_PARSER_ID_HTMLTAG'        , 'html' );

global $wgOMParsers, $wgOMModelParserMapping;
$wgOMParsers = array(
		'WikiObjectModelParser',
		'WOMCategoryParser',
		'WOMLinkParser',
		'WOMPropertyParser',
		'WOMSectionParser',
		'WOMTemplateParser',
		'WOMParserFunctionParser',
		'WOMParameterParser',
		'WOMParamValueParser',
		'WOMListItemParser',
		'WOMTableParser',
		'WOMTableCellParser',
		'WOMMagicWordParser',
		'WOMHTMLTagParser',
);

$wgOMModelParserMapping = array(
	WOM_TYPE_TEXT           => WOM_PARSER_ID_TEXT,
	WOM_TYPE_CATEGORY       => WOM_PARSER_ID_CATEGORY,
	WOM_TYPE_SECTION        => WOM_PARSER_ID_SECTION,
	WOM_TYPE_PROPERTY       => WOM_PARSER_ID_PROPERTY,
	WOM_TYPE_LINK           => WOM_PARSER_ID_LINK,
	WOM_TYPE_TEMPLATE       => WOM_PARSER_ID_TEMPLATE,
	WOM_TYPE_PARSERFUNCTION => WOM_PARSER_ID_PARSERFUNCTION,
	WOM_TYPE_PARAMETER      => WOM_PARSER_ID_PARAMETER,
	WOM_TYPE_TMPL_FIELD     => WOM_PARSER_ID_PARAMETER,
	WOM_TYPE_PARAM_VALUE    => WOM_PARSER_ID_PARAM_VALUE,
	WOM_TYPE_LISTITEM       => WOM_PARSER_ID_LISTITEM,
	WOM_TYPE_TABLE          => WOM_PARSER_ID_TABLE,
	WOM_TYPE_TBL_CELL       => WOM_PARSER_ID_TABLECELL,
	WOM_TYPE_MAGICWORD      => WOM_PARSER_ID_MAGICWORD,
	WOM_TYPE_HTMLTAG        => WOM_PARSER_ID_HTMLTAG,
);

global $wgOMSentenceObjectTypes;
$wgOMSentenceObjectTypes = array(
	WOM_TYPE_TEXT,
	WOM_TYPE_PROPERTY,
	WOM_TYPE_LINK,
	WOM_TYPE_CATEGORY,
	WOM_TYPE_MAGICWORD,
);

// APIs
global $wgAPIModules;
$wgAPIModules['womset'] = 'ApiWOMSetObjectModel';
$wgAutoloadClasses['ApiWOMSetObjectModel'] = $wgOMIP . '/includes/apis/WOM_SetObjectModel.php';
$wgAPIModules['womget'] = 'ApiWOMGetObjectModel';
$wgAutoloadClasses['ApiWOMGetObjectModel'] = $wgOMIP . '/includes/apis/WOM_GetObjectModel.php';


