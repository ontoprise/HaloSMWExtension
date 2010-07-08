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

	if($auth->user !== NULL && $auth->user != "") {
		if($auth->type == SWT_ADDIN_TYPE_DOMAIN) {
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
				$exception->message = "Wrong authenticated domain name, please check";
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
		// set $wgUser field
		global $wgUser;
		$wgUser = User::newFromName($auth->user);
		if ( ($wgUser === null) || !$wgUser->getId() ) {
			$exception->ret_code = SWT_ADDIN_E_WRONG_NAME;
			$exception->message = "Wrong authenticated login name, please check";
			return $exception;
		}
		// compare password
		if($auth->type != SWT_ADDIN_TYPE_DOMAIN) {
			if($auth->encrypted && $wgUser->mPassword == $auth->password) {
			} else if(!$auth->encrypted && $wgUser->checkPassword($auth->password)) {
			} else {
				$exception->ret_code = SWT_ADDIN_E_WRONG_PASSWORD;
				$exception->message = "Wrong account password, please check";
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
			$exception->message = "Cannot edit, no privilege, please check your account settings";
		} else if(!$isEdit && ($restriction->restriction & SWT_ADDIN_CANNOT_READ)) {
			$exception->ret_code = SWT_ADDIN_E_NO_PRIVILEGE;
			$exception->message = "Cannot read, no privilege, please check your account settings";
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

		if(class_exists("SCProcessor")) {
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
	
	public function getFormFieldValues($auth, $title, $form_name) {
		$ret = new PropertyPairsType();
		$ret->exception = smwgWTExceptionCheck($auth);
		if($ret->exception->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;
		$ret->values = array();
		
		if(class_exists("SCProcessor")) {
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
		if(class_exists("SCProcessor")) {
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

		$ret->values = array();
		if(class_exists("SCProcessor")) {
			$ret->values = SCProcessor::getPossibleForms(array(Title::newFromText($form_name)->getText()), 0);
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
	 * 	new string[] { "Project report" }
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
	 *
	 * @return string
	 */
	public function savePageToForm($auth, $form_name, $page_name, $properties, $freetext, $categories, $active_form, $applicable_forms) {
		$ret = smwgWTExceptionCheck($auth, true);
		if($ret->ret_code != SWT_ADDIN_E_SUCCESS) return $ret;

		$form_title = Title::newFromText( $form_name, SF_NS_FORM );
		if(!$form_title->exists()) {
			$ret->ret_code = SWT_ADDIN_E_FORM_NOT_EXIST;
			$ret->message = "Form does not exist";
			return $ret;
		}

		$content = "";
		if(class_exists("SCProcessor")) {
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
		$freetext = str_replace("</nowiki>", "</ nowiki>", $freetext); // trick here
		$freetext = str_replace("\n", "</nowiki>\n\n<nowiki>", $freetext);

		$content .= "<nowiki>";
		$content .= $freetext;
		$content .= "</nowiki>\n\n";

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
		if($title->exists()) {
			// merge categories
			extract( $db->tableNames('categorylinks', 'page') );
			$res = $db->query("SELECT $categorylinks.cl_to FROM $categorylinks LEFT JOIN $page
			ON $categorylinks.cl_from = $page.page_id
			WHERE $categorylinks.cl_sortkey = '".mysql_real_escape_string($title->getText())."' AND $page.page_namespace = ".NS_MAIN, $fname);

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

		$content .= "\n\n''This article is generated via Page Upload extension by third party webservice clients like Microsoft Outlook Addin; ".
			"any edits on this page could be overwritten by future uploads under the same subject.''";

		$revision = Revision::newFromTitle( $title );
//		if (( $revision === NULL ) || ($revision->getText() != $content)) {
			$article = new Article($title);
		if ( $revision === NULL ) {
			$article->doEdit($content,'');
		}
			
			if(class_exists("SCProcessor")) {
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
	 * @return string
	 */
	public function savePageBasic($auth, $form_name, $page_name, $properties, $freetext) {
		return $this->savePageToForm($auth, $form_name, $page_name, $properties, $freetext, $categories, $form_name, NULL);
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

		if(class_exists("SCProcessor")) {
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
}