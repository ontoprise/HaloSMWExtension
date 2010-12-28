<?php
require_once 'Util.php';

class TestSCRest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = false;
	
	private $url = "http://localhost:7071/dev/index.php/Special:RESTful/";
	private $user = "WikiSysop";
	private $pw = "root";
	
	function testLookupFormMapping(){
		$u = $this->url . 'lookup/form/Wiki_Mail';
		$cc = new cURL();
		$ret = $cc->post($u, '');
		$ps = explode("\r\n\r\n", $ret, 2); // standard HTML 1.1
		$ret = str_replace('\n', "\n", $ps[1]);
		$ret = preg_replace('/\s*\n\s*/', "\n", $ret);
		$acceptable = '{
                                                success : true,
                                                msg : "[
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.sent\",
                        map : \"Project Bug.Project Bug.report date\"
                },
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.sent\",
                        map : \"Project Task.Project Task.start date\"
                },
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.to\",
                        map : \"Project Bug.Project Bug.debugger\"
                },
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.to\",
                        map : \"Project Task.Project Task.owner\"
                }
]"
}';
		$acceptable = preg_replace('/\s*\n\s*/', "\n", $acceptable);

		$this->assertEquals($ret, $acceptable);
	}
	
	function testLookupTemplateMapping(){
		$u = $this->url . 'lookup/template/Wiki_Mail';
		$cc = new cURL();
		$ret = $cc->post($u, '');
		$ps = explode("\r\n\r\n", $ret, 2); // standard HTML 1.1
		$ret = str_replace('\n', "\n", $ps[1]);
		$ret = preg_replace('/\s*\n\s*/', "\n", $ret);
		$acceptable = '{
                                                success : true,
                                                msg : "[
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.sent\",
                        map : \"Project Bug.Project Bug.report date\"
                },
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.sent\",
                        map : \"Project Task.Project Task.start date\"
                },
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.to\",
                        map : \"Project Bug.Project Bug.debugger\"
                },
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.to\",
                        map : \"Project Task.Project Task.owner\"
                }
]"
}';
		$acceptable = preg_replace('/\s*\n\s*/', "\n", $acceptable);

		$this->assertEquals($ret, $acceptable);
	}
	
	function testLookupFieldMapping(){
		$u = $this->url . 'lookup/field/Wiki_Mail/to';
		$cc = new cURL();
		$ret = $cc->post($u, '');
		$ps = explode("\r\n\r\n", $ret, 2); // standard HTML 1.1
		$ret = str_replace('\n', "\n", $ps[1]);
		$ret = preg_replace('/\s*\n\s*/', "\n", $ret);
		$acceptable = '{
                                                success : true,
                                                msg : "[
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.to\",
                        map : \"Project Bug.Project Bug.debugger\"
                },
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.to\",
                        map : \"Project Task.Project Task.owner\"
                }
]"
}';
		$acceptable = preg_replace('/\s*\n\s*/', "\n", $acceptable);

		$this->assertEquals($ret, $acceptable);
	}
	
	function testApplyMapping(){
		$u = $this->url . 'mapping/Same_as/Wiki_Mail/Wiki_Mail/from/Project_Bug/Project_Bug/reporter';
		$cc = new cURL();
		$ret = $cc->post($u, '');
		$ps = explode("\r\n\r\n", $ret, 2); // standard HTML 1.1
		$ret = str_replace('\n', "\n", $ps[1]);
		$ret = preg_replace('/\s*\n\s*/', "\n", $ret);
		$acceptable = '{
                                                success : true,
                                                msg : "[
                {
                        type : \"same as\",
                        src : \"Wiki Mail.Wiki Mail.from\",
                        map : \"Project Bug.Project Bug.reporter\"
                }
]"
}';
		$acceptable = preg_replace('/\s*\n\s*/', "\n", $acceptable);

		$this->assertEquals($ret, $acceptable);
	}
	
//		try {
//			$et = $this->getEditToken();
//		}  catch (Exception $e){
//			echo("\r\nLogin Exception\r\n");
//			// print_r($e);
//		}
//		$this->writeArticles(array(''), $et);

	private function writeArticles($texts, $et){
		$cc = new cURL();
		
		foreach($texts as $text){
			$param = "action=edit&title=Talk:Main_Page&summary=Hello%20World&text=$text&token=$et";
			$editArticle = $cc->post($this->url."api.php", $param);
		}
	}
	
	private function getEditToken(){
		$cc = new cURL();
		
		$loginXML = $cc->post($this->url."api.php","action=login&lgname=".$this->user."&lgpassword=".$this->pw."&format=xml");
		
		$loginXML = substr($loginXML, strpos($loginXML, 'token="') + strlen('token="'));
		$loginXML = substr($loginXML, 0, strpos($loginXML, '"'));
		
		$loginXML = $cc->post($this->url."api.php","action=login&lgname=".$this->user."&lgpassword=".$this->pw."&format=xml&lgtoken=".$loginXML);
		
		$editToken = $cc->post($this->url."api.php","action=query&prop=info|revisions&intoken=edit&titles=Main%20Page&format=xml");
		
		$editToken = substr($editToken, strpos($editToken, "<?xml"));
		
		$domDocument = new DOMDocument();

		$cookies = array();
		$success = $domDocument->loadXML($editToken);
		$domXPath = new DOMXPath($domDocument);

		$nodes = $domXPath->query('//page/@edittoken');
		$et = "";
		foreach ($nodes AS $node) {
			$et = $node->nodeValue;
		}
		$et = urlencode($et);
		
		return $et;
	}
}

?>