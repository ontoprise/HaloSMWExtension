<?php

// check if original file was called from command line or Webserver
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    die( "This script must be run from the command line\n" );
}

require_once dirname(__FILE__) . '/../../../../tests/tests_halo/SeleniumTestCase_Base.php';

class TestExternalImageLinks extends SeleniumTestCase_Base {
  
  protected function setUp() {
    parent::setUp();
    $this->linkWikitext = "[http://www.cato-at-liberty.org/wp-content/uploads/Google.jpg http://www.cato-at-liberty.org/wp-content/uploads/Google.jpg]";
    $this->linkElementLocator = "css=a[href=\"http://www.cato-at-liberty.org/wp-content/uploads/Google.jpg\"]";
    $this->elementNotPresentMsg = "Element not present: <a href=\"http://www.cato-at-liberty.org/wp-content/uploads/Google.jpg\">";
    $this->wikitextNotPresenMsg = "Wikitext not equal to [http://www.cato-at-liberty.org/wp-content/uploads/Google.jpg http://www.cato-at-liberty.org/wp-content/uploads/Google.jpg]";
  }

  public function testLinks() {
    $this->login();
    $this->open("/mediawiki/index.php?title=Testlink&action=edit");
    $this->type("id=wpTextbox1", $this->linkWikitext);
    $this->click("id=wpSave");
    $this->waitForPageToLoad("30000");
    $this->click("id=ca-edit");
    $this->waitForPageToLoad("30000");
    for ($second = 0;; $second++) {
      if ($second >= 60)
        $this->fail($this->elementNotPresentMsg);
      try {
        if ($this->isElementPresent($this->linkElementLocator))
          break;
      } catch (Exception $e) {
        
      }
      sleep(1);
    }

    $this->click("id=toggle_wpTextbox1");
    $this->assertEquals($this->linkWikitext, $this->getValue("id=wpTextbox1"), $this->wikitextNotPresenMsg);
    $this->click("id=toggle_wpTextbox1");
    try {
      $this->assertTrue($this->isElementPresent($this->linkElementLocator), $this->elementNotPresentMsg);
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
      array_push($this->verificationErrors, $e->toString());
    }
    $this->click("id=toggle_wpTextbox1");
    $this->assertEquals($this->linkWikitext, $this->getValue("id=wpTextbox1"), $this->wikitextNotPresenMsg);
  }

}

?>