<?php
class SMWAdvRequestOptions extends SMWRequestOptions {
    
    public $isCaseSensitive = true;
    /**
     * If true, all string constraints will be OR'ed instead of AND'ed.
     * Default is false.
     *
     * @var boolean
     */
    public $disjunctiveStrings = false;
}