CKEDITOR.dialog.add( 'SMWqi', function( editor ) {
    var wgScript = window.parent.wgScript;
    var locationQi =  wgScript + '?action=ajax&rs=smwf_qi_getPage&rsargs[]=CKE';
    var querySource;
    
	return {
		title: 'Insert Query',

		minWidth: 900,
		minHeight: (window.outerHeight == undefined) ? 400 : parseInt(window.outerHeight * 0.6),

		contents: [
			{
				id: 'tab1_smw_qi',
				label: 'Tab1',
				title: 'Tab1',
				elements : [
					{
						id: 'qiframe',
						type: 'html',
						label: "Text",
                        style: 'width:100%; height:100%;',
						html: '<iframe name="CKeditorQueryInterface" \
                                       style="width:100%; height:100%" \
                                       scrolling="auto" src="'+locationQi+'"></iframe>'
					}
				 ]
			}
		 ],

         onShow : function() {
            // fix size of inner window for iframe
            var node = document.getElementsByName('tab1_smw_qi')[0];
            var child = node.firstChild;
            while ( child && (child.nodeType != 1 || child.nodeName.toUpperCase() != 'TABLE') )
                child = child.nextSibling;
            if (child) {
                child.style.height = '100%';
                var cells = child.getElementsByTagName('td');
                for (var i= 0; i < cells.length; i++)
                    cells[i].style.height = '100%';
            }

			// start here the normal operation
			this.fakeObj = false;

    		var editor = this.getParentEditor(),
        		selection = editor.getSelection(),
            	element = null,
                qiDocument = window.frames['CKeditorQueryInterface'];
                
			// Fill in all the relevant fields if there's already one item selected.
    		if ( ( element = selection.getSelectedElement() ) && element.is( 'img' )
        			&& element.getAttribute( 'class' ) == 'FCK__SMWquery' )
            {
                this.fakeObj = element;
				element = editor.restoreRealElement( this.fakeObj );
    			selection.selectElement( this.fakeObj );
                querySource = element.getHtml().replace(/_$/, '');
                querySource = querySource.replace(/fckLR/g, '\r\n');

                if ( typeof qiDocument.qihelper == 'undefined' )
                    qiDocument.onload = function() {
                        qiDocument.initialize_qi_from_querystring(querySource);
                    }
                else
                    qiDocument.qihelper.initFromQueryString(querySource);

            }
            else {
                if ( typeof qiDocument.qihelper != 'undefined' )
                    qiDocument.qihelper.doReset();
            }
        },

		onOk: function() {
			var qiDocument = window.frames['CKeditorQueryInterface'];
			var ask = qiDocument.qihelper.getAskQueryFromGui();
			ask = ask.replace(/\]\]\[\[/g, "]]\n[[");
			ask = ask.replace(/>\[\[/g, ">\n[[");
			ask = ask.replace(/\]\]</g, "]]\n<");
			ask = ask.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
            ask = ask.replace(/\r?\n/g, 'fckLR');
            ask = '<span class="fck_smw_query">' + ask + '<span>';

			var element = CKEDITOR.dom.element.createFromHtml(ask, editor.document),
				newFakeObj = editor.createFakeElement( element, 'FCK__SMWquery', 'span' );
			if ( this.fakeObj ) {
				newFakeObj.replace( this.fakeObj );
				editor.getSelection().selectElement( newFakeObj );
            } else
				editor.insertElement( newFakeObj );
		}

	};

} );