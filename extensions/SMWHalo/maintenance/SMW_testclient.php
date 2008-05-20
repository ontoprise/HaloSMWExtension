<?php
  ini_set("soap.wsdl_cache_enabled", "0"); 
  $client = new SoapClient("http://localhost/develwiki/index.php?action=get_wsdl");
  $query="[[Category:GardeningLog]]";
 // $query="PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX cat: <http://wiki/cats#> PREFIX prop: <http://wiki/props#> SELECT ?x WHERE { ?x rdf:type cat:Functional_group.  ?x prop:hasName ?x. FILTER ?y >= 34 }";
  try {
    $response = $client->query($query);
  } catch(Exception $e) {
    print_r($e);
  }
  echo($response);


?>