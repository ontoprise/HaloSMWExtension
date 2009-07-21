/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
* SMW_Language.js
* 
* A class that reads language strings from the server by an ajax call.
* 
* @author Thomas Schweitzer
*
*/

var SR_Language = Class.create();

/**
 * This class provides SR_Language dependent strings for an identifier.
 * 
 */
SR_Language.prototype = {

    /**
     * @public
     * 
     * Constructor.
     */
    initialize: function() {
    },

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
                var msg = wgUserSR_LanguageStrings[id];
                if (!msg) {
                    msg = id;
                } 
                break;
                
            case "cont":
                var msg = wgContSR_LanguageStrings[id];
                if (!msg) {
                    msg = id;
                } 
                break;
            default: 
                var msg = wgUserSR_LanguageStrings[id];
                if (!msg) {
                    var msg = wgContSR_LanguageStrings[id];
                    if (!msg) {
                        msg = id;
                    }
                }
        } 
            
        // Replace variables
        msg = msg.replace(/\$n/g,wgCanonicalNamespace); 
        msg = msg.replace(/\$p/g,wgPageName);
        msg = msg.replace(/\$t/g,wgTitle);
        msg = msg.replace(/\$u/g,wgUserName);
        msg = msg.replace(/\$s/g,wgServer);
        return msg;
    }
    
}

// Singleton of this class

var gsrLanguage = new SR_Language();