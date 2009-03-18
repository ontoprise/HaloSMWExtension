<?php
/*
 * Created on 28.03.2007
 *
 * Author: kai
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
 	protected $typeHint;
 	
 	public function GardeningParamTitle($ID, $label, $options, $title = null) {
 		parent::GardeningParameterObject($ID, $label, $options);
 		$this->defaultValue = $title;
 		$this->typeHint = -1;
 	}
 	
 	/**
 	 * Activates or deactivates AC 
 	 * $enable: true/false
 	 */
 	public function setAutoCompletion($enable) {
 		$this->ACEnabled = $enable;
 	}
 	
 	/**
 	 * Sets typeHint (=NAMESPACE). Only entities of this type
 	 * are returned.
 	 */
 	public function setTypeHint($typeHint) {
 		$this->typeHint = $typeHint;
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
 		$attributes .= " ".($this->typeHint != -1) ? "typeHint=\"".$this->typeHint."\"" : "";
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
 		
 		$fileTitle = Title::newFromText($value);
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
		$res = $db->select( $db->tableName('image'),
		             array('img_name'), array('LOWER(img_name) LIKE LOWER('. $db->addQuotes('%.'.$this->fileExtension).')' ),
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
			$htmlResult .= '- '.wfMsg('smw_gard_import_nofiles', strtoupper($this->fileExtension)).' - <br>'.wfMsg('smw_gard_import_addfiles', $uploadLink, strtoupper($this->fileExtension));
		} else {
			$htmlResult .= wfMsg('smw_gard_import_choosefile', strtoupper($this->fileExtension))." ".wfMsg('smw_gard_import_addfiles', $uploadLink, strtoupper($this->fileExtension)).
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
 		} else if (value != null && !in_array($value, $this->listOfValues) && ($this->options & SMW_GARD_PARAM_REQUIRED) != 0) {
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
?>
