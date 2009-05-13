/*
 * Dialog which appears on a drag&drop operation
 * 
 * Should be used to create 
 */

var WikiFormSelectDialog = new Object() ;

// This method opens a dialog window using the standard dialog template.
WikiFormSelectDialog.OpenDialog = function( dialogName, width, height, customValue, parentWindow, resizable )
{
    // Setup the dialog info.
    var oDialogInfo = new Object() ;
      
    oDialogInfo.CurrentPage = window.top.wgPageName;
    oDialogInfo.Editor = window ;
    oDialogInfo.Filename = customValue ;     // Optional
    
    var wikipathIndex = FCKConfig.BasePath.indexOf("/extensions");
    var wikiPath = FCKConfig.BasePath.substring(0, wikipathIndex);
    
    var sUrl = wikiPath+"/index.php/" + 'Main_Page' ;
    WikiFormSelectDialog.Show( oDialogInfo, dialogName, sUrl, width, height, window.top, resizable ) ;
}

WikiFormSelectDialog.Show = function( dialogInfo, dialogName, pageUrl, dialogWidth, dialogHeight, parentWindow, resizable )
{
    var iTop  = (FCKConfig.ScreenHeight - dialogHeight) / 2 ;
    var iLeft = (FCKConfig.ScreenWidth  - dialogWidth)  / 2 ;

    var sOption  = "location=no,menubar=no,toolbar=no,dependent=yes,dialog=yes,minimizable=no,alwaysRaised=yes" +
        ",resizable="  + ( resizable ? 'yes' : 'no' ) +
        ",width="  + dialogWidth +
        ",height=" + dialogHeight +
        ",top="  + iTop +
        ",left=" + iLeft ;

    if ( !parentWindow )
        parentWindow = window ;

    FCKFocusManager.Lock() ;

    var oWindow = parentWindow.open( '', 'FCKeditorDialog_' + dialogName, sOption, true ) ;

    if ( !oWindow )
    {
        alert( FCKLang.DialogBlocked ) ;
        FCKFocusManager.Unlock() ;
        return ;
    }

    oWindow.moveTo( iLeft, iTop ) ;
    oWindow.resizeTo( dialogWidth, dialogHeight ) ;
    oWindow.focus() ;
    oWindow.location.href = pageUrl ;

    oWindow.dialogArguments = dialogInfo ;

    // On some Gecko browsers (probably over slow connections) the
    // "dialogArguments" are not set to the target window so we must
    // put it in the opener window so it can be used by the target one.
    parentWindow.FCKLastDialogInfo = dialogInfo ;

    this.Window = oWindow ;

    // Try/Catch must be used to avoid an error when using a frameset
    // on a different domain:
    // "Permission denied to get property Window.releaseEvents".
    try
    {
        window.top.parent.addEventListener( 'mousedown', WikiFormSelectDialog.CheckFocus, true ) ;
        window.top.parent.addEventListener( 'mouseup', WikiFormSelectDialog.CheckFocus, true ) ;
        window.top.parent.addEventListener( 'click', WikiFormSelectDialog.CheckFocus, true ) ;
        window.top.parent.addEventListener( 'focus', WikiFormSelectDialog.CheckFocus, true ) ;
    }
    catch (e)
    {}
}

WikiFormSelectDialog.CheckFocus = function()
{
    // It is strange, but we have to check the FCKDialog existence to avoid a
    // random error: "FCKDialog is not defined".
    if ( typeof( WikiFormSelectDialog ) != "object" )
        return false ;

    if ( WikiFormSelectDialog.Window && !WikiFormSelectDialog.Window.closed )
        WikiFormSelectDialog.Window.focus() ;
    else
    {
        // Try/Catch must be used to avoid an error when using a frameset
        // on a different domain:
        // "Permission denied to get property Window.releaseEvents".
        try
        {
            window.top.parent.removeEventListener( 'onmousedown', WikiFormSelectDialog.CheckFocus, true ) ;
            window.top.parent.removeEventListener( 'mouseup', WikiFormSelectDialog.CheckFocus, true ) ;
            window.top.parent.removeEventListener( 'click', WikiFormSelectDialog.CheckFocus, true ) ;
            window.top.parent.removeEventListener( 'onfocus', WikiFormSelectDialog.CheckFocus, true ) ;
        }
        catch (e)
        {}
    }
    return false ;
}

/*
 * Drag and drop handler 
 * 
 */
DndHook = new Object();

DndHook.DND_HOOK_ACTIVE = true;
DndHook._ExecDrop2 = function( evt )
    {
        
        if ( FCK.MouseDownFlag )
        {
            FCK.MouseDownFlag = false ;
            return ;
        }
        var droppedFile = DndHook.getFileName();
        
        DndHook.PasteTextAndOpenChildWindow(droppedFile) ;
       
        
        evt.preventDefault() ;
        evt.stopPropagation() ;
    }
    
DndHook._ExecDrop2dragover = function(evt) {
        var range = new FCKDomRange( FCK.EditorWindow ) ;
        range.MoveToNodeContents(evt.rangeParent);
             
        range.MoveToElementStartIndex(evt.rangeParent, evt.rangeOffset);
        
        range.Select();
}

DndHook.getFileName = function(evt) {
	 
  

  // Request the XP Connect privilege so we can access the XPCOM API. This will
  // cause a dialog to be displayed to the user asking them if they want to grant
  // this privilege to this script.
  netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');

 

  // Load in the native DragService manager from the browser.
  var dragService =
    Components.classes["@mozilla.org/widget/dragservice;1"]
      .getService(Components.interfaces.nsIDragService);

  // Load in the currently-executing Drag/drop session.
  var dragSession = dragService.getCurrentSession();

  

  // Create an instance of an nsITransferable object using reflection.
  var transferObject =
    Components.classes["@mozilla.org/widget/transferable;1"]
      .createInstance();

  // Bind the object explicitly to the nsITransferable interface. We need to do this to ensure that
  // methods and properties are present and work as expected later on.
  transferObject = transferObject.QueryInterface(Components.interfaces.nsITransferable);

  

  // I've chosen to add only the x-moz-file MIME type. Any type can be added, and the data for that format
  // will be retrieved from the Drag/drop service.
  transferObject.addDataFlavor("application/x-moz-file");

  // Get the number of items currently being dropped in this drag/drop operation.
  var numItems = dragSession.numDropItems;
 

  // Request the 'file read' privilege. We need to do this in order to be able to set the value of
  // a FileInput box. If we don't do this, we'll get a security exception when we try to set that
  // value.
  netscape.security.PrivilegeManager.enablePrivilege('UniversalFileRead');
 
  for (var i = 0; i < numItems; i++)
  {
    // Get the data for the given drag item from the drag session into our prepared
    // Transfer object.
    dragSession.getData(transferObject, i);

   

    // Start creating a String to hold all the text that will be in a debug alert.
    var supportedTypes = "transferobject supported transfer types array is:\n";

    // Get the set of all flavors supported by this Transfer object.
    var typeArray = transferObject.flavorsTransferableCanExport();
    var curItem = null;

    // Iterate through the array of supported MIME types, and write them all out to the
    // temporary output string.
    for (var j = 0; j < typeArray.Count(); j++)
    {
      curItem = typeArray.GetElementAt(j);

      // Cast this object as a C String via the QueryInterface method.
      curItem = curItem.QueryInterface(Components.interfaces.nsISupportsCString);

      supportedTypes += (curItem + "\n");
    }

   

    // We need to pass in Javascript 'Object's to any XPConnect method which
    // requires OUT parameters. The out value will then be saved as a new
    // property called Object.value.
    var dataObj = new Object();
    var dropSizeObj = new Object();

    // Get the Mozilla File data type from the ITransferable object.
    transferObject.getTransferData("application/x-moz-file", dataObj, dropSizeObj);

    // Cast the returned data object as an nsIFile so that we can retrieve it's filename.
    var droppedFile = dataObj.value.QueryInterface(Components.interfaces.nsIFile);
    //alert("dataObj filename is " + droppedFile.path + ", num is " + dropSizeObj.value);
    return droppedFile.leafName;

   
 
    }
}
    
    
DndHook.PasteTextAndOpenChildWindow = function(filename) {
    FCKTools.RunFunction( WikiFormSelectDialog.OpenDialog, WikiFormSelectDialog, ['WikiFormSelectDialog', 800, 640, filename] ) ;
    DndHook.insertText("[["+filename+"]]") ;
}

/**
 * Method which inserts the given text at the current cursor position (or selection).
 * 
 */
DndHook.insertText= function(text){
    
    sHtml = FCKTools.HTMLEncode( text )  ;
        //sHtml = FCKTools.ProcessLineBreaks( oEditor, FCKConfig, sHtml ) ;

        // FCK.InsertHtml() does not work for us, since document fragments cannot contain node fragments. :(
        // Use the marker method instead. It's primitive, but it works.
        var range = new FCKDomRange( FCK.EditorWindow ) ;
        var oDoc = FCK.EditorDocument ;
        range.MoveToSelection() ;
        range.DeleteContents() ;
        var marker = [] ;
        for ( var i = 0 ; i < 5 ; i++ )
            marker.push( parseInt(Math.random() * 100000, 10 ) ) ;
        marker = marker.join( "" ) ;
        range.InsertNode ( oDoc.createTextNode( marker ) ) ;
        var bookmark = range.CreateBookmark() ;

        // Now we've got a marker indicating the paste position in the editor document.
        // Find its position in the HTML code.
        var htmlString = oDoc.body.innerHTML ;
        var index = htmlString.indexOf( marker ) ;

        // Split it the HTML code up, add the code we generated, and put them back together.
        var htmlList = [] ;
        htmlList.push( htmlString.substr( 0, index ) ) ;
        htmlList.push( sHtml ) ;
        htmlList.push( htmlString.substr( index + marker.length ) ) ;
        htmlString = htmlList.join( "" ) ;

        if ( FCKBrowserInfo.IsIE )
            FCK.SetInnerHtml( htmlString ) ;
        else
            oDoc.body.innerHTML = htmlString ;

        range.MoveToBookmark( bookmark ) ;
        range.Collapse( false ) ;
        range.Select() ;
        range.Release() ;
        return true ;
}