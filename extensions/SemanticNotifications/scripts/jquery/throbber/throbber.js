/**
 * @author thsc
 */
steal.plugins('jquery').then(function($) {
	var throbber = wgScriptPath+"/extensions/SemanticNotifications/skins/images/ajax-loader.gif";
	$.fn.throbber = function(showThrobber) {
		return this.each(function(){
			var $this = $(this);
			showThrobber = typeof showThrobber === "undefined" ? true : showThrobber;
			if (showThrobber) {
				$this.append('<img class="throbber" style="position:absolute; z-index:100;" src="' + throbber + '" />');
			} else {
				$('.throbber').remove();
			}
		});
	}
});
