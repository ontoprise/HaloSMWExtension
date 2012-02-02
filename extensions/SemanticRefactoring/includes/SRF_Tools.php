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
 *
 * @author Kai Kuehn
 *
 */
class SRFTools {
	public static function deleteArticle($a) {
		global $wgUser;
		$reason = "Removed by Semantic Refactoring extension";
		$id = $a->getTitle()->getArticleID();
		if ( wfRunHooks( 'ArticleDelete', array( &$a, &$wgUser, &$reason, &$error ) ) ) {
			if ( $a->doDeleteArticle( $reason ) ) {
				wfRunHooks( 'ArticleDeleteComplete', array( &$a, &$wgUser, $reason, $id ) );
				return true;
			}
		}
		return false;
	}

	public static function makeTitleListUnique($titles) {
		usort($titles, array("SRFTools", "compareTitles"));

		$result = array();
		$last = reset($titles);
		if ($last !== false) $result[] = $last;
		for($i = 1, $n = count($titles); $i < $n; $i++ ) {
			if ($titles[$i]->getPrefixedText() == $last->getPrefixedText()) {
				$titles[$i] = NULL;
				continue;
			}
			$last = $titles[$i];
			$result[] = $titles[$i];
		}

		return $result;
	}




	public static function containsTitle($title, $set) {
		for($i = 0, $n = count($set); $i < $n; $i++ ) {
			if ($set[$i]->getPrefixedText() == $title->getPrefixedText()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Find nodes with the given type-ID below $node.
	 *
	 * @param WikiObjectModel $node
	 * @param string $id Type-ID
	 * @param array (out) $results
	 *
	 * @return
	 */
	public static function findObjectByID($node, $id, & $results) {

		if ($node->isCollection()) {
			$objects = $node->getObjects();
			foreach($objects as $o) {
				if ($o->getTypeID() == $id) {

					$results[] = $o;

				}
				self::findObjectByID($o, $id, $results);
			}
		} else {
			if ($node->getTypeID() == $id) {

				$results[] = $node;

			}
		}
	}

	public static function splitRecordValues($value) {
		$valueArray = explode(";", $value);
		array_walk($valueArray, array('SRFTools', 'trim'));
		return $valueArray;
	}

	/* callback methods */
	private static function compareTitles($a, $b) {
		return strcmp($a->getPrefixedText(), $b->getPrefixedText());
	}

	private static function trim(& $s, $i) {
		$s = trim($s);
	}
}