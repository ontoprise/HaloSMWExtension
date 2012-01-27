<?php
abstract class SRFInstanceLevelOperation extends SRFRefactoringOperation {

	// set of instances this operation is about
	protected $instanceSet;

	public function __construct($instanceSet) {
		parent::__construct();
		foreach($instanceSet as $i) {
			$this->instanceSet[] = Title::newFromText($i);
		}
	}

	/**
	 * Applies and stores the changes of this refactoring operation.
	 * (not used if several operations are combined, see applyOperation)
	 *
	 * (non-PHPdoc)
	 * @see extensions/SemanticRefactoring/includes/SRFRefactoringOperation::refactor()
	 */
	public function refactor($save = true, & $logMessages) {
		SRFRefactoringOperation::applyOperations($save, $this->instanceSet, $this, $logMessages);
	}

	/**
	 * Applies the operation and returns the changed wikitext.
	 *
	 * @param Title $title
	 * @param string $wikitext
	 * @param array $logMessages
	 *
	 * @return string
	 */
	public abstract function applyOperation($title, $wikitext, & $logMessages);
}