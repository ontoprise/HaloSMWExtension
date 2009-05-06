<?php

/**
 * Query Expander augments search terms.
 *
 * @author: Kai Kühn
 *
 * Created on: 27.01.2009
 */
class QueryExpander {
    
    /**
     * Retrieves an title object by first exact matching and then
     * near-matching (if edit distance functions are available)
     *
     * @param string $term
     * @param integer $ns
     * @return Title
     */
    private static function getNearTitle($term, $ns) {
        global $smwgUseEditDistance;
        $title = Title::newFromText($term, $ns);
        $exactMatch = true;
        if (!$title->exists() && $smwgUseEditDistance) {
            $title = USStore::getStore()->getSingleTitle($term, $ns);
            $exactMatch = false;
        }
        return array($title, $exactMatch);
    }
    

    /**
     * Connects terms by the specified operator.
     *
     * @param array $terms Can be titles, property values or strings
     * @param string $operator
     * @return string
     */
    public static function opTerms(array $terms, $operator) {
        $i = 0;
        $connectedParts = 0; // number of _actually_ connected $terms
        $result = "";
        foreach($terms as $t) {
            if ($t == NULL) continue;
            if ($t instanceof Title) {
                if ($i === 0) $result .= self::quoteIfNecessary($t->getText()); else $result .= " $operator ".self::quoteIfNecessary($t->getText());
                $connectedParts++;
            } else if ($t instanceof SMWPropertyValue ) {
                if ($i === 0) $result .= self::quoteIfNecessary($t->getXSDValue()); else $result .= " $operator ".self::quoteIfNecessary($t->getXSDValue());
                $connectedParts++;
            } else if ($t instanceof SMWStringValue ) {
                if ($i === 0) $result .= self::quoteIfNecessary($t->getXSDValue()); else $result .= " $operator ".self::quoteIfNecessary($t->getXSDValue());
                $connectedParts++;
            } else if ($t instanceof SMWWikiPageValue ) {
                if ($i === 0) $result .= self::quoteIfNecessary($t->getXSDValue()); else $result .= " $operator ".self::quoteIfNecessary($t->getXSDValue());
                $connectedParts++;
            } else if (is_string($t) && strlen($t) > 0) {
                if ($i === 0) $result .= self::quoteIfNecessary($t); else $result .= " $operator ".self::quoteIfNecessary($t);
                $connectedParts++;
            }
            $i++;
        }
        return $connectedParts <= 1 ? $result : "(".$result.")";
    }

    private static function quoteIfNecessary($str) {
        $containsOperators = strpos($str, " AND ") || strpos($str, " OR ");
        $isQuoted = (substr($str, 0, 1) == '"' && substr($str, strlen($str)-1, 1) == '"');
        return !$containsOperators && !$isQuoted && strpos($str, " ") !== false ? "\"$str\"" : $str;
    }

    private static function unQuoteIfNecessary($term) {
        if (substr($term, 0, 1) == '"' && substr($term, strlen($term)-1, 1) == '"') {
            return substr($term, 1, strlen($term)-2);

        }
        return $term;
    }
    
    /**
     * Determines which terms are aggregated terms, ie. which belong together.
     * 
     * Returns a value for each terms how often it appears in the set of
     * best matches. 
     *
     * @param array of strings $terms
     * @return hash array (string => int)
     */
    public static function findAggregatedTerms($terms) {
        $titles = USStore::getStore()->getPageTitles($terms);

        $sm = new SmithWaterman();
        $seqB = implode(' ', $terms);
        $maximums = array();
        foreach($titles as $seqA) {

            $matches = $sm->getBestMatches(strtolower($seqA->getText()), $seqB);

            if (count($matches) > 0) {
                $m = trim(reset($matches));
                if (!array_key_exists($m, $maximums)) $maximums[$m] = 1; else $maximums[$m]++;
            }
            
        }
        return $maximums;
    }
}

?>