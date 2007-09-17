var DivContainer = Class.create();

DivContainer.prototype = {


	/**
	 * @public
	 *
	 * Constructor. set container number and tab number.
	 */
	initialize: function() {
		this.visibility = true;
	},

	createContainer: function(contnum, tabnr) {
		this.contnum = contnum;
		this.tabnr = tabnr;
	},

	/**
	 * fire content changed event to notify the framework
	 */
	contentChanged : function() {
		stb_control.contentChanged(this.getContainerNr());
	},

	// tab
	setTab : function(tabnr) {
		this.tabnr = tabnr;
	},

	getTab : function() {
		return this.tabnr;
	},

	setContainerNr : function(contnum) {
		this.contnum = contnum;
	},

	getContainerNr : function() {
		return this.contnum;
	},

	setVisibility : function(visibility) {
		this.visibility = visibility;
	},

	isVisible : function() {
		return this.visibility;
	},

	setHeadline : function(headline) {
		this.headline = headline;
		$("stb_cont"+this.getContainerNr()+"-headline").update("<div style=\"cursor:pointer;cursor:hand;\" onclick=\"stb_control.contarray["+this.getContainerNr()+"].switchVisibility()\"><a id=\"stb_cont" + this.getContainerNr() + "-link\" class=\"minusplus\" href=\"javascript:void(0)\">&nbsp;</a>" + headline);
	},

	setContent : function(content) {
		this.content = content;
		$("stb_cont"+this.getContainerNr()+"-content").update(content);
	},

	setContentStyle : function(style) {
		$("stb_cont"+this.getContainerNr()+"-content").setStyle(style);
	},

	switchVisibility : function(container) {
		if (this.isVisible()) {
			if (this.getContainerNr() == HELPCONTAINER) {
				stb_control.setHelpCookie(0);
			}
			this.setVisibility(0);
		} else {
			if (this.getContainerNr() == HELPCONTAINER) {
				stb_control.setHelpCookie(1);
			}
			this.setVisibility(1);
		}
		// inform framework to hide
		stb_control.contentChanged(this.getContainerNr());
	},

	getVisibleHeight : function() {
		return $('stb_cont'+this.getContainerNr()+"-content").offsetHeight;
	},

	getNeededHeight : function() {
		return $('stb_cont'+this.getContainerNr()+"-content").scrollHeight;
	}
}
