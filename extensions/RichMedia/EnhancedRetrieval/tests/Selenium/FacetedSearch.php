<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://localhost/");
  }

  public function testMyTestCase()
  {
		try {
				$this->assertTrue($this->isTextPresent("Results 1 to 10 of 169"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->type("query", "");
		$this->focus("query");
		$this->typeKeys("query", "B");
		$this->typeKeys("query", "a");
		$this->typeKeys("query", "n");
		$this->typeKeys("query", "k");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Bank of America Center (Houston)" == $this->getText("link=Bank of America Center (Houston)")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Results 1 to 10 of 11" == $this->getText("//div[@id='pager-header']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		try {
				$this->assertTrue($this->isElementPresent("link=Bank of America Center"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("//div[@id='docs']/div[3]/div/div[1]/p/a[5]");
		try {
				$this->assertTrue($this->isTextPresent("Skyscrapers in Charlotte, North Carolina"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("link=less");
		$this->click("//div[@id='docs']/div[3]/div/div[2]/a");
		try {
				$this->assertTrue($this->isTextPresent("This is the description of Bank_of_America_Corporate_Center."));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("link=hide");
		$this->type("query", "");
		$this->typeKeys("query", "");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Results 1 to 10 of 169" == $this->getText("pager-header")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("link=Building");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Results 1 to 10 of 34" == $this->getText("//div[@id='pager-header']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Building" == $this->getText("//div[@id='selection']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("//img[@title='Remove filter']");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("(no facet filter set)" == $this->getText("//div[@id='selection']/div")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("link=Building");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Building" == $this->getText("//div[@id='selection']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("show_detailsproperty_smwh_Year_built_xsdvalue_dt_values");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("1969 - 1972 (2)" == $this->getText("link=1969 - 1972 (2)")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		try {
				$this->assertTrue($this->isElementPresent("link=1973 - 1977 (1)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		try {
				$this->assertTrue($this->isElementPresent("link=1978 - 1981 (1)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		try {
				$this->assertTrue($this->isElementPresent("link=1982 - 1986 (1)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		try {
				$this->assertTrue($this->isElementPresent("link=1987 - 1990 (3)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("link=1987 - 1990 (3)");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Year built" == $this->getText("//div[@id='selection']/span[2]")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		try {
				$this->assertTrue($this->isTextPresent("Results 1 to 3 of 3"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("show_detailsproperty_smwh_Located_in_t_values");
		$this->click("link=Seattle");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Results 1 to 2 of 2" == $this->getText("//div[@id='pager-header']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("show_detailsproperty_smwh_Located_in_t_values");
		try {
				$this->assertTrue($this->isElementPresent("link=Seattle"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		try {
				$this->assertTrue($this->isElementPresent("link=Remove restriction"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("show_detailsproperty_smwh_Height_stories_xsdvalue_d_values");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("56 - 56 (1)" == $this->getText("link=56 - 56 (1)")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		try {
				$this->assertTrue($this->isElementPresent("link=58 - 59 (1)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("link=58 - 59 (1)");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Results 1 to 1 of 1" == $this->getText("//div[@id='pager-header']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("show_detailsproperty_smwh_Height_stories_xsdvalue_d_values");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("59 - 59 (1)" == $this->getText("link=59 - 59 (1)")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("link=show");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("59" == $this->getText("//div[@id='docs']/div/div/div[2]/table/tbody/tr[3]/td[2]")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		try {
				$this->assertTrue($this->isElementPresent("link=Seattle"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("//div[@id='selection']/span[2]/img");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Year built" == $this->getText("link=Year built")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("//div[@id='selection']/span[2]/img");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Results 1 to 3 of 3" == $this->getText("//div[@id='pager-header']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("show_detailsproperty_smwh_Height_stories_xsdvalue_d_values");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("58 - 58 (2)" == $this->getText("link=58 - 58 (2)")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("link=Remove restriction");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Results 1 to 10 of 33" == $this->getText("//div[@id='pager-header']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		$this->click("show_detailsproperty_smwh_Height_stories_xsdvalue_d_values");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("42 - 54 (5)" == $this->getText("link=42 - 54 (5)")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		try {
				$this->assertTrue($this->isElementPresent("link=55 - 67 (16)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		try {
				$this->assertTrue($this->isElementPresent("link=68 - 81 (6)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		try {
				$this->assertTrue($this->isElementPresent("link=82 - 94 (2)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		try {
				$this->assertTrue($this->isElementPresent("link=95 - 108 (4)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
		$this->click("link=Remove all filters");
		for ($second = 0; ; $second++) {
				if ($second >= 60) $this->fail("timeout");
				try {
						if ("Results 1 to 10 of 169" == $this->getText("//div[@id='pager-header']/span")) break;
				} catch (Exception $e) {}
				sleep(1);
		}

		try {
				$this->assertTrue($this->isTextPresent("(no facet filter set)"));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
				array_push($this->verificationErrors, $e->toString());
		}
  }
}
?>