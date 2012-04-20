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

CKEDITOR.dialog.add( 'MWImage', function( editor )    
    {       
        // Load image preview.
        var IMAGE = 1,
        LINK = 2,
        PREVIEW = 4,
        CLEANUP = 8,
        regexGetSize = /^\s*(\d+)((px)|\%)?\s*$/i,
        regexGetSizeOrEmpty = /(^\s*(\d+)((px)|\%)?\s*$)|^$/i,
        pxLengthRegex = /^\d+px$/,
        SrcInWiki,
        imgLabelField = (window.parent.wgAllowExternalImages || window.parent.wgAllowExternalImagesFrom )
        ? editor.lang.mwplugin.fileNameExtUrl
        : editor.lang.mwplugin.fileName;

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
//            if ( !this.dontResetSize )
//                resetSize( this );
//
//            if ( this.firstLoad )
//                CKEDITOR.tools.setTimeout( function(){
//                    switchLockRatio( this, 'check' );
//                }, 0, this );

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

        var searchTimer, searchPagesTimer,
        searchLabel = editor.lang.mwplugin.searchLabel,
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
            var LoadPreviewImage = function(result) {
                var url = result.responseText.Trim();                                
                if (! url)
                    url = CKEDITOR.getUrl( editor.skinPath + 'images/noimage.png' );
                SrcInWiki = url;
                // Query the preloader to figure out the url impacted by based href.
                previewPreloader.setAttribute( 'src', url );
                dialog.preview.setAttribute( 'src', previewPreloader.$.src );
                updatePreview( dialog );
                dialog.originalElement.setAttribute( 'src', url );                
            }
            window.parent.sajax_request_type = 'GET' ;
            window.parent.sajax_do_call( 'wfSajaxGetImageUrl', [img], LoadPreviewImage ) ;
        }

        var updatePreview = function( dialog ) {
            //Don't load before onShow.
            if ( !dialog.originalElement || !dialog.preview )
                return 1;

            // Read attributes and update imagePreview;
            dialog.commitContent( PREVIEW, dialog.preview );
            return 0;
        }

        var SetSearchMessage = function ( dialog, message ) {
            message = searchLabel.replace(/%s/, message);
            var	e = dialog.getContentElement( 'info', 'imgList' ),
            label = document.getElementById(e.domId).getElementsByTagName('label')[0];
            e.html = message;
            label.innerHTML = message;
        }

        var ClearSearch = function(dialog) {
            var	e = dialog.getContentElement( 'info', 'imgList' );
            e.items = [];
            var div = document.getElementById(e.domId),
            select = div.getElementsByTagName('select')[0];
            while ( select.options.length > 0 )
                select.remove( 0 )
        }

        var OnUrlChange = function( dialog ) {

            //var dialog = this.getDialog();

            var StartSearch = function() {
                var	e = dialog.getContentElement( 'info', 'imgFilename' ),
                link = e.getValue().Trim();

                SetSearchMessage( dialog, editor.lang.mwplugin.searching ) ;
            
                // Make an Ajax search for the pages.
                window.parent.sajax_request_type = 'GET' ;
                window.parent.sajax_do_call( 'wfSajaxSearchImageCKeditor', [link], LoadSearchResults ) ;
            }

            var LoadSearchResults = function(result) {
                var results = result.responseText.split( '\n' ),
                select = dialog.getContentElement( 'info', 'imgList' );

                ClearSearch(dialog) ;

                if ( results.length == 0 || ( results.length == 1 && results[0].length == 0 ) ) {
                    SetSearchMessage( dialog, editor.lang.mwplugin.noImgFound ) ;
                }
                else {
                    if ( results.length == 1 )
                        SetSearchMessage( dialog, editor.lang.mwplugin.oneImgFound ) ;
                    else
                        SetSearchMessage( dialog, results.length + editor.lang.mwplugin.manyImgFound ) ;

                    for ( var i = 0 ; i < results.length ; i++ ) {
                        if (results[i] == '___TOO__MANY__RESULTS___')
                            select.add ( editor.lang.mwplugin.tooManyResults );
                        else{                           
                            select.add( results[i].replace(/_/g, ' '), results[i]);
                        }
                     }

                     
                }

            }

            var e = dialog.getContentElement( 'info', 'imgFilename' ),
            link = e.getValue().Trim();

            if ( searchTimer )
                window.clearTimeout( searchTimer ) ;

            if( /^(http|https):\/\//.test( link ) ) {
                SetSearchMessage( dialog, editor.lang.mwplugin.externalLink ) ;
                return ;
            }

            if ( link.length < 1  )
                SetSearchMessage( dialog, editor.lang.mwplugin.startTyping ) ;
            else
                SetSearchMessage( dialog, editor.lang.mwplugin.searching ) ;
            searchTimer = window.setTimeout( StartSearch, 500 ) ;

        }
        // return 0 create no image link, 1 image with local url, 2 image link with ext. url
        var createImageLink = function ( uri ) {
            uri = decodeURI( uri ).toLowerCase();
            // external link and wgAllowExternalImages is not set
            if (! uri.match(/^https?:\/\//))
                return 1;
            if (typeof window.parent.wgAllowExternalImages != 'undefined' &&
                window.parent.wgAllowExternalImages )
                return 2;
            if (typeof window.parent.wgAllowExternalImagesFrom != 'undefined') {
                for (var i = 0; i < window.parent.wgAllowExternalImagesFrom.length; i++ ) {
                    if (uri.startsWith(window.parent.wgAllowExternalImagesFrom[i].toLowerCase()))
                        return 2;
                }
            }
            return 0;

        }
        
        var OnImageLinkChange = function( dialog ) {
           if ( searchPagesTimer )
                    window.clearTimeout( searchPagesTimer ) ;
                
           var imageLinkElement = dialog.getContentElement( 'info', 'imageLink' );
           var pageListElement = dialog.getContentElement( 'info', 'pageList' );
           
            var StartPageSearch = function() {
                var link = imageLinkElement.getValue().Trim();               
            
                // Make an Ajax search for the pages.
                window.parent.sajax_request_type = 'GET' ;
                window.parent.sajax_do_call( 'wfSajaxSearchArticleCKeditor', [link], LoadSearchResults ) ;
                ClearSearch() ;
                SetSearchMessage( editor.lang.mwplugin.searching ) ;
            }
            
            var ClearSearch = function() {     
                pageListElement.clear();               
            }
            
            var SetSearchMessage = function ( message ) {
                message = editor.lang.mwplugin.searchLabel.replace(/%s/, message);
                label = document.getElementById(pageListElement.domId).getElementsByTagName('label')[0];
                pageListElement.html = message;
                label.innerHTML = message;
            }

            var LoadSearchResults = function(result) {
                
            
                var results = [];
                if(result && result.responseText.trim()){  
			results = result.responseText.split( '\n' ); 
                    if(results.length){
                        if(results.length > 1)
                            SetSearchMessage( results.length + editor.lang.mwplugin.manyPagesFound ) ;
                        else
                            SetSearchMessage( editor.lang.mwplugin.onePageFound ) ;
                        for ( var i = 0 ; i < results.length ; i++ ) {
                            if (results[i] == '___TOO__MANY__RESULTS___')
                                pageListElement.add ( editor.lang.mwplugin.tooManyResults );
                            else{
                                pageListElement.add ( results[i].replace(/_/g, ' '), results[i] );  
                            }
                            
                        }
                    }
                    else{
                        SetSearchMessage( editor.lang.mwplugin.noPagesFound ) ;
                    }                    
                }
                else{
                    SetSearchMessage( editor.lang.mwplugin.noPagesFound ) ;                                    
                }
            }
            
            searchPagesTimer = window.setTimeout( StartPageSearch, 500 ) ;
        

        }
        return {
            
            title : editor.lang.mwplugin.imgTitle,
            minWidth : 420,
            minHeight : 310,
            contents : [
            {
                id : 'info',
                label : 'Tab info',
                elements :
                [
                {
                    type : 'hbox', 
                    style : 'width: 60%;',
                    children: [
                    {
                        type: 'vbox',
                        style : 'width:40%;',
                        children: [
                        {
                            id: 'imgFilename',
                            type: 'text',
                            label: imgLabelField,
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
                                    
                                    this.getDialog().dontResetSize = true;

                                    this.setValue( url );		// And call this.onChange()
                                    // Manually set the initial value.(#4191)
                                    this.setInitValue();
                                }
                            },
                            commit : function( type, element )
                            {
                                if ( type == IMAGE && ( this.getValue() || this.isChanged() ) )
                                {
                                    var doImageLink = createImageLink( this.getValue() );
                                    if ( doImageLink > 0) {
                                        element.setAttribute( '_cke_saved_src', decodeURI( this.getValue() ) );
                                        element.setAttribute( '_fck_mw_filename', decodeURI( this.getValue() ) );
                                        if ( doImageLink > 1 )
                                            element.setAttribute( 'src', decodeURI( this.getValue() ) );
                                        else
                                            element.setAttribute( 'src', SrcInWiki );
                                    }
                                    else {
                                        element.setAttribute( 'href', decodeURI( this.getValue() ) );
                                    }
                                }
                                else if ( type == CLEANUP )
                                {
                                    element.setAttribute( 'src', '' );	// If removeAttribute doesn't work.
                                    element.removeAttribute( 'src' );
                                    element.setAttribute('href', '');
                                    element.removeAttribute( 'href' );
                                }
                            },
                            validate : CKEDITOR.dialog.validate.notEmpty( editor.lang.image.urlMissing )
                        },
                        
                        {
                            id: 'imgList',
                            type: 'select',
                            size: 6,
                            label: editor.lang.mwplugin.searchLabel.replace(/%s/, editor.lang.mwplugin.startTyping),
                            title: 'image list',
                            required: false,
                            style: 'border: 1px; width:100%;',
                            items: [  ],
                            onChange: function () {
                                var dialog = this.getDialog(),
                                newImg = this.getValue(),
                                e = dialog.getContentElement( 'info', 'imgFilename' );
                                if ( newImg == editor.lang.mwplugin.tooManyResults ) 
                                    return;

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
//                                dialog.originalElement.setAttribute( 'src', newImg );

                            }
                        },                                       
                        {
                            id   : 'imageLink',
                            title: 'image link',
                            style: 'width:100%;',                                     
                            type : 'text',
                            label: editor.lang.mwplugin.imgLinkLabel,
                           
                            onKeyUp: function () {
                              OnImageLinkChange( this.getDialog() );  
                            },
                                               
                            setup : function( type, element )
                            {
                                if ( type == IMAGE )
                                {                                                                                                
                                    this.setValue(element.getAttribute('link'));                                   
                                }
                            },
                            commit : function( type, element )
                            {
                                if ( type == IMAGE && (this.getValue() || this.isChanged()))
                                {
                                    element.setAttribute('link', decodeURI(this.getValue()));                                                    
                                }                                            
                            }                                          
                        },
                        {
                            id: 'pageList',
                            type: 'select',
                            label: editor.lang.mwplugin.searchLabel.replace(/%s/, editor.lang.mwplugin.startTyping),
                            size: 6,
                            title: 'page list',
                            required: false,
                            style: 'border: 1px; width:100%;',
                            items: [  ],
                            onChange: function () {
                                var dialog = this.getDialog(),
                                selectedPage = this.getValue(),
                                imageLinkElement = dialog.getContentElement( 'info', 'imageLink' );
                                if ( selectedPage == editor.lang.mwplugin.tooManyResults ) 
                                    return;

                                imageLinkElement.setValue(selectedPage.replace(/_/g, ' '));                         
                            }
                        }
                        ]
                    },
                    {
                        type : 'hbox', 
                        style : 'height:100%; important!',
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
                            '</td></tr></table></div></div>',
                            setup : function( type, element ) {
                                if(element){
                                    var imgSrc = element.getAttribute('src');
                                    CKEDITOR.document.getById( previewImageId ).setAttribute('src', imgSrc);
                                }
                            }
                        }
                        ]
                    }

                    ]
                },
                {
                    id: 'imgCaption',
                    type: 'text',
                    label: editor.lang.mwplugin.caption,
                    style: 'border: 1px;',
                    
                    setup : function( type, element ) {
                        if ( type == IMAGE )
                            this.setValue( decodeURIComponent(element.getAttribute( '_fck_mw_caption' )));
                    },
                    commit : function( type, element ) {
                        if ( type == IMAGE ) {
                            if ( this.getValue() || this.isChanged() ) {
                                element.setAttribute( '_fck_mw_caption', encodeURIComponent(this.getValue()));
                            }
                        }
                        else if ( type == PREVIEW )
                            element.setAttribute( '_fck_mw_caption', encodeURIComponent(this.getValue()));
                        else if ( type == CLEANUP )
                            element.removeAttribute( '_fck_mw_caption' );
                    }

                },
                {
                    type: 'hbox',
                    children:
                    [
                    {
                        id: 'imgSpecialType',
                        type: 'select',
                        label: editor.lang.mwplugin.imgType,                                        
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
                                            element.removeClass('fck_mw_border');
                                            element.addClass('fck_mw_frame');
                                            break;
                                        case 'Frame' :
                                            element.setAttribute('_fck_mw_type', 'frame');
                                            element.removeClass('fck_mw_border');
                                            element.addClass('fck_mw_frame');
                                            break;
                                        case 'Border' :
                                            element.setAttribute('_fck_mw_type', 'border');
                                            element.removeClass('fck_mw_frame');
                                            element.addClass('fck_mw_border');
                                            break;
                                        default:
                                            element.setAttribute('_fck_mw_type', '');
                                            element.removeClass('fck_mw_border');
                                            element.addClass('fck_mw_frame');
                                    }
                                }
                            }
                            else if ( type == CLEANUP )
                                element.setAttribute('_fck_mw_type', '');
                        }
                    },
                    {
                        id: 'imgAlign',
                        type: 'select',
                        label: editor.lang.common.align,                       
                        items: [
                          [''],
                        [ editor.lang.mwplugin.alignNone, 'None' ],
                        [ editor.lang.common.alignLeft , 'Left' ],
                        [ editor.lang.common.alignRight, 'Right' ],
                        [ editor.lang.common.alignCenter, 'Center' ]
                        ],
                        setup : function( type, element ) {
                            var location = element.getAttribute('_fck_mw_location') || '',
                            align = location.replace(/[\w\s]*fck_mw_(right|left|center|none)[\w\s]*/, '$1');
                            if ( type == IMAGE && align )
                                this.setValue( align.FirstToUpper() );
                        },
                        commit : function( type, element ) {
                            if ( type == IMAGE ) {
                                if ( this.getValue() || this.isChanged() ) {
                                    var newVal = this.getValue().toLowerCase().Trim(),
                                    classes = [ 'right', 'left', 'center', 'none' ];

                                    if ( newVal) {
                                        for (var i = 0; i < classes.length; i++ ) {
                                            if ( newVal == classes[i] )
                                                element.addClass('fck_mw_' + classes[i]);
                                            else
                                                element.removeClass('fck_mw_' + classes[i]);
                                        }
                                        element.setAttribute('_fck_mw_location', newVal);
                                    }
                                    else {
                                        element.removeClass('fck_mw_right');
                                        element.removeClass('fck_mw_center');
                                        element.removeClass('fck_mw_none');
                                        element.addClass('fck_mw_left');
                                        element.removeAttribute('_fck_mw_location');
                                    }
                                }
                            }
                        }
                    },
                    {
                        id: 'imgWidth',
                        type: 'text',
                        label: editor.lang.common.width,                                        
                        setup : function( type, element ) {
                            var imgWidth = element.getAttribute('width');
                            if ( type == IMAGE && imgWidth )
                                this.setValue( imgWidth );                            
                        },
                        commit : function( type, element ) {
                            var value = this.getValue();                            
                            if ( type == IMAGE )
                            {
                                value = value.replace(/\D/g, '');
                                if ( value )
                                    element.setAttribute('width', value + 'px');
                                else if (this.isChanged())
                                    element.removeAttribute( 'width' );
                            }
                            else if ( type == PREVIEW )
                            {                                
                                var oImageOriginal = this.getDialog().originalElement;
                                if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
                                    element.setAttribute('width', oImageOriginal.$.width + 'px');
                            
                            }
                            else if ( type == CLEANUP )
                            {
                                element.removeAttribute( 'width' );
                            }
                        }

                    },
                    {
                        id: 'imgHeight',
                        type: 'text',
                        label: editor.lang.common.height,                                        
                        setup : function( type, element ) {
                            var imgHeight = element.getAttribute('height');                           
                            if ( type == IMAGE && imgHeight )
                                this.setValue( imgHeight );
                        },
                        commit : function( type, element )
                        {
                            var value = this.getValue();
                            if ( type == IMAGE )
                            {
                                value = value.replace(/\D/g, '');
                                if ( value )
                                    element.setAttribute( 'height', value + 'px');
                                else if (this.isChanged())
                                    element.removeAttribute( 'height' );                                
                            }
                            else if ( type == PREVIEW )
                            {                                
                                var oImageOriginal = this.getDialog().originalElement;
                                if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
                                    element.setAttribute( 'height', oImageOriginal.$.height + 'px' );
                            }
                            else if ( type == CLEANUP )
                            {
                                element.removeAttribute( 'height' );
                            }
                        }

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
                // Change the image element into a link when it's an external URL
                if ( this.imageElement.getAttribute('href') ) {
                    var link = editor.document.createElement( 'a' );
                    link.setAttribute('href', this.imageElement.getAttribute('href'));
                    var text = this.imageElement.getAttribute('alt') || this.imageElement.getAttribute('href');
                    link.setText( text );
                    this.imageElement = link;
                }
                else {
                    // set some default classes for alignment and border if this is not defined
                    var attrClass = this.imageElement.getAttribute('class');
                    if ( !( attrClass && attrClass.match(/fck_mw_(frame|border)/) ) )
                        this.imageElement.addClass('fck_mw_border');
                    if ( !( attrClass && attrClass.match(/fck_mw_(left|right|center)/) ) )
                        this.imageElement.addClass('fck_mw_right');
                }
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
                this.reset();
                this.imageEditMode = false;
                this.dontResetSize = false;
                
                // clear old selection list from a previous call
                var imageListElement = this.getContentElement( 'info', 'imgList' );
                imageListElement.clear();
                var pageListElement = this.getContentElement( 'info', 'pageList' );
                pageListElement.clear();

                var editor = this.getParentEditor();
                // set correct label for image list
                var message = editor.lang.mwplugin.searchLabel.replace(/%s/, editor.lang.mwplugin.startTyping);
                imageListElement.label = message;

                var selection = editor.getSelection(),
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

                //only local images which are not fake objects
                if ( element
                  && element.getName() === 'img'
                  && !element.getAttribute( 'data-cke-realelement' )
                  && (element.getAttribute('_fck_mw_location') || element.getAttribute('_fck_mw_filename')))
                {
                    this.imageEditMode = element.getName();
                    this.imageElement = element;
                    SrcInWiki = element.getAttribute( 'src' );
                }
                if ( this.imageEditMode )
                {
                    // Use the original element as a buffer since we don't want
                    // temporary changes to be committed, e.g. if the dialog is canceled.
                    this.cleanImageElement = this.imageElement;
                    this.imageElement = this.cleanImageElement.clone( true, true );

                    // Fill out all fields.
                    this.setupContent( IMAGE, this.imageElement );

                }
                else
                    this.imageElement =  editor.document.createElement( 'img' );

                // Dont show preview if no URL given.
                var imgValue = CKEDITOR.tools.trim( this.getValueOf( 'info', 'imgFilename' ));
                if (imgValue)
                {
                    OnUrlChange( this );                             
                } 
                var imgLinkValue = CKEDITOR.tools.trim( this.getValueOf( 'info', 'imageLink' ));
                if(imgLinkValue){
                    OnImageLinkChange( this );
                }
                
                
            },
            onHide : function()
            {
                if ( this.preview )
                    this.commitContent( CLEANUP, this.preview );

                if ( this.originalElement )
                {
                    this.originalElement.removeListener( 'load', onImgLoadEvent );
                    this.originalElement.removeListener( 'error', onImgLoadErrorEvent );
                    this.originalElement.removeListener( 'abort', onImgLoadErrorEvent );
                    this.originalElement.remove();
                    this.originalElement = false;		// Dialog is closed.
                }

                delete this.imageElement;                
              
                
            }


        }    
});
