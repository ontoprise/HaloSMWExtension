<?php

/**
 * @file
 * @ingroup LinkedData_Tests
 */

/**
 * Test suite for LOD policies.
 * Start the triple store with these options before running the test:
 * msgbroker=none client=MyStore driver=ontobroker-quad wsport=8090 console reasoner=owl restfulws
 *
 * @author Magnus Niemann
 *
 */
class TestLODPolicySuite extends PHPUnit_Framework_TestSuite {

    public static $mPolicy = array(
        "uri" => "http://www.example.org/smw-lde/smwTrustPolicies/P42",
        "id" => "P42",
        "Description" => "Just an example policy for testing purposes",
        "Pattern" => "{ GRAPH ?graph {?s ?p ?o}}",
        "HeuristicURI" => "http://www.example.org/smw-lde/smwTrustPolicies/PreferInternalInformation",
        "HeuristicLabel" => "PreferInternalInformation",
        "ParURI" => "http://www.example.org/smw-lde/smwTrustPolicies/ParUser",
        "ParName" => "PAR_USER",
        "ParLabel" => "User",
        "ParDescription" => "Please enter the SMW+ uri of the user whose trust markings should be used"
    );

    public static function suite() {

        $suite = new TestLODPolicySuite();
        $suite->addTestSuite('TestLODPolicy');
        return $suite;
    }

    /**
     * Loads the Policy from the triple store and checks its content.
     */
    public static function checkPolicyinTripleStore(PHPUnit_Framework_TestCase $testCase) {
        $store = LODPolicyStore::getInstance();
        $policy = $store->loadPolicy(self::$mPolicy["id"]);
        $testCase->assertNotNull($policy);
        $testCase->assertEquals(self::$mPolicy["id"], $policy->getID());
        $testCase->assertEquals(self::$mPolicy["Description"], $policy->getDescription());
        $testCase->assertEquals(self::$mPolicy["Pattern"], $policy->getPattern());
        $heu = $policy->getHeuristic();
        $testCase->assertEquals(self::$mPolicy["HeuristicLabel"], $heu->getLabel());
        $pars = array_values($policy->getParameters());
        $testCase->assertEquals(1, count($pars));
        $par = $pars[0];
        $testCase->assertEquals(self::$mPolicy["ParName"], $par->getName());
        $testCase->assertEquals(self::$mPolicy["ParLabel"], $par->getLabel());
        $testCase->assertEquals(self::$mPolicy["ParDescription"], $par->getDescription());
    }

    protected function setUp() {
        /*
          print("Setup\n");
          $store = LODPolicyStore::getInstance();
          $policy = TestLODPolicySuite::createPolicy();
          $r = TestLODPolicy::storePolicy($store, $policy, NULL);
         */
    }

    protected function tearDown() {
        // Delete the graph in the triple store that contains the source definitions
        $tsa = new TSCTripleStoreAccess();
        $tsa->dropGraph(LODPolicyStore::getTrustGraph());
        $tsa->flushCommands();
    }

//--- Helper functions ---

    /**
     * Creates a LODPolicy object
     * @return LODPolicy
     * 		A sample object
     */
    public static function createPolicy() {
        $sd = new LODPolicy(self::$mPolicy["id"], self::$mPolicy["uri"]);
        $sd->setDescription(self::$mPolicy["Description"]);
        $sd->setPattern(self::$mPolicy["Pattern"]);
        $he = new LODHeuristic(self::$mPolicy["HeuristicURI"]);
        $he->setLabel(self::$mPolicy["HeuristicLabel"]);
        $sd->setHeuristic($he);
        $pa = new LODParameter(self::$mPolicy["ParURI"]);
        $pa->setName(self::$mPolicy["ParName"]);
        $pa->setLabel(self::$mPolicy["ParLabel"]);
        $pa->setDescription(self::$mPolicy["ParDescription"]);
        $sd->setParameters(array($pa));
        return $sd;
    }

    /**
     * Compares the content in the database table with persistent triples for
     * the $id with the $expected result and prints the $errMsg if the strings
     * do not match.
     */
    public static function checkPersistentTriples($testCase, $id, $expected, $errMsg) {

        // Read the generated TriG from the database
        $store = TSCStorage::getDatabase();
        $trigs = $store->readPersistentTriples("LODPolicy", $id);
        $trig = "";
        foreach ($trigs as $t) {
            $trig .= $t . "\n";
        }

        // Remove whitespaces
        $trig = preg_replace("/\s*/", "", $trig);
        $expected = preg_replace("/\s*/", "", $expected);

        $testCase->assertEquals($expected, $trig, $errMsg);
    }

}

/**
 * This test case tests the backend of the class LODPolicy. Source
 * Definitions are stored in, retrieved and deleted from the triple store.
 *
 * The triple store must be running.
 *
 * @author thsc
 *
 */
class TestLODPolicy extends PHPUnit_Framework_TestCase {

    protected $backupGlobals = FALSE;

    function setUp() {

    }

    function tearDown() {
        TSCStorage::getDatabase()->deleteAllPersistentTriples();
    }

    /**
     * Tests the creation a LODPolicy object.
     */
    function testCreatePolicy() {
        $ps = LODPolicyStore::getInstance();
        $pol = new LODPolicy("P42", $ps->getTrustGraph() . "P42");
        $this->assertNotNull($pol);
    }

    /**
     * Tests the creation of the LODPolicyStore object.
     *
     */
    function testCreateLODPolicyStore() {
        $ps = LODPolicyStore::getInstance();
        $this->assertNotNull($ps);
    }

    /**
     * Tests storing a LODPolicy object in the triple store.
     */
    function testStorePolicy() {
        $store = LODPolicyStore::getInstance();
        $policy = TestLODPolicySuite::createPolicy();
        $r = TestLODPolicy::storePolicy($store, $policy, NULL);

        $this->assertTrue($r);
    }

    /**
     * Tests loading a LODPolicy object from the Triple Store
     *
     */
    function testLoadPolicy() {
        TestLODPolicySuite::checkPolicyinTripleStore($this);
    }

    /**
     * Tests deleting LODPolicy object from the Triple Store
     *
     */
    function testDeletePolicy() {
        $store = LODPolicyStore::getInstance();
        $store->deletePolicy(TestLODPolicySuite::$mPolicy["id"]);

        // Make sure that the policy is no longer available
        $Policy = $store->loadPolicy(TestLODPolicySuite::$mPolicy["id"]);

        $this->assertEquals(null, $Policy);
    }

    /**
     * Test retrieving all IDs of policies
     *
     */
    function testGetPolicyIDs() {
        $store = LODPolicyStore::getInstance();
        $g = "http://www.example.org/smw-lde/smwTrustPolicies/";
        $h = "PreferInternalInformation";
        $r = $store->storePolicy($g . "P_1", "P_1", "", "", $h, array());
        $this->assertTrue($r);
        $r = $store->storePolicy($g . "P_2", "P_2", "", "", $h, array());
        $this->assertTrue($r);
        $r = $store->storePolicy($g . "P_3", "P_3", "", "", $h, array());
        $this->assertTrue($r);

        $ids = $store->getAllPolicyIDs();
        // print_r($ids);
        $this->assertContains("P_1", $ids);
        $this->assertContains("P_2", $ids);
        $this->assertContains("P_3", $ids);

        // cleanup
        $store->deleteAllPolicies();
    }

    /**
     * Test retrieving all policies.
     *
     */
    function testLoadAllPolicies() {
        $store = LODPolicyStore::getInstance();
        $g = "http://www.example.org/smw-lde/smwTrustPolicies/";
        $h = "PreferInternalInformation";
        $r = $store->storePolicy($g . "P_1", "P_1", "", "", $h, array());
        $this->assertTrue($r);
        $r = $store->storePolicy($g . "P_2", "P_2", "", "", $h, array());
        $this->assertTrue($r);
        $r = $store->storePolicy($g . "P_3", "P_3", "", "", $h, array());
        $this->assertTrue($r);

        $policies = $store->loadAllPolicies();
        // print_r($policies);
        $this->assertEquals(3, count(array_values($policies)));

        // cleanup
        $store->deleteAllPolicies();
    }

    /**
     * Tests deleting all LODPolicy objects from the Triple Store
     *
     */
    function testDeleteAllPolicies() {
        $store = LODPolicyStore::getInstance();
        // Create a source definition...
        $policy = TestLODPolicySuite::createPolicy();
        TestLODPolicy::storePolicy($store, $policy, NULL);
        // ... and delete all definitions
        $store->deleteAllPolicies();

        // Make sure that the source definition no longer exists
        $this->assertEquals(null, $store->loadPolicy(TestLODPolicySuite::$mPolicy["id"]));
    }

    /**
     * Tests storing a LODPolicy object in the triple store with help
     * of the persistency layer of the TS. Different persistency IDs are tested,
     * automatic ones and user defined.
     */
    function testStorePersistentPolicy() {
        $this->checkStorePersistentPolicy(true);
        $this->checkStorePersistentPolicy("MyOwnPolicyID");
    }

    /**
     * Tests deleting all LODPolicy objects from the Triple Store and
     * the persistency layer.
     *
     */
    function testDeleteAllLPersistentPolicies() {
        $store = LODPolicyStore::getInstance();
        // Create a policy ...
        $this->testStorePolicy();
        // ... and delete all policies
        $store->deleteAllPolicies();

        // Make sure that the policy no longer exists in the TS
        $this->assertEquals(null, $store->loadPolicy(TestLODPolicySuite::$mPolicy["id"]));

        // Make sure that the source definition no longer exists in the
        // persistency layer
        TestLODPolicySuite::checkPersistentTriples($this,
                        TestLODPolicySuite::$mPolicy["id"], "",
                        "testDeleteAllLPersistentSDs failed.");
    }

    /**
     * Tests storing a LODPolicy object in the triple store with help
     * of the persistency layer of the TS.
     *
     * @param bool/string $persistencyID
     * 		The persistency ID that is used for storing and deleting the Policy.
     */
    private function checkStorePersistentPolicy($persistencyID) {
        $store = LODPolicyStore::getInstance();
        $sd = TestLODPolicySuite::createPolicy();
        // Store the Policy and persist it
        $r = TestLODPolicy::storePolicy($store, $sd, $persistencyID);

        $this->assertTrue($r);

        // Test if the Policy was stored in the TS
        $this->testLoadPolicy();

        // Test if the Policy was saved in the persistency layer
        $expected = <<<EXP
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix smw-lde: <http://www.example.org/smw-lde/smw-lde.owl#> .
@prefix smwGraphs: <http://www.example.org/smw-lde/smwGraphs/> .
@prefix smwTrustPolicies: <http://www.example.org/smw-lde/smwTrustPolicies/> .

<http://www.example.org/smw-lde/smwGraphs/TrustGraph> {
        <http://www.example.org/smw-lde/smwTrustPolicies/P42> rdf:type smw-lde:Policy .
        <http://www.example.org/smw-lde/smwTrustPolicies/P42> smw-lde:ID "P42"^^xsd:string .
        <http://www.example.org/smw-lde/smwTrustPolicies/P42> smw-lde:description "Just an example policy for testing purposes"^^xsd:string .
        <http://www.example.org/smw-lde/smwTrustPolicies/P42> smw-lde:pattern "{ GRAPH ?graph {?s ?p ?o}}"^^xsd:string .
        <http://www.example.org/smw-lde/smwTrustPolicies/P42> smw-lde:heuristic smwTrustPolicies:PreferInternalInformation .
        smwTrustPolicies:PreferInternalInformation rdfs:label "PreferInternalInformation"^^xsd:string .
        <http://www.example.org/smw-lde/smwTrustPolicies/P42> smw-lde:parameter <http://www.example.org/smw-lde/smwTrustPolicies/ParUser> .
        <http://www.example.org/smw-lde/smwTrustPolicies/ParUser> smw-lde:paramName "PAR_USER"^^xsd:string .
        <http://www.example.org/smw-lde/smwTrustPolicies/ParUser> rdfs:label "User"^^xsd:string .
        <http://www.example.org/smw-lde/smwTrustPolicies/ParUser> smw-lde:description "Please enter the SMW+ uri of the user whose trust markings should be used"^^xsd:string .
}
EXP;
        $id = $persistencyID === true ? $sd->getID() : $persistencyID;
        TestLODPolicySuite::checkPersistentTriples($this, $id, $expected,
                        "checkStorePersistentPolicy#1 failed for ID $id.");

        // Delete the policy and its persistent data
        $store->deletePolicy($id);

        // Verify that the definition no longer exists in the TS
        $policy = $store->loadPolicy($id);
        $this->assertEquals(null, $policy);

        // Verify that the definition no longer exists in the persistence layer
        TestLODPolicySuite::checkPersistentTriples($this, $id, "",
                        "testStorePersistentPolicy#2 failed for ID $id.");
    }

    public static function storePolicy($store, $policy, $persistentID) {
        $pars = array();
        foreach ($policy->getParameters() as $p) {
            $pars[] = array("uri" => $p->getURI(),
                "name" => $p->getName(),
                "label" => $p->getLabel(),
                "description" => $p->getDescription());
        }
        // print_r($pars);
        $heu = $policy->getHeuristic()->getLabel();
        return $store->storePolicy($policy->getURI(),
                $policy->getID(),
                $policy->getDescription(),
                $policy->getPattern(),
                $heu,
                $pars,
                $persistentID);
    }

}