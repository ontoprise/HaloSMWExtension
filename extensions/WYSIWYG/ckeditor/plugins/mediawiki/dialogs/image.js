CKEDITOR.dialog.add( 'MWImage', function( editor ) {
{

	// Load image preview.
	var IMAGE = 1,
		LINK = 2,
		PREVIEW = 4,
		CLEANUP = 8,
		regexGetSize = /^\s*(\d+)((px)|\%)?\s*$/i,
		regexGetSizeOrEmpty = /(^\s*(\d+)((px)|\%)?\s*$)|^$/i,
		pxLengthRegex = /^\d+px$/,
		SrcInWiki;

	var onImgLoadEvent = function()	{
		// Image is ready.
		var original = this.originalElement;
		original.setCustomData( 'isReady', 'true' );
		original.removeListener( 'load', onImgLoadEvent );
		original.removeListener( 'error', onImgLoadErrorEvent );
		original.removeListener( 'abort', onImgLoadErrorEvent );

		// Hide loader
		CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );

		// New image -> new domensions
		if ( !this.dontResetSize )
			resetSize( this );

		if ( this.firstLoad )
			CKEDITOR.tools.setTimeout( function(){switchLockRatio( this, 'check' );}, 0, this );

		this.firstLoad = false;
		this.dontResetSize = false;
	};

	var onImgLoadErrorEvent = function(){
		// Error. Image is not loaded.
		var original = this.originalElement;
		original.removeListener( 'load', onImgLoadEvent );
		original.removeListener( 'error', onImgLoadErrorEvent );
		original.removeListener( 'abort', onImgLoadErrorEvent );

		// Set Error image.
		var noimage = CKEDITOR.getUrl( editor.skinPath + 'images/noimage.png' );

		if ( this.preview )
			this.preview.setAttribute( 'src', noimage );

		// Hide loader
		CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );
	};

    var searchTimer,
        searchLabel = 'Automatic search results (%s)',
        numbering = function( id )
		{
			return CKEDITOR.tools.getNextId() + '_' + id;
		},
		imagePreviewLoaderId = numbering( 'ImagePreviewLoader' ),
		imagePreviewBoxId = numbering( 'ImagePreviewBox' ),
		previewLinkId = numbering( 'previewLink' ),
		previewImageId = numbering( 'previewImage' );

    var previewPreloader;
    
    var GetImageUrl = function( dialog, img ) {
        var StartSearchImageUrl = function () {
            window.parent.sajax_request_type = 'GET' ;
            window.parent.sajax_do_call( 'wfSajaxGetImageUrl', [img], LoadPreviewImage ) ;
        }
        var LoadPreviewImage = function(result) {
            var url = result.responseText.Trim();
            if (! url)
                url = CKEDITOR.getUrl( editor.skinPath + 'images/noimage.png' );
            SrcInWiki = url;
            // Query the preloader to figure out the url impacted by based href.
            previewPreloader.setAttribute( 'src', url );
			dialog.preview.setAttribute( 'src', previewPreloader.$.src );
			updatePreview( dialog );
        }
        var updatePreview = function( dialog ) {
			//Don't load before onShow.
			if ( !dialog.originalElement || !dialog.preview )
				return 1;

			// Read attributes and update imagePreview;
			dialog.commitContent( PREVIEW, dialog.preview );
			return 0;
		}

        StartSearchImageUrl();

    }
    var OnUrlChange = function( dialog ) {

        //var dialog = this.getDialog();

        var StartSearch = function() {
            var	e = dialog.getContentElement( 'info', 'imgFilename' ),
                link = e.getValue().Trim();

            if ( link.length < 2  )
                    return ;
            SetSearchMessage( 'searching...' ) ;
            
            // Make an Ajax search for the pages.
            window.parent.sajax_request_type = 'GET' ;
            window.parent.sajax_do_call( 'wfSajaxSearchImageCKeditor', [link], LoadSearchResults ) ;
        }

        var LoadSearchResults = function(result) {
            var results = result.responseText.split( '\n' ),
                select = dialog.getContentElement( 'info', 'imgList' );

            ClearSearch() ;

            if ( results.length == 0 || ( results.length == 1 && results[0].length == 0 ) ) {
                SetSearchMessage( 'no images found' ) ;
            }
            else {
                if ( results.length == 1 )
                    SetSearchMessage( 'one image found' ) ;
                else
                    SetSearchMessage( results.length + ' images found' ) ;

                for ( var i = 0 ; i < results.length ; i++ )
                    select.add ( results[i].replace(/_/g, ' '), results[i] );
            }

        }
        var ClearSearch = function() {
            var	e = dialog.getContentElement( 'info', 'imgList' );
            e.items = [];
            var div = document.getElementById(e.domId),
                select = div.getElementsByTagName('select')[0];
            while ( select.options.length > 0 )
                select.remove( 0 )
        }

        var SetSearchMessage = function ( message ) {
            message = searchLabel.replace(/%s/, message);
            var	e = dialog.getContentElement( 'info', 'imgList' ),
            label = document.getElementById(e.domId).getElementsByTagName('label')[0];
            e.html = message;
            label.innerHTML = message;
        }

        var e = dialog.getContentElement( 'info', 'imgFilename' ),
        link = e.getValue().Trim();

        if ( searchTimer )
            window.clearTimeout( searchTimer ) ;

        if( /^(http|https):\/\//.test( link ) ) {
            SetSearchMessage( 'external link... no search for it' ) ;
            return ;
        }

        if ( link.length < 1  )	{
            ClearSearch() ;
            SetSearchMessage( 'start typing in the above field' ) ;
            return ;
        }

        SetSearchMessage( 'stop typing to search' ) ;
        searchTimer = window.setTimeout( StartSearch, 500 ) ;

    }

        return {
            title : 'Mediawiki Image',
            minWidth : 420,
            minHeight : 310,
			contents : [
				{
					id : 'info',
					label : 'Tab info',
					elements :
					[
                        {
                            type: 'hbox',
                            children: [
                                {
                                    type: 'vbox',
                                    style : 'width:40%;',
                                    children: [
                                        {
                                            id: 'imgFilename',
                                            type: 'text',
                                            label: 'Image file name',
                                            title: 'image file name',
                                            style: 'border: 1px;',
                                            onKeyUp: function () {
                                                OnUrlChange( this.getDialog() );
                                            },
                                            setup : function( type, element )
											{
												if ( type == IMAGE )
												{
													var url = element.getAttribute( '_fck_mw_filename' ) ||
                                                              element.getAttribute( '_cke_saved_src' ) ||
                                                              element.getAttribute( 'src' );
													var field = this;

													this.getDialog().dontResetSize = true;

													field.setValue( url );		// And call this.onChange()
													// Manually set the initial value.(#4191)
													field.setInitValue();
												}
											},
											commit : function( type, element )
											{
												if ( type == IMAGE && ( this.getValue() || this.isChanged() ) )
												{
													element.setAttribute( '_cke_saved_src', decodeURI( this.getValue() ) );
													element.setAttribute( 'src', SrcInWiki );
                                                    element.setAttribute( '_fck_mw_filename', decodeURI( this.getValue() ) );
												}
												else if ( type == CLEANUP )
												{
													element.setAttribute( 'src', '' );	// If removeAttribute doesn't work.
													element.removeAttribute( 'src' );
												}
											},
											validate : CKEDITOR.dialog.validate.notEmpty( editor.lang.image.urlMissing )
                                        },

                                        {
                                            id: 'imgList',
                                            type: 'select',
                                            size: 5,
                                            label: 'Automatic search results (start typing in the above field)',
                                            title: 'image list',
                                            required: false,
                                            style: 'border: 1px; width:100%;',
                                            items: [  ],
                                            onChange: function () {
                                                var dialog = this.getDialog(),
                                                    newImg = this.getValue(),
                                                    e = dialog.getContentElement( 'info', 'imgFilename' );

                                                e.setValue(newImg.replace(/_/g, ' '));
                                                GetImageUrl( dialog, newImg );

                                                var original = dialog.originalElement;

												dialog.preview.removeStyle( 'display' );

												original.setCustomData( 'isReady', 'false' );
                                                // Show loader
												var loader = CKEDITOR.document.getById( imagePreviewLoaderId );
												if ( loader )
                                                    loader.setStyle( 'display', '' );

                                                original.on( 'load', onImgLoadEvent, dialog );
												original.on( 'error', onImgLoadErrorEvent, dialog );
												original.on( 'abort', onImgLoadErrorEvent, dialog );
												original.setAttribute( 'src', newImg );

                                            }
                                        }
                                    ]
                                },
                                {
									type : 'vbox',
									height : '250px',
                                    style : 'width:60%;',
									children :
									[
										{
											type : 'html',
											style : 'width:95%;',
											html : '<div>' + CKEDITOR.tools.htmlEncode( editor.lang.common.preview ) +'<br>'+
											'<div id="' + imagePreviewLoaderId + '" class="ImagePreviewLoader" style="display:none"><div class="loading">&nbsp;</div></div>'+
											'<div id="' + imagePreviewBoxId + '" class="ImagePreviewBox"><table><tr><td>'+
											'<a href="javascript:void(0)" target="_blank" onclick="return false;" id="' + previewLinkId + '">'+
											'<img id="' + previewImageId + '" alt="" /></a>' +
											( editor.config.image_previewText ||
											'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. '+
											'Maecenas feugiat consequat diam. Maecenas metus. Vivamus diam purus, cursus a, commodo non, facilisis vitae, '+
											'nulla. Aenean dictum lacinia tortor. Nunc iaculis, nibh non iaculis aliquam, orci felis euismod neque, sed ornare massa mauris sed velit. Nulla pretium mi et risus. Fusce mi pede, tempor id, cursus ac, ullamcorper nec, enim. Sed tortor. Curabitur molestie. Duis velit augue, condimentum at, ultrices a, luctus ut, orci. Donec pellentesque egestas eros. Integer cursus, augue in cursus faucibus, eros pede bibendum sem, in tempus tellus justo quis ligula. Etiam eget tortor. Vestibulum rutrum, est ut placerat elementum, lectus nisl aliquam velit, tempor aliquam eros nunc nonummy metus. In eros metus, gravida a, gravida sed, lobortis id, turpis. Ut ultrices, ipsum at venenatis fringilla, sem nulla lacinia tellus, eget aliquet turpis mauris non enim. Nam turpis. Suspendisse lacinia. Curabitur ac tortor ut ipsum egestas elementum. Nunc imperdiet gravida mauris.' ) +
											'</td></tr></table></div></div>'
										}
									]
                                },

                            ]
                        },
                        {
                            id: 'imgCaption',
                            type: 'text',
                            label: 'Caption',
                            style: 'border: 1px;',
							onChange : function()
							{
								//updatePreview( this.getDialog() );
							},
							setup : function( type, element ) {
								if ( type == IMAGE )
									this.setValue( element.getAttribute( 'alt' ) );
							},
							commit : function( type, element ) {
								if ( type == IMAGE ) {
									if ( this.getValue() || this.isChanged() ) {
                                        var test = this.getValue();
										element.setAttribute( 'alt', this.getValue() );
                                    }
								}
								else if ( type == PREVIEW )
									element.setAttribute( 'alt', this.getValue() );
								else if ( type == CLEANUP )
									element.removeAttribute( 'alt' );
							}

                        },
                        {
                            type: 'hbox',
                            children:
                                [
                                    {
                                        id: 'imgSpecialType',
                                        type: 'select',
                                        label: 'Special Type',
                                        items: [
                                            [ ' ' ],
                                            [ 'Thumbnail' ],
                                            [ 'Frame' ],
                                            [ 'Border' ]
                                        ],
                                        setup : function( type, element ) {
                                            var imgType = element.getAttribute( '_fck_mw_type') || '',
                                                typeName = {
                                                    thumb : 'Thumbnail',
                                                    frame : 'Frame',
                                                    border : 'Border'
                                                }
                                            if ( type == IMAGE && imgType )
                                                this.setValue( typeName[imgType] );
                                        },
                                        commit : function( type, element ) {
                                            if ( type == IMAGE ) {
                                                if ( this.getValue() || this.isChanged() ) {
                                                    switch (this.getValue()) {
                                                        case 'Thumbnail':
                                                            element.setAttribute('_fck_mw_type', 'thumb');
                                                            break;
                                                        case 'Frame' :
                                                            element.setAttribute('_fck_mw_type', 'frame');
                                                            break;
                                                        case 'Border' :
                                                            element.setAttribute('_fck_mw_type', 'border');
                                                            break;
                                                        default:
                                                            element.setAttribute('_fck_mw_type', '');
                                                    }
                                                }
                                            }
                                            else if ( TYPE == CLEANUP )
                                                element.setAttribute('_fck_mw_type', '');
                                        }
                                    },
                                    {
                                        id: 'imgAlign',
                                        type: 'select',
                                        label: 'Align',
                                        items: [
                                            [ ' ' ],
                                            [ 'Right' ],
                                            [ 'Left' ],
                                            [ 'Center' ]
                                        ],
                                        setup : function( type, element ) {
                                            var className = element.getAttribute( 'class') || '',
                                                align = className.replace(/fck_mw_(right|left|center)/, '$1');
                                            if ( type == IMAGE && align )
                                                this.setValue( align.FirstToUpper() );
                                        },
                                        commit : function( type, element ) {
                                            if ( type == IMAGE ) {
                                                if ( this.getValue() || this.isChanged() ) {
                                                    var className = element.getAttribute( 'class') || '';
                                                    if ( className.match(/fck_mw_(right|left|center)/) &&
                                                         this.getValue() ) {
                                                        element.setAttribute( 'class',
                                                            className.replace(/fck_mw_(right|left|center)/, this.getValue().toLowerCase().Trim() )
                                                        );
                                                    }
                                                    
                                                }
                                            }
                                        }
                                    },
                                    {
                                        id: 'imgWidth',
                                        type: 'text',
                                        label: 'Width',
                                        size: '4',
                                        setup : function( type, element ) {
                                            var imgStyle = element.getAttribute( 'style') || '',
                                                match = /(?:^|\s)width\s*:\s*(\d+)/i.exec( imgStyle ),
                                                imgStyleWidth = match && match[1] || 0;
                                            if ( type == IMAGE && imgStyleWidth )
                                                this.setValue( imgStyleWidth );
                                        },
                                        commit : function( type, element, internalCommit ) {
											var value = this.getValue();
											if ( type == IMAGE )
											{
												if ( value )
													element.setStyle( 'width', CKEDITOR.tools.cssLength( value ) );
												else if ( !value && this.isChanged( ) )
													element.removeStyle( 'width' );
                                                
												!internalCommit && element.removeAttribute( 'width' );
											}
											else if ( type == PREVIEW )
											{
												var aMatch = value.match( regexGetSize );
												if ( !aMatch )
												{
													var oImageOriginal = this.getDialog().originalElement;
													if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
														element.setStyle( 'width',  oImageOriginal.$.width + 'px');
												}
												else
													element.setStyle( 'width', CKEDITOR.tools.cssLength( value ) );
											}
											else if ( type == CLEANUP )
											{
												element.removeAttribute( 'width' );
												element.removeStyle( 'width' );
											}
										}

                                    },
                                    {
                                        id: 'imgHeight',
                                        type: 'text',
                                        label: 'Height',
                                        size: '4',
                                        setup : function( type, element ) {
                                            var imgStyle = element.getAttribute( 'style') || '',
                                                match = /(?:^|\s)height\s*:\s*(\d+)/i.exec( imgStyle ),
                                                imgStyleWidth = match && match[1] || 0;
                                            if ( type == IMAGE && imgStyleWidth )
                                                this.setValue( imgStyleWidth );
                                        },
										commit : function( type, element, internalCommit )
										{
											var value = this.getValue();
											if ( type == IMAGE )
											{
												if ( value )
													element.setStyle( 'height', CKEDITOR.tools.cssLength( value ) );
												else if ( !value && this.isChanged( ) )
													element.removeStyle( 'height' );

												if ( !internalCommit && type == IMAGE )
													element.removeAttribute( 'height' );
											}
											else if ( type == PREVIEW )
											{
												var aMatch = value.match( regexGetSize );
												if ( !aMatch )
												{
													var oImageOriginal = this.getDialog().originalElement;
													if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
														element.setStyle( 'height', oImageOriginal.$.height + 'px' );
												}
												else
													element.setStyle( 'height',  CKEDITOR.tools.cssLength( value ) );
											}
											else if ( type == CLEANUP )
											{
												element.removeAttribute( 'height' );
												element.removeStyle( 'height' );
											}
										}

                                    },
                                    {
                                        type: 'html',
                                        width: '100%',
                                        html: ''
                                    }
                                ]
                        }
                    ]
                }
            ],

            onOk : function() {

                if (this.imageEditMode && this.imageEditMode == "img" ) {
                    this.imageElement = this.cleanImageElement;
					delete this.cleanImageElement;
                }
                else {
                    this.imageElement = editor.document.createElement( 'img' );
                }
                // Set attributes.
				this.commitContent( IMAGE, this.imageElement );

                // Remove empty style attribute.
				if ( !this.imageElement.getAttribute( 'style' ) )
					this.imageElement.removeAttribute( 'style' );

                // Insert a new Image.
				if ( !this.imageEditMode )
				{
					editor.insertElement( this.imageElement );
				}
            },

    		onShow : function()
        	{
                this.imageEditMode = false;
                this.dontResetSize = false;
                
                // clear old selection list from a previous call
                var	e = this.getContentElement( 'info', 'imgList' );
                    e.items = [];
                var div = document.getElementById(e.domId),
                    select = div.getElementsByTagName('select')[0];
                while ( select.options.length > 0 )
                    select.remove( 0 );

                var editor = this.getParentEditor(),
                    selection = editor.getSelection(),
    				element = selection.getSelectedElement();

                //Hide loader.
				CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );
				// Create the preview before setup the dialog contents.
				previewPreloader = new CKEDITOR.dom.element( 'img', editor.document );
				this.preview = CKEDITOR.document.getById( previewImageId );

                // Copy of the image
				this.originalElement = editor.document.createElement( 'img' );
				this.originalElement.setAttribute( 'alt', '' );
				this.originalElement.setCustomData( 'isReady', 'false' );

				if ( element && element.getName() == 'img' && !element.getAttribute( '_cke_realelement' )
					|| element && element.getName() == 'input' && element.getAttribute( 'type' ) == 'image' )
				{
					this.imageEditMode = element.getName();
					this.imageElement = element;
					SrcInWiki = element.getAttribute( 'src' );
				}

				if ( this.imageEditMode )
				{
					// Use the original element as a buffer from  since we don't want
					// temporary changes to be committed, e.g. if the dialog is canceled.
					this.cleanImageElement = this.imageElement;
					this.imageElement = this.cleanImageElement.clone( true, true );

					// Fill out all fields.
					this.setupContent( IMAGE, this.imageElement );

				}
				else
					this.imageElement =  editor.document.createElement( 'img' );

				// Dont show preview if no URL given.
				if ( !CKEDITOR.tools.trim( this.getValueOf( 'info', 'imgFilename' ) ) )
				{
					this.preview.removeAttribute( 'src' );
					this.preview.setStyle( 'display', 'none' );
				}
        	}

        }
}
} );
