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

class SMW_RMLanguageEn extends SMW_RMLanguage {
	
	protected $smwUserMessages = array(
		//'specialpages-group-di_group' => 'Data Import',
	
		/* Messages of the Document and Media Ontology */
		
		
		/* Messages for the Media File Upload */
		'smw_rm_formbuttontext' => 'Attach file',
		'smw_rm_savebuttontext' => 'Upload file and save metadata',
	
		'smw_rm_uploadheadline' => 'Uploading files',
		'smw_rm_uploadtext' => 'Use this form to upload documents, images or media and to attach it to this article:<br/>',
		'smw_rm_upload_size' => '<b>Maximum</b> upload file size:<b> $1 </b>',
		'smw_rm_upload_permtypes' => 'Permitted file types:',
		'smw_rm_upload_type_image' => '<li><b>Images: </b>$1',
		//'smw_rm_upload_type_pdf' => '<li><b>Pdf: </b><li>$1',
		'smw_rm_upload_type_doc' => '<li><b>Documents: </b>$1',
		'smw_rm_upload_type_audio' => '<li><b>Audio: </b>$1',
		'smw_rm_upload_type_video' => '<li><b>Video: </b>$1',
		'smw_rm_upload_error_ext_ns'=> 'There is no valid Namespace defined for the file extension: $1',
		'smw_rm_uploadlegend' => 'Upload file',
		'smw_rm_dest_file_help_tooltip' => 'Here you can enter the destination filename of your choice. This will also be part of the article name.',
		'smw_rm_ignore_warning_help_tooltip' => 'When checking this any warning (non-fatal conditions, like case-sensitiveness) during the upload process will be ignored and the file is uploaded without any request.',
		'smw_rm_sflegend' => 'Meta information',
//		'smw_rm_upload-permitted'            => 'permitted file types: <br>$1.',
//		'smw_rm_upload-preferred'            => 'preferred file types: <br>$1.',
//		'smw_rm_upload-prohibited'           => 'prohibited file types: <br>$1.',
		//'smw_rm_upload-maxfilesize'          => 'Maximum $1',
	
		'smw_rm_uploadsuccess_headline' => 'Upload successful!',
		'smw_rm_uploadsuccess_message' => 'Your selected file was successfully attached to the article.',
		'smw_rm_uploadsuccess_legend' => 'Details',
		'smw_rm_uploadsuccess_filename' => '<b>File:</b> $1 <br/>',
		'smw_rm_uploadsuccess_articlename' => '<b>Attached to article:</b> $1',
		'smw_rm_uploadsuccess_closewindow' => 'You can now close this window.',	
	
		'smw_rm_embed_desc_link' => 'Click to visit the description page of $1 in top window.',
		'smw_rm_noembed' => 'Your Browser does not support embedded objects.<br>Click <a href="$1">here</a> to download the file.',
		'smw_rm_embed_notarget' => 'This is the FileViewer. You must specify a target in the URL;<br/>like \'Special:EmbedWindow?target=Image:File.ext\'.',
		'smw_rm_embed_save' => 'Save $1.',
		'smw_rm_embed_view' => 'View',
		'smw_rm_embed_fullres'=> 'Full resolution',
		'smw_rm_embed_fittowindow' => 'Fit to window',
		'smw_rm_embed_desctext' => 'Description page',
		'smw_rm_embed_savetext' => 'Save'
	);

	protected $smwRMNamespaces = array(
		
	);

	protected $smwRMNamespaceAliases = array(
		 
	);
}


