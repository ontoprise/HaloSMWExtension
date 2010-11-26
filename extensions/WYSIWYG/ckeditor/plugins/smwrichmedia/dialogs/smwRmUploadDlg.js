(function(){
CKEDITOR.dialog.add( 'SMWrichmedia', function( editor ) {
    var getUri =  function () {
            var src = window.parent.wgScript + '?title=Special:UploadWindow',
                article = window.parent.wgTitle;
            if (window.parent.wgPageName == 'Special:AddData') {
                //obviously we are in Special:AddData and wgTitle is not containing what we're loooking for...
        		// try target= ...first
            	var regexS = "[\\?&]target=([^&#]*)";
        		var regex = new RegExp( regexS );
            	var result = regex.exec( window.parent.location.href );
                if (result == null) {
                    //target not found, it has to be the path now!
                    article = window.parent.location.pathname.match( /[^\/]+\/?$/ )[0];
                }
                else {
                    article = result[1];
                }
            }
            return src + '&RMUpload[RelatedArticles]='+article+'&wpIgnoreWarning=true';
        },
        location = getUri(),
        numbering = function( id )
		{
			return CKEDITOR.tools.getNextId() + '_' + id;
		},
		iframeId = numbering( 'CKeditorRmUpload' );


	return {
		title: 'Upload Media',

		minWidth: 800,
		minHeight: (window.outerHeight == undefined) ? 400 : parseInt(window.outerHeight * 0.6),

		contents: [
			{
				id: 'tab1_smw_rm',
				label: 'Tab1',
				title: 'Tab1',
                buttons : [ CKEDITOR.dialog.cancelButton ],
				elements : [
					{
						id: 'iframe_rm',
						type: 'html',
						label: "Text",
                        style: 'width:100%; height:100%;',
						html: '<div><iframe id="' + iframeId + '" name="'+ iframeId +'" \
                                       style="width:100%; height:100%; visibility:hidden;" \
                                       scrolling="auto"></iframe></div>'
					}
				 ]
			}
		],

        onShow : function() {
            // fix size of inner window for iframe
            var node = document.getElementsByName('tab1_smw_rm')[0];
            var child = node.firstChild;
            while ( child && (child.nodeType != 1 || child.nodeName.toUpperCase() != 'TABLE') )
                child = child.nextSibling;
            if (child) {
                child.style.height = '100%';
                var cells = child.getElementsByTagName('td');
                for (var i= 0; i < cells.length; i++)
                    cells[i].style.height = '100%';
            }
            // reload iframe
            document.getElementById(iframeId).src = location;
            document.getElementById(iframeId).style.visibility = 'visible';

        }

	};

} );

})();