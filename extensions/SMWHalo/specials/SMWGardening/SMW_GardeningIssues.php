<?php
/*
 * Created on 18.10.2007
 *
 * Author: kai
 * 
 * Provide access to Gardening issue table. Gardening issues can be categorized:
 * 
 * 	- covariance
 * 	- not defined
 * 	- missing / doubles
 * 	- wrong value / entity
 * 	- incompatible entity
 * 
 */
 
 define('SMW_GARDENINGLOG_SORTFORTITLE', 0);
 define('SMW_GARDENINGLOG_SORTFORVALUE', 1);
 
 /**
  * Abstract class to access GardeningIssue store.
  */
 abstract class SMWGardeningIssuesAccess {
 	
 	
 	/**
 	 * Setups GardeningIssues table(s).
 	 */
 	public abstract function setup($verbose);
 	
 	/**
 	 * Clear all Gardening issues
 	 * 
 	 * @param if not NULL, clear only this type of issue. Otherwise all.
 	 */
 	public abstract function clearGardeningIssues($bot_id = NULL, Title $t1 = NULL);
 	
 	/**
 	 * Get Gardening issues. Every parameter may be NULL.
 	 * 
 	 * @param $bot_id Bot-ID
 	 * @param $options instance of SMWRequestOptions
 	 * @param $gi_type type of issue. (Can be an array!)
 	 * @param $gi_class type of class of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $sortfor column to sort for. Default by title.
 	 * 				One of the constants: SMW_GARDENINGLOG_SORTFORTITLE, SMW_GARDENINGLOG_SORTFORVALUE 
 	 */
 	public abstract function getGardeningIssues($bot_id = NULL, $options = NULL, $gi_type = NULL, $gi_class = NULL, Title $t1 = NULL,  $sortfor = NULL);
 	
 	/**
 	 * Add Gardening issue about articles.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $t2 Title 
 	 * @param $value optional value. Depends on $gi_type
 	 */
 	public abstract function addGardeningIssueAboutArticles($bot_id, $gi_type, Title $t1, Title $t2, $value = NULL);
 	
 	/**
 	 * Add Gardening issue about an article.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 */
 	public abstract function addGardeningIssueAboutArticle($bot_id, $gi_type, Title $t1);
 	
 	/**
 	 * Add Gardening issue about values.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $value Depends on $gi_type
 	 */
 	public abstract function addGardeningIssueAboutValue($bot_id, $gi_type, Title $t1, $value);
 	
 	 
 }

/**
 * Simple record class to store a Gardening issue.
 * 
 * @author kai
 */
abstract class GardeningIssue {
	
	protected $bot_id;
	protected $gi_type;
	protected $t1;
	protected $t2;
	protected $value;
	
	protected $subIssues = NULL;
	
	protected function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
		$this->bot_id = $bot_id;
		$this->gi_type = $gi_type;
		if ($t1_ns != -1 && $t1 != NULL && $t1 != '') {
			$this->t1 = Title::newFromText($t1, $t1_ns);
		}
		if ($t2_ns != -1 && $t2 != NULL && $t2 != '') {
			$this->t2 = Title::newFromText($t2, $t2_ns);
		}
		$this->value = $value;
	}
	
	/**
	 * Creates an issue depending of the $bot_id
	 * 
	 * @return instance of subclass of GardeningIssue.
	 */ 
	public static function createIssue($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
		global $registeredBots;
		$issueClassName = get_class($registeredBots[$bot_id])."Issue";
		return new $issueClassName($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value);
	}
	
	
	public function getBotID() {
		return $this->bot_id;
	}
	
	public function getType() {
		return $this->gi_type;
	}
	
	public function getTitle1() {
		return $this->t1;
	}
	
	public function getTitle2() {
		return $this->t2;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * Converts a semicolon separated list of Title strings
	 * to a comma separated list of displayable links.
	 * 
	 * @param & $skin Current skin object.
	 * @param $value List of semicolon separated titles.
	 * 
	 * @return comma separated list of displayable links (HTML string)
	 */
	protected function explodeTitlesToLinkObjs(& $skin, $value) {
 		$titleNames = explode(';', $value);
 		$result = "";
 		foreach($titleNames as $tn) {
 			$title = Title::newFromText($tn);
 			if ($title != NULL) $result .= $skin->makeLinkObj($title).", ";
 		}
 		return substr($result, 0, strlen($result)-2);
 	}
	
	
	public function getRepresentation(& $skin) {
		if ($this->subIssues == NULL) {
			return $this->getTextualRepresenation($skin);
		} else {
			return $this->getTextualRepresentationForSubIssues($skin);
		}
	}
	
	/**
	 * Add group of subIssues. Informal contract is that all members 
	 * of the group should have the same title1 ID.
	 */
	public function addSubIssues(array & $subIssues) {
		$this->subIssues = $subIssues;
	}
	
	/**
	 * Returns textual representation of Gardening issue.
	 * 
	 * @param & $skin reference to skin object to create links. 
	 */
	protected abstract function getTextualRepresenation(& $skin);
	
	/**
	 * Returns textual representation of a group of Gardening issues.
	 * 
	 * 
	 * @param & $skin reference to skin object to create links. 
	 */
	protected function getTextualRepresentationForSubIssues(& $skin) {
		return NULL;
	}
}

/**
 * Abstract class which defines an interface for a GardeningIssue Filter.
 */
abstract class GardeningIssueFilter {
	
	protected $gi_issue_classes;
	
	/**
	 * Returns array of strings representing the gardening issue classes.
	 * The index is used to access the issue class.
	 * 
	 * @return array of strings
	 */
	public function getIssueClasses() {
 		return $this->gi_issue_classes;
 	}
 	
	/**
	 * Returns filtering FORM.
	 * 
	 * @param $specialAttPage special page title object
	 * @param $request Request object for accessing URL parameters
	 * 
	 * @return HTML string of form.
	 */
	public function getFilterControls($specialAttPage, $request) {
		global $registeredBots;
		$html = "<form action=\"".$specialAttPage->getFullURL()."\">";
		
		$html .= "<select name=\"bot\">";
		
		$sent_bot_id = $request->getVal('bot');
		foreach($registeredBots as $bot_id => $bot) {
			if ($sent_bot_id == $bot_id) {
		 		$html .= "<option value=\"".$bot->getBotID()."\" selected=\"selected\" onclick=\"gardeningLogPage.selectBot('".$bot->getBotID()."')\">".$bot->getLabel()."</option>";
			} else {
				$html .= "<option value=\"".$bot->getBotID()."\" onclick=\"gardeningLogPage.selectBot('".$bot->getBotID()."')\">".$bot->getLabel()."</option>";
			}
				
		}
 		$html .= 	"</select>";
 		
 		// type of Gardening issue
		$type = $request->getVal('class');
 		$html .= "<span id=\"issueClasses\"><select name=\"class\">";
		$i = 0;
		foreach($this->getIssueClasses() as $class) {
			if ($i == $type) {
		 		$html .= "<option value=\"$i\" selected=\"selected\">$class</option>";
			} else {
				$html .= "<option value=\"$i\">$class</option>";
			}
			$i++;		
		}
 		$html .= 	"</select>";
 		
		$html .= $this->getUserFilterControls($specialAttPage, $request);
 		$html .= "</span>";	
 		$html .= "<input type=\"submit\" value=\" Go \">";
 		$html .= "</form>";	
 		return $html;
	}
	
	/**
	 * Returns associative array of additional parameters to link.
	 * 
	 * @param array(parameter => value)
	 */
	public function linkUserParameters(& $wgRequest) {
		return array();
	}
	
	
	/**
	 * Returns user-defined filtering elements.
	 * 
	 * @param $specialAttPage special page title object. May be NULL (ajax)
	 * @param $request Request object for accessing URL parameters. May be NULL (ajax)
	 * 
	 * @return HTML string of form elements
	 */
	public abstract function getUserFilterControls($specialAttPage, $request);
	
	/**
	 * Returns GardeningIssue objects.
	 * 
	 * @param $options SMWRequestOptions object.
	 * @param $request Request object for accessing URL parameters.
	 * 
	 * @return array of GardeningIssue objects.
	 */
	public function getData($options, $request) {
		$bot = $request->getVal('bot');
		if ($bot == NULL) return array(); 
		
		$type = $request->getVal('class');
		
		$gi_store = SMWGardening::getGardeningIssuesAccess();
		return $gi_store->getGardeningIssues($bot, $options, NULL, $type == 0 ? NULL : $type);
	}
}
?>
