<?php

// ----------------------------------------------------------------------------------
// RAP Net API Fetch Operaton
// ----------------------------------------------------------------------------------

/**
 * The fetch operation gets all known information about a ressource.
 *
 * History:<UL>
 * <LI>05-16-2004                : Initial version by Phil Dawes pdawes@users.sf.net</LI><UL>
 *
 * @version  V0.9.1
 * @author Phil Dawes <pdawes@users.sf.net>
 *
 * @package netapi
 * @todo nothing
 * @access	public
 */

function fetch($model,$serializer){
  $uri = $_REQUEST['r'];

  $urir = new Resource($uri);

  $outm = new MemModel();

  getBNodeClosure($urir, $model, $outm);

  echo $serializer->Serialize($outm);
  
  $outm->close();
}

function getBNodeClosure($res,$sourcem, &$outm) { 
  $resourcem = $sourcem->find($res,NULL,NULL);
  $it = $resourcem->getStatementIterator();
  while ($it->hasNext()){
	$stmt = $it->next();
	$outm->add(new Statement($res,$stmt->predicate(), $stmt->object()));
	if (is_a($stmt->object(),'BlankNode')){
	  getBNodeClosure($stmt->object(),$sourcem,$outm);
	}
  }  
}

?>