<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */



global $asfIP;
include_once($asfIP . '/languages/ASF_Language.php');

class ASFLanguageEn extends ASFLanguage {
	
	protected $asfUserMessages = array(
		'asf_free_text' => "Free text:",
		'asf_dummy_article_edit_comment' => "Created by the Automatic Semantic Forms Extension",
		'asf_dummy_article_content' => "'''This article is required by the Automatic Semantic Forms Extension. Please do not move, edit or delete this article.'''",
		'asf_category_section_label' => "Data required for category $1:",
		'asf_duplicate_property_placeholder' => "Please enter value in the input field above.",
		'asf_unresolved_annotations' => "Additional data:",
		
		'asf_tt_intro' => "Click to open $1",
		'asf_tt_type' => "The <b>type</b> of this property is $1.",
		'asf_tt_autocomplete' => "This input field <b>autocompletes</b> on $1.",
		'asf_tt_delimiter' => "Several values are allowed in this input field. \"$1\" is used as <b>delimiter</b>.",
	
		'asf_categories_with_no_props_section' => "Categories for which no (extra) form input fields could be created:",
	
		'automaticsemanticforms' => "Automatic Semantic Forms",
		
		'asf_autogenerated_msg' => "<p>'''This form has been generated automatically.'''</p>"
	);

}


