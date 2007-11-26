<?php
/*
 * Created on 22.11.2007
 *
 * Author: kai
 */
 class SMWRefactorStatistics extends SpecialPage {
	
	public function __construct() {
		parent::__construct('RefactorStatistics');
	}
	public function execute() {
		global $wgRequest, $wgOut;
		$specialAttPage = Title::newFromText('RefactorStatistics', NS_SPECIAL);
		$wgOut->setPageTitle(wfMsg('refactorstatistics'));
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
					'<table border="0">'.
					'<tr><td width="120px">Page:</td><td><input type="text" size="45" class="wickEnabled" pastens="true" name="entitiytitle"/></td></tr>' .
					'</table>' .
					'<input type="submit" value="Show Statistics"/>' .
				'</form>';
	}
	private function showResults() {
		global $wgRequest;
		$title = $wgRequest->getVal('entitiytitle');
		if ($title == NULL) return "";
		$op = new RefactorOperation(Title::newFromText(urldecode($title)));
		
		return $op->getStatisticsAsHTML();
	}
 } 

  
 class RefactorOperation {
 	
 	protected $title;
 	
 	private $numOfInstances = -1;
 	private $numOfCategories = -1;
 	private $numOfProperties = -1;
 	private $numberOfUsages = -1;
 	private $numOfTargets = -1;
 	
 	public function __construct($title) {
 		$this->title = $title;
 	}
 	
 	private function getStatistics() {
 		
 		switch($this->title->getNamespace()) {
 			case NS_CATEGORY: {
		 		list($this->numOfInstances, $this->numOfCategories) = smwfGetSemanticStore()->getNumberOfInstancesAndSubcategories($this->title);
		 		$this->numOfProperties = smwfGetSemanticStore()->getNumberOfProperties($this->title);
 				break;
 			}
 			case SMW_NS_PROPERTY: {
 				$this->numberOfUsages = smwfGetSemanticStore()->getNumberOfUsage($this->title);
 				break;
 			}
 			case NS_MAIN: {
 				$this->numOfTargets = smwfGetSemanticStore()->getNumberOfPropertiesForTarget($this->title);
 				break;
 			}
 			case NS_TEMPLATE: {
 				$this->numberOfUsages = smwfGetSemanticStore()->getNumberOfUsage($this->title);
 				break;
 			}
 		}
 	}
 	public function getStatisticsAsHTML() {
 		$this->getStatistics();
 		
 		$tableContent = "";
 		
 		if ($this->numOfInstances != -1) {
 			$tableContent .= '<tr><td>Number of instances</td><td>'.$this->numOfInstances.'</td></tr>';
 		}
 		if ($this->numOfCategories != -1) {
 			$tableContent .= '<tr><td>Number of subcategories</td><td>'.$this->numOfCategories.'</td></tr>';
 		}
 		if ($this->numOfProperties != -1) {
 			$tableContent .= '<tr><td>Number of properties</td><td>'.$this->numOfProperties.'</td></tr>';
 		}
 		if ($this->numberOfUsages != -1) {
 			$tableContent .= '<tr><td>Number of usage</td><td>'.$this->numberOfUsages.'</td></tr>';
 		}
 		if ($this->numOfTargets != -1) {
 			$tableContent .= '<tr><td>Number of targets</td><td>'.$this->numOfTargets.'</td></tr>';
 		}
 		return '<table border="0">'.$tableContent.'</table>';
 		
 	}
 }	
 
 
?>
