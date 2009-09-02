<?php

/**
 * This file is based on code from the PHP iCalendar project (http://phpicalendar.net/)
 * which is licenced under GNU General Public License, version 2. 
 * 
 * @author Ingo Steinbauer
 *
 */

class ICalParser {

	public function parse($iCalString){
		$lines = nl2br($iCalString);
		$lines = explode("<br />", $lines);
		$iCals = array();

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
					$valarm_set = TRUE;
					break;
				case 'END:VALARM':
					$valarm_set = FALSE;
					break;
				case 'BEGIN:VEVENT':
					$iCal = array();
					break;
				case 'END:VEVENT':
					$iCals[] = $iCal;
					break;
				default:
					unset ($field, $data, $prop_pos, $property);
					if (ereg ("([^:]+):(.*)", $line, $line)){
						$field = $line[1];
						$data = $line[2];
						$property = strtoupper($field);
						$prop_pos = strpos($property,';');
						if ($prop_pos !== false) $property = substr($property,0,$prop_pos);
							
						switch ($property) {
							// Start VTODO Parsing
							case 'DUE':
								$iCal['due'] = $data;
								$iCal['due'] = $this->convertDate($iCal['due']);
								break;
							case 'COMPLETED':
								$iCal['completed'] = $datetime[1];
								$iCal['completed'] = $this->convertDate($iCal['completed']);
								break;
							case 'PRIORITY':
								$iCal['priority'] = "$data";
								break;
							case 'STATUS':
								$iCal['status'] = "$data";
								break;
							case 'GEO':
								$iCal['geo'] = "$data";
								break;
							case 'CLASS':
								$iCal['class'] = "$data";
								break;
							case 'CATEGORIES':
								$iCal['categories'] = "$data";
								break;
								// End VTODO Parsing
							case 'DTSTART':
								$iCal['dtstart'] = $data;
								$iCal['dtstart'] = $this->convertDate($iCal['dtstart']);
								break;
							case 'DTEND':
								$iCal['dtend'] = $data;
								$iCal['dtend'] = $this->convertDate($iCal['dtend']);
								break;
							case 'EXDATE':
								$iCal['exdate'] = $data;
								$iCal['exdate'] = $this->convertDate($iCal['exdate']);
								break;
							case 'SUMMARY':
								if ($valarm_set == FALSE) {
									$iCal['summary'] = $data;
								} else {
									$iCal['valarm_summary'] = $data;
								}
								break;
							case 'DESCRIPTION':
								if ($valarm_set == FALSE) {
									$iCal['description'] = $data;
								} else {
									$iCal['valarm_description'] = $data;
								}
								break;
							case 'UID':
								$iCal["uid"] = $data;
								break;
							case 'X-WR-CALNAME':
								$iCal['actual_calname'] = $data;
								break;
							case 'X-WR-TIMEZONE':
								$iCal['calendar_tz'] = $data;
								break;
							case 'ATTENDEE':
								if($iCal['attendee'] != ""){
									$iCal['attendee'] .= "; ";
								}
								if(strpos($field, "CN=") > 0){
									$iCal['attendee'] .= ereg_replace (".*CN=([^;]*).*", "\\1", $field);
								}
								if(strpos($data, "mailto:") > 0){
									$iCal['attendee'] .= ", ".ereg_replace (".*mailto:(.*).*", "\\1", $data);
								}
								if(strpos($field, "RSVP=") > 0){
									$iCal['attendee'] .= ", ".ereg_replace (".*RSVP=([^;]*).*", "\\1", $field);
								}
								if(strpos($field, "PARTSTAT=") > 0){
									$iCal['attendee'] .= ", ".ereg_replace (".*PARTSTAT=([^;]*).*", "\\1", $field);
								}
								if(strpos($field, "ROLE=") > 0){
									$iCal['attendee'] .= ", ".ereg_replace (".*ROLE=([^;]*).*", "\\1", $field);
								} 
								break;
							case 'ORGANIZER':
								if($iCal['organizer'] != ""){
									$iCal['organizer'] .= "; ";
								}
							if(strpos($field, "CN=") > 0){
									$iCal['organizer'] .= ereg_replace (".*CN=([^;]*).*", "\\1", $field);
								}
								if(strpos($data, "mailto:") > 0){
									$iCal['organizer'] .= ", ".ereg_replace (".*mailto:(.*).*", "\\1", $data);
								}
								if(strpos($field, "RSVP=") > 0){
									$iCal['organizer'] .= ", ".ereg_replace (".*RSVP=([^;]*).*", "\\1", $field);
								}
								if(strpos($field, "PARTSTAT=") > 0){
									$iCal['organizer'] .= ", ".ereg_replace (".*PARTSTAT=([^;]*).*", "\\1", $field);
								}
								if(strpos($field, "ROLE=") > 0){
									$iCal['organizer'] .= ", ".ereg_replace (".*ROLE=([^;]*).*", "\\1", $field);
								}
								break;
							case 'URL':
								$iCal['url'] = $data;
								break;
							default:
								if(strpos(':',$data) > 1) $iCal['other'] .= $data ."; ";
						}
					}
			}
		}
		return $iCals;
	}
	
	function convertDate($date){
		$year = $date[0].$date[1].$date[2].$date[3];
		$mon = $date[4].$date[5];
		$mday = $date[6].$date[7];
		$hours = $date[9].$date[10];
		$minutes = $date[11].$date[12];
		$seconds = $date[13].$date[14];

		return $year."/".$mon."/".$mday." "
				.$hours.":".$minutes.":".$seconds;
	}
}
?>