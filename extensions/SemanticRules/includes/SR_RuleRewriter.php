<?php
abstract class RuleRewriter {
    
    /**
     * Rewrites a rule in an arbirary language.
     *
     * @param String $ruletext
     * @return String rewritten $ruletext
     */
    public abstract function rewrite($ruletext);
}
?>