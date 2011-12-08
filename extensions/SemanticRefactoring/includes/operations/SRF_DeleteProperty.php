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
class SMWRFDeletePropertyOperation extends SMWRFRefactoringOperation {

    var $property;
    var $options;

    public function __construct($category, $options) {
        $this->property = Title::newFromText($category, NS_CATEGORY);
        $this->options = $options;
    }

    public function queryAffectedPages() {

        $store = smwfGetSemanticStore();
        $smwstore = smwfGetStore();
        $propertyDi = SMWDIProperty::newFromUserLabel($this->property);
        $instances = $smwstore->getAllPropertySubjects($propertyDi);
        
        $directSubProperties = $store->getDirectSubProperties($this->property);
        $allSubProperties = $store->getSubProperties($this->property);

        // get all queries $this->property is used in
        $queries = array();
        $qrc_dopDi = SMWDIProperty::newFromUserLabel(QRC_DOP_LABEL);
        $propertyWPDi = SMWDIWikiPage::newFromTitle($this->oldCategory);
        $subjects = smwfGetStore()->getPropertySubjects($qrc_dopDi, $propertyWPDi);
        foreach($subjects as $s) {
            $queries[] = $s->getTitle()->getPrefixedDBkey();
        }
        $results = array();
        $results['instances'] = $instances;
        $results['queries'] = $queries;
        $results['directSubProperties'] = $directSubcategories;
        $results['allSubProperties'] = $allSubCategories;

        return $results;
    }

    public function refactor($save = true) {
        if (array_key_exists('onlyCategory', $this->options) && $this->options['onlyCategory'] == true) {
            $a = new Article($this->property);
            $a->doDelete();
            return;
        }

        if (array_key_exists('removeInstances', $this->options) && $this->options['removeInstances'] == true) {
            // if instances are completely removed, there is no need to remove annotations before
        } else if (array_key_exists('removeCategoryAnnotations', $this->options) && $this->options['removeCategoryAnnotations'] == true) {

        }

        if (array_key_exists('removeQueries', $this->options) && $this->options['removeQueries'] == true) {

        }

        if (array_key_exists('deleteSubcategories', $this->options) && $this->options['deleteSubcategories'] == true) {
                
        }

        if (array_key_exists('deleteAllInstancesOfSubcategories', $this->options) && $this->options['deleteAllInstancesOfSubcategories'] == true) {

        }
    }
}