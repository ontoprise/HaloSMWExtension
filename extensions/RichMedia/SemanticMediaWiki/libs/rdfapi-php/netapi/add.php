<?php

// ----------------------------------------------------------------------------------
// RAP Net API Add Operaton
// ----------------------------------------------------------------------------------

/**
 * The add operation allows the user to add statements to a model on the server.
 *
 * History:<UL>
 * <LI>05-27-2004				 : Success message added by Chris Bizer chris@bizer.de</LI>
 * <LI>05-16-2004                : Initial version by Phil Dawes pdawes@users.sf.net</LI><UL>
 *
 * @version  V0.9.1
 * @author Phil Dawes <pdawes@users.sf.net>
 *
 * @package netapi
 * @todo nothing
 * @access	public
 */

function addStatementsToModel($model,$contenttype,$postdata){
  $p = getParser($contenttype);
  $m = $p->parse2model($postdata);
  $it = $m->getStatementIterator();
  while ($it->hasNext()){
	$statement = $it->next();
	$model->add($statement);
  }
  echo "200 - The data has been added to the model.";
}

?>