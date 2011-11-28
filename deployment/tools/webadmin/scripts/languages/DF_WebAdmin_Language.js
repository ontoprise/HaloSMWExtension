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
* @ingroup WebAdmin
* 
* DFWebAdminLanguage.js
* 
* A class that reads language strings from the server by an ajax call.
* 
* @author Kai Kühn
*
*/

var dfgWebAdminLanguage = {


    /*
     * @public
     * 
     * Returns a SR_Language dependent message for an ID, or the ID, if there is 
     * no message for it.
     * 
     * @param string id
     *          ID of the message to be retrieved.
     * @return string
     *          The SR_Language dependent message for the given ID.
     */
    getMessage: function(id, type) {
        switch (type) {
            case "user":
                var msg = dfgUserDFWebAdmin_LanguageStrings[id];
                if (!msg) {
                    msg = id;
                } 
                break;
                
            case "cont":
                var msg = dfgContDFWebAdmin_LanguageStrings[id];
                if (!msg) {
                    msg = id;
                } 
                break;
            default: 
                var msg = dfgUserDFWebAdmin_LanguageStrings[id];
                if (!msg) {
                    var msg = dfgContDFWebAdmin_LanguageStrings[id];
                    if (!msg) {
                        msg = id;
                    }
                }
        } 
            
        
        return msg;
    }
    
}

