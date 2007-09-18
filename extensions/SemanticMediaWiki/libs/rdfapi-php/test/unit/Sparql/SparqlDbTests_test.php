<?php
/**
*   SparqlEngineDb unit tests
*   Run this script by executing /test/unit/sparqldbTests.php
*/
require_once dirname(__FILE__) . '/../../config.php';
require_once RDFAPI_TEST_INCLUDE_DIR . 'test/unit/Sparql/cases.php';
require_once RDFAPI_TEST_INCLUDE_DIR . 'test/unit/Sparql/SparqlTestHelper.php';
require_once RDFAPI_INCLUDE_DIR . 'sparql/SparqlParser.php';
require_once RDFAPI_INCLUDE_DIR . 'sparql/SparqlEngine.php';
require_once RDFAPI_INCLUDE_DIR . 'dataset/NamedGraphMem.php';
//require_once 'Console/Color.php';

class testSparqlDbTests extends UnitTestCase
{
    protected static $strModelUri = 'unittest-model';



    public function testAllTestgroupsNoReload()
    {
        echo "<b>SparqlDbTests</b><br/>\n";
        //prepare
        $parser   = new SparqlParser();
        $strLastDataFile = null;

        foreach ($_SESSION['sparqlTestGroups'] as $arGroup) {
            if (isset($arGroup['deact'])) continue;
            $checkfunc = $arGroup['checkfunc'];
//echo count($_SESSION[$arGroup['tests']]) . " tests\n";
            foreach ($_SESSION[$arGroup['tests']] as $name) {
                if (is_array($name)) {
                    $fileData   = $name['data'];
                    $fileQuery  = $name['query']  . '.rq';
                    $fileResult = $name['result'] . '.res';
                    $title      = $name['query'];
                } else {
                    $fileData   = $name . '.n3';
                    $fileQuery  = $name . '.rq';
                    $fileResult = $name . '.res';
                    $title      = $name;
                }

                if (in_array($title, $_SESSION['testSparqlDbTestsIgnores'])) {
//                    echo Console_Color::convert('%y');
//                    echo '  ignoring ' . $title . "\n";
//                    echo Console_Color::convert('%n');
                    continue;
                }
//echo '  ' . $title . "\n";
                $_SESSION['test'] = $title . ' test';
                $e = null;

                if ($fileData != $strLastDataFile) {
                    //re-use database if not changed
                    list($database, $dbModel) = $this->prepareDatabase();
                    //import statements into database
                    $dbModel  ->load(SPARQL_TESTFILES . 'data/' . $fileData);
                    $strLastDataFile = $fileData;
                }

                $qs       = file_get_contents(SPARQL_TESTFILES . 'query/'  . $fileQuery, 'r');
                $res      = file_get_contents(SPARQL_TESTFILES . 'result/' . $fileResult, 'r');
                unset($result);
                eval($res);
                $q        = $parser->parse($qs);
                try {
                    $t    = $dbModel->sparqlQuery($qs);

                    if ($t instanceof MemModel) {
                        $bOk = $t->equals($result);
                    } else {
                        $bOk = SparqlTestHelper::$checkfunc($t, $result);
                    }
                    $this->assertTrue($bOk);
                } catch (Exception $e) {
                    $bOk = false;
                    $t = null;
                }
/*
                if (!$bOk) {
                    echo Console_Color::convert('%RTest failed: ' . $title . "%n\n");
                    if ($e != null) {
                        echo get_class($e) . ': ' . $e->getMessage() . "\n";
                    }
                    echo 'Query string: ' . $qs . "\n";
    echo Console_Color::convert('%p');
    var_dump($result);
    echo Console_Color::convert('%n');
    echo Console_Color::convert('%r');
    var_dump($t);
    echo Console_Color::convert('%n');
//var_dump($q);
die();
                }
/**/
            }
//            echo $arGroup['title'] . " done\n";
        }
    }



    /**
    *   Instantiates the database object and clears
    *   any existing statements to have a fresh place
    *   for a unit test.
    *
    *   @return array       array($database, $dbModel)
    */
    protected function prepareDatabase()
    {
        $database = ModelFactory::getDbStore(
            $GLOBALS['dbConf']['type'],
            $GLOBALS['dbConf']['host'],
            $GLOBALS['dbConf']['database'],
            $GLOBALS['dbConf']['user'],
            $GLOBALS['dbConf']['password']
        );
        if ($database->modelExists(self::$strModelUri)) {
            //need to remove model
            $database->removeNamedGraphDb(self::$strModelUri);
        }
        $dbModel  = $database->getNewModel(self::$strModelUri);
        return array($database, $dbModel);
    }//protected function prepareDatabase()

}//class testSparqlDbTests extends UnitTestCase
?>