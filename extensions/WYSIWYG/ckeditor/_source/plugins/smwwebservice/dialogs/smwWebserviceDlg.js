/*
 * Copyright (C) ontoprise GmbH
 *
 * Vulcan Inc. (Seattle, WA) and ontoprise GmbH (Karlsruhe, Germany)
 * expressly waive any right to enforce any Intellectual Property
 * Rights in or to any enhancements made to this program.
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

CKEDITOR.dialog.add( 'SMWwebservice', function( editor ) {
    var wgScript = window.parent.wgScript;
    var location =  wgScript + '?action=ajax&rs=smwf_uws_getPage';
    
	return {
		title: editor.lang.smwwebservice.titleWsDef,

		minWidth: 900,
		minHeight: (window.outerHeight == undefined) ? 400 : parseInt(window.outerHeight * 0.6),


		contents: [
			{
				id: 'tab1_smw_wws',
				label: 'Tab1',
				title: 'Tab1',
				elements : [
					{
						id: 'wwsframe',
						type: 'html',
						label: "Text",
                        style: 'width:100%; height:100%;',
						html: '<iframe name="CKeditorWebserviceDef" \
                                       style="width:100%; height:100%" \
                                       scrolling="auto" src="'+location+'"></iframe>'
					}
				 ]
			}
		 ],


		onOk: function() {
			var wwsFrame = window.frames['CKeditorWebserviceDef'];
            var content = wwsFrame.useWSSpecial.createWSSyn();
            content = content.replace(/\r?\n/, 'fckLR');
            content = CKEDITOR.tools.htmlEncode(content);
			
            content = '<span class="fck_smw_webservice">' + content + '</span>';

			var element = CKEDITOR.dom.element.createFromHtml(content, editor.document),
				newFakeObj = editor.createFakeElement( element, 'FCK__SMWwebservice', 'span' );
			if ( this.fakeObj ) {
				newFakeObj.replace( this.fakeObj );
				editor.getSelection().selectElement( newFakeObj );
            } else
				editor.insertElement( newFakeObj );
		},

        onShow: function() {
            // fix size of inner window for iframe
            var node = document.getElementsByName('tab1_smw_wws')[0];
            var child = node.firstChild;
            while ( child && (child.nodeType != 1 || child.nodeName.toUpperCase() != 'TABLE') )
                child = child.nextSibling;
            if (child) {
                child.style.height = '100%';
                var cells = child.getElementsByTagName('td');
                for (var i= 0; i < cells.length; i++)
                    cells[i].style.height = '100%';
            }
        }

	};

} );
