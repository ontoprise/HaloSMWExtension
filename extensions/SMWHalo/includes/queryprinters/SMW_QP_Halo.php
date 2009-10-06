<?php
global $smwgIP;
require_once "$smwgIP/includes/SMW_QP_CSV.php";
require_once "$smwgIP/includes/SMW_QP_Embedded.php";
require_once "$smwgIP/includes/SMW_QP_List.php";
require_once "$smwgIP/includes/SMW_QP_RSSlink.php";
require_once "$smwgIP/includes/SMW_QP_Template.php";



/**
 * Describes a QP parameter.
 *
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
class SMWQPParameter {

    // actual parameter used by QP
    public $mParam;

    // parameter name to show
    public $mParamName;

    // possible values of the parameter. Can be:

    // 1. enums (array of string)
    // 2. string (<string>)
    // 3. number (<number>)
    // 4. boolean (<boolean>)
    public $mValues;

    // default value (optional)
    public $mDefault;

    // more verbose description than $mParamName (optional)
    public $mParamDescription;
    
    // AC constraints
    public $mConstraints = NULL;

    public function __construct($param, $paramName, $values, $default = NULL, $paramDescription = NULL) {
        $this->mParam = $param;
        $this->mParamName = $paramName;
        $this->mValues = $values;
        $this->mDefault = $default;
        $this->mParamDescription = $paramDescription;
    }

    public function getParameter() {
        return $this->mParam;
    }

    public function getParameterName() {
        return $this->mParamName;
    }

    public function getDefaultValue() {
        return $this->mDefault;
    }

    public function getValues() {
        return $this->mValues;
    }

    public function getParameterDescription() {
        return $this->mParamDescription;
    }
    
    public function setConstraints($constraints) {
        $this->mConstraints = $constraints;
    }
}

function smwfhCreateDefaultParameters() {
    $order = new SMWQPParameter('order', 'Order', array('ascending','descending'), NULL, "Sort order");
    $link = new SMWQPParameter('link', 'Link', array('all','subject', 'none'), NULL, "Show everything as link, only subjects or nothing at all.");
    $limit = new SMWQPParameter('limit', 'Limit', '<number>', NULL, "Instance display limit");
    $headers = new SMWQPParameter('headers', 'Headers', array('show', 'hide'), NULL, "Show headers or not.");
    $intro = new SMWQPParameter('intro', 'Intro', '<string>', NULL, "Intro text");
    $mainlabel = new SMWQPParameter('mainlabel', 'Mainlabel', '<string>', NULL, "Name of main column");
    $default = new SMWQPParameter('default', 'Order', '<string>', NULL, "Displayed when there are no results at all.");
    return array($order, $link, $limit, $headers, $intro, $mainlabel, $default);;
}

class SMWHaloTableResultPrinter extends SMWTableResultPrinter {
    // supported parameters
    protected $mParameters;


    public function __construct($format, $inline) {
        parent::__construct($format, $inline);
        $this->mParameters = smwfhCreateDefaultParameters();
    }

    function getSupportedParameters() {
        return $this->mParameters;
    }
}


class SMWHaloCsvResultPrinter extends SMWCsvResultPrinter{
    protected $mParameters;

    public function __construct($format, $inline) {
        parent::__construct($format, $inline);
        $this->mParameters = smwfhCreateDefaultParameters();
        $sep = new SMWQPParameter('sep', 'Separator', '<string>', ',', "Separator used");
        $this->mParameters[] = $sep;
    }

    function getSupportedParameters() {
        return $this->mParameters;
    }

}

class SMWHaloEmbeddedResultPrinter extends SMWEmbeddedResultPrinter{
    protected $mParameters;

    public function __construct($format, $inline) {
        parent::__construct($format, $inline);
        $this->mParameters = smwfhCreateDefaultParameters();
        $embeddOnly = new SMWQPParameter('embedonly', 'Embedd only', '<boolean>', NULL, "Show header or not");
        $embedformat = new SMWQPParameter('embedformat', 'Embedd format', '<string>', NULL, "Embedded format");
        $this->mParameters[] = $embeddOnly;
        $this->mParameters[] = $embedformat;
    }

    function getSupportedParameters() {
        return $this->mParameters;
    }


}




class SMWHaloListResultPrinter extends SMWListResultPrinter {
    protected $mParameters;

    public function __construct($format, $inline) {
        parent::__construct($format, $inline);
        $order = new SMWQPParameter('order', 'Order', array('ascending','descending'), NULL, "Sort order");
        $link = new SMWQPParameter('link', 'Link', array('all','subject', 'none'), NULL, "Show everything as link, only subjects or nothing at all.");
        $limit = new SMWQPParameter('limit', 'Limit', '<number>', NULL, "Instance display limit");
        $default = new SMWQPParameter('default', 'Default text', '<string>', NULL, "Displayed when there are no results at all.");
        $sep = new SMWQPParameter('sep', 'Separator', '<string>', NULL, "Separator used");
        $template = new SMWQPParameter('template', 'Template', '<string>', NULL, "Template used to display");
        $userparam = new SMWQPParameter('userparam', 'User param', '<string>', NULL, "User param");
        $this->mParameters[] = $order;
        $this->mParameters[] = $link;
        $this->mParameters[] = $limit;
        $this->mParameters[] = $default;
        $this->mParameters[] = $sep;
        $this->mParameters[] = $template;
        $this->mParameters[] = $userparam;
    }

    function getSupportedParameters() {
        return $this->mParameters;
    }


}

class SMWHaloCountResultPrinter extends SMWListResultPrinter {
    protected $mParameters;

    public function __construct($format, $inline) {
        parent::__construct($format, $inline);
        $order = new SMWQPParameter('order', 'Order', array('ascending','descending'), NULL, "Sort order");
        $default = new SMWQPParameter('default', 'Default text', '<string>', NULL, "Displayed when there are no results at all.");
        $limit = new SMWQPParameter('limit', 'Limit', '<number>', NULL, "Instance display limit");
       
        $this->mParameters[] = $order;
        $this->mParameters[] = $default;
        $this->mParameters[] = $limit;
       
    }

    function getSupportedParameters() {
        return $this->mParameters;
    }


}

class SMWHaloRSSResultPrinter extends SMWRSSResultPrinter {
    protected $mParameters;

    public function __construct($format, $inline) {
        parent::__construct($format, $inline);
        $title = new SMWQPParameter('title', 'Title', '<string>', NULL, "Title");
        $template = new SMWQPParameter('description', 'Description', '<string>', NULL, "Description of RSS feed");

        $this->mParameters[] = $title;
        $this->mParameters[] = $template;
    }

    function getSupportedParameters() {
        return $this->mParameters;
    }

}

class SMWHaloTemplateResultPrinter extends SMWTemplateResultPrinter {
    protected $mParameters;

    public function __construct($format, $inline) {
        parent::__construct($format, $inline);
        $template = new SMWQPParameter('template', 'Template', '<string>', NULL, "Template used to display");
        $userparam = new SMWQPParameter('userparam', 'User param', '<string>', NULL, "User param");

        $this->mParameters[] = $template;
        $this->mParameters[] = $userparam;
    }

    function getSupportedParameters() {
        return $this->mParameters;
    }


}


