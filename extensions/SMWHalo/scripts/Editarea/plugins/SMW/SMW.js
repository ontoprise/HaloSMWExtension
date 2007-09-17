/**
 * Plugin designed to work with an installation of MediaWiki. The plugin provides
 * the standard wiki buttons with full functionality.
 * @author Markus Nitsche, 2007
 */

var EditArea_SMW= {

	/**
	 * Get called once this file is loaded (editArea still not initialized)
	 * @return nothing
	 */
	init: function(){
		this.keystrokes = 0;
	}
	/**
	 * Returns the HTML code for a specific control string or false if this plugin doesn't have that control.
	 * A control can be a button, select list or any other HTML item to present in the EditArea user interface.
	 * Language variables such as {$lang_somekey} will also be replaced with contents from
	 * the language packs.
	 *
	 * @param {string} ctrl_name: the name of the control to add
	 * @return HTML code for a specific control or false.
	 * @type string	or boolean
	 */
	,get_control_html: function(ctrl_name){
		switch(ctrl_name){
			case "bold":
				return parent.editAreaLoader.get_button_html('Bold text', 'bold.png', 'bold_cmd', this.baseURL);
			case "italic":
				return parent.editAreaLoader.get_button_html('Italic text', 'italic.png', 'italic_cmd', this.baseURL);
			case "intlink":
				return parent.editAreaLoader.get_button_html('Internal link', 'intlink.png', 'intlink_cmd', this.baseURL);
			case "extlink":
				return parent.editAreaLoader.get_button_html('External link (remember http:// prefix)', 'extlink.png', 'extlink_cmd', this.baseURL);
			case "heading":
				return parent.editAreaLoader.get_button_html('Level 2 headline', 'heading.png', 'heading_cmd', this.baseURL);
			case "img":
				return parent.editAreaLoader.get_button_html('Embedded image', 'img.png', 'img_cmd', this.baseURL);
			case "media":
				return parent.editAreaLoader.get_button_html('Media file link', 'media.png', 'media_cmd', this.baseURL);
			case "formula":
				return parent.editAreaLoader.get_button_html('Mathematical formula (LaTeX)', 'formula.png', 'formula_cmd', this.baseURL);
			case "nowiki":
				return parent.editAreaLoader.get_button_html('Ignore wiki formatting', 'nowiki.png', 'nowiki_cmd', this.baseURL);
			case "signature":
				return parent.editAreaLoader.get_button_html('Your signature with timestamp', 'signature.png', 'signature_cmd', this.baseURL);
			case "line":
				return parent.editAreaLoader.get_button_html('Horizontal line (use sparingly)', 'line.png', 'line_cmd', this.baseURL);
			}
		return false;
	}
	/**
	 * Get called once EditArea is fully loaded and initialised
	 *
	 * @return nothing
	 */
	,onload: function(){

	}

	/**
	 * Is called each time the user touch a keyboard key.
	 *
	 * @param (event) e: the keydown event
	 * @return true - pass to next handler in chain, false - stop chain execution
	 * @type boolean
	 */
	,onkeyup: function(e){
		parent.changeEdit();
		parent.refreshSTB.setUserIsTyping();
		if(navigator.userAgent.indexOf('Firefox') != -1){
			editArea.resync_highlight();
		}
	}

	/**
	 * Executes a specific command, this function handles plugin commands.
	 *
	 * @param {string} cmd: the name of the command being executed
	 * @param {unknown} param: the parameter of the command
	 * @return true - pass to next handler in chain, false - stop chain execution
	 * @type boolean
	 */
	,execCommand: function(cmd, param){
		// Handle commands
		switch(cmd){
			case "bold_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "'''", "''' ", "Bold text");
				return false;
			case "italic_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "''","'' ", "Italic text");
				return false;
			case "intlink_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "[[","]] ", "Link title");
				return false;
			case "extlink_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "[","] ", "http://www.example.com link title");
				return false;
			case "heading_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "\n== ", " ==\n", "Headline text");
				return false;
			case "img_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "[[Image:", "]] ", "Example.jpg");
				return false;
			case "media_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "[[Media:", "]] ", "Example.ogg");
				return false;
			case "formula_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "<math>", "</math> ", "Insert formula here");
				return false;
			case "nowiki_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "<nowiki>", "</nowiki> ", "Insert non-formatted text here");
				return false;
			case "signature_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "--~~~ ", "", "");
				return false;
			case "line_cmd":
				parent.editAreaLoader.insertTags("wpTextbox1", "\n----\n", "", "");
				return false;
		}
		// Pass to next handler in chain
		return true;
	}

};

// Adds the plugin class to the list of available EditArea plugins
editArea.add_plugin("SMW", EditArea_SMW)