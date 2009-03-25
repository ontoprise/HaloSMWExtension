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
		'smw_rm_formbuttontext' => 'attach document and media',
		'smw_rm_savebuttontext' => 'Upload file and save metadata',
	
		
		'smw_rm_uploadtext'                  => 'Uploading files<br>Use this form to upload documents, images or media and to attach it to this articles:<br/>$1.',
		'smw_rm_upload_size_types' => 'You can upload files with maximum $1 of the following permitted file types:<br> $2',
//		'smw_rm_upload-permitted'            => 'permitted file types: <br>$1.',
//		'smw_rm_upload-preferred'            => 'preferred file types: <br>$1.',
//		'smw_rm_upload-prohibited'           => 'prohibited file types: <br>$1.',
		//'smw_rm_upload-maxfilesize'          => 'Maximum $1',
	
		'smw_rm_uploadsuccesfulltitle' => 'Your upload was successful!',
		'smw_rm_uploadsuccessmessage1' => 'The Upload of $1 was successful.',
		'smw_rm_uploadsuccessmessage2' => '',
		'smw_wws_articles_header' => 'Seiten, die den Web-Service "$1" benutzen',	
	);

	protected $smwRMNamespaces = array(
		
	);

	protected $smwRMNamespaceAliases = array(
		 
	);
}


