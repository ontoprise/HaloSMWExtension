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
/**
 * Operation which can be added to an InstanceLevelOperation.
 * Applies on arbitrary operation on a piece of wiki text.
 *  
 * @author kuehn
 *
 */
abstract class SRFApplyOperation {
	
	/**
     * Applies the operation and returns the changed wikitext.
     *
     * @param Title $title
     * @param string $wikitext
     * @param array $logMessages
     *
     * @return string
     */
    public abstract function applyOperation($title, $wikitext, & $logMessages);
    
    /**
     * Denotes if a save operation is required.
     * 
     */
    public function requireSave() {
    	return true;
    }
}