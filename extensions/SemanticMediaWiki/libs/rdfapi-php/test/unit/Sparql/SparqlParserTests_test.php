<?php
require_once dirname(__FILE__) . '/../../config.php';
require_once RDFAPI_INCLUDE_DIR . 'sparql/SparqlParser.php';
require_once dirname(__FILE__) . '/filterCases.php';


/**
*   Test Sparql parser
*/
class testSparqlParserTests extends UnitTestCase
{
    function testEdgeCases()
    {
        $query = <<<EOT
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ldap: <http://purl.org/net/ldap#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX data: <ldap://ldap.seerose.biz/>
SELECT ?Attr ?Val WHERE {<ldap://ldap.seerose.biz/dc=biz,dc=seerose,ou=People,cn=Sebastian+Dietzold> ?Attr ?Val}
EOT;
        $p = new SparqlParser();
        $q = $p->parse($query);

        $query = <<<EOT
PREFIX foaf:       <http://xmlns.com/foaf/0.1/>
SELECT DISTINCT ?value
WHERE {
      {?s ?p ?value . FILTER (isIRI(?value)) }
UNION {?s ?value ?o . FILTER (isIRI(?value)) }
UNION {?value ?p ?o . FILTER (isIRI(?value)) }
      }
EOT;
        $p = new SparqlParser();
        $q = $p->parse($query);
    }



    function testParseFilter()
    {
        echo "<b>FilterParser tests</b><br/>\n";
        foreach ($GLOBALS['testSparqlParserTestsFilter'] as $arFilterTest) {
            list($query, $result) = $arFilterTest;

            $p = new SparqlParser();
            $q = $p->parse($query);

            $res        = $q->getResultPart();
            $constraint = $res[0]->getConstraint();
            $tree       = $constraint[0]->getTree();

            self::removeLevel($tree);
            $this->assertEqual($result, $tree);
            if ($result != $tree) {
                var_dump($tree);
                echo '----------------------' . "\n" . $query . "\n";
                echo "\n!!!!!!!!        " . self::renderTree($tree) . "\n\n";
            }
        }
    }

    /**
    *   Tests if Query::getLanguageTag() works correctly
    */
    function testQueryGetLanguageTag()
    {
        $this->assertEqual('en', Query::getLanguageTag('?x@en'));
        $this->assertEqual('en', Query::getLanguageTag('?x@en^^xsd:integer'));
        $this->assertEqual('en', Query::getLanguageTag('?x^^xsd:integer@en'));
        $this->assertEqual('en_US', Query::getLanguageTag('?x@en_US'));
    }


    /**
    *   Tests if Query::getDatatype() works correctly
    */
    function testQueryGetDatatype()
    {
        $q = new Query();
        $q->addPrefix('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
        $q->addPrefix('rdf' , 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $q->addPrefix('xsd' , 'http://www.w3.org/2001/XMLSchema#');

        $this->assertNull($q->getDatatype('?name'));
        $this->assertNull($q->getDatatype('?name@en'));
        $this->assertEqual(
            'http://www.w3.org/2001/XMLSchema#integer',
            $q->getDatatype('?name^^xsd:integer')
        );
        $this->assertEqual(
            'http://www.w3.org/2001/XMLSchema#integer',
            $q->getDatatype('?name^^<http://www.w3.org/2001/XMLSchema#integer>')
        );
    }


    function testQueryGetFullUri()
    {
        $q = new Query();
        $q->addPrefix('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
        $q->addPrefix('rdf' , 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $q->addPrefix('xsd' , 'http://www.w3.org/2001/XMLSchema#');

        $this->assertEqual('http://www.w3.org/2001/XMLSchema#integer', $q->getFullUri('xsd:integer'));
        $this->assertFalse($q->getFullUri('yyy:integer'));
        $this->assertFalse($q->getFullUri('integer'));
    }



    /**
    *   Helper method that creates a sparql filter string from the
    *   given filter tree.
    */
    static function renderTree($tree)
    {
        if (!is_array($tree) || !isset($tree['type'])) {
            return 'Parser is broken';
        }
        $negation = isset($tree['negated']) ? '!' : '';
        switch ($tree['type']) {
            case 'equation':
                return $negation . '(' . self::renderTree($tree['operand1'])
                    . ' ' . $tree['operator'] . ' '
                    . self::renderTree($tree['operand2']) . ')';
            case 'value':
                return $negation . $tree['value'];
            case 'function':
                return $negation . $tree['name'] . '('
                    . implode(
                        ', ',
                        array_map(
                            array('self', 'renderTree'),
                            $tree['parameter']
                        )
                    ) . ')';
            default:
                var_dump($tree);
                throw new Exception('Unsupported tree type: ' . $tree['type']);
                break;
        }
    }//static function renderTree($tree)



    /**
    *   Removes "level" keys from the tree array.
    *   It is an implementation detail and should not taken into account
    */
    static function removeLevel(&$tree)
    {
        if (isset($tree['level'])) {
            unset($tree['level']);
        }
        if (isset($tree['type']) && $tree['type'] == 'equation') {
            self::removeLevel($tree['operand1']);
            self::removeLevel($tree['operand2']);
        }
    }
}
?>