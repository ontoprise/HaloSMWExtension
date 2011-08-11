<?PHP
/**
* ----------------------------------------------------------------------------------
* Class: Individual
* ----------------------------------------------------------------------------------
*
* @package 	ontModel
*/


/**
* Interface that encapsulates an individual in an ontology, sometimes referred
* to as a fact or assertion.
* In order to be recognised as an individual, rather than a generic resource, 
* at least one rdf:type statement, referring to a known class, must be present 
* in the model.
*
* <BR><BR>History:
* <LI>10-05-2004                : First version of this class.</LI>
*
* @version  V0.9.1
* @author Daniel Westphal <mail at d-westphal dot de>
*
*
* @package 	ontModel
* @access	public
**/	

class Individual extends OntResource   
{
	
	/**
    * Constructor
	* You can supply a uri
    *
    * @param string $uri 
	* @access	public
    */		
	function Individual($uri = null)
	{
		parent::OntResource($uri);
	}
} 
?>