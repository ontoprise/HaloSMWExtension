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
								$iCal['-ic-due'] = $data;
								$iCal['due'] = $this->convertDate($iCal['ic-due']);
								break;
							case 'COMPLETED':
								$iCal['ic-completed'] = $datetime[1];
								$iCal['ic-completed'] = $this->convertDate($iCal['ic-completed']);
								break;
							case 'PRIORITY':
								$iCal['ic-priority'] = "$data";
								break;
							case 'STATUS':
								$iCal['ic-status'] = "$data";
								break;
							case 'GEO':
								$iCal['ic-geo'] = "$data";
								break;
							case 'CLASS':
								$iCal['ic-class'] = "$data";
								break;
							case 'CATEGORIES':
								$iCal['ic-categories'] = "$data";
								break;
								// End VTODO Parsing
							case 'DTSTART':
								$iCal['ic-start'] = $data;
								$iCal['ic-start'] = $this->convertDate($iCal['ic-start']);
								break;
							case 'DTEND':
								$iCal['ic-end'] = $data;
								$iCal['ic-dtend'] = $this->convertDate($iCal['ic-end']);
								break;
							case 'EXDATE':
								$iCal['ic-exdate'] = $data;
								$iCal['ic-exdate'] = $this->convertDate($iCal['exdate']);
								break;
							case 'SUMMARY':
								if ($valarm_set == FALSE) {
									$iCal['ic-summary'] = $data;
								} else {
									$iCal['ic-valarm-summary'] = $data;
								}
								break;
							case 'DESCRIPTION':
								if ($valarm_set == FALSE) {
									$iCal['ic-description'] = $data;
								} else {
									$iCal['ic-valarm-description'] = $data;
								}
								break;
							case 'UID':
								$iCal["ic-uid"] = $data;
								break;
							case 'X-WR-CALNAME':
								$iCal['ic-actual-calname'] = $data;
								break;
							case 'X-WR-TIMEZONE':
								$iCal['ic-x-wr-tz'] = $data;
								break;
							case 'ATTENDEE':
								// if($iCal['attendee'] != ""){
								//	$iCal['attendee'] .= "; ";
								//}
								if(strpos($field, "CN=") > 0){
									if(array_key_exists('ic-attendee-name', $iCal)){
										$iCal['ic-attendee-name'] .= ", ";
									}
									$iCal['ic-attendee-name'] .= ereg_replace (".*CN=([^;]*).*", "\\1", $field);
								}

								if(strpos($data, "mailto:") > 0 || strpos($data, "mailto:") === 0){
									if(array_key_exists('ic-attendee', $iCal)){
										$iCal['ic-attendee'] .= ", ";
									}
									$iCal['ic-attendee'] .= ereg_replace (".*mailto:(.*).*", "\\1", $data);
								}

								//	if(strpos($field, "RSVP=") > 0){
								//		$iCal['attendee'] .= ", ".ereg_replace (".*RSVP=([^;]*).*", "\\1", $field);
								//	}
								//	if(strpos($field, "PARTSTAT=") > 0){
								//		$iCal['attendee'] .= ", ".ereg_replace (".*PARTSTAT=([^;]*).*", "\\1", $field);
								//	}
								//	if(strpos($field, "ROLE=") > 0){
								//		$iCal['attendee'] .= ", ".ereg_replace (".*ROLE=([^;]*).*", "\\1", $field);
								//	}
								break;
							case 'ORGANIZER':
								if(strpos($field, "CN=") > 0){
									if(array_key_exists('ic-organizer-name', $iCal)){
										$iCal['ic-organizer-name'] .= ", ";
									}
									$iCal['ic-organizer-name'] .= ereg_replace (".*CN=([^;]*).*", "\\1", $field);
								}
								if(strpos($data, "mailto:") > 0 || strpos($data, "mailto:") === 0){
									if(array_key_exists('ic-organizer', $iCal)){
										$iCal['ic-organizer'] .= ", ";
									}
									$iCal['ic-organizer'] .= ereg_replace (".*mailto:(.*).*", "\\1", $data);
								}
								
								// if(strpos($field, "RSVP=") > 0){
								//	$iCal['organizer'] .= ", ".ereg_replace (".*RSVP=([^;]*).*", "\\1", $field);
								//}
								//if(strpos($field, "PARTSTAT=") > 0){
								//	$iCal['organizer'] .= ", ".ereg_replace (".*PARTSTAT=([^;]*).*", "\\1", $field);
								//}
								//if(strpos($field, "ROLE=") > 0){
								//	$iCal['organizer'] .= ", ".ereg_replace (".*ROLE=([^;]*).*", "\\1", $field);
								//}
								break;
							case 'URL':
								$iCal['ic-url'] = $data;
								break;
							case 'LOCATION':
								$iCal['ic-location'] = $data;
								break;
							case 'TZNAME':
								if($tzname == ""){
									$tzname = $data;
								}
								$iCal['ic-timezone'] = data;
								break;
							default:
								if(strpos(':',$data) > 1) $iCal['ic-other'] .= $data ."; ";
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