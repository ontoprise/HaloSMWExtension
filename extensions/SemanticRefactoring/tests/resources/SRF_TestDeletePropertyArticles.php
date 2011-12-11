<?php
$srfDeletePropertyArticles = array(
//------------------------------------------------------------------------------        
            'Property:Has child' =>
<<<TEXT
Category
TEXT
,
//------------------------------------------------------------------------------        
            'Property:Has son' =>
<<<TEXT
[[Subproperty of::Property:Has child]]
TEXT
,
//------------------------------------------------------------------------------        
            'Bernd' =>
<<<TEXT
[[Category:Man]]
[[Has son::Kai]]
TEXT
,
//------------------------------------------------------------------------------        
            'All sons' =>
<<<TEXT
These are all sons in the wiki
{{#ask: [[Has son::+]] }}
TEXT
,
//------------------------------------------------------------------------------        
            'Category:Employee' =>
<<<TEXT
[[Category:Person]]
TEXT
,
//------------------------------------------------------------------------------        
            'Thomas' =>
<<<TEXT
[[Employee of::Ontoprise]]
[[Category:Employee]]
TEXT
);