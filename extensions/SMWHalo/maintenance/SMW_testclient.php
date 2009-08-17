<?php
  ini_set("soap.wsdl_cache_enabled", "0"); 
 $client = new SoapClient("http://localhost/mediawiki/index.php?action=ajax&rs=srf_ws_getWSDL&rsargs[]=get_flogic");
  $query="FORALL X4, X1, X2, X3 X1[HasFather->X4] <- X1[HasUncle->X2] AND X2[HasSon->X3].";
 // $query="PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX cat: <http://wiki/cats#> PREFIX prop: <http://wiki/props#> SELECT ?x WHERE { ?x rdf:type cat:Functional_group.  ?x prop:hasName ?x. FILTER ?y >= 34 }";
  try {
    $response = $client->parseRule($query);
  } catch(Exception $e) {
    print_r($e);
  }
   $xml = simplexml_load_string($response);
        print_r($xml);


