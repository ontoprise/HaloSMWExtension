<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Collaboration-Extension.
*
*   The Collaboration-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Collaboration-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
 * @ingroup Collaboration
 * 
 * Internationalization file for Collaboration
 *
 */

$messages = array();

/** 
 * English
 */
$messages['en'] = array(
	/* general/maintenance messages */
    'ce_collaboration'				=> 'Collaboration',
	'ce_allowed'					=> 'Gratulation! CE works as intended!',
	'ce_warning'					=> 'Collaboration extension warning',
	'ce_var_undef'					=> 'The following variable has not been correctly initialized: "$1". <br/> Please check your settings.',

	/* comment form */
	/* warnings */
	'ce_cf_disabled'				=> 'Comments has been disabled.',
	'ce_cf_already_shown'			=> 'The comment form is already shown in this page.',
	'ce_cf_all_not_allowed'			=> 'Nobody is actually allowed to enter comments.',
	'ce_cf_you_not_allowed'			=> 'You are actually not allowed to enter comments.',

	/*header*/
	'ce_cf_header_text'				=> 'add a comment',
	'ce_cf_header_tooltip'			=> 'You can open the comment form by clicking this text.',
	/* author */
	'ce_cf_author'					=> 'Author:',
	/* rating */
	'ce_cf_article_rating'			=> 'Rate the quality of this article',
	'ce_cf_article_rating2'			=> '(optional)',
	'ce_ce_rating_0'				=> 'good',
	'ce_ce_rating_1'				=> 'ok',
	'ce_ce_rating_2'				=> 'bad',
	'ce_cf_rating_title_b'			=> 'You can add a bad rating to your comment by clicking this icon.',
	'ce_cf_rating_title_n'			=> 'You can add a neutral rating to your comment by clicking this icon.',
	'ce_cf_rating_title_g'			=> 'You can add a good rating to your comment by clicking this icon.',
	
	/*comments*/
	'ce_cf_comment'					=> 'Comment',
	'ce_cf_predef'					=> 'Enter your comment here...',
	'ce_cf_submit_button_name'		=> 'Add Comment',
	'ce_cf_reset_button_name'		=> 'Cancel',

	/* comment processing */
	'ce_com_cannot_create'			=> 'Can not create comment.',
	'ce_com_edit_sum'				=> 'This comment article was created by Collaboration Extension.',
	'ce_comment_exists'				=> 'Comment article "$1" already exists.',
	'ce_com_created'				=> 'Comment article successfully created.',
);

/** 
 * German
 */
$messages['de'] = array(
	/* general/maintenance messages */
	'ce_collaboration'				=> 'Collaboration',
	'ce_allowed'					=> 'Gratulation! CE funktioniert wie erwartet.',
	'ce_warning'					=> 'Collaboration Extension Warnung',
	'ce_var_undef'					=> 'Folgende Variable wurde nicht richtig gesetzt: "$1". <br/> Bitte prüfen Sie ihre Einstellungen',

	/* comment form */
	/* warnings */
	'ce_cf_disabled'				=> 'Kommentare sind deaktiviert.',
	'ce_cf_already_shown'			=> 'Das Kommentar-Formular wird auf dieser Seite bereits angezeigt.',
	'ce_cf_all_not_allowed'			=> 'Niemand darf aktuell Kommentare eingeben.',
	'ce_cf_you_not_allowed'			=> 'Sie dürfen aktuell keine Kommentare eingeben.',
 
	/*header*/
	'ce_cf_header_text'				=> 'Einen Kommentar hinzuf&uuml;gen',
	'ce_cf_header_tooltip'			=> 'Sie können das Kommentarformular durch Klicken öffnen.', 
	/* author */
	'ce_cf_author'					=> 'Autor:',

	/* rating */
	'ce_cf_article_rating'			=> 'Bewerten Sie die Qualit&auml;t dieses Artikels',
	'ce_cf_article_rating2'			=> '(optional)',
	'ce_ce_rating_0'				=> 'gut',
	'ce_ce_rating_1'				=> 'ok',
	'ce_ce_rating_2'				=> 'schlecht',
	'ce_cf_rating_title_b'			=> 'Sie können Ihrem Kommentar durch Klicken dieses Bildes eine schlechte Bewertung hinzufügen.',
	'ce_cf_rating_title_n'			=> 'Sie können Ihrem Kommentar durch Klicken dieses Bildes eine neutrale Bewertung hinzufügen.',
	'ce_cf_rating_title_g'			=> 'Sie können Ihrem Kommentar durch Klicken dieses Bildes eine gute Bewertung hinzufügen.',


	/*comments*/
	'ce_cf_comment'					=> 'Kommentar',
	'ce_cf_predef'					=> 'Geben Sie hier Ihren Kommentar ein...',
	'ce_cf_submit_button_name'		=> 'Kommentar hinzufügen',
	'ce_cf_reset_button_name'		=> 'Abbrechen',

	/* comment processing */
	'ce_com_cannot_create'			=> 'Kann Kommentar nicht erstellen.',
	'ce_com_edit_sum'				=> 'Dieser Artikel wurde von der Collaboration Extension erstellt.',
	'ce_comment_exists'				=> 'Kommentar Artikel "$1" bereits vorhanden.',
	'ce_com_created'				=> 'Kommentar Artikel erfolgreich erstellt.',
);