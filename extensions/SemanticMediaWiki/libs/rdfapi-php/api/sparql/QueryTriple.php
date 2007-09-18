<?php
// ---------------------------------------------
// Class: QueryTriple
// ---------------------------------------------
require_once RDFAPI_INCLUDE_DIR . 'sparql/SparqlEngineDb/SqlGenerator.php';

/**
* Represents a query triple.
*
* @author   Tobias Gauss <tobias.gauss@web.de>
* @version	$Id$
*
* @package sparql
*/
Class QueryTriple extends Object{

	/**
	* The QueryTriples Subject.
	*/
	protected $subject;

	/**
	* The QueryTriples Predicate.
	*/
	protected $predicate;

	/**
	* The QueryTriples Object.
	*/
	protected $object;


	/**
	* Constructor
	*/
	public function QueryTriple($sub,$pred,$ob)
	{
		$this->subject   = $sub;
		$this->predicate = $pred;
		$this->object    = $ob;
	}

	/**
	* Returns the Triples Subject.
	*
	* @return Node
	*/
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	* Returns the Triples Predicate.
	*
	* @return Node
	*/
	public function getPredicate()
	{
		return $this->predicate;
	}

	/**
	* Returns the Triples Object.
	*
	* @return Node
	*/
	public function getObject()
	{
		return $this->object;
	}



    /**
    *   Returns an array of all variables in this triple.
    *
    *   @return array   Array of variable names
    */
    public function getVariables()
    {
        $arVars = array();

        foreach (array('subject', 'predicate', 'object') as $strVar) {
            if (SparqlEngineDb_SqlGenerator::isVariable($this->$strVar)) {
                $arVars[] = $this->$strVar;
            }
        }

        return $arVars;
    }

}

// end class: QueryTriple.php
?>