<?php
if ( !defined( 'MEDIAWIKI' ) ) die;

require_once 'SMW_AddIn.php';

define('SWT_ADDIN_ALL', 0);
define('SWT_ADDIN_CANNOT_REGISTER', 1);
define('SWT_ADDIN_CANNOT_READ', 2);
define('SWT_ADDIN_CANNOT_EDIT', 4);

define('SWT_ADDIN_E_SUCCESS', '0');
define('SWT_ADDIN_E_WRONG_NAME', '100');
define('SWT_ADDIN_E_WRONG_DOMAIN', '101');
define('SWT_ADDIN_E_WRONG_PASSWORD', '102');
define('SWT_ADDIN_E_NO_PRIVILEGE', '200');
define('SWT_ADDIN_E_CREATE_CATEGORY', '500');

define('SWT_ADDIN_TYPE_DOMAIN', 'domain');
define('SWT_ADDIN_TYPE_COMMON', 'common');

define('SWT_ADDIN_E_PAGE_NOT_EXIST', '401');

define('SWT_ADDIN_E_FORM_NOT_MATCH', '801');
define('SWT_ADDIN_E_FORM_NOT_EXIST', '802');

define('SMW_TYPE_STRING', 'string');
define('SMW_TYPE_DATE', 'date');
define('SMW_TYPE_NUMBER', 'number');
define('SMW_TYPE_EMAIL', 'email');
define('SMW_TYPE_PAGE', 'page');

class RestrictionType {
	/**
	 * Restriction of SMW AddIn
	 * @param int $restriction
	 * e.g. SWT_ADDIN_CAN_READ | SWT_ADDIN_CAN_EDIT
	 */
	var $restriction;
	/**
	 * Supported domains of SMW AddIn
	 * @param string[] $supportedDomains
	 */
	var $supportedDomains;
	// any extra message if we want
	var $message;
}
class AuthenticationType {
	var $user;
	// for domain user, the domain name
	// othwise, password, plain / md5 encrypted
	var $password;
	// common, domain
	var $type;
	// is encrypted password
	var $encrypted;
}

class ExceptionType {
	var $ret_code;
	var $message;
}
class AuthReturnType {
	var $exception;
	/**
	 * Restriction of SMW AddIn
	 * @param RestrictionType $restriction
	 */
	var $restriction;
}
class PageInfoType {
	var $exception;
	var $info;
}
class StringsType {
	var $exception;
	var $values;
}
class Comment {
	var $name;
	var $comment;
}
class CommentsType {
	var $exception;
	var $values;
}
class PropertyPairsType {
	var $exception;
	var $values;
}


function smwgWTGetRestriction($auth) {
	$restriction = new RestrictionType();
	$restriction->restriction = SWT_ADDIN_CANNOT_EDIT | SWT_ADDIN_CANNOT_READ | SWT_ADDIN_CANNOT_REGISTER;

	global $wgGroupPermissions, $wgUser, $wgAuthDomains;
	if($auth->user == NULL || $auth->user == "") {
		$groups = array('*');
	} else {
		$wgUser = User::newFromName($auth->user);
		$groups = $wgUser->getEffectiveGroups();
	}
	foreach($groups as $group) {
		if($wgGroupPermissions[$group]['createaccount']) {
			$restriction->restriction &= ~SWT_ADDIN_CANNOT_REGISTER;
		}
		if($wgGroupPermissions[$group]['read']) {
			$restriction->restriction &= ~SWT_ADDIN_CANNOT_READ;
		}
		if($wgGroupPermissions[$group]['edit']) {
			$restriction->restriction &= ~SWT_ADDIN_CANNOT_EDIT;
		}
	}
	$restriction->supportedDomains = $wgAuthDomains;

	return $restriction;
}

function smwgWTValidate($auth) {
	$exception = new ExceptionType();
	$exception->ret_code = SWT_ADDIN_E_SUCCESS;
	$exception->message = "Succeed";

	if($auth->type == SWT_ADDIN_TYPE_DOMAIN) {
		global $wgUser;
		if($wgUser->getId()) {
			global $wgDomainAuthDomain, $wgDomainAuthUser;;
			$auth->user = $wgUser->getName();
			$auth->password = $wgDomainAuthDomain;
		}
		global $wgAuthDomains;
		$domainMatches = false;
		if($wgAuthDomains !== NULL) {
			foreach($wgAuthDomains as $domain) {
				if($auth->password == $domain) {
					$domainMatches = true;
					break;
				}
			}
		}
		if(!$domainMatches) {
			$exception->ret_code = SWT_ADDIN_E_WRONG_DOMAIN;
			$exception->message = "Authentication Failed. Your domain is not supported by this wiki.";
			return $exception;
		} else {
			// auto create account
			$u = User::newFromName($auth->user);
			if ( !$u->getId() ) {
				$authPlugin = new DomainAuthenticationPlugin();
				$u->addToDatabase();
				$u->setToken();
				$authPlugin->initUser( $u, true );
			}
		}
	}

	if($auth->user !== NULL && $auth->user != "") {
		// set $wgUser field
		global $wgUser;
		$wgUser = User::newFromName($auth->user);
		if ( ($wgUser === null) || !$wgUser->getId() ) {
			$exception->ret_code = SWT_ADDIN_E_WRONG_NAME;
			$exception->message = "Authentication Failed. Please check your username/password again.";
			return $exception;
		}
		// compare password
		if($auth->type != SWT_ADDIN_TYPE_DOMAIN) {
			if($auth->encrypted && $wgUser->mPassword == $auth->password) {
			} else if(!$auth->encrypted && $wgUser->checkPassword($auth->password)) {
			} else {
				$exception->ret_code = SWT_ADDIN_E_WRONG_PASSWORD;
				$exception->message = "Authentication Failed. Please check your username/password again.";
				return $exception;
			}
		}
	}

	return $exception;
}

function smwgWTExceptionCheck($auth, $isEdit = false) {
	$exception = smwgWTValidate($auth);
	if($exception->ret_code == SWT_ADDIN_E_SUCCESS) {
		$restriction = smwgWTGetRestriction($auth);
		if($isEdit && ($restriction->restriction & SWT_ADDIN_CANNOT_EDIT)) {
			$exception->ret_code = SWT_ADDIN_E_NO_PRIVILEGE;
			$exception->message = "This account has no privilege to edit. Please contact your administrator.";
		} else if(!$isEdit && ($restriction->restriction & SWT_ADDIN_CANNOT_READ)) {
			$exception->ret_code = SWT_ADDIN_E_NO_PRIVILEGE;
			$exception->message = "This account has no privilege to read. Please contact your administrator.";
		}
	}
	return $exception;
}


class AddIn2 extends AddIn{
	public function getCategories() {
		global $wgGroupPermissions;
		if(!$wgGroupPermissions['*']['read']) {
			return NULL;
		}

		return parent::getCategories();
	}

	public function getProperties() {
		global $wgGroupPermissions;
		if(!$wgGroupPermissions['*']['read']) {
			return NULL;
		}

		return parent::getProperties();
	}

	public function getTitles($categoryTitles) {
		global $wgGroupPermissions;
		if(!$wgGroupPermissions['*']['read']) {
			return NULL;
		}

		return parent::getTitles($categoryTitles);
	}

	public function getTitleProperties($title) {
		global $wgGroupPermissions;
		if(!$wgGroupPermissions['*']['read']) {
			return NULL;
		}

		return parent::getTitleProperties($title);
	}

	public function addCategory($categoryName) {
		global $wgGroupPermissions;
		if(!$wgGroupPermissions['*']['edit']) {
			return NULL;
		}

		return parent::addCategory($categoryName);
	}

	public function saveNewMail($subject, $sender, $receivers, $ccs, $sentDate, $basedMailPage, $action, $categories, $body, $attachments) {
		global $wgGroupPermissions;
		if(!$wgGroupPermissions['*']['edit']) {
			return NULL;
		}

		return parent::saveNewMail($subject, $sender, $receivers, $ccs, $sentDate, $basedMailPage, $action, $categories, $body, $attachments);
	}

	public function getPageInfo($title) {
		global $wgGroupPermissions;
		if(!$wgGroupPermissions['*']['read']) {
			return NULL;
		}

		return parent::getPageInfo($title);
	}

	///////////////////////////////////////////////////////
	// new functions
	public function authenticate($auth){
		$addin = new AuthReturnType();
		$addin->exception = smwgWTValidate($auth);

		if($addin->exception->ret_code == SWT_ADDIN_E_SUCCESS) {
			$addin->restriction = smwgWTGetRestriction($auth);
			if(!$auth->encrypted) {
				global $wgUser;
				$addin->restriction->message = $wgUser->mPassword;
			}
		}
		return $addin;
	}

	public function getCategories2($auth) {
		$ret = new StringsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$ret->values = parent::getCategories();
		return $ret;
	}

	public function getCategories2Detail($auth) {
		$ret = new CommentsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$db =& wfGetDB( DB_SLAVE );
		$page = $db->tableName('page');
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND page_is_redirect = 0';

		$res = $db->select( $page, 'page_title', $sql, 'SMW::getCategories' );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$t = Title::makeTitle( NS_CATEGORY, $row->page_title );
				$revision = Revision::newFromTitle( $t );

				$r = new Comment();
				$r->name = $t->getText();
				$lines = explode("\n", $revision->getComment(), 2);
				$r->comment = $lines[0];
				$result[] = $r;
			}
		}
		$db->freeResult($res);
		$ret->values = $result;

		return $ret;
	}

	public function getProperties2($auth) {
		$ret = new StringsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$ret->values = parent::getProperties();
		return $ret;
	}

	public function getTitles2($auth, $categoryTitles) {
		$ret = new StringsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$ret->values = parent::getTitles($categoryTitles);
		return $ret;
	}

	public function getTitleProperties2($auth, $title) {
		$ret = new PropertyPairsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$ret->values = parent::getTitleProperties($title);

		// Mapped form lookup
		// E.g.
		// Applicable forms: Outlook Mail, Wiki Mail, Appointment
		// Lookup one level depth:
		// (Outlook Mail, Wiki Mail),
		// (Outlook Mail, Wiki Mail, Appointment, Bug, Status, Milestone),
		// (Wiki Mail, Appointment, Outlook Appointment)
		//
		// returns
		// {form}->Outlook Mail
		// {form}->Wiki Mail
		// {form}->Bug
		// {form}->Status
		// {form}->Milestone
		// {form}->Appointment
		// {form}->Outlook Appointment

		if( $this->validSemanticConnector() ) {
			$enabledForms = SCProcessor::getEnabledForms($title);
			if(count($enabledForms) > 0) {
				$possible_forms = SCProcessor::getPossibleForms($enabledForms, 0);
				foreach($possible_forms as $possible_form) {
					$prop = new PropertyPair;
					$prop->name = '{form}';
					$prop->value = $possible_form;
					$ret->values[] = $prop;
				}
			}
		}

		return $ret;
	}
	
	/**
	 * Get Form/Field/Values for specified page
	 * '{free text}' for free text with no templates
	 *
	 * @param AuthenticationType $auth
	 * @param string $title
	 * @param string $form_name
	 * @return PropertyPairsType
	 */
	public function getFormFieldValues($auth, $title, $form_name) {
		$ret = new PropertyPairsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;
		$ret->values = array();

		if( $this->validSemanticConnector() ) {
			foreach(SCProcessor::getPageMappedFormData($title, $form_name) as $template_field => $val) {
				$prop = new PropertyPair;
				$prop->name = $template_field;
				$prop->value = $val;
				$ret->values[] = $prop;
			}
		}

		return $ret;
	}

	public function addCategory2($auth, $categoryName) {
		$ret = smwgWTExceptionCheck($auth, true);
		if($ret->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		if(!parent::addCategory($categoryName)) {
			$ret->ret_code = SWT_ADDIN_E_CREATE_CATEGORY;
			$ret->message = "Error when creating new category.";
		}

		return $ret;
	}

	public function saveNewMail2($auth, $subject, $sender, $receivers, $ccs, $sentDate, $basedMailPage, $action, $categories, $body, $attachments) {
		$ret = smwgWTExceptionCheck($auth, true);
		if($ret->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$title = parent::saveNewMail($subject, $sender, $receivers, $ccs, $sentDate, $basedMailPage, $action, $categories, $body, $attachments);
		$ret->message = $title;
		return $ret;
	}

	public function getPageInfo2($auth, $title) {
		$ret = new PageInfoType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$ret->info = parent::getPageInfo($title);
		
		if( $this->validSemanticConnector() ) {
			if($ret->info !== NULL) {
				$af = SCProcessor::getActivedForm($title);
				if($af !== NULL) {
					$prop = new PropertyPair;
					$prop->name = '{default form}';
					$prop->value = $af[0];
					$ret->info->properties[] = $prop;
				}
			}
		}
		
		return $ret;
	}

	/**
	 * Get all mapped form names related to specified form
	 * E.g. ['Project bug', 'Project report'] is related to 'Upload mail'
	 *
	 * @param AuthenticationType $auth
	 * @param string $form_name
	 *
	 * @return string[]
	 */
	public function getPossibleForms($auth, $form_name) {
		$ret = new StringsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		if( $this->validSemanticConnector() ) {
			$ret->values = SCProcessor::getPossibleForms(array(Title::newFromText($form_name)->getText()), 0);
		} else {
			$ret->values = array();
		}
		
		return $ret;
	}

	/**
	 * Save page to wiki with specified forms, return Wiki title if success, otherwise, return null
	 * E.g.
	 * savePageToForm(
	 * 	"Test",
	 * 	"Upload Mail",
	 * 	new PropertyPair[] { new PropertyPair(sender), new PropertyPair(receiver), new PropertyPair(cc), new PropertyPair(sent date) },
	 * 	"This is just a test",
	 * 	new string[] { "hello", "cool" },
	 * 	"Project bug",
	 * 	new string[] { "Project report" },
	 *  false
	 * );
	 *
	 * @param AuthenticationType $auth
	 * @param string $form_name
	 * @param string $page_name
	 * @param PropertyPairArray $properties
	 * @param string $freetext
	 * @param string[] $categories
	 * @param string $active_form
	 * @param string[] $applicable_forms
	 * @param bool $no_wrapper
	 *
	 * @return ExceptionType
	 */
	public function savePageToForm($auth, $form_name, $page_name, $properties, $freetext, $categories, $active_form, $applicable_forms, $no_wrapper) {
		$ret = smwgWTExceptionCheck($auth, true);
		if($ret->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$form_title = Title::newFromText( $form_name, SF_NS_FORM );
		if(!$form_title->exists()) {
			$ret->ret_code = SWT_ADDIN_E_FORM_NOT_EXIST;
			$ret->message = "Form does not exist";
			return $ret;
		}

		$content = "";
		if( $this->validSemanticConnector() ) {
			SCProcessor::resetPageForms($page_name);

			$templates = SCProcessor::getTemplateField($form_name);

			$tfs = array();
			foreach($properties as $pv) {
				$tf = explode('.', $pv->name, 2);
				$t = Title::newFromText($tf[0],NS_TEMPLATE)->getText();
				$tfs[$t][] = array('field' => $tf[1], 'value' => $pv->value);
				if(!isset($templates[$t]) || !in_array($tf[1], $templates[$t])) {
					$ret->ret_code = SWT_ADDIN_E_FORM_NOT_MATCH;
					$ret->message = "Form or template does not match";
					return $ret;
				}
			}
			foreach($tfs as $template => $fv) {
				$content .= "{{" . $template . "\n";
				foreach($fv as $pv) {
					$content .= "|" . $pv['field'] . "=" . $pv['value'] . "\n";
				}
				$content .= "}}\n";
			}
		}
		if(!$no_wrapper) {
			$freetext = str_replace("</nowiki>", "</ nowiki>", $freetext); // trick here
			$freetext = str_replace("\n", "</nowiki>\n\n<nowiki>", $freetext);
	
			$content .= "<nowiki>";
		}
		$content .= $freetext;
		if(!$no_wrapper) {
			$content .= "</nowiki>\n\n";
		}

		$fname = "SMW::savePageToForm";
		$db =& wfGetDB( DB_SLAVE );

		$cates = array();
		if($categories !== NULL && isset($categories) && count($categories) > 0) {
			foreach($categories as $category) {
				$cates[] = str_replace('_', ' ', ucfirst($category));
			}
		}

		// just overwrite the subject
		$title = Title::newFromText( $page_name );
		global $wgTitle;
		$wgTitle = $title;
		if($title->exists()) {
			// merge categories
			extract( $db->tableNames('categorylinks', 'page') );
			$res = $db->query("SELECT $categorylinks.cl_to FROM $categorylinks LEFT JOIN $page
			ON $categorylinks.cl_from = $page.page_id
			WHERE $categorylinks.cl_sortkey = '".wfAddInStrencode($title->getText())."' AND $page.page_namespace = ".NS_MAIN, $fname);

			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$cates[] = str_replace( '_', ' ', ucfirst($row->cl_to) );
				}
			}
			$db->freeResult($res);
		}

		foreach(array_unique($cates) as $c) {
			$content .= "[[Category:" . $c . "]]";
		}

		if(!$no_wrapper) {	
			$content .= "\n\n''This article is generated via Page Upload extension by third party webservice clients like Microsoft Outlook Addin; ".
				"any edits on this page could be overwritten by future uploads under the same subject.''";
		}
		
		$revision = Revision::newFromTitle( $title );
		//		if (( $revision === NULL ) || ($revision->getText() != $content)) {
		$article = new Article($title);
		if ( $revision === NULL ) {
			$article->doEdit($content,'');
		}
			
		if( $this->validSemanticConnector() ) {
			SCProcessor::saveEnabledForms($title->getText(), $form_title->getText());
			$content = SCProcessor::toMappedFormContent($content, $title, Title::newFromText( $active_form, SF_NS_FORM ));
			$article->doEdit($content,'');
			if(!is_array($applicable_forms)) $applicable_forms = array();
			SCProcessor::saveEnabledForms($page_name, $active_form, array_merge($applicable_forms, array($form_title->getText())));
		}
		//		}
		$ret->message = $title->getText();
		return $ret;
	}
	/**
	 * Save page to wiki with specified form, return Wiki title if success, otherwise, return null
	 * E.g.
	 * savePageBasic(
	 * 	$auth,
	 * 	"Upload Mail",
	 * 	new PropertyPair[] { new PropertyPair(sender), new PropertyPair(receiver), new PropertyPair(cc), new PropertyPair(sent date) },
	 * 	"This is just a test"
	 * );
	 *
	 * @param AuthenticationType $auth
	 * @param string $form_name
	 * @param string $page_name
	 * @param PropertyPairArray $properties
	 * @param string $freetext
	 *
	 * @return ExceptionType
	 */
	public function savePageBasic($auth, $form_name, $page_name, $properties, $freetext) {
		return $this->savePageToForm($auth, $form_name, $page_name, $properties, $freetext, $categories, $form_name, NULL, false);
	}

	/**
	 * Set page forms on wiki, return Wiki title if success, otherwise, return null
	 * E.g.
	 * setPageForms(
	 * 	$auth,
	 * 	"Upload Mail",
	 * 	"Project bug",
	 * 	new string[] { "Project report" }
	 * );
	 *
	 * @param AuthenticationType $auth
	 * @param string $page_name
	 * @param string $active_form
	 * @param string[] $applicable_forms
	 *
	 * @return string
	 */
	public function setPageForms($auth, $page_name, $active_form, $applicable_forms) {
		$ret = smwgWTExceptionCheck($auth, true);
		if($ret->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$form_title = Title::newFromText( $active_form, SF_NS_FORM );
		if(!$form_title->exists()) {
			$ret->ret_code = SWT_ADDIN_E_FORM_NOT_EXIST;
			$ret->message = "Form does not exist";
			return $ret;
		}

		$title = Title::newFromText( $page_name );
		if(!$title->exists()) {
			$ret->ret_code = SWT_ADDIN_E_PAGE_NOT_EXIST;
			return $ret;
		}

		if( $this->validSemanticConnector() ) {
			$revision = Revision::newFromTitle( $title );
			$content = $revision->getText();

			$article = new Article($title);
			$content = SCProcessor::toMappedFormContent($content, $title, $form_title);
			$article->doEdit($content,'');
			if(!is_array($applicable_forms)) $applicable_forms = array();
			SCProcessor::saveEnabledForms($page_name, $active_form, array_merge($applicable_forms, array($form_title->getText())));
		}

		$ret->message = $title->getText();
		return $ret;
	}

	/**
	 * Create form and template for Add-In items
	 *
	 * E.g.
	 * createFormTemplate(
	 * 	"Test",
	 * 	"Contact",
	 * 	new PropertyPair[] { new PropertyPair(sender), new PropertyPair(receiver), new PropertyPair(cc), new PropertyPair(sent date) }
	 * );
	 *
	 * @param AuthenticationType $auth
	 * @param string $name
	 * @param PropertyPairArray $properties, PropertyPair value uses property type, defined as SMW_TYPE_xxx
	 *
	 * @return string
	 */
	public function createFormTemplate($auth, $name, $properties) {
		$ret = smwgWTExceptionCheck($auth, true);
		if($ret->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		global $wgTitle;

		// create properties
		foreach($properties as $p) {
			if($p->value == SMW_TYPE_STRING) {
			} else if($p->value == SMW_TYPE_DATE) {
			} else if($p->value == SMW_TYPE_NUMBER) {
			} else if($p->value == SMW_TYPE_EMAIL) {
			} else if($p->value == SMW_TYPE_PAGE) {
			}
		}

		// create template
		$pre = "";
		$template = "";
		foreach($properties as $p) {
			$pre .= "|" . $p->name . "=\n";
			$template .= "
{{#if:{{{" . $p->name . "|}}}|
! " . $p->name . "
{{!}} {{{" . $p->name . "|}}}
{{!}}-
}}";
		}
		$content = "
<noinclude>
This is the '" . $name . "' template.
It should be called in the following format:
<pre>
{{" . $name . "
		$pre
	}}
	</pre>
	Edit the page to see the template text.
	</noinclude><includeonly>
	----
	{|
	$template
	|}
	</includeonly>
";
	$title = Title::newFromText( $name, NS_TEMPLATE );
	$wgTitle = $title;
	$article = new Article($title);
	$article->doEdit($content,'');

	// create form
	$content = "
<noinclude>
This is the '" . $name . "' form.
To add a page with this form, enter the page name below;
if a page with that name already exists, you will be sent to a form to edit that page.

{{#forminput:" . $name . "}}

</noinclude><includeonly>
{{{for template|" . $name . "}}}
{{{end template}}}

This is a page created by some upload clients. Please edit this page with other forms.

{{{standard input|cancel}}}
</includeonly>
";
	$title = Title::newFromText( $name, SF_NS_FORM );
	$wgTitle = $title;
	$article = new Article($title);
	$article->doEdit($content,'');

	$ret->message = $title->getText();
	return $ret;
	}

	/**
	 * Get stopword list on wiki
	 *
	 * @param AuthenticationType $auth
	 *
	 * @return StringsType
	 */
	public function getStopwords($auth) {
		$ret = new StringsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$arr = array();

		global $wgEnableWikiTagsStopword;
		if($wgEnableWikiTagsStopword) {

			global $wgUploadDirectory;
			$file = $wgUploadDirectory.'/stopword.txt';
			if(file_exists($file)) {
				$stopword = file_get_contents($file);
				if($stopword !== FALSE) {
					$arr = explode("\n", $stopword);
				}
			}
		}

		$ret->values = $arr;
		return $ret;
	}

	/**
	 * Get default settings on wiki
	 *
	 * stopword => if stopword is enabled
	 * trigram_threshold => threshold of trigram
	 *
	 * @param AuthenticationType $auth
	 *
	 * @return PropertyValue[]
	 */
	public function getWikiSettings($auth) {
		$ret = new PropertyPairsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$vals = array();

		global $wgEnableWikiTagsStopword, $wgWikiTagsTrigramThreshold;
		if($wgEnableWikiTagsStopword) {
			$prop = new PropertyPair;
			$prop->name = 'stopword';
			$prop->value = strval($wgEnableWikiTagsStopword);
			$vals[] = $prop;
		}
		if($wgWikiTagsTrigramThreshold && $wgWikiTagsTrigramThreshold > 0 && $wgWikiTagsTrigramThreshold <= 1) {
			$prop = new PropertyPair;
			$prop->name = 'trigram_threshold';
			$prop->value = strval($wgWikiTagsTrigramThreshold);
			$vals[] = $prop;
		}

		$ret->values = $vals;

		return $ret;
	}

	private function validSemanticForm() {
//		return (defined( 'SF_VERSION' ) && preg_match('/^1\.[79]/', SF_VERSION));
		return (defined( 'SF_VERSION' ));
	}
	
	private function validSemanticConnector() {
		return (defined( 'SMW_CONNECTOR_VERSION' ));
	}

	private function __getSiteFormSettings($form_name) {
		$vals = array();
		if( !$form_name ) return $vals;

		$form_printer = new SFFormPrinter();
		// based on SF 1.7 source, SF_FormPrinter.inc, function formHTML
		$form_title = Title::makeTitleSafe(SF_NS_FORM, $form_name);
		$form_article = new Article($form_title);
		$form_def = $form_article->getContent();

		$form_def = StringUtils::delimiterReplace('<noinclude>', '</noinclude>', '', $form_def);
		$form_def = strtr($form_def, array('<includeonly>' => '', '</includeonly>' => ''));

		// parse wiki-text
		// add '<nowiki>' tags around every triple-bracketed form definition
		// element, so that the wiki parser won't touch it - the parser will
		// remove the '<nowiki>' tags, leaving us with what we need
		global $sfgDisableWikiTextParsing;
		if (! $sfgDisableWikiTextParsing) {
			global $wgParser, $wgUser;
			$form_def = "__NOEDITSECTION__" . strtr($form_def, array('{{{' => '<nowiki>{{{', '}}}' => '}}}</nowiki>'));
			$wgParser->mOptions = new ParserOptions();
			$wgParser->mOptions->initialiseFromUser($wgUser);
			$form_def = $wgParser->parse($form_def, $form_title, $wgParser->mOptions)->getText();
		}

		// turn form definition file into an array of sections, one for each
		// template definition (plus the first section)
		$form_def_sections = array();
		$start_position = 0;
		$section_start = 0;
		$all_values_for_template = array();
		// unencode and HTML-encoded representations of curly brackets and
		// pipes - this is a hack to allow for forms to include templates
		// that themselves contain form elements - the escaping is needed
		// to make sure that those elements don't get parsed too early
		$form_def = str_replace(array('&#123;', '&#124;', '&#125;'), array('{', '|', '}'), $form_def);
		// and another hack - replace the 'free text' standard input with
		// a field declaration to get it to be handled as a field
		$form_def = str_replace('standard input|free text', 'field|<freetext>', $form_def);
		while ($brackets_loc = strpos($form_def, "{{{", $start_position)) {
			$brackets_end_loc = strpos($form_def, "}}}", $brackets_loc);
			$bracketed_string = substr($form_def, $brackets_loc + 3, $brackets_end_loc - ($brackets_loc + 3));
			$tag_components = explode('|', $bracketed_string);
			$tag_title = trim($tag_components[0]);
			if ($tag_title == 'for template' || $tag_title == 'end template') {
				// create a section for everything up to here
				$section = substr($form_def, $section_start, $brackets_loc - $section_start);
				$form_def_sections[] = $section;
				$section_start = $brackets_loc;
			}
			$start_position = $brackets_loc + 1;
		} // end while
		$form_def_sections[] = trim(substr($form_def, $section_start));

		// cycle through form definition file (and possibly an existing article
		// as well), finding template and field declarations and replacing them
		// with form elements, either blank or pre-populated, as appropriate
		$all_fields = array();
		$template_name = "";
		for ($section_num = 0; $section_num < count($form_def_sections); $section_num++) {
			$tif = new SFTemplateInForm();
			$start_position = 0;
			$template_text = "";
			// the append is there to ensure that the original array doesn't get
			// modified; is it necessary?
			$section = " " . $form_def_sections[$section_num];

			while ($brackets_loc = strpos($section, '{{{', $start_position)) {
				$brackets_end_loc = strpos($section, "}}}", $brackets_loc);
				$bracketed_string = substr($section, $brackets_loc + 3, $brackets_end_loc - ($brackets_loc + 3));
				$tag_components = explode('|', $bracketed_string);
				$tag_title = trim($tag_components[0]);
				// =====================================================
				// for template processing
				// =====================================================
				if ($tag_title == 'for template') {
					$template_name = trim($tag_components[1]);
					$tif->template_name = $template_name;

					$template_label = $template_name;
					// cycle through the other components
					for ($i = 2; $i < count($tag_components); $i++) {
						$component = $tag_components[$i];
						$sub_components = explode('=', $component);
						if (count($sub_components) == 2) {
							if ($sub_components[0] == 'label') {
								$template_label = $sub_components[1];
							}
						}
					}

					$all_fields = $tif->getAllFields();

					$start_position = $brackets_end_loc;
					// =====================================================
					// end template processing
					// =====================================================
				} elseif ($tag_title == 'end template') {

					// remove this tag, reset some variables, and close off form HTML tag
					$start_position = $brackets_end_loc;

					$template_name = null;
					if (isset($template_label)) {
						unset ($template_label);
					}
					// =====================================================
					// field processing
					// =====================================================
				} elseif ($tag_title == 'field') {
					$field_name = trim($tag_components[1]);
					// cycle through the other components
					$is_mandatory = false;
					//          $is_hidden = false;
					//          $is_restricted = false;
					//          $is_uploadable = false;
					$is_list = false;
					$input_type = null;
					$field_args = array();
					$default_value = "";
					$possible_values = null;
					$semantic_property = null;
					$default_value = "";
					for ($i = 2; $i < count($tag_components); $i++) {
						$component = trim($tag_components[$i]);
						if ($component == 'mandatory') {
							$is_mandatory = true;
							//            } elseif ($component == 'hidden') {
							//              $is_hidden = true;
							//            } elseif ($component == 'restricted') {
							//              $is_restricted = true;
						} elseif ($component == 'uid') {
							// uid patch
							$field_args['uid'] = true;
							//            } elseif ($component == 'uploadable') {
							//              $field_args['is_uploadable'] = true;
						} elseif ($component == 'list') {
							$is_list = true;
						} else {
							$sub_components = explode('=', $component);
							if (count($sub_components) == 2) {
								if ($sub_components[0] == 'input type') {
									$input_type = $sub_components[1];
								} elseif ($sub_components[0] == 'default') {
									$default_value = $sub_components[1];
								} elseif ($sub_components[0] == 'values') {
									// remove whitespaces from list
									$possible_values = array_map('trim', explode(',', $sub_components[1]));
								} elseif ($sub_components[0] == 'values from category') {
									$possible_values = SFUtils::getAllPagesForCategory($sub_components[1], 10);
								} elseif ($sub_components[0] == 'values from concept') {
									$possible_values = SFUtils::getAllPagesForConcept($sub_components[1]);
								} elseif ($sub_components[0] == 'property') {
									$semantic_property = $sub_components[1];
									//                } elseif ($sub_components[0] == 'default filename') {
									//                  $default_filename = str_replace('&lt;page name&gt;', $page_title, $sub_components[1]);
									//                  $field_args['default filename'] = $default_filename;
								} else {
									$field_args[$sub_components[0]] = $sub_components[1];
								}
							}
						}
					}

					$settings = array();
					if(array_key_exists('uid', $field_args)) {
						// special case, the uid
						$settings["diabled"] = true;
						if (array_key_exists('id_prefix', $field_args)) {
							$default_value = SFFormInputs::getId($field_args['id_prefix']);
						} else {
							global $sfgIdPrefix;
							$default_value = SFFormInputs::getId($sfgIdPrefix);
						}
						$settings["default_value"] = $default_value;
					} else {
						// create an SFFormField instance based on all the
						// parameters in the form definition, and any information from
						// the template definition (contained in the $all_fields parameter)
						$form_field = SFFormField::createFromDefinition($field_name, $input_name,
							$is_mandatory, $is_hidden, $is_uploadable, $possible_values, $is_disabled,
							$is_list, $input_type, $field_args, $all_fields, $strict_parsing);
						// if a property was set in the form definition, overwrite whatever
						// is set in the template field - this is somewhat of a hack, since
						// parameters set in the form definition are meant to go into the
						// SFFormField object, not the SFTemplateField object it contains;
						// it seemed like too much work, though, to create an
						// SFFormField::setSemanticProperty() function just for this call
						if ($semantic_property != null) {
							$form_field->template_field->setSemanticProperty($semantic_property);
						}
						if ($is_list) {
							if(array_key_exists('delimiter', $field_args)) {
								$is_list = $field_args['delimiter'];
							} else {
								$is_list = ",";
							}
						}

						if ($form_field->input_type != '' &&
							array_key_exists($form_field->input_type, $form_printer->mInputTypeHooks) &&
							$form_printer->mInputTypeHooks[$form_field->input_type] != null) {

							// last argument to function should be a hash, merging the default
							// values for this input type with all other properties set in
							// the form definition, plus some semantic-related arguments
							$hook_values = $form_printer->mInputTypeHooks[$form_field->input_type];
							$other_args = $form_field->getArgumentsForInputCall($hook_values[1]);
						} else { // input type not defined in form
							$template_field = $form_field->template_field;
								
							$field_type = $template_field->field_type;
							$is_list = ($form_field->is_list || $template_field->is_list);
							if ($field_type != '' &&
								array_key_exists($field_type, $form_printer->mSemanticTypeHooks) &&
								isset($form_printer->mSemanticTypeHooks[$field_type][$is_list])) {

								$hook_values = $form_printer->mSemanticTypeHooks[$field_type][$is_list];
								$other_args = $form_field->getArgumentsForInputCall($hook_values[1]);

								if($hook_values[0][1] == 'textAreaHTML') $input_type = 'textarea';
								else if($hook_values[0][1] == 'checkboxHTML') $input_type = 'checkbox';
								else if($hook_values[0][1] == 'dateEntryHTML') $input_type = 'date';
								else if($hook_values[0][1] == 'dropdownHTML') $input_type = 'dropdown';
								else if($hook_values[0][1] == 'checkboxesHTML') $input_type = 'checkboxes';
							}
						}
						if($is_mandatory) $settings["is_mandatory"] = true;
						if($other_args['is_list']) {
							if(array_key_exists('delimiter', $field_args)) {
								$delimiter = $field_args['delimiter'];
							} else {
								$delimiter = ",";
							}
							$settings["is_list"] = $delimiter;
						}
						if( $form_field->template_field->semantic_property ) {
							$settings["property"] = $form_field->template_field->semantic_property;
						}
						
						if(isset($input_type)) $settings["input_type"] = $input_type;
						if(isset($default_value) && $default_value!='') $settings["default_value"] = $default_value;
						if(isset($possible_values)) $settings["possible_values"] = implode(',', $possible_values);
						if(count($other_args['possible_values']) > 0) $settings["possible_values"] = implode(',', $other_args['possible_values']);
					}
					if($template_name != null && $template_name !== '') {
						$vals[] = array( "name" => "$template_name.$field_name", "value" => $settings );
					}

					$start_position = $brackets_end_loc;

					// =====================================================
					// default outer level processing
					// =====================================================
				} else { // tag is not one of the three allowed values
					// ignore tag
					$start_position = $brackets_end_loc;
				} // end if
			} // end while
		} // end for
		
		return $vals;
	}
	
	/**
	 * Get template field type of a form.
	 *
	 * template.field => field render type, date / text / textarea / dropdown list:value 1|value 2|...
	 * param=value, delimiter is set to "|"
	 *
	 * disabled
	 * is_mandatory
	 * is_list = delimiter
	 * input_type = input type
	 *     (
	 *       text, textarea, combobox, date, datetime, datetime with timezone,
	 *       radiobutton, checkbox, checkboxes, dropdown and listbox
	 *     )
	 * default_value =
	 * possible_values =
	 *
	 * E.g.,
	 * input_type=dropdown list|is_mandatory|is_list = ,|default_value = test|possible_values = possible,test,good
	 * for uid
	 * input type=textbox|disabled|default_value=BUG-001
	 *
	 * @param AuthenticationType $auth
	 * @param string $form_name
	 * @return PropertyPairsType
	 */
	public function getSiteFormSettings($auth, $form_name) {
		$ret = new PropertyPairsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;
		
		$vals = array();
		if( $this->validSemanticForm() ) {
			$settings = $this->__getSiteFormSettings($form_name);
			foreach($settings as $v) {
				$settings = '';
				$val = $v["value"];
				if(isset($val["diabled"])) $settings .= "|diabled";
				if(isset($val["is_mandatory"])) $settings .= "|is_mandatory";
				if(isset($val["is_list"])) $settings .= "|is_list=" . $val["is_list"];
				if(isset($val["input_type"])) $settings .= "|input_type=" . $val["input_type"];
				if(isset($val["default_value"])) $settings .= "|default_value=" . $val["default_value"];
				if(isset($val["possible_values"])) $settings .= "|possible_values=" . $val["possible_values"];
				
				$prop = new PropertyPair;
				$prop->name = $v["name"];
				$prop->value = $settings;
				$vals[] = $prop;
			}
		}
		$ret->values = $vals;

		return $ret;
	}
	
	private function __getPageCurrentForm($page_name) {
		if( !$this->validSemanticForm() ) {
			return NULL;
		}

		$form_name = "";
		
		$page_title = Title::newFromText( $page_name );
		$article = new Article($page_title);
		$forms = SFLinkUtils::getFormsForArticle($article);
		if(count($forms)>0) $form_name = $forms[0];
		
//		$revision = Revision::newFromTitle( $page_title );
//		
//		if ( $revision !== NULL ) {
//			if(class_exists("SCProcessor")) {
//				$sStore = SCStorage::getDatabase();
//				$pid = $page_title->getArticleID();
//				$current_form = $sStore->getCurrentForm($pid);
//				if($current_form !== NULL) {
//					$form_name = Title::makeTitleSafe(SF_NS_FORM, $current_form)->getText();
//				}
//			}
//		}
		return $form_name;
	}
	
	/**
	 * Get current form of specified page
	 * If no form matched, returned message will be blank
	 *
	 * @param AuthenticationType $auth
	 * @param string $page_name
	 * @return ExceptionType
	 */
	public function getPageCurrentForm($auth, $page_name) {
		$ret = smwgWTExceptionCheck($auth, true);
		if($ret->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;
		
		$form_name = $this->__getPageCurrentForm($page_name);
		if($form_name === NULL) {
			return $ret;
		}
		$ret->message = $form_name;
		
		return $ret;
	}

	/**
	 * Get form mapping settings, result in src_template.field => target_template.field pair
	 *
	 * @param AuthenticationType $auth
	 * @param string $src_form_name
	 * @param string $target_form_name
	 * @return PropertyPairsType
	 */
	public function getFormMappingSettings($auth, $src_form_name, $target_form_name) {
		$ret = new PropertyPairsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$vals = array();

		if( $this->validSemanticConnector() ) {
			$sStore = SCStorage::getDatabase();
			$form_name = Title::newFromText($src_form_name)->getText();
			$res = $sStore->lookupFormMapping($form_name);

			foreach($res as $r) {
				$m = explode('.', $r['map']);
				if($m[0] == $target_form_name) {
					$prop = new PropertyPair;
					$prop->name = $r['src'];
					$prop->value = $r['map'];
					$vals[] = $prop;
				}
			}
		}

		$ret->values = $vals;

		return $ret;
	}

	/**
	 * Same as method 'getTitles2', the first value in return->values is server timestamp.
	 * First character is reserved, '+xxx' for add or modifed titles, '-xxx' for removed titles
	 *
	 * If timestamp is '0' or null, all titles will thus be returned
	 *
	 * @param AuthenticationType $auth
	 * @param string[] $categoryTitles
	 * @param string $timestamp
	 * @return StringsType
	 */
	public function getUpdatedTitles($auth, $categoryTitles, $timestamp) {
		$ret = new StringsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$result = array();

		if($timestamp === 0) {
			$db =& wfGetDB( DB_SLAVE );

			$page = $db->tableName('page');

			if (($categoryTitles == NULL) || (count($categoryTitles) == 0)) {
				$sql = '(page_namespace='. NS_MAIN .' or page_namespace='.NS_CATEGORY.')';
				//$sql = '(page_namespace='. NS_MAIN .')';
				$res = $db->select( $page, 'page_title', $sql, 'SMW::getTitles');
			} else {
				$categorylinks = $db->tableName('categorylinks');
				$cates = '\'\'';
				foreach($categoryTitles as $cate){
					$cates .= ',\''.wfAddInStrencode(Title::makeTitle(NS_CATEGORY, $cate)->getDBkey()).'\'';
				}
				$res = $db->query('SELECT p.page_title from '.$page.' p LEFT JOIN '.$categorylinks.' c ON c.cl_from=p.page_id WHERE c.cl_to IN ('.$cates.') AND p.page_namespace=' . NS_MAIN);
			}
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = '+' . Title::makeTitle( NS_MAIN, $row->page_title)->getText();
				}
			}
			$db->freeResult($res);
		}

		$ret->values = $result;
		array_unshift($ret->values, strval(time()));
		return $ret;
	}
	
	private function getPropertyInputType($field) {
		$form_printer = new SFFormPrinter();
		$field_type = $field->field_type;
		if ($field_type != '' &&
			array_key_exists($field_type, $form_printer->mSemanticTypeHooks) &&
			isset($form_printer->mSemanticTypeHooks[$field_type][false])) {
				
			$hook_values = $form_printer->mSemanticTypeHooks[$field_type][false];
			if($hook_values[0][1] == 'textAreaHTML') $input_type = 'textarea';
			else if($hook_values[0][1] == 'checkboxHTML') $input_type = 'checkbox';
			else if($hook_values[0][1] == 'dateEntryHTML') $input_type = 'date';
			else if($hook_values[0][1] == 'dropdownHTML') $input_type = 'dropdown';
			else if($hook_values[0][1] == 'checkboxesHTML') $input_type = 'checkboxes';
		}
		return $input_type;
	}
	
	/**
	 * @param AuthenticationType $auth
	 * @param string $page_name
	 * @return PropertyPairsType
	 */
	public function getTitlePropertyDefinitions($auth, $page_name) {
		$ret = new PropertyPairsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$page_title = Title::newFromText( $page_name );
		if( !$page_title->exists() ) {
			$ret->exception = SWT_ADDIN_E_PAGE_NOT_EXIST;
			return $ret;
		}
		
		$ret->values = array();
		
		if( !$this->validSemanticForm() ) {
			return $ret;
		}
		
//		$form_name = $this->__getPageCurrentForm($page_name);
//		$form_setting = $this->__getSiteFormSettings($form_name);
		$template_field = SFTemplateField::create('', ucfirst(''));
		
		$article = new Article($page_title);
		$tfvs = AddIn2::parseToTemplates($article->getContent());
		
		// property => flag | ...
		// id = 
		// input_type = input type
		//    (
		//       text, textarea, combobox, date, datetime, datetime with timezone,
		//       radiobutton, checkbox, checkboxes, dropdown and listbox
		//    )
		// possible_values =
		
		foreach($tfvs as $id => $tfv) {
			if(!is_array($tfv)) {
				// get semantic data in plain wiki
				$text = $tfv;
				$offset = 0;
				$len = strlen($text);
				$result = '';
				do {
					$min = $len;
					$type = FALSE;
					$idx_nowiki = stripos($text, '<nowiki>', $offset);
					if($idx_nowiki !== FALSE) {
						$min = $idx_nowiki;
						$type = 1;
					}
					$idx_noinclude = stripos($text, '<noinclude>', $offset);
					if($idx_noinclude !== FALSE && $idx_noinclude < $min) {
						$min = $idx_noinclude;
						$type = 2;
					}
					$idx_doublebracket = strpos($text, '{{', $offset);
					if($idx_doublebracket !== FALSE && $idx_doublebracket < $min) {
						$min = $idx_doublebracket;
						$type = 3;
					}
					if($type !== FALSE) {
						$result .= substr($text, $offset, $min - $offset);
						$offset = $min;
						if($type == 1) {
							$offset = stripos($text, '</nowiki>', $offset);
							$offset += strlen('</nowiki>');
						}else if($type == 2) {
							$offset = stripos($text, '</noinclude>', $offset);
							$offset += strlen('</noinclude>');
						}else if($type == 3) {
							$brackets = 2;
							$offset += 2;
							$square_brackets = 0;
							for(;$offset<$len;++$offset) {
								$c = $text{$offset};
								if($c == '[') ++$square_brackets;
								else if($c == ']') --$square_brackets;
								else if($square_brackets == 0) {
									if($c == '{') ++$brackets;
									else if($c == '}') {
										--$brackets;
										if($brackets == 0) {
											++ $offset;
											break;
										}
									}
								}
							}
						}
						if($offset <= $min) {
							$offset = $len;
							$type = FALSE;
						}
					}
				} while ($type !== FALSE);
				$result .= substr($text, $offset, $len - $offset);
				if (preg_match_all('/\[\[([^:=\[\]]*:*?[^:=\[\]]*)(:[:=])([^\]\|]*).*?\]\]/mis', $result, $matches)) {
					foreach ($matches[1] as $i => $semantic_property) {
						$value = $matches[3][$i];
						$template_field->setSemanticProperty(ucfirst($semantic_property));
						
						$input_type = $this->getPropertyInputType($template_field);
						
						$args = "id=$id.$semantic_property.$value";
						if( $input_type ) $args .= "|input_type=$input_type";
						if( count($template_field->possible_values) > 0) {
							$args .= "|possible_values=" . implode(',', $template_field->possible_values);
						}

						$prop = new PropertyPair;
						$prop->name = $semantic_property;
						$prop->value = $args;
						$ret->values[] = $prop;
					}
				}
				continue;
			}
			$tif = new SFTemplateInForm();
			$tif->template_name = $tfv["name"];
			$all_fields = $tif->getAllFields();

			foreach($all_fields as $field) {
				if(!$field->semantic_property) continue;
				if( isset( $tfv['fields'][$field->field_name] ) ) {
					// apply multiple controls, TBD!!!
					$input_type = $this->getPropertyInputType($field, $tfv['fields'][$field->field_name]);

					$args = "id=$id.{$field->semantic_property}.{$tfv['fields'][$field->field_name]}";
					if( $input_type ) $args .= "|input_type=$input_type";
					if( count($field->possible_values) > 0) {
						$args .= "|possible_values=" . implode(',', $field->possible_values);
					}
					$prop = new PropertyPair;
					$prop->name = $field->semantic_property;
					$prop->value = $args;
					$ret->values[] = $prop;
				} else {
//					// to add new field values, TBD!!!
//					$this->getPropertyInputType($field, '');
				}
			}
		}

		return $ret;
	}
	
	/**
	 * @param AuthenticationType $auth
	 * @param string $page_name
	 * @param string $prop_id
	 * 		id.property.value
	 * @param string $property
	 * @param string $new_value
	 * @return ExceptionType
	 */
	public function updateTitlePropertyValue($auth, $page_name, $prop_id, $property, $new_value) {
		$ret = smwgWTExceptionCheck($auth, true);
		if($ret->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;
		$ret->message = '';

		$page_title = Title::newFromText( $page_name );
		if( !$page_title->exists() ) {
			$ret->exception = SWT_ADDIN_E_PAGE_NOT_EXIST;
			return $ret;
		}
		
		if( !$this->validSemanticForm() ) {
			return $ret;
		}
		
		global $wgTitle;
		$wgTitle = $page_title;
		
		$pro = explode(".", $prop_id, 2);
		$idx = intval($pro[0]);
		
		$template_field = SFTemplateField::create('', ucfirst(''));
		
		$article = new Article($page_title);
		$text = $article->getContent();
		$content = '';
		
		$idx2 = 0;
		$start = 0;
		$offset = 0;
		$len = strlen($text);
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos($text, '<nowiki>', $offset);
			if($idx_nowiki !== FALSE) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos($text, '<noinclude>', $offset);
			if($idx_noinclude !== FALSE && $idx_noinclude < $min) {
				$min = $idx_noinclude;
				$type = 2;
			}
			$idx_triplebracket = strpos($text, '{{{', $offset);
			$idx_doublebracket = strpos($text, '{{', $offset);
			if($idx_doublebracket !== FALSE && $idx_doublebracket < $min && $idx_doublebracket !== $idx_triplebracket) {
				$min = $idx_doublebracket;
				$type = 3;
				
				++ $idx2;
				if( $idx2 > $idx ) {
					// update property value
					$content = substr($text, 0, $start);
					$content .= $this->__updatePropertyValue( substr($text, $start, $min - $start), $prop_id, $property, $new_value );
					$content .= substr($text, $min);
					break;
				}

				if(substr($text, $min + 2, 1) == '#') {
					$is_parserfunc = true;
				}
				$pfstart = $min;
				
				$template = AddIn2::parseTemplate($text, $min);
				$start = $min;
				++ $idx2;
				if( $idx2 > $idx ) {
					// update property value
					if($is_parserfunc) {
						$content = $text;
					} else {
						$content = substr($text, 0, $pfstart);
						$content .= $this->__updateTemplatePropertyValue( $template, $prop_id, $property, $new_value );
						$content .= substr($text, $min);
					}
					break;
				}
			}
			if($type !== FALSE) {
				$offset = $min;
				if($type == 1) {
					$offset = stripos($text, '</nowiki>', $offset);
				}else if($type == 2) {
					$offset = stripos($text, '</noinclude>', $offset);
				}
			}
		} while ($type !== FALSE);
		if( $idx2 == $idx ) {
			// update property value
			$content = substr($text, 0, $start);
			$content .= substr($text, $start, $min - $start);
			$content .= substr($text, $min);
		}
		$article->doEdit($content, '');

		$ret->message = $page_title->getText();
		return $ret;
	}
	private function __updatePropertyValue( $text, $prop_id, $property, $new_value ) {
		$offset = 0;
		$len = strlen($text);
		
		$pro = explode(".", $prop_id, 2);
		
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos($text, '<nowiki>', $offset);
			if($idx_nowiki !== FALSE) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos($text, '<noinclude>', $offset);
			if($idx_noinclude !== FALSE && $idx_noinclude < $min) {
				$min = $idx_noinclude;
				$type = 2;
			}
			$idx_doublebracket = strpos($text, '{{', $offset);
			if($idx_doublebracket !== FALSE && $idx_doublebracket < $min) {
				$min = $idx_doublebracket;
				$type = 3;
			}
			if($type !== FALSE) {
				$result = substr($text, $offset, $min - $offset);
				if (preg_match_all('/\[\[([^:=\[\]]*:*?[^:=\[\]]*)(:[:=])([^\]\|]*).*?\]\]/mis', $result, $matches, PREG_OFFSET_CAPTURE)) {
					foreach ($matches[1] as $i => $semantic_property) {
						$value = $matches[3][$i][0];
						if( ("{$semantic_property[0]}.$value" == $pro[1]) && ($semantic_property[0] == $property) ) {
							$content = substr($text, 0, $offset + $matches[3][$i][1]);
							$content .= $new_value;
							$content .= substr($text, $offset + $matches[3][$i][1] + strlen($value));
							return $content;
						}
					}
				}
				$offset = $min;
				if($type == 1) {
					$offset = stripos($text, '</nowiki>', $offset);
					$offset += strlen('</nowiki>');
				}else if($type == 2) {
					$offset = stripos($text, '</noinclude>', $offset);
					$offset += strlen('</noinclude>');
				}else if($type == 3) {
					$brackets = 2;
					$offset += 2;
					$square_brackets = 0;
					for(;$offset<$len;++$offset) {
						$c = $text{$offset};
						if($c == '[') ++$square_brackets;
						else if($c == ']') --$square_brackets;
						else if($square_brackets == 0) {
							if($c == '{') ++$brackets;
							else if($c == '}') {
								--$brackets;
								if($brackets == 0) {
									++ $offset;
									break;
								}
							}
						}
					}
				}
				if($offset <= $min) {
					$offset = $len;
					$type = FALSE;
				}
			}
		} while ($type !== FALSE);
		$result = substr($text, $offset, $len - $offset);
		if (preg_match_all('/\[\[([^:=\[\]]*:*?[^:=\[\]]*)(:[:=])([^\]\|]*).*?\]\]/mis', $result, $matches, PREG_OFFSET_CAPTURE)) {
			foreach ($matches[1] as $i => $semantic_property) {
				$value = $matches[3][$i][0];
				if( ("{$semantic_property[0]}.$value" == $pro[1]) && ($semantic_property[0] == $property) ) {
					$content = substr($text, 0, $offset + $matches[3][$i][1]);
					$content .= $new_value;
					$content .= substr($text, $offset + $matches[3][$i][1] + strlen($value));
					return $content;
				}
			}
		}
		return $text;
	}
	private function __updateTemplatePropertyValue( $template, $prop_id, $property, $new_value ) {
		$pro = explode(".", $prop_id, 2);
		
		$tif = new SFTemplateInForm();
		$tif->template_name = $template["name"];
		$all_fields = $tif->getAllFields();
		foreach($all_fields as $field) {
			if(!$field->semantic_property) continue;
			if( isset( $template['fields'][$field->field_name] ) ) {
				// apply multiple controls, TBD!!!
				if( ("{$field->semantic_property}.{$template['fields'][$field->field_name]}" === $pro[1]) && 
					($field->semantic_property === $property) ) {

					$content .= "{{" . $template['name'] . "\n";
					foreach($template['fields'] as $f => $v) {
						if( $field->field_name == $f ) {
							$content .= "|{$f}={$new_value}\n";
						} else {
							$content .= "|{$f}={$v}\n";
						}
					}
					$content .= "}}\n";
					return $content;
				}
			}
		}

		$content .= "{{" . $template['name'] . "\n";
		foreach($template['fields'] as $f => $v) $content .= "|{$f}={$v}\n";
		$content .= "}}\n";
		return $content;
	}
	// code copied from SemanticConnector, SC_ArticleUtils
	static function parseTemplate( $text, &$offset ) {
		++ $offset;
		$start = $offset;
		$curly_brackets = 1;
		$square_brackets = 0;
		$len = strlen($text);
		$group = array();
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos($text, '<nowiki>', $offset);
			if($idx_nowiki !== FALSE) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos($text, '<noinclude>', $offset);
			if($idx_noinclude !== FALSE && $idx_noinclude < $min) {
				$min = $idx_noinclude;
				$type = 2;
			}
			for(; $offset < $min && $curly_brackets > 0; ++$offset) {
				$c = $text{$offset};
				if($c == '[') ++$square_brackets;
				else if($c == ']') --$square_brackets;
				else if($square_brackets == 0) {
					if($c == '{') ++$curly_brackets;
					else if($c == '}') --$curly_brackets;
					else if($curly_brackets == 2 && $c == '|') {
						$group[] = trim(substr($text, $start + 1, $offset - $start - 1));
						$start = $offset;
					}
				}
			}
			if($curly_brackets == 0) break;
			if($type !== FALSE) {
				$offset = $min;
				if($type == 1) {
					$offset = stripos($text, '</nowiki>', $offset);
				}else if($type == 2) {
					$offset = stripos($text, '</noinclude>', $offset);
				}
			}
		} while ($offset < $len);
		$group[] = trim(substr($text, $start + 1, $offset - $start - 3));
		
		$result = array('name' => Title::newFromText($group[0])->getText(), 'fields' => array());
		foreach($group as $k => $tdata) {
			if($k > 0) {
				$f = explode('=', $tdata, 2);
				$result['fields'][trim($f[0])] = trim($f[1]);
			}
		}
		return $result;
	}
	static function parseToTemplates( $text ) {
		$result = array();
		$start = 0;
		$offset = 0;
		$len = strlen($text);
		do {
			$min = $len;
			$type = FALSE;
			$idx_nowiki = stripos($text, '<nowiki>', $offset);
			if($idx_nowiki !== FALSE) {
				$min = $idx_nowiki;
				$type = 1;
			}
			$idx_noinclude = stripos($text, '<noinclude>', $offset);
			if($idx_noinclude !== FALSE && $idx_noinclude < $min) {
				$min = $idx_noinclude;
				$type = 2;
			}
			$idx_triplebracket = strpos($text, '{{{', $offset);
			$idx_doublebracket = strpos($text, '{{', $offset);
			if($idx_doublebracket !== FALSE && $idx_doublebracket < $min && $idx_doublebracket !== $idx_triplebracket) {
				$min = $idx_doublebracket;
				$type = 3;
				$result[] = substr($text, $start, $min - $start);
				if(substr($text, $min + 2, 1) == '#') {
					$is_parserfunc = true;
					$pfstart = $min;
				}
				$template = AddIn2::parseTemplate($text, $min);
				$start = $min;
				if($is_parserfunc) {
					$result[] = substr($text, $pfstart, $min - $pfstart);
				} else {
					$result[] = $template;
				}
			}
			if($type !== FALSE) {
				$offset = $min;
				if($type == 1) {
					$offset = stripos($text, '</nowiki>', $offset);
				}else if($type == 2) {
					$offset = stripos($text, '</noinclude>', $offset);
				}
			}
		} while ($type !== FALSE);
		$result[] = substr($text, $start, $len - $start);
		
		return $result;
	}
	
}