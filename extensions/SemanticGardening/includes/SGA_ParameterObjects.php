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
 * @ingroup SemanticGardening
 * 
 * Created on 28.03.2007
 *
 * @author Kai K�hn
 */
 define('SMW_GARD_PARAM_REQUIRED', 1);
 define('SMW_GARD_PARAM_OPTIONAL' , 2);
 define('SMW_GARD_NO_PARAM', 'no-param');
 
 abstract class GardeningParameterObject {
 	
 	protected $ID;
 	protected $label;
 	protected $options;
 	
 	protected function GardeningParameterObject($ID, $label, $options) {
 		$this->ID = $ID;
 		$this->label = $label;
 		$this->options = $options;
 	}
 	
 	/**
 	 * Validates if the given $value matches the parameter constraints.
 	 */
 	public abstract function validate($value);
 	
 	public abstract function serializeAsHTML();
 	
 	
 	
 	final public function getID() {
 		return $this->ID;
 	}
 	
 	final public function getLabel() {
 		return $this->label;
 	}
 	
 	final public function getOptions() {
 		return $this->options;
 	}
 }
 
 class GardeningParamNumber extends GardeningParameterObject {
 	
 	protected $rangeMin, $rangeMax;
 	
 	public function GardeningParamNumber($ID, $label, $options, $rangeMin, $rangeMax) {
 		parent::GardeningParameterObject($ID, $label, $options);
 		$this->rangeMin = $rangeMin;
 		$this->rangeMax = $rangeMax;
 	}
 	
 	public function validate($value) {
 		if ($value == '' && ($this->options & SMW_GARD_PARAM_REQUIRED) != 0) {
 			return wfMsg('smw_gard_missing_parameter');
 		} else if ($value != null) { 
 			if (!is_numeric($value)) {
 				return wfMsg('smw_gard_value_not_numeric');
 			} else if (($value+0) < $this->rangeMin || ($value+0) > $this->rangeMax) {
 				return wfMsg('smw_out_of_range');
 			}
 		}
 		return true;
 	}
 	
 	public function serializeAsHTML() {
 		$html = "<span id=\"parentOf_".$this->ID."\"><br>".$this->label." (".$this->rangeMin."-".$this->rangeMax.")";
 		$html .= ($this->options & SMW_GARD_PARAM_REQUIRED != 0) ? "*" : "";
 		$html .= ": <input type=\"text\" name=\"".$this->ID."\"/></span>";
 		$html .= "<span id=\"errorOf_".$this->ID."\" class=\"errorText\"></span>";
 		return $html;
 	}
 	
 	
 }
 
 class GardeningParamString extends GardeningParameterObject {
 	protected $defaultValue;
 	
 	
 	public function GardeningParamString($ID, $label, $options, $defaultValue = "") {
 		parent::GardeningParameterObject($ID, $label, $options);
 		$this->defaultValue = $defaultValue;
 	}
 	
 	public function validate($value) {
 		if ($value == '' && ($this->options & SMW_GARD_PARAM_REQUIRED) != 0) {
 			return wfMsg('smw_gard_missing_parameter');
 		} 
 		return true;
 		
 	}
 	
 	public function serializeAsHTML() {
 		$html = "<span id=\"parentOf_".$this->ID."\"><br>".$this->label;
 		$html .= ($this->options & SMW_GARD_PARAM_REQUIRED != 0) ? "*" : "";
 		$html .= ": <input type=\"text\" name=\"".$this->ID."\" size=\"30\" value=\"".$this->defaultValue."\"/></span>";
 		$html .= "<span id=\"errorOf_".$this->ID."\" class=\"errorText\"></span>";
 		return $html;
 	}
 	
 	
 }
 
 class GardeningParamTitle extends GardeningParameterObject {
 	protected $title;
 	protected $ACEnabled;
 	protected $constraints;
 	
 	public function GardeningParamTitle($ID, $label, $options, $title = null) {
 		parent::GardeningParameterObject($ID, $label, $options);
 		$this->defaultValue = $title;
 		$this->constraints = NULL;
 	}
 	
 	/**
 	 * Activates or deactivates AC 
 	 * $enable: true/false
 	 */
 	public function setAutoCompletion($enable) {
 		$this->ACEnabled = $enable;
 	}
 	
 	/**
 	 * Sets constraints. 
 	 */
 	public function setConstraints($constraints) {
 		$this->constraints = $constraints;
 	}
 	
 	public function hasAutoCompletion() {
 		return $this->ACEnabled;
 	}
 	
 	public function validate($value) {
 		//TODO: check if article $value exists
 		if ($value == '' && ($this->options & SMW_GARD_PARAM_REQUIRED) != 0) {
 			return wfMsg('smw_gard_missing_parameter');
 		} 
 		return true;
 	}
 	
 	public function serializeAsHTML() {
 		$html = "<span id=\"parentOf_".$this->ID."\"><br>".$this->label;
 		$html .= ($this->options & SMW_GARD_PARAM_REQUIRED != 0) ? "*" : "";
 		$attributes = ($this->ACEnabled) ? "class=\"wickEnabled\"" : "";
 		$attributes .= " ".(!is_null($this->constraints)) ? "constraints=\"".$this->constraints."\"" : "";
 		$html .= ": <input type=\"text\" name=\"".$this->ID."\" $attributes size=\"30\"/></span>";
 		$html .= "<span id=\"errorOf_".$this->ID."\" class=\"errorText\"></span>";
 		return $html;
 	}
 	
 	
 }
 
 class GardeningParamFile extends GardeningParameterObject {
 	protected $loc;
 	
 	
 	public function GardeningParamFile($ID, $label, $options, $defaultValue = null) {
 		parent::GardeningParameterObject($ID, $label, $options);
 		$this->loc = $defaultValue;
 	}
 	
 	
 	public function validate($value) {
 		//TODO: check if article $value exists
 		if ($value == '' && ($this->options & SMW_GARD_PARAM_REQUIRED) != 0) {
 			return wfMsg('smw_gard_missing_parameter');
 		} 
 		return true;
 	}
 	
 	public function serializeAsHTML() {
 		$html = "<span id=\"parentOf_".$this->ID."\"><br>".$this->label;
 		$html .= ($this->options & SMW_GARD_PARAM_REQUIRED != 0) ? "*" : "";
 		$html .= ": <input type=\"file\" name=\"".$this->ID."\" size=\"50\"/></span>";
 		$html .= "<span id=\"errorOf_".$this->ID."\" class=\"errorText\"></span>";
 		return $html;
 	}
 }
 
 
 class GardeningParamFileList extends GardeningParameterObject {
 	protected $selection;
 	protected $fileExtension;
 	
 	/**
 	 * @param ID
 	 * @param label
 	 * @param options
 	 * @param fileExtension type of files which should be displayed.
 	 * @param defaultSelection selected entry by default, may be omitted. 
 	 */
 	public function GardeningParamFileList($ID, $label, $options, $fileExtension, $defaultSelection = -1) {
 		parent::GardeningParameterObject($ID, $label, $options);
 		$this->selection = $defaultSelection; // no selection by default
 		$this->fileExtension = $fileExtension;
 	}
 	 	
 	public function validate($value) {
 		
 		//$file = wfImageDir($value); old MW 1.10 code
 		
 		// new MW 1.12 code
 		if ($value == NULL || $value == '') return wfMsg('smw_gard_missing_selection');
 		
 		$fileTitle = Title::newFromText($value, NS_FILE);
 		$file = wfFindFile($fileTitle)->getPath();
 	 		
 		$valid = file_exists($file) || ($this->options & SMW_GARD_PARAM_REQUIRED) == 0;
 		if (!$valid) {
 			return wfMsg('smw_gard_missing_selection');
 		} 
 		return true;
 	}
 	
 	public function serializeAsHTML() {
 		$html = "<br>$this->label<span id=\"parentOf_".$this->ID."\">".$this->getUploadedFilesAsHTML()."</span>";
 		$html .= "<span id=\"errorOf_".$this->ID."\" class=\"errorText\"></span>";
 		return $html;
 	}
 	
 	private function getUploadedFilesAsHTML() {
 		$htmlResult = "";
 		$db =& wfGetDB( DB_SLAVE );
		$fname = 'getUploadedFilesAsHTML';
		$ext_cond = array();
		foreach($this->fileExtension as $ext) {
			$ext_cond[] = '(LOWER(img_name) LIKE LOWER('. $db->addQuotes('%.'.$ext).'))'; 
		}
		$res = $db->select( $db->tableName('image'),
		             array('img_name'), array(implode(" OR ",$ext_cond)),
		             $fname, null );
		$result = array();
		if($db->numRows( $res ) > 0)
		{
			$row = $db->fetchObject($res);
			while($row)
			{
				$result[]= $row->img_name;
				$row = $db->fetchObject($res);
			}
		}
		global $wgUser, $wgLang;
		$skin = $wgUser->getSkin();
		$specialPageAliases = $wgLang->getSpecialPageAliases();
		$uploadLink = $skin->makeKnownLinkObj(Title::newFromText($specialPageAliases['Upload'][0], NS_SPECIAL), $specialPageAliases['Upload'][0]);
		if (count($result) == 0) {
			$htmlResult .= '- '.wfMsg('smw_gard_import_nofiles', strtoupper(implode(",",$this->fileExtension))).' - <br>'.wfMsg('smw_gard_import_addfiles', $uploadLink, strtoupper(implode(",",$this->fileExtension)));
		} else {
			$htmlResult .= wfMsg('smw_gard_import_choosefile', strtoupper(implode(",",$this->fileExtension)))." ".wfMsg('smw_gard_import_addfiles', $uploadLink, strtoupper(implode(",",$this->fileExtension))).
							'<div id="gardening-import"><table border="0" cellspacing="0" cellpadding="0">';
			for($i = 0, $n = count($result); $i < $n; $i++) {
				$htmlResult .= '<tr><td width="100px"><input type="radio" name="'.$this->ID.'" value="'.$result[$i].'" '.($this->selection == $i ? "checked=\"checked\"" : "").'/></td>';
				$htmlResult .= '<td width="200px">'.$result[$i].'</td>';
				//$htmlResult .= '<td><button name="remove" type="button">Remove</button></td>';
				$htmlResult .= '</tr>';
			}
			$htmlResult .= '</table></div>';
		}
		$db->freeResult($res);
		return $htmlResult;
 	}
 }
  
 class GardeningParamBoolean extends GardeningParameterObject {
 	protected $defaultChecked;
 	
 	public function GardeningParamBoolean($ID, $label, $options, $defaultValue = false) {
 		parent::GardeningParameterObject($ID, $label, $options);
 		$this->defaultChecked = $defaultValue;
 	}
 	
 	public function validate($value) {
 		if ($value == null && ($this->options & SMW_GARD_PARAM_REQUIRED) != 0) {
 			return wfMsg('smw_gard_missing_parameter');
 		} 
 		return true;
 	}
 	
 	public function serializeAsHTML() {
 		$html = "<span id=\"parentOf_".$this->ID."\"><br>";
 		$req = ($this->options & SMW_GARD_PARAM_REQUIRED != 0) ? "*" : "";
 		$html .= $this->label.$req.": <input type=\"checkbox\" name=\"".$this->ID."\" value=\"".$this->label.$req."\" ".($this->defaultChecked ? "checked=\"checked\"" : "")."/></span>";
 		$html .= "<span id=\"errorOf_".$this->ID."\" class=\"errorText\"></span>";
 		return $html;
 	}
 	
 	
 }
 
 class GardeningParamListOfValues extends GardeningParameterObject {
 	
 	protected $listOfValues;
 	
 	public function GardeningParamListOfValues($ID, $label, $options, $listOfValues) {
 		parent::GardeningParameterObject($ID, $label, $options);
 		$this->listOfValues = $listOfValues;
 	}
 	
 	public function getListOfValues() {
 		return $this->listOfValues;
 	}
 	
 	public function validate($value) {
 		if ($value == null && ($this->options & SMW_GARD_PARAM_REQUIRED) != 0) {
 			return wfMsg('smw_gard_missing_parameter');
 		} else if ($value != null && !in_array($value, $this->listOfValues) && ($this->options & SMW_GARD_PARAM_REQUIRED) != 0) {
 			return wfMsg('smw_unknown_value');
 		}
 		return true;
 	}
 	
 	public function serializeAsHTML() {
 		$html = "<span id=\"parentOf_".$this->ID."\"><br>";
 		$req = ($this->options & SMW_GARD_PARAM_REQUIRED != 0) ? "*" : "";
 		$html .= $this->label.$req.": ";
 		$html .= "<select name=\"".$this->ID."\" size=\"".count($this->listOfValues)."\"/>";
 		foreach ($this->listOfValues as $item) {
 			$html .= "<option>$item</options>";
 		}
 		$html .= "</select></span>";
 		$html .= "<span id=\"errorOf_".$this->ID."\" class=\"errorText\"></span>";
 		return $html;
 	}
 	
 	
 }

