<?php
$srfRenameCategoryArticles = array(
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
            'Testlink' =>
<<<TEXT
Link to this category [[:Category:Man]]
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
            'Property:Is human' =>
<<<TEXT
{{#ask: [[Depends on category::Category:Man]] }}
TEXT
,
//------------------------------------------------------------------------------        
            'Category info' =>
<<<TEXT
{{#ask: [[:Category:Man]]|?Label }}
TEXT
,
//------------------------------------------------------------------------------        
            'Property:Has employee' =>
<<<TEXT
[[Has domain and range::Category:Company; Category:Person]]
TEXT
);