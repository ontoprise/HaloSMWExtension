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


class SMWQPWSSimpleTable extends SMWResultPrinter {

	protected function getResultText( $res, $outputmode ) {
		// print header
		$result = '<table class="smwtable" width="100%">'."\n";
			  
		if ( $this->mShowHeaders != SMW_HEADERS_HIDE ) {
			$result .= "\t<tr>\n";
			
			foreach ( $res->getPrintRequests() as $pr ) {
				$result .= "\t\t<th>".$pr->getText( $outputmode, null)."</th>\n";
			}
			
			$result .= "\t</tr>\n";
		}

		// print all result rows
		while ( $row = $res->getNext() ) {
			$result .= "\t<tr>\n";
			$fieldcount = - 1;
			foreach ( $row as $field ) {
				//$fieldcount = $fieldcount + 1;

				$result .= "\t\t<td";
				$alignment = trim( $field->getPrintRequest()->getParameter( 'align' ) );
				if ( ( $alignment == 'right' ) || ( $alignment == 'left' ) || ( $alignment == 'center' ) ) {
					$result .= ' style="text-align:' . $alignment . ';"';
				}
				$result .= ">";

				$first = true;
				while ( ( $object = $field->getNextObject() ) !== false ) {
					if ( $first ) {
						if ( $object->isNumeric() ) { // additional hidden sortkey for numeric entries
							$result .= '<span class="smwsortkey">' . $object->getDataItem()->getSortKey() . '</span>';
						}
						$first = false;
					} else {
						$result .= '<br />';
					}
					// use shorter "LongText" for wikipage
					$result .= ( ( $object->getTypeID() == '_wpg' ) || ( $object->getTypeID() == '__sin' ) ) ?
						   $object->getLongText( $outputmode, null ):
						   $object->getShortText( $outputmode, null );
				}
				$result .= "</td>\n";
			}
			$result .= "\t</tr>\n";
		}

		// print further results footer
		if ( $this->linkFurtherResults( $res ) ) {
			$link = $res->getQueryLink();
			if ( $this->getSearchLabel( $outputmode ) ) {
				$link->setCaption( $this->getSearchLabel( $outputmode ) );
			}
			$result .= "\t<tr class=\"smwfooter\"><td class=\"sortbottom\" colspan=\"" . $res->getColumnCount() . '"> ' . $link->getText( $outputmode, $this->mLinker ) . "</td></tr>\n";
		}
		$result .= "</table>\n"; // print footer
		$this->isHTML = ( $outputmode == SMW_OUTPUT_HTML ); // yes, our code can be viewed as HTML if requested, no more parsing needed
		return $result;
	}

}
