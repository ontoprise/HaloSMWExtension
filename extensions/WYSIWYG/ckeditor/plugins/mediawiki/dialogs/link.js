CKEDITOR.dialog.add( 'MWLink', function( editor ) {
{
    // need this to use the getSelectedLink function from the plugin
    var plugin = CKEDITOR.plugins.link;
    var searchTimer;
    var OnUrlChange = function() {

        var dialog = this.getDialog();

        var StartSearch = function() {
            var	e = dialog.getContentElement( 'mwLinkTab1', 'linkTarget' ),
                link = e.getValue().Trim();

            if ( link.length < 1  )
                    return ;

            SetSearchMessage( 'searching...' ) ;

            // Make an Ajax search for the pages.
            window.parent.sajax_request_type = 'GET' ;
            window.parent.sajax_do_call( 'wfSajaxSearchArticleCKeditor', [link], LoadSearchResults ) ;
        }

        var LoadSearchResults = function ( result ) {
            var results = result.responseText.split( '\n' ),
                select = dialog.getContentElement( 'mwLinkTab1', 'linkList' );

            ClearSearch() ;

            if ( results.length == 0 || ( results.length == 1 && results[0].length == 0 ) ) {
                SetSearchMessage( 'no articles found' ) ;
            }
            else {
                if ( results.length == 1 )
                    SetSearchMessage( 'one article found' ) ;
                else
                    SetSearchMessage( results.length + ' articles found' ) ;

                for ( var i = 0 ; i < results.length ; i++ )
                    select.add ( results[i].replace(/_/g, ' '), results[i] );
            }
        }

        var ClearSearch = function() {
            var	e = dialog.getContentElement( 'mwLinkTab1', 'linkList' );
            e.items = [];
            var div = document.getElementById(e.domId),
                select = div.getElementsByTagName('select')[0];
            while ( select.options.length > 0 )
                select.remove( 0 )
        }

        var SetSearchMessage = function ( message ) {
            var	e = dialog.getContentElement( 'mwLinkTab1', 'searchMsg' );
            e.html = message;
            document.getElementById(e.domId).innerHTML = message;
        }

        var e = dialog.getContentElement( 'mwLinkTab1', 'linkTarget' ),
        link = e.getValue().Trim();

        if ( searchTimer )
            window.clearTimeout( searchTimer ) ;
        
        if ( link.StartsWith( '#' ) ) {
            SetSearchMessage( 'anchor link... no search for it' ) ;
            return ;
        }
        
        if ( link.StartsWith( 'mailto:' ) )	{
            SetSearchMessage( 'e-mail link... no search for it' ) ;
            return ;
        }

        if( /^(http|https|news|ftp):\/\//.test( link ) ) {
            SetSearchMessage( 'external link... no search for it' ) ;
            return ;
        }

        if ( link.length < 1 ) {
            ClearSearch() ;
            SetSearchMessage( 'start typing in the above field' ) ;
            return ;
        }

        SetSearchMessage( 'stop typing to search' ) ;
        searchTimer = window.setTimeout( StartSearch, 500 ) ;

    }
    var WikiPageSelected = function() {
        var dialog = this.getDialog(),
            target = dialog.getContentElement( 'mwLinkTab1', 'linkTarget' ),
            select = dialog.getContentElement( 'mwLinkTab1', 'linkList' );
        target.setValue(select.getValue().replace(/_/g, ' '));
    }

        return {
            title : 'Mediawiki Link',
            minWidth : 350,
            minHeight : 140,
            resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
			contents : [
				{
					id : 'mwLinkTab1',
					label : 'Link label',
                    title : 'Link title',
					elements :
					[
                        {
                            id: 'linkTarget',
                            type: 'text',
                            label: 'Define the wiki page for the link target:',
                            title: 'Link target',
                            style: 'border: 1px;',
                            onKeyUp: OnUrlChange
                        },
                        {
                            id: 'searchMsg',
                            type: 'html',
                            style: 'font-size: smaller; font-style: italic;',
                            html: 'start typing in the above field'
                        },
                        {
                            id: 'linkList',
                            type: 'select',
                            size: 5,
                            label: 'Choose an existing wikipage for the link target:',
                            title: 'Page list',
                            required: false,
                            style: 'border: 1px; width:100%;',
                            onChange: WikiPageSelected,
                            items: [  ]
                        }
		            ]
                }
            ],

            onOk : function() {
                var e = this.getContentElement( 'mwLinkTab1', 'linkTarget'),
                    link = e.getValue().Trim().replace(/ /g, '_'),
                    attributes = {href : link};

                if ( !this._.selectedElement ) {
                    // Create element if current selection is collapsed.
                    var selection = editor.getSelection(),
                        ranges = selection.getRanges( true );
                    if ( ranges.length == 1 ) {
                        if ( ranges[0].collapsed ) {
                            var text = new CKEDITOR.dom.text( attributes.href, editor.document );
                            ranges[0].insertNode( text );
                            ranges[0].selectNodeContents( text );
                            selection.selectRanges( ranges );
                            if (text == link)
                                attributes._fcknotitle=true;
                        }
                        else attributes._fcknotitle=true; // remove this if the else part is fixed
                        /*
                        else {
                            var temp = ranges.clone();
                            try {
                                var node = temp[0].extractContents().getFirst();
                                if (node.$.nodeType == 3 && node.$.nodeValue == link)
                                    attributes._fcknotitle=true;
                            } catch (e) {}
                        }
                        */
                    }

                    // Apply style.
                    var style = new CKEDITOR.style( {element : 'a', attributes : attributes} );
                    style.type = CKEDITOR.STYLE_INLINE;		// need to override... dunno why.
                    style.apply( editor.document );

                } else {
                    // We're only editing an existing link, so just overwrite the attributes.
                    var element = this._.selectedElement,
                        textView = element.getHtml();

                    if (textView == link)
                        attributes._fcknotitle = 'true';
                    else
                        element.removeAttributes( ['_fcknotitle'] );
                    element.setAttributes( attributes );

                    if ( this.fakeObj )
                        editor.createFakeElement( element, 'cke_anchor', 'anchor' ).replace( this.fakeObj );

                    delete this._.selectedElement;
                }
            },

    		onShow : function()
        	{
                // clear old selection list from a previous call
                var	e = this.getContentElement( 'mwLinkTab1', 'linkList' );
                    e.items = [];
                var div = document.getElementById(e.domId),
                    select = div.getElementsByTagName('select')[0];
                while ( select.options.length > 0 )
                    select.remove( 0 );
                e = this.getContentElement( 'mwLinkTab1', 'searchMsg' );
                var message = 'start typing in the above field';
                e.html = message;
                document.getElementById(e.domId).innerHTML = message;

            	this.fakeObj = false;

                var editor = this.getParentEditor(),
                    selection = editor.getSelection(),
    				element = null;

        		// Fill in all the relevant fields if there's already one link selected.
            	if ( ( element = plugin.getSelectedLink( editor ) ) && element.hasAttribute( 'href' ) )
                	selection.selectElement( element );
    			else if ( ( element = selection.getSelectedElement() ) && element.is( 'img' )
        				&& element.getAttribute( '_cke_real_element_type' )
            			&& element.getAttribute( '_cke_real_element_type' ) == 'anchor' )
                {
                    this.fakeObj = element;
    				element = editor.restoreRealElement( this.fakeObj );
        			selection.selectElement( this.fakeObj );
            	}

                href = ( element  && ( element.getAttribute( '_cke_saved_href' ) || element.getAttribute( 'href' ) ) ) || '';
                if (href) {
                    var e = this.getContentElement( 'mwLinkTab1', 'linkTarget');
                    e.setValue(href);
                }
                this._.selectedElement = element;
        	}

        }
}
} );
