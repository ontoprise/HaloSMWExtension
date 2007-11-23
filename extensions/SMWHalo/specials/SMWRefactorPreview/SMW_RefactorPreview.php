<?php
/*
 * Created on 22.11.2007
 *
 * Author: kai
 */
 class SMWRefactorPreview extends SpecialPage {
	
	public function __construct() {
		parent::__construct('RefactorPreview');
	}
	public function execute() {
		global $wgRequest, $wgOut;
		$specialAttPage = Title::newFromText('RefactorPreview', NS_SPECIAL);
		$wgOut->setPageTitle(wfMsg('refactorpreview'));
		$html = "<div id=\"header\">".$this->showHeader()."</div>";
		$html .= "<div id=\"menu\">".$this->showOperationMenu($specialAttPage)."</div>";
		$html .= "<div id=\"refactorpreviewresults\">".$this->showResults()."</div>";
		$wgOut->addHTML($html);
	}
	
	private function showHeader() {
		return 'This page shows statistics of the usage of a pages. ' .
				'It gives hints how complex a refactoring operation might get and ' .
				'what page should be refactored at all.';
	}
	
	private function showOperationMenu($specialAttPage) {
		
		return '<form action="'.$specialAttPage->getFullURL().'">' .
					'<table border="0"><tr><td width="120px">Choose operation:</td><td><select name="operation" id="operations">' .
						'<option>Remove</option>' .
						'<option>Rename</option>' .
						'<option>Join</option>' .
					'</select></td></tr>' .
					'<tr><td width="120px">Page:</td><td><input type="text" size="45" class="wickEnabled" pastens="true" name="entitiytitle"/></td></tr>' .
					'</table>' .
					'<input type="submit" value="Show Preview"/>' .
				'</form>';
	}
	private function showResults() {
		global $wgRequest;
		$title = $wgRequest->getVal('entitiytitle');
		if ($title == NULL) return "";
		$op = new RemoveOperation(Title::newFromText(urldecode($title)));
		
		return $op->getStatisticsAsHTML();
	}
 } 

 abstract class RefactorOperation {
 	
 	protected $title;
 	
 	public function __construct($title) {
 		$this->title = $title;
 	}
 	
 	public abstract function getStatisticsAsHTML();
 }
 
 class RemoveOperation extends RefactorOperation {
 	
 	private $numOfInstances;
 	private $numOfProperties;
 	
 	public function __construct($title) {
 		parent::__construct($title);
 	}
 	
 	private function getStatistics() {
 		
 		switch($this->title->getNamespace()) {
 			case NS_CATEGORY: {
		 		$this->numOfInstances = smwfGetSemanticStore()->getNumberOfInstances($this->title);
		 		$this->numOfProperties = smwfGetSemanticStore()->getNumberOfProperties($this->title);
 				break;
 			}
 			case SMW_NS_PROPERTY: {
 				break;
 			}
 			case NS_MAIN: {
 				break;
 			}
 			case NS_TEMPLATE: {
 				break;
 			}
 		}
 	}
 	public function getStatisticsAsHTML() {
 		$this->getStatistics();
 		return "Number of instances: ".$this->numOfInstances." Number of Properties: ".$this->numOfProperties;
 	}
 }	
 
 class RenameOperation extends RefactorOperation {
 	public function getStatisticsAsHTML() {
 		return "";
 	}
 }
 
 class JoinOperation extends RefactorOperation {
 	public function getStatisticsAsHTML() {
 		return "";
 	}
 }
?>
