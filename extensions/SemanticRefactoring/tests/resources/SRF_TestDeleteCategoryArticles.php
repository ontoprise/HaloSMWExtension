<?php
$srfDeleteCategoryArticles = array(
//------------------------------------------------------------------------------        
            'Category:Person' =>
<<<TEXT
Category
TEXT
,
//------------------------------------------------------------------------------        
            'Category:Man' =>
<<<TEXT
[[Category:Person]]
TEXT
,
//------------------------------------------------------------------------------        
            'Kai' =>
<<<TEXT
[[Category:Man]]
TEXT
,
//------------------------------------------------------------------------------        
            'All men' =>
<<<TEXT
These are all men in the wiki
{{#ask: [[Category:Man]] }}
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
,
//------------------------------------------------------------------------------        
            'Property:Has name' =>
<<<TEXT
[[Has domain and range::Category:Person]]
TEXT
,
//------------------------------------------------------------------------------        
            'Property:Has employee' =>
<<<TEXT
[[Has domain and range::Category:Company; Category:Person]]
TEXT
);