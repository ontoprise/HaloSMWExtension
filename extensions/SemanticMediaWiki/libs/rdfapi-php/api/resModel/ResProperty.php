<?PHP
// ----------------------------------------------------------------------------------
// Class: ResProperty
// ----------------------------------------------------------------------------------

/**
* An RDF Property.
*
* <BR><BR>History:<UL>
* <LI>09-10-2004                : First version of this class.</LI>
*
* @version  V0.9.1
* @author Daniel Westphal <mail at d-westphal dot de>
*
*
* @package resModel
* @access	public
**/
class ResProperty extends ResResource  
{
	
	/**
    * Constructor
	* You can supply a URI
    *
    * @param string $uri 
	* @access	public
    */	
	function ResProperty($uri)
	{
		parent::ResResource($uri);
	}
} 
?>