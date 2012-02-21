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
class SRFCopyArticleOperation extends SRFApplyOperation {

    private $oldValue;
    private $newValue;

    public function __construct($oldValue, $newValue) {
        
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }
    
    public function applyOperation(& $title, $wikitext, & $logMessages) {
        
        // replace oldValue by newValue in title
        $newTitleString = str_replace( $this->oldValue,  $this->newValue, $title->getPrefixedText());

        // if replacing does not change title, do nothing
        if ($newTitleString == $title->getPrefixedText()) return;
         
        // copy article and also replace oldValue by newValue in text
        $newTitle = Title::newFromText($newTitleString);
        $article = new Article($title);
        $newArticle = new Article($newTitle);
        $text = $article->getRawText();
        $text = str_replace( $this->oldValue,  $this->newValue, $text);
        $newArticle->insertNewArticle($text, "inserted by Semantic Refactoring copy operation", false, false);

        $logMessages[$title->getPrefixedText()][] = new SRFLog("Copied '$1' to '$2' by replacing $3 by $4", $title, "", array($title, $newTitle, $this->oldValue, $this->newValue));
        
        // change title to new title so that all subsequent 
        // operations take place on the new title.
        $title = $newTitle;
        
        return $wikitext;
    }

    public function requireSave() {
        return false;
    }
}