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
 * @ingroup RichMedia
 * @author Markus Krötzsch
 */

global $smwgRMIP;
include_once($smwgRMIP . '/languages/SMW_RMLanguage.php');

class SMW_RMLanguageFr extends SMW_RMLanguage {
	
	protected $smwUserMessages = array(	
		/* Messages of the Document and Media Ontology */
		
		
		/* Messages for the Media File Upload */
		'smw_rm_formbuttontext' => 'Attacher un fichier',
		'smw_rm_savebuttontext' => 'Transférer un fichier et enregistrer ses méta-données',
	
		'smw_rm_uploadheadline' => 'Transférer des fichiers',
		'smw_rm_uploadtext' => 'Utilisez ce formulaire pour transférer des documents, des images ou des médias, et les attacher à cet article:<br/>',
		'smw_rm_upload_size' => 'Taille <b>maximum</b> des fichiers à transférer:<b> $1 </b>',
		'smw_rm_upload_permtypes' => 'Formats de fichiers autorisés et restrictions de transfert',
		'smw_rm_upload_type_image' => '<li><b>Images: </b>$1',
		//'smw_rm_upload_type_pdf' => '<li><b>Pdf: </b><li>$1',
		'smw_rm_upload_type_doc' => '<li><b>Documents: </b>$1',
		'smw_rm_upload_type_audio' => '<li><b>Audio: </b>$1',
		'smw_rm_upload_type_video' => '<li><b>Video: </b>$1',
		'smw_rm_upload_error_ext_ns'=> 'Il n\'y a pas d\'espace de nom valide défini pour l\'extension de fichier: $1',
		'smw_rm_uploadlegend' => 'Transférer un fichier',
		'smw_rm_dest_file_help_tooltip' => 'Entrer ici le nom du fichier de destination de votre choix. Ce sera donc une partie du nom de l\'article qui sera ensuite publié.',
		'smw_rm_ignore_warning_help_tooltip' => 'Si vous cochez cette case, tous les avertissements (en cas de problème non-fatal comme les différences minuscules-majuscules) au cours du processus de transfert seront ignorés et le fichier sera importé sur le serveur sans aucun message particulier.',
		'smw_rm_sflegend' => 'Méta-informations',
//		'smw_rm_upload-permitted'            => 'Formats de fichiers autorisés: <br>$1.',
//		'smw_rm_upload-preferred'            => 'Formats de fichiers préférés: <br>$1.',
//		'smw_rm_upload-prohibited'           => 'Formats de fichiers interdits: <br>$1.',
		//'smw_rm_upload-maxfilesize'          => 'Maximum $1',
	
		'smw_rm_uploadsuccess_headline' => 'Transfert du fichier réussi !',
		'smw_rm_uploadsuccess_message' => 'Votre fichier sélectionné a été attaché avec succès à l\'article.',
		'smw_rm_uploadsuccess_legend' => 'Détails',
		'smw_rm_uploadsuccess_filename' => '<b>Fichier:</b> $1 <br/>',
		'smw_rm_uploadsuccess_articlename' => '<b>Attaché à l\'article:</b> $1',
		'smw_rm_uploadsuccess_closewindow' => 'Vous pouvez maintenant fermer cette fenêtre.',	
	
		'smw_rm_embed_desc_link' => 'Cliquez en haut de la fenêtre pour visiter la page de description du fichier $1.',
		'smr_rm_embedload' => 'Fichier en cours de transfert ...',
		'smw_rm_noembed' => 'Votre navigateur internet ne supporte pas les objets incorporés de type <code>$1</code>.',
		'smw_rm_embed_notarget' => 'C\'est la visionneuse de fichiers. Vous devez spécifier une cible dans l\'URL ;<br/>comme \'Special:EmbedWindow?target=<strong>Image:Fichier.ext</strong>\'.',
		'smw_rm_embed_save' => 'Enregistrer $1.',
		'smw_rm_embed_view' => 'Afficher',
		'smw_rm_embed_fullres'=> 'Pleine résolution',
		'smw_rm_embed_fittowindow' => 'Ajuster à la fenêtre',
		'smw_rm_embed_desctext' => 'Page de détails du fichier',
		'smw_rm_embed_savetext' => 'Enregistrer',
		
		'smw_rm_wrong_namespace' => 'Vous avez choisi un mauvais espace de nom pour ce fichier. Vouliez-vous dire $1 ?',
		'smw_rm_filenotfound' => 'Fichier non trouvé: $1.',
		'smw_rm_preview'=> 'Afficher dans la visionneuse: '
	);

	protected $smwRMNamespaces = array(
		
	);

	protected $smwRMNamespaceAliases = array(
		 
	);
}


