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
 * @file
 * @ingroup DITermImport
 * 
 * @author Ingo Steinbauer
 */

/*
 * This CP overwrites existing articles if they have the same title
 * as one of the new terms.
 */
class DICPOverwrite extends DIConflictPolicy {
	
	public function createArticle(
			$term, $templateName, $extraCategories, $delimiter, $title, $termImportName, $log, $botId, $damId){

		$article = new Article($title);
				
		$updated = false;
		if ($article->exists()) $updated = true;
		
		$articleAccess = DICPHArticleAccess::getInstance();
		
		$existingTIFAnnotations = $articleAccess->getExistingTIFAnnotations($title);
		if($updated){
			$existingTIFAnnotations['updated'][] = $termImportName;  
		} else {
			$existingTIFAnnotations['added'][] = $termImportName;
		}
		$existingTIFAnnotations = 
			"\n".$articleAccess->createTIFAnnotationsString($existingTIFAnnotations);

		// Create the content of the article based on the creation pattern
		$content = 
			$articleAccess->createArticleContent($term, $templateName, $extraCategories, $delimiter);

		// Create/update the article
		$success = $article->doEdit($content.$existingTIFAnnotations, wfMsg('smw_ti_creationComment'));
		if (!$success) {
			$log->addGardeningIssueAboutArticle($botId, SMW_GARDISSUE_CREATION_FAILED, $title);
			return wfMsg('smw_ti_creationFailed', $title);
		}

		echo "\r\nArticle ".$title->getFullText();
		echo $updated==true ? " updated\n" : " created.\n";
		$log->addGardeningIssueAboutArticle(
			$botId,
			$updated == true ? SMW_GARDISSUE_UPDATED_ARTICLE
			: SMW_GARDISSUE_ADDED_ARTICLE,
			$title);

		return true;
	}
}