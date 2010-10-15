<?php

// ----------------------------------------------------------------------------------
// Class: NTripleSerializer
// ----------------------------------------------------------------------------------


/**
 * PHP N-Triple Serializer
 * 
 * This class serialises models to N-Triple Syntax.
 * 
 *
 * <b>History:</b>
 * <ul>
 * <li>11-15-2003 Initial version</li>
 * </ul>
 *
 *
 * @author Daniel Westphal <mail@d-westphal.de>
 * @version V0.9.1
 * @package syntax
 * @access public
 **/



class NTripleSerializer extends Object {

  var $debug; 
  var $model;
  var $res; 

  
  
  

   /**
    * Constructor
    *
    * @access   public
    */
  function NTripleSerializer() { 
    $this->debug=FALSE;
  }
 
  /**
   * Serializes a model to N Triple syntax.
   *
   * @param     object Model $model
   * @return    string
   * @access    public
   */
  function & serialize(&$m) { 

        if (is_a($m, 'DbModel')) $m = $m->getMemModel();
    
    $this->reset();
    if (!HIDE_ADVERTISE) {
        $this->res .= '# Generated by NTripleSerializer.php from RDF RAP.' .
            LINEFEED . '# http://www.wiwiss.fu-berlin.de/suhl/bizer/rdfapi/index.html'.
            LINEFEED . LINEFEED;
    }

    foreach ($m->triples as $t) { 

      $s=$t->getSubject();
      if (is_a($s, 'Blanknode')) {
      	$subject='_:'.$s->getURI();
      } else {
          $subject = '<' . ereg_replace(' ', '', $s->getURI()) . '>';
      }

	  $p=$t->getPredicate();  
	  $predicate='<'.ereg_replace(' ', '', $p->getURI()).'>';

	  $o=$t->getObject();     
      if (is_a($o, 'literal')) {
	  	$object='"'.$o->getLabel().'"';
	  	if ($o->getLanguage()!='') $object.='@'.$o->getLanguage();		  	
	  	if ($o->getDatatype()!='') $object.='^^<'.$o->getDatatype().">";		  	
	  } elseif (is_a($o, 'Blanknode')) {
	    $object='_:'.$o->getURI();
	  } else {$object='<'.ereg_replace(' ', '', $o->getURI()).'>';};	
		
      $this->res.=$subject.' '.$predicate.' '.$object.' .';
      $this->res.=LINEFEED.LINEFEED;
    }
   
    return $this->res;
  } 
  

/**
 * Serializes a model and saves it into a file.
 * Returns FALSE if the model couldn't be saved to the file.
 *
 * @access	public
 * @param     object MemModel $model
 * @param     string $filename
 * @return    boolean
 * @access    public
 */
 function  saveAs(&$model, $filename) {

   // serialize model
   $n3 = $this->serialize($model);

   // write serialized model to file
   $file_handle = @fopen($filename, 'w');
   if ($file_handle) {
      fwrite($file_handle, $n3);
      fclose($file_handle);
      return TRUE;
   }else{
      return FALSE;
   };
 }
  

  /* ==================== Private Methods from here ==================== */


/**
 * Readies this object for serializing another model
 * @access private
 * @param void
 * @returns void 
 **/
  function reset() {
    $this->res="";
    $this->model=NULL;
  }

}


?>