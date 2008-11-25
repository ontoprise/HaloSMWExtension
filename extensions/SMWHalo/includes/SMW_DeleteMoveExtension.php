<?php

define ('SMW_DELETEPAGE_NUMLINKS', 20);

class SMWDeleteMoveExtension {
	public static function showLinksToArticle($article) {
		global $wgUser, $wgRequest, $wgScript;
		$wgSkin = $wgUser->getSkin();
			
		// check if 'prev' or 'next' button was pressed
		$offset = ($wgRequest->getVal("prevButton") != NULL ) ? $wgRequest->getVal("prevOffset") : $wgRequest->getVal("nextOffset");
		if ($offset == NULL || !is_numeric($offset)) $offset = 0;
		
		$title = $wgRequest->getVal("title");
		$action = $wgRequest->getVal("action");
		$html = "";
			
			
		// get links and total number of links to the page which is about to be deleted.
		$pages = self::getLinksToTitle($article->getTitle(), $offset);
		$total = self::getTotalNumOfLinksToTitle($article->getTitle());
			
		if (count($pages) == 0) {
			$html .= wfMsg('smw_deletepage_nolinks');
		} else {

			$html .= "<form id=\"linkstopage\" action=\"$wgScript?title=$title&action=$action\" method=\"post\">";
			$html .= "<fieldset><legend>".wfMsg('smw_deletepage_linkstopage')."</legend>";
			// obergrenze nicht korrekt
			$html .= (intval($offset / SMW_DELETEPAGE_NUMLINKS)+1)." / ".(intval($total / SMW_DELETEPAGE_NUMLINKS+1))."<br>";
			$html .= "<table style=\"margin-left: 10px;\">";
			$i = 1;
			foreach($pages as $p) {
				$html .= "<tr>";
				$html .= "<td>$i. ".$wgSkin->makeKnownLinkObj($p)."</td>";
				$html .= "</tr>";
				$i++;
			}
			$html .= "</table>";
			$prev_disabled = $offset == 0 ? "disabled=\"disabled\"" : "";
			$next_disabled = $offset + SMW_DELETEPAGE_NUMLINKS >= $total ? "disabled=\"disabled\"" : "";
			$html .= "<input name=\"prevOffset\" type=\"hidden\" value=\"".($offset-SMW_DELETEPAGE_NUMLINKS)."\" $prev_disabled/>";
			$html .= "<input name=\"nextOffset\" type=\"hidden\" value=\"".($offset+SMW_DELETEPAGE_NUMLINKS)."\" $next_disabled/>";
			$html .= "<input name=\"prevButton\" type=\"submit\" value=\"".wfMsg('smw_deletepage_prev')."\" $prev_disabled/>";
			$html .= "<input name=\"nextButton\" type=\"submit\" value=\"".wfMsg('smw_deletepage_next')."\" $next_disabled/>";
			$html .= "</fieldset>";

			$html .= "</form>";

		}
		return $html;
	}

	private static function getLinksToTitle($title, $offset = 0, $limit = SMW_DELETEPAGE_NUMLINKS) {
		$db =& wfGetDB( DB_SLAVE );
		$result = array();
			
		//either page or image...
		//problem: file redirects get lost
		if (!Namespace::isImage($title->getNamespace())) {
			$page_links = $db->tableName('pagelinks');
			$page = $db->tableName('page');

			$res = $db->query('SELECT page_title AS title, page_namespace AS ns FROM '.$page_links.' JOIN '.$page.' ON pl_from = page_id
                           WHERE pl_title = '.$db->addQuotes($title->getDBkey()).' AND pl_namespace = '.$title->getNamespace().
                       ' GROUP BY title, ns LIMIT '.$limit.' OFFSET '.$offset);
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = Title::newFromText($row->title, $row->ns);
				}
			}
		}
		else {
			$image_links = $db->tableName('imagelinks');
			$page = $db->tableName('page');
			
			$res = $db->query('SELECT page_title AS title, page_namespace AS ns FROM '.$image_links.','.$page.'
							   WHERE il_to = '.$db->addQuotes($title->getDBkey()).' AND (il_from = page_id) LIMIT '.$limit.' OFFSET '.$offset);
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$result[] = Title::newFromText($row->title, $row->ns);
				}
			}
		}

		$db->freeResult($res);
		return $result;
	}

	private static function getTotalNumOfLinksToTitle($title) {
		$db =& wfGetDB( DB_SLAVE );
		$result = 0;
			
		//either page or image...
		//problem: file redirects get lost
		if (!Namespace::isImage($title->getNamespace())) {
			$page_links = $db->tableName('pagelinks');
			
			$res = $db->query('SELECT COUNT(DISTINCT pl_from) AS num FROM '.$page_links.'
	                           WHERE pl_title = '.$db->addQuotes($title->getDBkey()).' AND pl_namespace = '.$title->getNamespace());
			if($db->numRows( $res ) > 0) {
				$row = $db->fetchObject($res);
				$result += $row->num;
			}
		}
		else {
			// imagelinks
			$image_links = $db->tableName('imagelinks');

			$res = $db->query('SELECT COUNT(DISTINCT il_from) AS num FROM '.$image_links.'
	                           WHERE il_to = '.$db->addQuotes($title->getDBkey()));
			if($db->numRows( $res ) > 0) {
				$row = $db->fetchObject($res);
				$result += $row->num;
			}
		}
		$db->freeResult($res);

		return $result;
	}
}
?>