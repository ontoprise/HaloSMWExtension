<?php
$srfRenamePropertyArticles = array(
//------------------------------------------------------------------------------        
            'Property:Has child' =>
<<<TEXT
Son of a person
TEXT
,
//------------------------------------------------------------------------------        
            'Property:Has kid' =>
<<<TEXT
[[Is same as::Property:Has child]]
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
[[Has son::Kai]]
TEXT
,
//------------------------------------------------------------------------------        
            'All sons' =>
<<<TEXT
These are all sons in the wiki
{{#ask: [[Has son::+]]|?Has son }}
TEXT
,
//------------------------------------------------------------------------------        
            'Pages' =>
<<<TEXT
These are page links:
[[Property:Has son]]
TEXT

);