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
 	 * Get Gardening issues for a given Title.
 	 * 
 	 * @param $t1 Title issue is about.
 	 * @param $gi_type type of issue.
 	 */
 	public abstract function getGardeningIssues($bot_id, $gi_type = NULL, Title $t1 = NULL);
 	
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
 	
 	/**
 	 * Updates Gardening issue about 2 articles. If no issue exists, it will be created.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $t2 Title 
 	 * @param $value new value.
 	 */
 	public abstract function updateGardeningIssueAboutArticles($bot_id, $gi_type, Title $t1, Title $t2, $value = NULL); 
 	
  	
 	/**
 	 * Updates Gardening issue about an article with a value.  If no issue exists, it will be created.
 	 * 
 	 * @param $gi_type type of issue.
 	 * @param $t1 Title issue is about.
 	 * @param $value new value
 	 */
 	public abstract function updateGardeningIssueAboutValue($bot_id, $gi_type, Title $t1, $value); 
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
	
	public static function createIssueAboutArticles($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
		global $registeredBots;
		$issueClassName = get_class($registeredBots[$bot_id])."Issue";
		return new $issueClassName($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value);
	}
	
	public static function createIssueAboutValue($bot_id, $gi_type, $t1_ns, $t1, $value) {
		global $registeredBots;
		$issueClassName = get_class($registeredBots[$bot_id])."Issue";
		return new $issueClassName($bot_id, $gi_type, $t1_ns, $t1, -1, NULL, $value);
	}
	
	public static function createIssueAboutArticle($bot_id, $gi_type, $t1_ns, $t1) {
		global $registeredBots;
		$issueClassName = get_class($registeredBots[$bot_id])."Issue";
		return new $issueClassName($bot_id, $gi_type, $t1_ns, $t1, -1, NULL, NULL);
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
	 * Returns textual representation of Gardening issue.
	 * 
	 * @param & $skin reference to skin object to create links. 
	 */
	public abstract function getTextualRepresenation(& $skin);
}

/**
 * Abstract class which defines an interface for a GardeningIssue Filter.
 */
abstract class GardeningIssueFilter {
	
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
		 		$html .= "<option value=\"".$bot->getBotID()."\" selected=\"selected\">".$bot->getLabel()."</option>";
			} else {
				$html .= "<option value=\"".$bot->getBotID()."\">".$bot->getLabel()."</option>";
			}
				
		}
 		$html .= 	"</select>";
 		
		$html .= $this->getUserFilterControls($specialAttPage, $request);
 						
 		$html .= 	"<input type=\"submit\" value=\" Go \">";
 		$html .= "</form>";	
 		return $html;
	}
	
	/**
	 * Returns user-defined filtering elements.
	 * 
	 * @param $specialAttPage special page title object
	 * @param $request Request object for accessing URL parameters
	 * 
	 * @return HTML string of form elements
	 */
	protected abstract function getUserFilterControls($specialAttPage, $request);
	
	/**
	 * Returns GardeningIssue objects.
	 * 
	 * @param $options SMWRequestOptions object.
	 * @param $request Request object for accessing URL parameters.
	 * 
	 * @return array of GardeningIssue objects.
	 */
	public abstract function getData($options, $request);
}
?>
