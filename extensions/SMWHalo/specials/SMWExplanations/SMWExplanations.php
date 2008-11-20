<?php

 if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWExplanations extends SpecialPage {
	
	public function __construct() {
		parent::__construct('Explanations');
		$this->explanation = "";
	}
/*
 * Overloaded function that is resopnsible for the creation of the Special Page
 */
	public function execute($query) {

		global $wgRequest, $wgOut, $smwgHaloScriptPath;
		$wgOut->setPageTitle(wfMsg('smw_explanations'));

		$split = array();
		if (isset($query)) {
			$split = preg_split('/(\/i:|\/p:|\/v:|\/c:|\/mode:)/', '/'.$query, -1, 
			                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		}
		if (count($split) > 0) {
			// Parameters passed in the form /i:<subject>/p:<property>...
			for ($i = 0; $i < count($split); $i+=2) {
				switch ($split[$i]) {
					case '/i:': 
						$instance = $split[$i+1];
						break;
					case '/p:': 
						$property = $split[$i+1];
						break;
					case '/v:': 
						$value = $split[$i+1];
						break;
					case '/c:': 
						$category = $split[$i+1];
						break;
					case '/mode:': 
						$mode = $split[$i+1];
						break;
				}
			}
		} else {
			// parameters passed in the form ?i=<subject>&p=<property>...
			//get request parameters
			$instance = $wgRequest->getVal( 'i' );
			$property = $wgRequest->getVal( 'p' );
			$value = $wgRequest->getVal( 'v' );
			$category = $wgRequest->getVal( 'c' );
			$mode = $wgRequest->getVal('mode');
		}
		$html = "";
		
		if($mode == "category"){
			$html = $this->getGUIHeader($mode, $instance, "", "", $category);
			if ($instance == "" || $category == ""){
				$html .= wfMsg('smw_expl_not_all_inputs');
			} else {
				$query = $this->getCategoryExplanationQuery($instance, $category);
				$explanation = $this->getExplanationForQuery($query);
				$html .= $this->formatExplanation($explanation);
			}
		} else {
			$html = $this->getGUIHeader($mode, $instance, $property, $value, "");
			if ($instance == "" || $property == "" || $value == ""){
				$html .= wfMsg('smw_expl_not_all_inputs');
			} else {
				$query = $this->getPropertyExplanationQuery($instance, $property, $value);
				$explanation = $this->getExplanationForQuery($query);
				$html .= $this->formatExplanation($explanation);
			}
		}
		
		$wgOut->addHTML($html);
	}
	/*
	 * This method is called whenever the Special Page is opened and
	 * either one of the necessary parameters is missing.
	 * Available parameters will be filled into the input fields.
	 */
	
	function getGUIHeader($mode, $i, $p, $v, $c){
		global $smwgHaloScriptPath;
		$imagepath = $smwgHaloScriptPath . '/skins/Explanations';
		$article = wfMsg('article');
		$property = Namespace::getCanonicalName(SMW_NS_PROPERTY);
		$value = wfMsg('smw_expl_value');
		$category = Namespace::getCanonicalName(NS_CATEGORY);
		$explainProp = wfMsg('smw_expl_explain_property');
		$explainCat = wfMsg('smw_expl_explain_category');
		$imgtitle = wfMsg('smw_expl_img');
		$imgstring = <<<IMG
		<image src="$imagepath/cog_go.png" alt="$imgtitle" title="$imgtitle" style="cursor:pointer" onclick="explanation.trigger()"/>
IMG;
		if($mode == "category"){
			$cchecked = "checked";
			$pchecked = "";
			$cdisplay = "";
			$pdisplay = "none";
		} else {
			$cchecked = "";
			$pchecked = "checked";
			$cdisplay = "none";
			$pdisplay = "";
		}
		$html = <<<END
		<div style="background:#F1F7AE; padding:5px"><form>
		<table style="background-color:#F1F7AE;"><tr><td>
		<input type="radio" name="SelectExplanationType" value="Explain property assignment" onclick="explanation.radioChecked()" id="pcheck" $pchecked /></td><td>$explainProp&nbsp;&nbsp;&nbsp;</td>
		<td style="display:$pdisplay" id="propertyrow">
		$article: <input id="instance" class="wickEnabled" type="text" value="$i" typehint="0"/>&nbsp;&nbsp;
		$property: <input id="property" class="wickEnabled" type="text" value="$p" typehint="102"/>&nbsp;&nbsp;
		$value: <input id="value" class="wickEnabled" type="text" value="$v"/>&nbsp;&nbsp;
		$imgstring<br/></td></tr><tr><td>
		<input type="radio" name="SelectExplanationType" value="Explain category assignment" onclick="explanation.radioChecked()" id="ccheck" $cchecked/></td><td>$explainCat&nbsp;&nbsp;&nbsp;</td>
		<td style="display:$cdisplay" id="categoryrow">
		$article: <input id="instance1" class="wickEnabled" type="text" value="$i" typehint="0"/>&nbsp;&nbsp;
		$category: <input id="category" class="wickEnabled" type="text" value="$c" typehint="14"/>&nbsp;&nbsp;
		$imgstring</td></tr></table></form></div><hr/><br/>
END;
		return $html;	
	}
	
	function getPropertyExplanationQuery($i, $p, $v){
		global $smwgNamespace;
		$propertyTitle = Title::newFromText($p, SMW_NS_PROPERTY);

		$isRelation = true;
		// Check if its a page property (relation) or attribute
		$p = $propertyTitle->getDBkey();
		if($propertyTitle->exists()){
			$hasTypeDV = SMWPropertyValue::makeProperty(SMW_SP_HAS_TYPE);
			$typearr = smwfGetStore()->getPropertyValues($propertyTitle, $hasTypeDV);
			print(count($typearr));
			$isRelation = count($typearr) == 0 ? 1 : (count($typearr) == 1) && ($typearr[0]->getXSDValue() == "_wpg");
		}

		$p = str_replace(" ", "_", $p);
		$p = '"' . $smwgNamespace . '/property#"#' . $p;
		
		if($isRelation){
			$valueTitle = Title::newFromText($v);
			if($valueTitle->exists()){
				$ns = $valueTitle->getNamespace();
				switch($ns){
					case NS_CATEGORY:
						$v = '"' . $smwgNamespace . '/category#"#' . $valueTitle->getDBkey();
						break;
					case SMW_NS_PROPERTY:
						$v = '"' . $smwgNamespace . '/property#"#' . $valueTitle->getDBkey();
						break;
					default:
						$v = '"' . $smwgNamespace . '/a#"#' . $valueTitle->getDBkey();
				}
			} else {
				$v = '"' . $smwgNamespace . '/a#"#' . $v;
			}
		} else {
			if(!is_numeric($v))
				$v = '"' . $v . '"';
		}
		
		$instanceTitle = Title::newFromText($i);
		if($instanceTitle->exists()){
			$ns = $instanceTitle->getNamespace();
			switch($ns){
				case NS_CATEGORY:
					$i = '"' . $smwgNamespace . '/category#"#' . $instanceTitle->getDBkey();
					break;
				case SMW_NS_PROPERTY:
					$i = '"' . $smwgNamespace . '/property#"#' . $instanceTitle->getDBkey();
					break;
				default:
					$i = '"' . $smwgNamespace . '/a#"#' . $instanceTitle->getDBkey();
			}
		} else {
			$i = '"' . $smwgNamespace . '/a#"#' . $i;
		}
	
		$query = "FORALL X,Y <- " . ($isRelation?("attr_(X, $p, Y) "):("attl_(X, $p, Y, \"\") ")) .  "AND equal(X,$i) AND equal(Y,$v).;explain";
		return $query;
	}
	
	function getCategoryExplanationQuery($i, $c){
		global $smwgNamespace;
		$category = Title::newFromText($c, NS_CATEGORY);
		$instance = Title::newFromText($i);
		if ($category->exists())
			$category = $category->getDBkey();
		if ($instance->exists())
			$instance = $instance->getDBkey();
		$category = str_replace(" ", "_", $category);
		$instance = str_replace(" ", "_", $instance);
	
		$query = 'FORALL X <- isa_(X, "' . $smwgNamespace . '/category#"#' . $c .') AND equal(X,"' . $smwgNamespace . '/a#"#' . $instance . ').;explain';
		return $query;
	}
	
	function getExplanationForQuery($query){
		try {
			global $wgServer, $wgScript, $smwgNamespace, $smwgWebserviceUser, $smwgWebServicePassword;
			ini_set("soap.wsdl_cache_enabled", "0");  //set for debugging
			$_client = new SoapClient("$wgServer$wgScript?action=ajax&rs=smwf_ws_getWSDL&rsargs[]=get_explanation", array('login'=>$smwgWebserviceUser, 'password'=>$smwgWebServicePassword));          
	 		$explanation = $_client->explain($smwgNamespace, $query);
			
	 		if($explanation == "-1"){
	 			$explanation = "No explanation could be provided. Please check if all inputs are correct.";
	 		}
			return $explanation;
		} catch (Exception $e){
			return wfMsg('smw_expl_error') . "<br/><br/><i>" . $e . "</i>";
		}
	}
	
	function formatExplanation($exp){
		$exp = str_replace("BECAUSE", wfMsg('smw_expl_because') . "<br/>", $exp);
		$exp = str_replace("AND", wfMsg('smw_expl_and') . "<br/>", $exp);
		return $exp;
	}

}
?>