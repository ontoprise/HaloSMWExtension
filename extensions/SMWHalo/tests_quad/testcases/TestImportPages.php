<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TestImportPages extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://localhost");
  }

  public function login()
  {
		$this->open("/mediawiki/index.php?title=Special:UserLogin");
		$this->type("wpName1", "WikiSysop");
		$this->type("wpPassword1", "root");
		$this->click("wpLoginAttempt");
		$this->waitForPageToLoad("30000");
  }
  
public function testImport()
  {
  	$this->login();
  	
    $this->open("/mediawiki/index.php/Special:Import");
    $pathToImportFile = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR . "pages.xml";
    echo "Importing file: " . $pathToImportFile;
    $this->assertTrue(file_exists($pathToImportFile), "Import file doesn't exist");
  	$this->type("xmlimport", $pathToImportFile);
    $this->click("//input[@value='Upload file']");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Importing pages..."), "Expected text is not present. Import failed");
    $this->assertTrue($this->isTextPresent("Al Gore 1 revision"), "Expected text is not present. Import failed");
    $this->assertTrue($this->isTextPresent("Fred 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("John Doe 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Joe Public 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Category:Person 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Liverpool 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Liverpudlian 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Category:City 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:ComingFrom 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Inhabitants 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Firstitem 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Seconditem 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Itemnumber 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Category:Project 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Start date 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:End date 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Project member 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Another complex project 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Category:Person1 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Has name 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Born in 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Lives in 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Has hobbies 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Type:Body Size 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Height 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Henry Morgan 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Gerald Fox 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Has author 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Is category 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Part of 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Firstname 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Knows 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Lastname 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Name 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Related to 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Mbox 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Homepage 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Phone 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Birth date 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Has default form 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Property:Rationale 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Category:Template 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:! 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:Header for wiki parts 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:Property 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:Tablelongrow 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:Tablerow 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:Cite web in help 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:Copyright of article 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:GNU Free Documentation License 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Template:Category 1 revision"), "Expected text is not present. Import failed"); 
    $this->assertTrue($this->isTextPresent("Import finished!"), "Expected text is not present. Import failed");
       
    $this->logout();
  }

  
  public function logout()
  {
		$this->open("/mediawiki/index.php/Special:UserLogout");
  }

}
?>