<?php
/*  Copyright 2007, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @author Markus Krötzsch
 */

global $smwgRMIP;
include_once($smwgRMIP . '/languages/SMW_RMLanguage.php');

class SMW_RMLanguageDe extends SMW_RMLanguage {
	
	protected $smwUserMessages = array(
		//'specialpages-group-di_group' => 'Data Import',
	
		/* Messages of the Document and Media Ontology */
		
		
		/* Messages for the Media File Upload */
		'smw_rm_formbuttontext' => 'Datei anhängen',
		'smw_rm_savebuttontext' => 'Datei anhängen und Meta-Informationen speichern',
	
		'smw_rm_uploadheadline' => 'Hochladen von Dateien',
		'smw_rm_uploadtext' => 'Benutzen Sie diese Form um Dokumente, Bilder oder Multimediadateien an diesen Artikel anzuhängen.<br/>',
		'smw_rm_upload_size' => '<b>Maximale</b> Dateigröße:<b> $1 </b>',
		'smw_rm_upload_permtypes' => 'Erlaubte Dateitypen:',
		'smw_rm_upload_type_image' => '<li><b>Bilder: </b>$1',
		//'smw_rm_upload_type_pdf' => '<li><b>Pdf: </b><li>$1',
		'smw_rm_upload_type_doc' => '<li><b>Dokumente: </b>$1',
		'smw_rm_upload_type_audio' => '<li><b>Audio: </b>$1',
		'smw_rm_upload_type_video' => '<li><b>Video: </b>$1',
		'smw_rm_upload_error_ext_ns'=> 'Für diese Dateierweiterung ist kein gültiger Namespace vorhanden: $1',
		'smw_rm_uploadlegend' => 'Datei hochladen',
		'smw_rm_dest_file_help_tooltip' => 'In diesem Feld können sie den Zielnamen im Wiki eintragen.',
		'smw_rm_ignore_warning_help_tooltip' => 'Wenn dieses Feld aktiviert ist, wird jede Art von Warnungmeldungen während des Upload-Prozesses ignoriert.',
		'smw_rm_sflegend' => 'Meta-Information',
//		'smw_rm_upload-permitted'            => 'permitted file types: <br>$1.',
//		'smw_rm_upload-preferred'            => 'preferred file types: <br>$1.',
//		'smw_rm_upload-prohibited'           => 'prohibited file types: <br>$1.',
		//'smw_rm_upload-maxfilesize'          => 'Maximum $1',
	
		'smw_rm_uploadsuccess_headline' => 'Upload erfolgreich!',
		'smw_rm_uploadsuccess_message' => 'Ihre ausgewählte Datei wurde erfolgreich an den Artikel angehangen.',
		'smw_rm_uploadsuccess_legend' => 'Details',
		'smw_rm_uploadsuccess_filename' => '<b>Datei:</b> $1 <br/>',
		'smw_rm_uploadsuccess_articlename' => '<b>Artikel:</b> $1',
		'smw_rm_uploadsuccess_closewindow' => 'Sie können jetzt dieses Fenster schließen.',

		'smw_rm_embed_desc_link' => 'Klicken Sie um zur Beschreibungsseite von $1 im Hauptfenster zu gelangen.',
		'smw_rm_noembed' => 'Ihr Browser unterst�tzt keine eingebetteten Objekte.<br>Klicken Sie <a href="$1">hier</a> um die Datei herunterzuladen.',
		'smw_rm_embed_notarget' => 'Dies ist der FileViewer. Sie m�ssen ein Zeilseite in der URL angeben;<br/>z.B. \'Special:EmbedWindow?target=Image:File.ext\'.',
		'smw_rm_embed_save' => '$1 speichern.',
		'smw_rm_embed_view' => 'Ansicht',
		'smw_rm_embed_fullres'=> 'Volle Aufl�sung',
		'smw_rm_embed_fittowindow' => 'An Fenster anpassen',
		'smw_rm_embed_desctext' => 'Beschreibungsseite'
	);

	protected $smwRMNamespaces = array(
		
	);

	protected $smwRMNamespaceAliases = array(
		 
	);
}


