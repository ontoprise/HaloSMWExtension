<?php

// ----------------------------------------------------------------------------------
// RDF API for PHP 
// ----------------------------------------------------------------------------------
// Version                   : 0.9.1
// Authors                   : Chris Bizer (chris@bizer.de),
//                             Radoslaw Oldakowski (radol@gmx.de)
// Description               : This file includes constatns and model package.
// ----------------------------------------------------------------------------------
// History:
// 08-09-2004                 : Packages added
// 08-06-2004                 : Class FindIterator added
// 11-19-2003                 : Class RdqlResultIterator added
// 07-31-2003                 : Classes N3Parser, N3Serializer added
// 07-27-2003                 : Classes RdqlParser, RdqlEngine, RdqlDbEngine, RdqlMemEngine added
// 06-25-2003                 : Class Model renamed to MemModel.
//                              Classes Model, DbModel, DbStore added.
//                              ADOdb Classes added
// 02-21-2003				  : Vocabularies and StatementIterator added.
// 09-15-2002                 : Version 0.1 of the API
// ----------------------------------------------------------------------------------


// Include Constants and base Object
require_once( RDFAPI_INCLUDE_DIR . 'constants.php' );
require_once( RDFAPI_INCLUDE_DIR . 'util/Object.php' );
include_once( RDFAPI_INCLUDE_DIR . PACKAGE_MODEL);


?>