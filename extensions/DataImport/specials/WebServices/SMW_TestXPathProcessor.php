<?php

require_once("SMW_XPathProcessor.php");

// test for sharepoint which tests
// 		default namespaces
//		namespaces defined in child nodes
//		aggregation functions
$xpathProcessor = new XPathProcessor("<GetListItemsResponse><![CDATA[' and ends with ']]></GetListItemsResponse>");
$result = $xpathProcessor->evaluateQuery("..Items.Item/CustomerReviews/Review[*]/Reviewer/Name");
print_r($result); 

?>