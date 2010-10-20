<?php

/**
 * @file
 * @ingroup DITIDataAccessLayer
 * This file is based on code from the PHP iCalendar project (http://phpicalendar.net/)
 * which is licenced under GNU General Public License, version 2.
 *
 * @author Ingo Steinbauer
 *
 */

class ICalParserForPOP3 {

	public function getUID($iCalString){
		$lines = nl2br($iCalString);
		$lines = explode("<br />", $lines);
		$iCals = array();
		$tzname = "";

		for($i=0; $i<count($lines); $i++){
			$line = $lines[$i];
			$nextline = $lines[$i+1];
			$nextline = str_replace("\r", "", $nextline);
			$nextline = str_replace("\n", "", $nextline);

			#handle continuation lines that start with either a space or a tab (MS Outlook)
			while (isset($nextline{0}) && ($nextline{0} == " " || $nextline{0} == "\t")) {
				$line = $line . substr($nextline, 1);
				$i += 1;
				$nextline = $lines[$i+1];
				$nextline = str_replace("\r", "", $nextline);
				$nextline = str_replace("\n", "", $nextline);
			}
			$line = str_replace('\n',"\n",$line);
			$line = trim(stripslashes($line));

			switch ($line) {
				case 'BEGIN:VALARM':
					break;
				case 'END:VALARM':
					break;
				case 'BEGIN:VEVENT':
					break;
				case 'END:VEVENT':
					return null;
					break;
				default:
					unset ($field, $data, $prop_pos, $property);
					if (@ ereg ("([^:]+):(.*)", $line, $line)){
						$field = $line[1];
						$data = $line[2];
						$property = strtoupper($field);
						$prop_pos = strpos($property,';');
						if ($prop_pos !== false) $property = substr($property,0,$prop_pos);
							
						if($property == 'UID'){
							return $data;
						}
					}
			}
		}
		return null;
	}
}