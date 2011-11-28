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
 * @ingroup DITIDataAccessLayer
 * This file is based on code from the PHP iCalendar project (http://phpicalendar.net/)
 * which is licenced under GNU General Public License, version 2.
 *
 * @author Ingo Steinbauer
 *
 */

class DIICalParserForPOP3 {

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