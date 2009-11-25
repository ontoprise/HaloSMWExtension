<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the RichMedia-Extension.
*
*   The RichMedia-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The RichMedia-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the settings that configure the upload converter extension.
 * It can convert uploaded PDF and MS Word documents to text.
 * 
 * @author Thomas Schweitzer
 */

global $smwgRMIP;
// Definition of converters for various mime types.

//define upload converters that are executed as external tools
global $smwgUploadConverterExternal;
// check OS is windows?
if (strpos(strtolower(php_uname('s')), "win") !== false) {
	$smwgUploadConverterExternal = array(
		'application/pdf' => "$smwgRMIP/bin/xpdf/pdftotext.exe -enc UTF-8 -layout {infile} {outfile}",
		'application/msword' => $smwgRMIP.'/bin/antiword/antiword.exe -m UTF-8.txt "{infile}" > "{outfile}"'
	);
}
// some Unix flavour
else {
	$smwgUploadConverterExternal = array();
	// check for antiword
	$exe = @exec('which antiword');
	if (strlen($exe) > 0) {
		$smwgUploadConverterExternal['application/msword'] = $exe.' -m '.$smwgRMIP.'/bin/antiword/UTF-8.txt "{infile}" > "{outfile}"';
	}
	$exe = @exec('which pdftotext');
	if (strlen($exe) > 0) {
		$smwgUploadConverterExternal['application/pdf'] = $exe.' -enc UTF-8 -layout "{infile}" "{outfile}"';
	}	
}

//define upload converters that are executed as internal PHP scripts
global $smwgUploadConverterInternal;
$smwgUploadConverterInternal = array(
		'application/vcard' => "UCVCardConverter",
		'application/icalendar' => "UCICalConverter");
		
global $wgAutoloadClasses;
$wgAutoloadClasses['UCVCardConverter'] = 
	$smwgRMIP."/specials/SMWUploadConverter/converters/UC_VCardConverter.php";
$wgAutoloadClasses['UCICalConverter'] = 
	$smwgRMIP."/specials/SMWUploadConverter/converters/UC_ICalConverter.php";

//Define mapping for vCard and iCalendar template
//set parameter name to null if you don't want to use a attribute
global $wgUploadConverterTemplateMapping;
$wgUploadConverterTemplateMapping = array();
$wgUploadConverterTemplateMapping['application/vcard'] = array();
$wgUploadConverterTemplateMapping['application/vcard']['TemplateName'] = "VCard";
$wgUploadConverterTemplateMapping['application/vcard']['N'] = 'N';
$wgUploadConverterTemplateMapping['application/vcard']['FN'] ='FN';
$wgUploadConverterTemplateMapping['application/vcard']['TITLE'] = 'TITLE';
$wgUploadConverterTemplateMapping['application/vcard']['ORG'] = 'ORG'; 
$wgUploadConverterTemplateMapping['application/vcard']['NICKNAME'] = 'NICKNAME';
$wgUploadConverterTemplateMapping['application/vcard']['TEL'] = 'TEL';
$wgUploadConverterTemplateMapping['application/vcard']['EMAIL'] = 'EMAIL';
$wgUploadConverterTemplateMapping['application/vcard']['URL'] = 'URL';
$wgUploadConverterTemplateMapping['application/vcard']['ADR'] = 'ADR';
$wgUploadConverterTemplateMapping['application/vcard']['BDAY'] = 'BDAY';
$wgUploadConverterTemplateMapping['application/vcard']['NOTE'] = 'NOTE';
$wgUploadConverterTemplateMapping['application/vcard']['CATEGORIES'] = 'CATEGORIES';

$wgUploadConverterTemplateMapping['application/icalendar'] = array();
$wgUploadConverterTemplateMapping['application/icalendar']['TemplateName'] = "ICalendar";
$wgUploadConverterTemplateMapping['application/icalendar']['categories'] = 'categories';
$wgUploadConverterTemplateMapping['application/icalendar']['start'] = 'start';
$wgUploadConverterTemplateMapping['application/icalendar']['end'] = 'end';
$wgUploadConverterTemplateMapping['application/icalendar']['summary'] = 'summary'; 
$wgUploadConverterTemplateMapping['application/icalendar']['description'] = 'description'; 
$wgUploadConverterTemplateMapping['application/icalendar']['attendee-name'] = 'attendees';
$wgUploadConverterTemplateMapping['application/icalendar']['organizer-name'] = 'organizer';
$wgUploadConverterTemplateMapping['application/icalendar']['url'] = 'url';
$wgUploadConverterTemplateMapping['application/icalendar']['location'] = 'location';





