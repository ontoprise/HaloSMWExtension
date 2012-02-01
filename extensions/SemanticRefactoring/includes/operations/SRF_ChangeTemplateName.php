<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */
class SRFChangeTemplateNameOperation extends SRFInstanceLevelOperation {
    
    private $old_template;
    private $new_template;
     

    public function __construct($instanceSet, $old_template, $new_template) {
        parent::__construct($instanceSet);
        $this->old_template = Title::newFromText($old_template, NS_TEMPLATE);
        $this->new_template = Title::newFromText($new_template, NS_TEMPLATE);
    }

   

    public function applyOperation($title, $wikitext, & $logMessages) {
        $pom = WOMProcessor::parseToWOM($wikitext);

        if (is_null($this->old_template) || is_null($this->new_template)) {
            return $pom->getWikiText();
        }
        
        # iterate trough the annotations
        $objects = $pom->getObjectsByTypeID(WOM_TYPE_TEMPLATE);

        foreach($objects as $o){

            $name = $o->getName();
             
            $parameters=array();
            if ($name == $this->old_template->getText()) {
                $o->setName($this->new_template->getText());
                $logMessages[$title->getPrefixedText()][] = new SRFLog("Changed template name '$1' to '$2'", $title, "", array($this->old_template, $this->new_template));
            }

        }

        // calls sync() internally
        $wikitext = $pom->getWikiText();

        // set final wiki text
        foreach($logMessages as $title => $set) {
            foreach($set as $lm) {
                $lm->setWikiText($wikitext);
            }
        }

        return $wikitext;
    }
}