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


class SMWQPWSTIXML extends SMWResultPrinter {

	protected $mSep = ',';
	protected $mTemplate = false;

	protected function getResultText( $res, $outputmode ) {

		$result = "<?xml version='1.0'?>";
		$result .= "<tixml xmlns='http://www.ontoprise.de/smwplus#'>";
		
		$result.= "<columns>";
		foreach ( $res->getPrintRequests() as $pr ) {
			$result.= "<title>".$pr->getText( $outputmode, null)."</title>";
		}
		$result.= "</columns>";
		
		
		while ( $row = $res->getNext() ) {
			$result.= "<row>";
			foreach ( $row as $key => $field ) {
				$values = array();
				while ( ( $object = $field->getNextObject() ) !== false ) {
					if( ( $object->getTypeID() == '_wpg' ) || ( $object->getTypeID() == '__sin' ) ){
						$values[] = $object->getLongText( $outputmode, null );
					} else {
						$values[] = $object->getShortText( $outputmode, null );
					}
						
				}
				$result.= "<item>".urlencode(htmlspecialchars(implode(',', $values)))."</item>";
			}
			$result.= "</row>";
		}
		
		$result .= "</tixml>";

		return $result;
	}

}
