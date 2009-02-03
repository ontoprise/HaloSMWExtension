<?php

class SMWStore2Adv extends SMWSQLStore2  {
	protected function getSQLConditions($requestoptions, $valuecol, $labelcol = NULL) {
		$sql_conds = '';
		if ($requestoptions !== NULL) {
			$db =& wfGetDB( DB_SLAVE );
			if ($requestoptions->boundary !== NULL) { // apply value boundary
				if ($requestoptions->ascending) {
					$op = $requestoptions->include_boundary?' >= ':' > ';
				} else {
					$op = $requestoptions->include_boundary?' <= ':' < ';
				}
				$sql_conds .= ' AND ' . $valuecol . $op . $db->addQuotes($requestoptions->boundary);
			}
			$operator = isset($requestoptions->disjunctiveStrings) && $requestoptions->disjunctiveStrings === true ? ' OR ' : ' AND ';
            $neutral = isset($requestoptions->disjunctiveStrings) && $requestoptions->disjunctiveStrings === true ? ' FALSE ' : ' TRUE ';
			if ($labelcol !== NULL) { // apply string conditions
				$sql_conds .= ' AND ( ';
				foreach ($requestoptions->getStringConditions() as $strcond) {
					$string = str_replace('_', '\_', $strcond->string);
					switch ($strcond->condition) {
						case SMWStringCondition::STRCOND_PRE:  $string .= '%'; break;
						case SMWStringCondition::STRCOND_POST: $string = '%' . $string; break;
						case SMWStringCondition::STRCOND_MID:  $string = '%' . $string . '%'; break;
					}
				    if ($requestoptions->isCaseSensitive) {
                        $sql_conds .=  mysql_real_escape_string($labelcol) . ' LIKE ' . $db->addQuotes($string). $operator;
                    } else {
                        $sql_conds .= ' UPPER(' . mysql_real_escape_string($labelcol) . ') LIKE UPPER(' . $db->addQuotes($string).') '.$operator;
                    }
				}
				$sql_conds .= ' '.$neutral.' ) ';
			}
		}
		return $sql_conds;
	}
}
?>