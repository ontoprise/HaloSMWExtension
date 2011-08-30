/*
 *  Copyright (c) 2011, MediaEvent Services GmbH & Co. KG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */
/**
 * @fileOverview Generic R2R utility methods
 * @author Christian Becker
 */
(function($){
			
	/**
	 * Generic R2R UI methods
	 */
	$.r2rUI = {};

	$.r2rUI.showError = function(title, message) {
		var dialog = $("<div class=\"r2redit-dialog\" title=\"" + title + "\">\
							<div class=\"ui-state-error ui-corner-all\" style=\"padding: 0 .7em;\">\
								<p>\
								<span class=\"ui-icon ui-icon-alert\"></span>\
								" + message + "\
								</p>\
						    </div>\
						</div>");
		dialog.dialog({
			autoOpen: true,
			height: 150,
			width: 300,
			modal: true,
			buttons: {
				"Ok": function() {
					$(this).dialog("close");
				}
			}		
		});
		$.r2rUI.fixJQueryUIDialogButtons(dialog);
	};
	
	$.r2rUI.showProgress = function(message) {
		if (message == null) {
			message = "Loading...";
		}
		$.r2rUI.progressDialog = $("<div></div>")
						.attr("title", message)
						.append($("<div></div>")
							.addClass("r2redit-dialog-loading-image")
						);
		$.r2rUI.progressDialog.dialog({dialogClass: "r2redit-dialog-loading", width: 250, height: 65, resizable: false, modal: true, closeOnEscape: false, buttons: {}});
	}
	
	$.r2rUI.hideProgress = function() {
		$.r2rUI.progressDialog.dialog("close");
	}
	
	/*
	 * Workaround: jQuery UI doesn't put the button text inside the ui-button-text span,
	 * but in the attribute "text" of the encompassing button element
	 */
	$.r2rUI.fixJQueryUIDialogButtons = function(dialog) {
		dialog.dialog("widget").find(".ui-dialog-buttonpane .ui-button").each(function(key, value) {
			var span = $(value).find(".ui-button-text");
			if ($(span).text() == "") {
				$(span).text($(value).attr("text"));
			}
		});
	}
		
})(jQuery);

/* Source: http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery */
jQuery.fn.extend({
insertAtCaret: function(myValue){
  return this.each(function(i) {
    if (document.selection) {
      this.focus();
      sel = document.selection.createRange();
      sel.text = myValue;
      this.focus();
    }
    else if (this.selectionStart || this.selectionStart == '0') {
      var startPos = this.selectionStart;
      var endPos = this.selectionEnd;
      var scrollTop = this.scrollTop;
      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
      this.focus();
      this.selectionStart = startPos + myValue.length;
      this.selectionEnd = startPos + myValue.length;
      this.scrollTop = scrollTop;
    } else {
      this.value += myValue;
      this.focus();
    }
  })
}
});