<?php


class TestTIReadPOP3 extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;
	
	function sendMailAndRunBot() {
		$accountData = $this->getAccountData();
		
		$this->createTermImportDefinition($accountData);
		
		$cd = isWindows() ? "" : "./";
		
		require_once("Mail.php");
		require_once("Mail\mime.php");

		$to = "Term Import <termimport@ontoprise.de>";
		$from = "Ingo Steinbauer <".$accountData["user"].">";
		
		
		$host = $accountData["server-smtp"];
		$username = $accountData["user"];
		$password = $accountData["password"];
		
		$accountDataString = " ".$host." ".$username;
		
		$text = "This is a text message";
		
		$smtp = Mail::factory('smtp',
			array ('host' => $host,
			'auth' => true,
			'username' => $username,
			'password' => $password));
			
		
		$subject = "Attaches Thunderbird iCal Mail";
		$headers = array ('From' => $from, 'Subject' => $subject);
		$file = $cd."testcases/TestTIPop3Attachments/TermineinladungEvent.eml";
		$mime = new Mail_mime();
		$mime->setTXTBody($text);
		$content = file_get_contents($file);
		$mime->addAttachment($content, 'message/rfc822',
			substr($file, strpos($file, "/") + 1), false, "7bit", "inline");
		$body = $mime->get();
		$finalHeaders = $mime->headers($headers);
		$mail = $smtp->send($to, $finalHeaders, $body);

		if (PEAR::isError($mail)) {
			return $mail->getMessage().$accountDataString;
		}

		$subject = "Attaches Thunderbird iCal and PDF Mail";
		$headers = array ('From' => $from, 'Subject' => $subject);
		$file = $cd."testcases/TestTIPop3Attachments\TestVCardandPDF.eml";
		$mime = new Mail_mime();
		$mime->setTXTBody($text);
		$content = file_get_contents($file);
		$mime->addAttachment($content, 'message/rfc822',
			substr($file, strpos($file, "/") + 1), false, "7bit", "inline");
		$body = $mime->get();
		$finalHeaders = $mime->headers($headers);
		$mail = $smtp->send($to, $finalHeaders, $body);
		
		if (PEAR::isError($mail)) {
			return $mail->getMessage();
		}
		
		$subject = "Attaches Outlook vCard Mail";
		$cc = "Ingo Steinbauer <steinbauer@ontoprise.de>";
		$headers = array ('From' => $from, 'Subject' => $subject, 'CC' => $cc);
		$file = $cd."testcases/TestTIPop3Attachments/TestHTMLOutlookVCardandUmlauts.eml";
		$text = "";
		$mime = new Mail_mime();
		$mime->setTXTBody($text);
		$content = file_get_contents($file);
		$mime->addAttachment($content, 'message/rfc822',
			substr($file, strpos($file, "/") + 1), false, "7bit", "inline");
		$body = $mime->get();
		$finalHeaders = $mime->headers($headers);
		$mail = $smtp->send($to, $finalHeaders, $body);
		
		if (PEAR::isError($mail)) {
			return $mail->getMessage();
		}
		
		sleep(60);
		
		exec($cd.'runBots smw_termimportupdatebot');
		
		sleep(60);
		return "";
	}
	
	function tearDown() {
	}
	
	/**
	 * This method checks if a new Term Import run article was created.
	 * A created article implies that the Term Import was executed.
	 */
	function testTermImportWasExecuted() {
		$error = "";
		$error = $this->sendMailAndRunBot();
		$this->assertEquals($error, "");
		
		$lastRunAfter = $this->getLastRunArticleName(); 
			
		$this->assertNotEquals($lastRunAfter, "TermImport:  ");
		$this->assertNotEquals($lastRunAfter, null);	
	}
	
	/**
	 * This method checks if the create Term Import run article contains
	 * text that tells that the TI was executed successfully. It also checks
	 * that the article does not contain text that tells the opposite.
	 */
	function testTermImportRunArticle(){
		$wikiText = smwf_om_GetWikiText($this->getLastRunArticleName());
		
		$messages = "";
		strpos($wikiText, "Was successfull: [[wasImportedSuccessfully::true]]") > 0
			? true : $messages .= "Term Import was not successfull. ";
		strpos($wikiText, "Was successfull: [[wasImportedSuccessfully::false]]") > 0
			? $messages .= "Term Import contained failures. " : true;

		$this->assertEquals($messages, "");
	}
	
	/**
	 * This method checks if three new articles (E-mails) have been created during 
	 * the Term Import. 
	 * It also checks if each one has an E-Mail as an attachment.
	 * It also checks for one email if the from, cc and subject attributes are set correctly
	 */
	function testTermImportCreatedArticles(){
		SMWQueryProcessor::processFunctionParams(
			array("[[wasAddedDuringTermImport::".$this->getLastRunArticleName()."]]")
			,$querystring,$params,$printouts);
		$queryResult = explode("|",
			SMWQueryProcessor::getResultFromQueryString($querystring,$params,
			$printouts, SMW_OUTPUT_WIKI));
		unset($queryResult[0]);
		$addedArticles = array();
		foreach($queryResult as $qr){
			$addedArticles[] = "E-mail:".substr($qr, 0, strpos($qr, "]"));
		}
		$this->assertEquals(count($addedArticles), 3);
		
		$articleWikiText = array();
		$articleWikiText[] = smwf_om_GetWikiText($addedArticles[0]); 
		$articleWikiText[] = smwf_om_GetWikiText($addedArticles[1]);
		$articleWikiText[] = smwf_om_GetWikiText($addedArticles[2]);
		$errorMessages = "Attachment E-mail:0MKv5w-1MgGX60dOR-0003oe@mrelayeu.kundenserver.de not found. ";
		$errorMessages .= "Attachment E-mail:4A951CEB.6070300@ontoprise.de not found. ";
		$errorMessages .= "Attachment E-mail:22537444.256641251383895405.JavaMail.servlet@kundenserver not found. ";
		
		foreach($articleWikiText as $text){
			if(strpos($text, "E-mail:0MKv5w-1MgGX60dOR-0003oe@mrelayeu.kundenserver.de") > 0){
				$errorMessages = 
					str_replace("Attachment E-mail:0MKv5w-1MgGX60dOR-0003oe@mrelayeu.kundenserver.de not found. "
						,"",$errorMessages);
					if(strpos($text, "==== Content ====") <= 0){
						$errorMessages .= "Body was not added. ";
					}
			} else if(strpos($text, "E-mail:4A9BD4AA.4050304@ontoprise.de") > 0){
				$errorMessages = 	
					str_replace("Attachment E-mail:4A951CEB.6070300@ontoprise.de not found. "
						,"",$errorMessages);
			} else if(strpos($text, "E-mail:22537444.256641251383895405.JavaMail.servlet@kundenserver") > 0){
				$errorMessages = 	
					str_replace("Attachment E-mail:22537444.256641251383895405.JavaMail.servlet@kundenserver not found. "
						,"",$errorMessages);
						
					if(strpos($text, "From:''' Ingo Steinbauer") <= 0){
						$errorMessages .= "Attribute 'from' was not set correctly. ";
					}
					if(strpos($text, "CC:''' Ingo Steinbauer,steinbauer@ontoprise.de") <= 0){
						$errorMessages .= "Attribute 'cc' was not set correctly. ";
					}
					if(strpos($text, "Subject:''' Attaches Outlook vCard Mail") <= 0){
						$errorMessages .= "Attribute 'subject' was not set correctly. ";
					} 
					if(strpos($text, "==== Content ====") > 0){
						$errorMessages .= "Empty body was added. ";
					}
			}
		}
			
		
		$this->assertEquals($errorMessages, "");
	}
	
	/**
	 * This method checks if the article for the attached E-Mail that contains
	 * an ICalender as attachment was overwritten correctly.
	 * 
	 * It also checks if the message_id and the date property were set correcly
	 */
	function testTermImportAttachedEmailWithICal(){
		$wikiText = smwf_om_GetWikiText("E-mail:0MKv5w-1MgGX60dOR-0003oe@mrelayeu.kundenserver.de");
		
		$messages = "";
		strpos($wikiText, "'''Attachment(s):''' {{#arraymap:File:5a873669-f5c5-4f88-902e-4bd507e441a4.ics") > 0
			? true : $messages .= "ICalendar was not attached. ";
		strpos($wikiText, "Date:''' 2009/08/26 13:19:16") > 0
			? true : $messages .= "Date property was not set. ";
		strpos($wikiText, "Message id:''' 0MKv5w-1MgGX60dOR-0003oe@mrelayeu.kundenserver.de") > 0
			? true : $messages .= "Message id was not set. ";
		
		$this->assertEquals($messages, "");
	}
	
	/**
	 * This method checks if the article for the attached E-Mail that contains
	 * a vCard and a pdf-document as attachment was overwritten correctly.
	 * 
	 */
	function testTermImportAttachedEmailWithVCardAndPDF(){
		$wikiText = smwf_om_GetWikiText("E-mail:4A9BD4AA.4050304@ontoprise.de");
		
		$messages = "";
		strpos($wikiText, "File:Willi_Tester.vcf|,|xyz|") > 0
			? true : $messages .= "VCalendar was not attached. ";
		strpos($wikiText, "#arraymap:File:semantic_web.pdf") > 0
			? true : $messages .= "PDF was not attached. ";
//		strpos($wikiText, "[[wasAddedDuringTermImport::TermImport:DONTCare| ]]") > 0
//			? true : $messages .= "Original annotation was removed. ";
		
		$this->assertEquals($messages, "");
	}
	
	/**
	 * This method checks if the article for the pdf-attachment was 
	 * overwritten correctly.
	 * 
	 */
	function testTermImportAttachment(){
		$wikiText = smwf_om_GetWikiText("File:Semantic_web.pdf");
		
		$messages = "";
		strpos($wikiText, "steinbauer@ontoprise.de") > 0
			? true : $messages .= "Creator was not set correctly. ";
		strpos($wikiText, "Creation date:''' 2009/08/31 15:48:26") > 0
			? true : $messages .= "Creation date was not added correctly. ";
		strpos($wikiText, "Related article(s):''' {{#arraymap:E-mail:4A9BD4AA.4050304@ontoprise.de") > 0
			? true : $messages .= "Related mail was not added correctly. ";
		// todo: find out why this does not work when using php unit
		// strpos($wikiText, "Scientific American: The Semantic Web") > 0
		//	? true : $messages .= "Pdf was not extracted correctly. ";
//		strpos($wikiText, "[[wasUpdatedDuringTermImport::TermImport:DONTCare| ]]") > 0
//			? true : $messages .= "Original annotation was removed. ";
		
		$this->assertEquals($messages, "");
	}
	
	
/**
	 * This method checks if the article for the vCard was overwritten correctly.
	 * It checks the following attributes: 
	 * 'N','FN', 'TITLE', 'ORG', 'NICKNAME','TEL', 'EMAIL', 'URL', 'ADR', 'BDAY', 'NOTE', 'CATEGORIES'
	 */
	function testTermImportVCard(){
		$wikiText = smwf_om_GetWikiText("File:Willi Tester.vcf");
		
		$messages = "";
		strpos($wikiText, "name = Tester,Willi") > 0
			? true : $messages .= "N was not added correctly. ";
		strpos($wikiText, "fullName = Willi Tester") > 0
			? true : $messages .= "FN was not added correctly. ";	
		strpos($wikiText, "title = Mr") > 0
			? true : $messages .= "Title was not added correctly. ";
		strpos($wikiText, "organization = testorganisation,testdivision") > 0
			? true : $messages .= "Org was not added correctly. ";
		strpos($wikiText, "nickName = willtest") > 0
			? true : $messages .= "Nick name was not added correctly. ";
		strpos($wikiText, "phone = 111 (Work, Voice); 2222 (Home, Voice); 4444 (Cell, Voice); 3333 (Fax)") > 0
			? true : $messages .= "Tel was not added correctly. ";
		strpos($wikiText, "businessstreeet.1,businesscity,businessstate,businesscountry (Work, Postal); privatestreet. 1,privatecity,privatestate,privatecountry ") > 0
			? true : $messages .= "Adr was not added correctly. ";
		strpos($wikiText, "email = test@test.de (Pref, Internet); test2@test.de (Internet)") > 0
			? true : $messages .= "E-mail was not added correctly. ";
//		strpos($wikiText, "URL:''' www.private.de (Home); www.business.com (Work)") > 0
//			? true : $messages .= "URL was not added correctly. ";
		strpos($wikiText, "birthday = 2003-02-01") > 0
			? true : $messages .= "BD was not added correctly. ";
		strpos($wikiText, "categories = TestVCard") > 0
			? true : $messages .= "Category was not added correctly. ";
		strpos($wikiText, "note = This is a note about Willi Tester.") > 0
			? true : $messages .= "Note was not added correctly. ";
//		strpos($wikiText, "[[wasIgnoredDuringTermImport::TermImport:DONTCare| ]]") > 0
//			? true : $messages .= "Original annotation was removed. ";
				
			
		$this->assertEquals($messages, "");
	}
	
	
	/**
	 * This method checks if the article for the iCalendar was overwritten correctly.
	 * 
	 */
	function testTermImportICalendar(){
		$wikiText = smwf_om_GetWikiText("File:5a873669-f5c5-4f88-902e-4bd507e441a4.ics");
		
		$messages = "";
		strpos($wikiText, "summary = New event") > 0
			? true : $messages .= "Summary was not added correctly. ";
		strpos($wikiText, "start = 2009/08/30 09:30:00") > 0
			? true : $messages .= "Start was not added correctly. ";
		strpos($wikiText, "end = 2009/08/30 10:30:00") > 0
			? true : $messages .= "End was not added correctly. ";
		strpos($wikiText, "organizer = steinbauer@ontoprise.de") > 0
			? true : $messages .= "Organizers was not added correctly. ";
		strpos($wikiText, "organizerName = Ingo Steinbauer") > 0
			? true : $messages .= "Organizers name was not added correctly. ";
		strpos($wikiText, "attendee = steinbauer@ontoprise.de, elfie1982@gmail.com") > 0
			? true : $messages .= "Attendee was not added correctly. ";
		strpos($wikiText, "location = Meeting room") > 0
			? true : $messages .= "Location was not added correctly. ";
		strpos($wikiText, "categories = Status") > 0
			? true : $messages .= "Categories was not added correctly. ";
		strpos($wikiText, "uid = 5a873669-f5c5-4f88-902e-4bd507e441a4") > 0
			? true : $messages .= "UID was not added correctly. ";
		strpos($wikiText, "This is the description.") > 0
			? true : $messages .= "Description was not added correctly. ";
			
		$this->assertEquals($messages, "");
	}
	
	
	
	function getLastRunArticleName(){
		SMWQueryProcessor::processFunctionParams(array("[[belongsToTermImport::TermImport:POP3]]"
			, "limit=1", "sort=hasImportDate", "order=descending")
			,$querystring,$params,$printouts);
		$queryResult = explode("|",
			SMWQueryProcessor::getResultFromQueryString($querystring,$params,
			$printouts, SMW_OUTPUT_WIKI));
		if(count($queryResult) > 1){
			$queryResult = explode(" ", substr($queryResult[1], 0, strpos($queryResult[1], "]"))); 
			return "TermImport:".$queryResult[0]." ".$queryResult[1]." ".$queryResult[2];
		} else {
			return "TermImport:  ";
		}
	}
	
	private function getAccountData(){
		$cd = isWindows() ? "" : "./";
		
		//$file = fopen($cd."testcases/TestTIPop3Attachments/path_to_account_data.txt", "r");
		$pathToAccountData = 
			explode("<br/>", file_get_contents($cd."testcases/TestTIPop3Attachments/path_to_account_data.txt"));
		//fclose($file);
		
		//$file = fopen($cd.trim($pathToAccountData[0]), "r");
		$content = explode("<br/>", file_get_contents($cd.trim($pathToAccountData[0])));
		//fclose($file);
		
		$accountData["server-pop3"] = trim($content[0]);
		$accountData["server-smtp"] = trim($content[1]);
		$accountData["user"] = trim($content[2]);
		$accountData["password"] = trim($content[3]);
		 
		return $accountData;
	}
	
	private function createTermImportDefinition($accountData){
		$tiDef = '<ImportSettings>
<ModuleConfiguration>
<TLModules>
<Module>
<id>ConnectLocal</id>
</Module>
</TLModules >
<DALModules>
<Module>
<id>ReadPOP3</id>
</Module>
</DALModules >
</ModuleConfiguration>
<DataSource>
<ServerAddress display="Server:" type="text">'.$accountData["server-pop3"].'</ServerAddress>
<UserName display="User:" type="text">'.$accountData["user"].'</UserName>
<Password display="Password:" type="text">'.$accountData["password"].'</Password>
<SSL display="Use SSL:" type="checkbox">on</SSL>
<AttachmentMP autocomplete="true" display="Extra Mapping Policy:" type="text">AttachmentMP</AttachmentMP>
</DataSource>
<MappingPolicy>
<page>MailImportMP</page>
</MappingPolicy >
<ConflictPolicy>
<overwriteExistingTerms>true</overwriteExistingTerms>
</ConflictPolicy >
<InputPolicy>
<terms>
<regex>
</regex>
<term>
</term>
</terms>
<properties>
<property>articleName</property>
<property>from</property>
<property>to</property>
<property>cc</property>
<property>date</property>
<property>subject</property>
<property>in_reply_to</property>
<property>followup_to</property>
<property>references</property>
<property>message_id</property>
<property>body</property>
<property>attachments</property>
<property>vCards</property>
<property>iCalendars</property>
</properties>
</InputPolicy>
<ImportSets>
<ImportSet>
<Name>termimport@ontoprise.de</Name>
</ImportSet>
</ImportSets>
<UpdatePolicy>
<maxAge value="1"/>
</UpdatePolicy>
</ImportSettings>

[[Category:TermImport]]';
		
		smwf_om_EditArticle('TermImport:POP3', 'PHPUnit', $tiDef, '');
	}
}

?>