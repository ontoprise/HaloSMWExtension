<?php
/*
 * Created on 23.06.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class SMWExplanationLiteral {

    private $_flogic;
    private $_text;
    private $_isConceptLiteral;

    public function SMWExplanationLiteral($flogic, $text, $isConceptLiteral){
        $this->setFlogic($flogic);
        $this->setText($text);
        $this->setConceptLiteral($isConceptLiteral);
    }

    public function getFlogic() {
        return $this->_flogic;
    }

    public function setFlogic($flogic) {
        $this->_flogic = $flogic;
    }

    public function getText() {
        return $this->_text;
    }

    public function setText($text) {
        $this->_text = $text;
    }
    public function isConceptLiteral() {
        return $this->_isConceptLiteral;
    }
    public function setConceptLiteral($isConceptLiteral) {
        $this->_isConceptLiteral = $isConceptLiteral;
    }
}
?>
