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
 * Get translated magic words, if available
 *
 * @param string $lang Language code
 * @return array
 */
function efDebugTemplateWords( $lang ) {
        $words = array();
 
        /**
         * English
         */
        $words['en'] = array(
                'debugTemplate'          => array( 0, 'debugTemplate' )
        );
 
        # English is used as a fallback, and the English synonyms are
        # used if a translation has not been provided for a given word
        return ( $lang == 'en' || !isset( $words[$lang] ) )
                ? $words['en']
                : array_merge( $words['en'], $words[$lang] );
}
 
?>
