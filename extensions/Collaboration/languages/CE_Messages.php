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
	'collaboration'      => 'Collaboration',
	'collaboration_desc' => 'Collaboration',
	'ce_sp_intro'        => 'This special page give you a quick overview about the latest comments in your wiki.',
	'ce_allowed'         => 'Gratulation! CE works as intended!',
	'ce_warning'         => 'Collaboration extension warning',
	'ce_var_undef'       => 'The following variable has not been correctly initialized: "$1". <br/> Please check your settings.',

	/* comment form */
	/* warnings */
	'ce_cf_disabled'        => 'Comments are currently disabled.',
	'ce_cf_already_shown'   => 'The comment form is already shown in this page.',
	'ce_cf_all_not_allowed' => 'Nobody is actually allowed to enter comments.',
	'ce_cf_you_not_allowed' => 'You are actually not allowed to enter comments. Please log in first.',

	/* author */
	'ce_cf_author' => 'Author:',
	/* rating */
	'ce_cf_article_rating'  => 'Rate the quality of this article',
	'ce_cf_article_rating2' => '(optional)',
	'ce_ce_rating_0'        => 'good',
	'ce_ce_rating_1'        => 'ok',
	'ce_ce_rating_2'        => 'bad',
	'ce_cf_rating_title_b'  => 'You can add a bad rating to your comment by clicking this icon.',
	'ce_cf_rating_title_n'  => 'You can add a neutral rating to your comment by clicking this icon.',
	'ce_cf_rating_title_g'  => 'You can add a good rating to your comment by clicking this icon.',

	/*comments*/
	'ce_cf_comment'            => 'Comment',
	'ce_cf_predef'             => 'Enter your comment here...',
	'ce_cf_submit_button_name' => 'Add Comment',
	'ce_cf_reset_button_name'  => 'Cancel',
	'ce_cf_file_attach'        => 'Attach article(s):',
	'ce_cf_file_upload_text'   => 'Upload file',
	'ce_cf_file_upload_link'   => 'Upload file',

	/* comment processing */
	'ce_com_cannot_create'               => 'Can not create comment.',
	'ce_com_create_sum'                  => 'This comment article was created by Collaboration Extension.',
	'ce_com_edit_sum'                    => 'This comment article was edited by Collaboration Extension.',
	'ce_comment_exists'                  => 'Comment article "$1" already exists.',
	'ce_com_created'                     => 'Comment article successfully created.',
	'ce_com_edited'                      => 'Comment article successfully edited.',
	'ce_com_edit_not_exists'             => 'You tried to edit a non-existing comment. Please check again.',
	'ce_nothing_deleted'                 => 'No comment deleted.',
	'ce_comment_delete_reason'           => 'Comment has been deleted via Collaboration GUI.',
	'ce_comment_deletion_successful'     => 'Comment has been successfully deleted.',
	'ce_comment_massdeletion_successful' => 'Comments have been successfully deleted.',
	'ce_comment_deletion_error'          => 'Comment could not be deleted.',
	'ce_comment_has_deleted'             => 'deleted this comment on',


	/* for scripts */
	'ce_com_default_header'          => 'Add comment',
	'ce_com_ext_header'              => 'Comments',
	'ce_invalid'                     => 'You didn\'t enter a valid comment.',
	'ce_reload'                      => ' Page is reloading ...',
	'ce_deleting'                    => 'Deleting comment ...',
	'ce_full_deleting'               => 'Completely deleting comments ...',
	'ce_delete'                      => 'Do you really want to delete this comment?',
	'ce_delete_button'               => 'Delete comment',
	'ce_cancel_button'               => 'Cancel',
	'ce_full_delete'                 => 'Completely delete this comment and all following replies for this comment.',
	'ce_close_button'                => 'Close window and refresh page',
	'ce_delete_title'                => 'Click here to delete this comment',
	'ce_edit_title'                  => 'Click here to edit this comment',
	'ce_edit_cancel_title'           => 'Click here to leave the edit mode.',
	'ce_reply_title'                 => 'Click here to reply to this comment',
	'ce_com_reply'                   => 'Reply',
	'ce_edit_rating_text'            => 'Rate the quality of this article ',
	'ce_edit_rating_text2'           => '(optional)',
	'ce_edit_button'                 => 'Update comment',
	'ce_com_show'                    => 'Show comments',
	'ce_com_hide'                    => 'Hide comments',
	'ce_com_view'                    => 'View',
	'ce_com_view_flat'               => 'Flat',
	'ce_com_view_threaded'           => 'Threaded',
	'ce_com_file_toggle'             => 'Show attachments',
	'ce_com_rating_text_short'       => 'Avg. article quality',
	'ce_com_rating_text'             => 'The average article quality based on',
	'ce_com_rating_text2'            => 'rating(s).',
	'ce_com_toggle_tooltip'          => 'Click to show/hide all comments',
	'ce_form_toggle_tooltip'         => 'Click to show/hide the comment form.',
	'ce_form_toggle_no_edit_tooltip' => 'You are not allowed to add new comments',
	'ce_edit_intro'                  => 'This comment has been last edited by',
	'ce_edit_date_intro'             => 'on'
);

/** 
 * German
 */
$messages['de'] = array(
	/* general/maintenance messages */
	'collaboration'      => 'Collaboration',
	'collaboration_desc' => 'Collaboration',
	'ce_sp_intro'        => 'Diese Spezialseite erlaubt Ihnen einen schnellen Überblick über die neusten Kommentare in Ihrem Wiki.',
	'ce_allowed'         => 'Gratulation! CE funktioniert wie erwartet.',
	'ce_warning'         => 'Collaboration Extension Warnung',
	'ce_var_undef'       => 'Folgende Variable wurde nicht richtig gesetzt: "$1". <br/> Bitte prüfen Sie ihre Einstellungen',

	/* comment form */
	/* warnings */
	'ce_cf_disabled'        => 'Kommentare sind deaktiviert.',
	'ce_cf_already_shown'   => 'Das Kommentar-Formular wird auf dieser Seite bereits angezeigt.',
	'ce_cf_all_not_allowed' => 'Niemand darf aktuell Kommentare eingeben.',
	'ce_cf_you_not_allowed' => 'Sie dürfen aktuell keine Kommentare eingeben. Bitte melden sich sich an.',
 
	/* author */
	'ce_cf_author' => 'Autor:',

	/* rating */
	'ce_cf_article_rating'  => 'Bewerten Sie die Qualit&auml;t dieses Artikels',
	'ce_cf_article_rating2' => '(optional)',
	'ce_ce_rating_0'        => 'gut',
	'ce_ce_rating_1'        => 'ok',
	'ce_ce_rating_2'        => 'schlecht',
	'ce_cf_rating_title_b'  => 'Sie können Ihrem Kommentar durch Klicken dieses Bildes eine schlechte Bewertung hinzufügen.',
	'ce_cf_rating_title_n'  => 'Sie können Ihrem Kommentar durch Klicken dieses Bildes eine neutrale Bewertung hinzufügen.',
	'ce_cf_rating_title_g'  => 'Sie können Ihrem Kommentar durch Klicken dieses Bildes eine gute Bewertung hinzufügen.',


	/*comments*/
	'ce_cf_comment'            => 'Kommentar',
	'ce_cf_predef'             => 'Geben Sie hier Ihren Kommentar ein...',
	'ce_cf_submit_button_name' => 'Kommentar hinzufügen',
	'ce_cf_reset_button_name'  => 'Abbrechen',
	'ce_cf_file_attach'        => 'Artikel anhängen:',
	'ce_cf_file_upload_text'   => 'Datei hochladen',
	'ce_cf_file_upload_link'   => 'Datei hochladen',

	/* comment processing */
	'ce_com_cannot_create'               => 'Kann Kommentar nicht erstellen.',
	'ce_com_create_sum'                  => 'Dieser Artikel wurde von der Collaboration Extension erstellt.',
	'ce_com_edit_sum'                    => 'Dieser Artikel wurde von der Collaboration Extension editiert.',
	'ce_comment_exists'                  => 'Kommentar Artikel "$1" bereits vorhanden.',
	'ce_com_created'                     => 'Kommentar Artikel erfolgreich erstellt.',
	'ce_com_edited'                      => 'Kommentar Artikel erfolgreich editiert.',
	'ce_com_edit_not_exists'             => 'Sie versuchten einen nicht existierenden Artikel zu editieren. Bitte überprüfen Sie dies erneut.',
	'ce_nothing_deleted'                 => 'Kein Kommentar gelöscht.',
	'ce_comment_delete_reason'           => 'Kommentar wurde per Collaboration GUI gelöscht.',
	'ce_comment_deletion_successful'     => 'Kommentar wurde erfolgreich gelöscht.',
	'ce_comment_massdeletion_successful' => 'Kommentare wurden erfolgreich gelöscht.',
	'ce_comment_deletion_error'          => 'Kommentar konnte nicht gelöscht werden.',
	'ce_comment_has_deleted'             => 'löschte diesen Kommentar am',

	/* for scripts */
	'ce_com_default_header'          => 'Erstellen',
	'ce_com_ext_header'              => 'Kommentare',
	'ce_invalid'                     => 'Sie haben keinen gültigen Kommentar eingegeben.',
	'ce_reload'                      => ' Seite wird neu geladen ...',
	'ce_deleting'                    => 'Lösche Kommentar ...',
	'ce_full_deleting'               => 'Lösche Kommentare vollständig ...',
	'ce_delete'                      => 'Möchten Sie diesen Kommentar wirklich löschen?',
	'ce_delete_button'               => 'Lösche Kommentar',
	'ce_cancel_button'               => 'Abbrechen',
	'ce_full_delete'                 => 'Lösche diesen Kommentar komplett und entferne auch alle nachfolgenden Antworten zu diesem Kommentar.',
	'ce_close_button'                => 'Fenster schließen und Seite neu laden',
	'ce_delete_title'                => 'Klicken Sie hier um diesen Kommentar zu löschen',
	'ce_edit_title'                  => 'Klicken Sie hier um diesen Kommentar zu editieren',
	'ce_edit_cancel_title'           => 'Klicken Sie hier um den Editiermodus zu verlassen.',
	'ce_reply_title'                 => 'Klicken Sie hier um auf diesen Kommentar zu antworten',
	'ce_com_reply'                   => 'Antworten',
	'ce_edit_rating_text'            => 'Bewerten Sie die Qualität dieses Artikels ',
	'ce_edit_rating_text2'           => '(optional)',
	'ce_edit_button'                 => 'Aktualisiere Kommentar',
	'ce_com_show'                    => 'Zeige Kommentare',
	'ce_com_hide'                    => 'Verstecke Kommentare',
	'ce_com_view'                    => 'Ansicht',
	'ce_com_view_flat'               => 'Flach',
	'ce_com_view_threaded'           => 'Eingerückt',
	'ce_com_file_toggle'             => 'Zeige Anhänge',
	'ce_com_rating_text_short'       => 'Durchschn. Artikelqualität',
	'ce_com_rating_text'             => 'Die durchschnittliche Qualität dieses Artikels basierend auf',
	'ce_com_rating_text2'            => 'Bewertung(en).',
	'ce_com_toggle_tooltip'          => 'Sie können die Kommentare durch Klicken öffnen/schließen',
	'ce_form_toggle_tooltip'         => 'Sie können das Kommentarformular durch Klicken öffnen/schließen.',
	'ce_form_toggle_no_edit_tooltip' => 'Sie dürfen leider keine neuen Kommentare erstellen.',
	'ce_edit_intro'                  => 'Dieser Kommentare wurde zuletzt bearbeitet von',
	'ce_edit_date_intro'             => 'am'
);

/** 
 * French
 */
$messages['fr'] = array(
	/* general/maintenance messages */
	'collaboration'      => 'Collaboration',
	'collaboration_desc' => 'Collaboration',
	'ce_sp_intro'        => 'Cette page spéciale vous donner un rapide aperçu des derniers commentaires publiés.',
	'ce_allowed'         => 'Félicitation ! L\'extension Collaboration fonctionne correctement !',
	'ce_warning'         => 'Avertissement de l\'extension Collaboration',
	'ce_var_undef'       => 'La variable suivante n\'a pas été correctement initialisée: "$1". <br/> Veuillez vérifier vos paramètres.',

	/* comment form */
	/* warnings */
	'ce_cf_disabled'        => 'Les commentaires sont actuellement désactivés.',
	'ce_cf_already_shown'   => 'Le formulaire de commentaires est déjà affiché dans cette page.',
	'ce_cf_all_not_allowed' => 'Personne n\'est actuellement autorisé à publier des commentaires.',
	'ce_cf_you_not_allowed' => 'Vous n\'êtes pas actuellement autorisé à publier des commentaires. Veuillez d\'abord vous connecter.',

	/* author */
	'ce_cf_author' => 'Auteur:',
	/* rating */
	'ce_cf_article_rating'  => 'Evaluez la qualité de cet article',
	'ce_cf_article_rating2' => '(optionnel)',
	'ce_ce_rating_0'        => 'bon',
	'ce_ce_rating_1'        => 'neutre',
	'ce_ce_rating_2'        => 'mauvais',
	'ce_cf_rating_title_b'  => 'Vous pouvez ajouter une mauvaise évaluation de cet article à votre commentaire en cliquant sur cette icone.',
	'ce_cf_rating_title_n'  => 'Vous pouvez ajouter une évaluation neutre de cet article à votre commentaire en cliquant sur cette icone.',
	'ce_cf_rating_title_g'  => 'Vous pouvez ajouter une bonne évaluation de cet article à votre commentaire en cliquant sur cette icone.',

	/*comments*/
	'ce_cf_comment'            => 'Commentaire',
	'ce_cf_predef'             => 'Entrez votre commentaire ici...',
	'ce_cf_submit_button_name' => 'Ajouter un commentaire',
	'ce_cf_reset_button_name'  => 'Annuler',
	'ce_cf_file_attach'        => 'Pièces jointes article(s):',
	'ce_cf_file_upload_text'   => 'Importer un fichier',
	'ce_cf_file_upload_link'   => 'Importer un fichier',

	/* comment processing */
	'ce_com_cannot_create'               => 'Impossible de créer un commentaire.',
	'ce_com_create_sum'                  => 'Commentaire créé par l\'extension Collaboration.',
	'ce_com_edit_sum'                    => 'Commentaire modifié par l\'extension Collaboration.',
	'ce_comment_exists'                  => 'Le commentaire "$ 1" existe déjà.',
	'ce_com_created'                     => 'Commentaire créé avec succès.',
	'ce_com_edited'                      => 'Commentaire modifié avec succès.',
	'ce_com_edit_not_exists'             => 'Vous avez essayé de modifier un commentaire inexistant. Veuillez ressayer à nouveau.',
	'ce_nothing_deleted'                 => 'Aucun commentaire supprimé.',
	'ce_comment_delete_reason'           => 'Commentaire supprimé via l\'interface graphique de l\'extension Collaboration.',
	'ce_comment_deletion_successful'     => 'Commentaire supprimé avec succès.',
	'ce_comment_massdeletion_successful' => 'Commentaires supprimés avec succès.',
	'ce_comment_deletion_error'          => 'Ce commentaire n\'a pas pu être supprimé.',
	'ce_comment_has_deleted'             => 'supprimer ce commentaire sur',

	/* for scripts */
	'ce_com_default_header'          => 'Ajouter un commentaire',
	'ce_com_ext_header'              => 'Commentaires',
	'ce_invalid'                     => 'Vous n\'avez pas entré un commentaire valide.',
	'ce_reload'                      => ' Rechargement de la page en cours ...',
	'ce_deleting'                    => 'Supression du commentaire en cours ...',
	'ce_full_deleting'               => 'Suppression complète des commentaires en cours ...',
	'ce_delete'                      => 'Voulez-vous vraiment supprimer ce commentaire ?',
	'ce_delete_button'               => 'Supprimer ce commentaire',
	'ce_cancel_button'               => 'Annuler',
	'ce_full_delete'                 => 'Supprimer complètement ce commentaire et toutes les réponses suivantes de ce commentaire.',
	'ce_close_button'                => 'Fermer la fenêtre et rafraîchir la page',
	'ce_delete_title'                => 'Cliquez ici pour supprimer ce commentaire',
	'ce_edit_title'                  => 'Cliquez ici pour modifier ce commentaire',
	'ce_edit_cancel_title'           => 'Cliquez ici pour quitter le mode modification.',
	'ce_reply_title'                 => 'Cliquez ici pour répondre à ce commentaire',
	'ce_com_reply'                   => 'Répondre',
	'ce_edit_rating_text'            => 'Evaluez la qualité de cet article ',
	'ce_edit_rating_text2'           => '(optionel)',
	'ce_edit_button'                 => 'Modifier ce commentaire',
	'ce_com_show'                    => 'Afficher les commentaires',
	'ce_com_hide'                    => 'Masquer les commentaires',
	'ce_com_view'                    => 'Voir',
	'ce_com_view_flat'               => 'Plat',
	'ce_com_view_threaded'           => 'Fil',
	'ce_com_file_toggle'             => 'Voir les pièces jointes',
	'ce_com_rating_text_short'       => 'Qualité Moy. de l\'article',
	'ce_com_rating_text'             => 'Moyenne de la qualité de l\'article basée sur',
	'ce_com_rating_text2'            => 'Evaluation(s).',
	'ce_com_toggle_tooltip'          => 'Cliquez pour afficher/masquer tous les commentaires',
	'ce_form_toggle_tooltip'         => 'Cliquez pour afficher/masquer le formulaire de commentaires.',
	'ce_form_toggle_no_edit_tooltip' => 'Vous n\'êtes pas autorisé à ajouter de nouveaux commentaires',
	'ce_edit_intro'                  => 'Ce commentaire a été modifié en dernier par',
	'ce_edit_date_intro'             => 'le'
);
