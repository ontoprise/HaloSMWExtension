
#Import SMW
include_once('extensions/SemanticMediaWiki/includes/SMW_Settings.php');
enableSemantics('http://wiki', true);

#EnhancedRetrieval
$wgSearchType = 'LuceneSearch';
$wgLuceneHost = 'localhost';
$wgLucenePort = 8123;
$wgLuceneSearchVersion = 2.1;
$wgUSPathSearch=true;
$wgLuceneSearchTimeout=5;
require_once('extensions/EnhancedRetrieval/includes/EnhancedRetrieval.php');
