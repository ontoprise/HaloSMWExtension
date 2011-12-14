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

#Import SMW, SMWHalo
include_once('extensions/SemanticMediaWiki/SemanticMediaWiki.php');
enableSemantics('http://wiki', true);
 
include_once('extensions/SMWHalo/includes/SMW_Initialize.php');
enableSMWHalo();

require_once('extensions/EnhancedRetrieval/includes/EnhancedRetrieval.php');

#Semantic Treeview 
require_once('extensions/Treeview/includes/TV_Initialize.php');
enableTreeView();

###Each extension wich depends on SMWHalo depends also on arclibrary####
include_once('extensions/ARCLibrary/ARCLibrary.php');

################################################################################################################

