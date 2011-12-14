<?php
$srfRenameInstanceArticles = array(
//------------------------------------------------------------------------------        
            'Thomas' =>
<<<TEXT
[[Has colleague::Kai]]
TEXT
,
//------------------------------------------------------------------------------        
            'People' =>
<<<TEXT
*[[Kai]]
*[[Thomas]]
TEXT
,
//------------------------------------------------------------------------------        
            'Help pages' =>
<<<TEXT
*[[Help:OntologyBrowser]]
*[[Help:Query Interface]]
TEXT
,
//------------------------------------------------------------------------------        
            'All colleagues of Kai' =>
<<<TEXT
These are all sons in the wiki
{{#ask: [[Has colleague::Kai]] }}
TEXT
,
//------------------------------------------------------------------------------        
            'All colleagues' =>
<<<TEXT
These are all sons in the wiki
{{#ask: [[Kai]]|?Has colleague }}
TEXT

);