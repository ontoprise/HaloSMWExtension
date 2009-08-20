<?php
global $smwgIP;
require_once "$smwgIP/includes/SMW_QP_CSV.php";
require_once "$smwgIP/includes/SMW_QP_iCalendar.php";
require_once "$smwgIP/includes/SMW_QP_Embedded.php";
require_once "$smwgIP/includes/SMW_QP_List.php";
require_once "$smwgIP/includes/SMW_QP_RSSLink.php";
require_once "$smwgIP/includes/SMW_QP_Template.php";
require_once "$smwgIP/includes/SMW_QP_vCard.php";




function smwfhCreateDefaultParameters() {
	$order = new SMWQPParameter('order', 'Order', array('ascending','descending'), NULL, "Sort order");
	$order = new SMWQPParameter('order', 'Order', array('ascending','descending'), NULL, "Sort order");
	$link = new SMWQPParameter('link', 'Link', array('all','subject', 'none'), NULL, "Show everything as link, only subjects or nothing at all.");
	$limit = new SMWQPParameter('limit', 'Limit', '<number>', NULL, "Instance display limit");
	$headers = new SMWQPParameter('headers', 'Headers', '<boolean>', NULL, "Show headers or not.");
	$intro = new SMWQPParameter('intro', 'Intro', '<string>', NULL, "Intro text");
	$mainlabel = new SMWQPParameter('label', 'Mainlabel', '<string>', NULL, "Name of main column");
	$default = new SMWQPParameter('default', 'Order', '<string>', NULL, "Displayed when there are no results at all.");
	return array($order, $link, $limit, $headers, $intro, $mainlabel, $default);;
}


class SMWHaloCsvResultPrinter extends SMWCsvResultPrinter{

	protected function setSupportedParameters() {
		$this->mParameters = smwfhCreateDefaultParameters();
		$sep = new SMWQPParameter('sep', 'Separator', '<string>', ',', "Separator used");
		$this->mParameters[] = $sep;
	}
}

class SMWHaloEmbeddedResultPrinter extends SMWEmbeddedResultPrinter{

	protected function setSupportedParameters() {
		$this->mParameters = smwfhCreateDefaultParameters();
		$embeddOnly = new SMWQPParameter('embedonly', 'Embedd only', '<boolean>', NULL, "Show header or not");
		$embedformat = new SMWQPParameter('embedformat', 'Embedd format', '<string>', NULL, "Embedded format");
		$this->mParameters[] = $embeddOnly;
		$this->mParameters[] = $embedformat;
	}
}

class SMWHaloiCalendarResultPrinter extends SMWiCalendarResultPrinter {
	protected function setSupportedParameters() {
		$iCalendarTitle = new SMWQPParameter('icalendartitle', 'iCalendar title', '<string>', NULL, "iCalendar title");
		$iCalendarDescription = new SMWQPParameter('icalendardescription', 'iCalendar description', '<string>', NULL, "iCalendar description");
		$this->mParameters[] = $iCalendarTitle;
		$this->mParameters[] = $iCalendarDescription;
	}
}


class SMWHaloListResultPrinter extends SMWListResultPrinter {
	protected function setSupportedParameters() {
		$sep = new SMWQPParameter('sep', 'Separator', '<string>', NULL, "Separator used");
		$template = new SMWQPParameter('template', 'Separator', '<string>', NULL, "Template used to display");
		$userparam = new SMWQPParameter('userparam', 'User param', '<string>', NULL, "User param");
		$this->mParameters[] = $sep;
		$this->mParameters[] = $template;
		$this->mParameters[] = $userparam;

	}
}

class SMWHaloRSSResultPrinter extends SMWRSSResultPrinter {
	protected function setSupportedParameters() {
		$title = new SMWQPParameter('title', 'Title', '<string>', NULL, "Title");
		$template = new SMWQPParameter('description', 'Description', '<string>', NULL, "Description of RSS feed");

		$this->mParameters[] = $title;
		$this->mParameters[] = $template;
		 
	}
}

class SMWHaloTemplateResultPrinter extends SMWTemplateResultPrinter {
protected function setSupportedParameters() {
        
        $template = new SMWQPParameter('template', 'Separator', '<string>', NULL, "Template used to display");
        $userparam = new SMWQPParameter('userparam', 'User param', '<string>', NULL, "User param");
        
        $this->mParameters[] = $template;
        $this->mParameters[] = $userparam;

    }
}

class SMWHalovCardResultPrinter extends SMWvCardResultPrinter {

}
?>