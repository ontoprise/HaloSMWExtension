<?php
 
/*
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USAw
 *  
 * Parts of the program use the file 'Image.php' and 'Flash.php' from the MediaWiki project. The respective source can be acquired from http://svn.wikimedia.org/viewvc/mediawiki/trunk/.
 * 
 * @author <grinfeder@miami.edu>
 * 
 * 
NOTE:
There are some bugs in that code throwing PHP warnings:
To fix these add
var $QTvars;
var $gotQTvars;
after the class definition and replace the 4 occurences of $gotQTvars with $this->gotQTvars
 
 
 */
 
$wgExtensionFunctions[] = "wfQTExtension";
 
 
/*
 * The QT class generates code in order to implement a QT object.
 */
class QT {
        /* Constructor */
        function QT( $input ) {
                QT::parseInput( $input ); // Parse the input
                QT::genCode(); // Generate the final code
        }
 
        /* Parser */
        function parseInput( $input ) {
                for($pos=0; $pos<strlen($input); $pos++) { // go through all arguments
                        if($input{$pos}=='=') { // separator between command
                                //ignore '=' if the attribute is QTvars
                                //this will enable to pass query string to QT files
                                if($gotQTvars) {
                                        $this->tmp .= $input{$pos};
                                        continue;
                                }
                                $this->instr = $this->tmp;
                                $this->tmp = '';
                                //set the flag for QTvars
                                if($this->instr == 'QTvars') $gotQTvars = 1;
                        }
                        else if($input{$pos}=='|') { // separator between arguments
                                //reset the flags for other attributes
                                if($gotQTvars) $gotQTvars = 0;
                                QT::setValue();
                                $this->tmp='';
                        } else {
                                $this->tmp .= $input{$pos};
                        }
                }
                if($this->tmp!='') QT::setValue(); // Deal with the rest of the input string
        }
 
        /* Coordinate commands with values */
        function setValue() {
                $this->value = $this->tmp;
                $this->{$this->instr} = $this->value;
                if($this->instr=='autoplay'|| // Whitelist of QT commands. Anything else but QT commands is ignored.
                        $this->instr=='controller') {
                        /* Create code for <embed> and <object> */
                        if($this->instr!='id') $this->codeEmbed .= ' ' . $this->instr . '="' . $this->value . '"';
                        if($this->instr!='name') $this->codeObject .= '<param name="' . $this->instr . '" value="' . $this->value . '">';
                }
        }
 
        /* Generate big, final chunk of code */
        function genCode() {
                // Possibly malicious settings:
                $codebase = 'http://www.apple.com/qtactivex/qtplugin.cab'; // Code Base /No need to change
                $classID = 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B'; // ClassID / No need to change
 
                // Default version Setting:
                $this->version='7,0,0,0'; // Version settings for <object>
                $this->url = $this->getTitle($this->file);//QT::imageUrl( $this->file, $this->fromSharedDirectory ); // get Wiki internal url
 
                // if QTvars is set append to the url
                if($this->QTvars) $this->url .= $this->QTvars;
 
                /* Final Code */
                $this->code = '<OBJECT CLASSID="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" WIDTH="160"HEIGHT="144" CODEBASE="http://www.apple.com/qtactivex/qtplugin.cab"><PARAM name="SRC" VALUE="' . $this->url . '">' . $this->codeObject . '<EMBED SRC="' . $this->url . '" WIDTH="' . $this->width . '" HEIGHT="' . $this->height . '" ' . $this->codeEmbed . ' PLUGINSPAGE="http://www.apple.com/quicktime/download/"></EMBED></OBJECT> ';
                return $this->code;
        }
 
        function getTitle($file) {
        	// independant from namespace
               $title = Title::makeTitleSafe("Image",$file);
               $img = new Image($title);
               $path = $img->getViewURL(false);
               return $path;
        }
}
function wfQTExtension() {
        global $wgParser;
        $wgParser->setHook( "QT", "renderQT" );
}
function renderQT( $input ) {
        global $code;
 
        // Constructor
        $QTFile = new QT( $input );
        $code = $QTFile->code;
 
        return $code; // send the final code to the wiki
}
?>