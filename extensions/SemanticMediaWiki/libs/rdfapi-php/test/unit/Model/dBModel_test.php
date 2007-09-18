<?php
// ----------------------------------------------------------------------------------
// Class: dBModel_test
// ----------------------------------------------------------------------------------

/**
 * This class tests the functions of DbModel and DbStore
 *
 * @version  $Id$
 * @author Tobias Gauß	<tobias.gauss@web.de>
 *
 * @package unittests
 * @access	public
 */

class dBModel_test extends UnitTestCase {

	function testSize(){
		$mysql_database = new DbStore('MySQL', 'localhost', 'rdf_db', 'test_user', '1234');
		//$mysql_database->createTables('MySQL');
		$_SESSION['test']='DbModel size test';
		$mysql_database->putModel($this->_generateModel(),'http://www.example.org');
		$dbmodel=$mysql_database->getModel('http://www.example.org');
		$this->assertEqual($dbmodel->size(),1);	
	    $dbmodel->delete();
	}
	

	function testAdd(){
		$_SESSION['test']='DbModel add test';
		$mysql_database = new DbStore('MySQL', 'localhost', 'rdf_db', 'test_user', '1234');
		$mysql_database->putModel($this->_generateModel(),'http://www.example.org');
		$dbmodel=$mysql_database->getModel('http://www.example.org');
		$statement=new Statement(new Resource('http://www.example.org/subject2'),new Resource('http://www.example.org/predicate2'),new Resource('http://www.example.org/object2'));
		$dbmodel->add($statement);
		$this->assertTrue($dbmodel->contains($statement));
		$this->assertEqual($dbmodel->size(),2);
		$dbmodel->delete();
	}
	

	function testRemove(){
		$_SESSION['test']='DbModel remove test';
		$mysql_database = new DbStore('MySQL', 'localhost', 'rdf_db', 'test_user', '1234');
		$mysql_database->putModel($this->_generateModel(),'http://www.example.org');
		$dbmodel=$mysql_database->getModel('http://www.example.org');
		$statement=new Statement(new Resource('http://www.example.org/subject2'),new Resource('http://www.example.org/predicate2'),new Resource('http://www.example.org/object2'));
		$dbmodel->remove($statement);
		$mod1=$dbmodel->getMemModel();
		$this->assertFalse($dbmodel->contains($statement));
		$dbmodel->delete();
	}
	
	
	function testSetBaseUri(){
		$_SESSION['test']='DbModel setBaseURI test';
		$mysql_database = new DbStore('MySQL', 'localhost', 'rdf_db', 'test_user', '1234');
		$mysql_database->putModel($this->_generateModel(),'http://www.example.org');
		$dbmodel=$mysql_database->getModel('http://www.example.org');
		$this->assertEqual($dbmodel->baseURI,'http://www.example.org#');	
		$dbmodel->delete();
	}
	
	
	function testContains(){
		$_SESSION['test']='DbModel testContains test';
		$mysql_database = new DbStore('MySQL', 'localhost', 'rdf_db', 'test_user', '1234');
		$mysql_database->putModel($this->_generateModel(),'http://www.example.org');
		$dbmodel=$mysql_database->getModel('http://www.example.org');
		$stat=new Statement(new Resource('http://www.example.org/subject1'),new Resource('http://www.example.org/predicate1'),new Resource('http://www.example.org/object1'));
		$stat2=new Statement(new Resource('http://www.example.org/subject2'),new Resource('http://www.example.org/predicate2'),new Resource('http://www.example.org/object2'));
		$this->assertTrue($dbmodel->contains($stat));	
		$this->assertFalse($dbmodel->contains($stat2));	
		$dbmodel->delete();
	}
	
	function testContainsAll(){
		$_SESSION['test']='DbModel testContainsAll test';
		$mysql_database = new DbStore('MySQL', 'localhost', 'rdf_db', 'test_user', '1234');
		$mysql_database->putModel($this->_generateModel(),'http://www.example.org');
		$dbmodel=$mysql_database->getModel('http://www.example.org');
		$memModel=$this->_generateModel();
		$this->assertTrue($dbmodel->containsAll($memModel));
		$dbmodel->delete();
	}
	
	function testContainsAny(){
		$_SESSION['test']='DbModel testContainsAny test';
		$mysql_database = new DbStore('MySQL', 'localhost', 'rdf_db', 'test_user', '1234');
		$mysql_database->putModel($this->_generateModel(),'http://www.example.org');
		$dbmodel=$mysql_database->getModel('http://www.example.org');
		$memModel=$this->_generateModel();
		$this->assertTrue($dbmodel->containsAny($memModel));
		$dbmodel->delete();
	}
	
	
	function testLiteral(){
		$_SESSION['test']='DbModel testContainsAny test';
		$mysql_database = new DbStore('MySQL', 'localhost', 'rdf_db', 'test_user', '1234');
		$mysql_database->putModel($this->_generateModelLiteral(),'http://www.example.org');
		$dbmodel=$mysql_database->getModel('http://www.example.org');
		$memModel=$dbmodel->getMemModel();
		$stat=$memModel->triples[0];
		$obj=$stat->getObject();
		$this->assertEqual($obj->getDatatype(),'test');
		$this->assertEqual($obj->getLanguage(),'DE');
		$dbmodel->delete();
	}
	
	
 	
//===================================================================
//                helper functions
//===================================================================

	/**
	* generates a simple MemModel
	*
	*/

	function _generateModel(){
		$model=new MemModel();
		$model->setBaseURI('http://www.example.org');
		$sub=new Resource('http://www.example.org/subject1');
		$pred=new Resource('http://www.example.org/predicate1');
		$obj=new Resource('http://www.example.org/object1');
		$model->add(new Statement($sub,$pred,$obj));
		return $model;
	}

	function _generateModelLiteral(){
		$model=new MemModel();
		$model->setBaseURI('http://www.example.org');
		$sub=new Resource('http://www.example.org/subject1');
		$pred=new Resource('http://www.example.org/predicate1');
		$obj=new Literal('http://www.example.org/object1');
		$obj->setDatatype('test');
		$obj->setLanguage('DE');
		$model->add(new Statement($sub,$pred,$obj));
		return $model;
	}

}
 	
?>