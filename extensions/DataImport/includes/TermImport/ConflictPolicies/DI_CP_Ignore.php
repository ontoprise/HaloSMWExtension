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
 * This CP ignores all terms for which an article with
 * the same name already exists.
 */
class DICPOverwrite extends DIConflictPolicy {
	
	public function createArticle(
			$term, $templateName, $extraCategories, $delimiter, $title, $termImportName, $log, $botId, $damId){

		$articleAccess = DICPHArticleAccess::getInstance();
		
		$article = new Article($title);
		
		if ($article->exists()) {
			echo wfMsg("\r\n".'smw_ti_articleNotUpdated', $title)."\n";
			$log->addGardeningIssueAboutArticle($botId, SMW_GARDISSUE_UPDATE_SKIPPED, $title);
				
			$existingTIFAnnotations['ignored'][] = $termImportName;
			$existingTIFAnnotations = 
				"\n".$articleAccess->createTIFAnnotationsString($existingTIFAnnotations);
			$article->doEdit(
				$article->getContent().$existingTIFAnnotations, wfMsg('smw_ti_creationComment'));
				
			return true;
		} else {
			$existingTIFAnnotations['added'][] = $termImportName;
		
			$existingTIFAnnotations = 
				"\n".$articleAccess->createTIFAnnotationsString($existingTIFAnnotations);

			// Create the content of the article based on the creation pattern
			$content = 
				$articleAccess->createArticleContent($term, $templateName, $extraCategories, $delimiter);

			// Create the article
			$success = $article->doEdit($content.$existingTIFAnnotations, wfMsg('smw_ti_creationComment'));
			if (!$success) {
				$log->addGardeningIssueAboutArticle($botId, SMW_GARDISSUE_CREATION_FAILED, $title);
				return wfMsg('smw_ti_creationFailed', $title);
			}

			echo "\r\nArticle ".$title->getFullText()." created.\n";
			$log->addGardeningIssueAboutArticle(
				$botId,
				SMW_GARDISSUE_ADDED_ARTICLE,
				$title);

			return true;
		}
	}
	
}