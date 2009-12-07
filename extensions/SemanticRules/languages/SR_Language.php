<?php
/**
 * Abstract Language class for SemanticRules extension
 * 
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
abstract class SR_Language {

    // the message arrays ...
    protected $srContentMessages;
    protected $srUserMessages;
    
    public function getUserMessages() {
    	return $this->srUserMessages;
    }
    
    public function getContentMessages() {
    	return $this->srContentMessages;
    }
}
