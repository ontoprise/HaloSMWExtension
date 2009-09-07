/**
 * this file provides the notificationhandling system
 * for the haloacl-extension
 *
 * notificationhandling includes:
 * -creation of dialogs with given callback
 * -subscribing to elements in dom
 *
 */

YAHOO.namespace("haloacl.notification");
YAHOO.haloacl.notification.counter = 0;



YAHOO.haloacl.notification.createDialogOk = function (renderedTo,title,content,callback){
    if(title == null){
        title = "Info";
    }
    title = "HaloACL: "+title;
    YAHOO.haloacl.notification.counter++;

    new Insertion.Bottom(renderedTo,"<div id='haloacl_notification"+YAHOO.haloacl.notification.counter+"' class='yui-skin-sam'>&nbsp;</div>");

    if(YAHOO.haloacl.debug)console.log("create dialog called");
    var handleYes = function() {
        callback.yes();
        this.hide();
    };


    var dialog = 	new YAHOO.widget.SimpleDialog("dialog"+YAHOO.haloacl.notification.counter,
    {
        width: "300px",
        fixedcenter: true,
        visible: false,
        draggable: false,
        close: true,
        text: content,
        icon: YAHOO.widget.SimpleDialog.ICON_INFO,
        constraintoviewport: true,
        buttons: [ {
            text:"Ok",
            handler:handleYes,
            isDefault:true
        }]
    } );

    dialog.setHeader(title);

    // Render the Dialog
    dialog.render('haloacl_notification'+YAHOO.haloacl.notification.counter);
    dialog.show();
    
    if(YAHOO.haloacl.debug)console.log("create dialog finished");

};

YAHOO.haloacl.notification.createDialogYesNo = function (renderedTo,title,content,callback,yestext,notext){
    if(title == null){
        title = "Info";
    }
    title = "HaloACL: "+title;

    YAHOO.haloacl.notification.counter++;
    if(yestext == null){
        yestext = gLanguage.getMessage('ok');
    };
    if(notext == null){
        notext = gLanguage.getMessage('cancel');
    };

    new Insertion.Bottom(renderedTo,"<div id='haloacl_notification"+YAHOO.haloacl.notification.counter+"' class='yui-skin-sam'>&nbsp;</div>");

    if(YAHOO.haloacl.debug)console.log("create dialog called");
    var handleYes = function(content) {
        callback.yes(content);
        this.hide();
    };
    var handleNo = function(content) {
        callback.no(content);
        this.hide();
    };

    var dialog = 	new YAHOO.widget.SimpleDialog("dialog"+YAHOO.haloacl.notification.counter,
    {
        width: "300px",
        fixedcenter: true,
        visible: false,
        draggable: false,
        close: true,
        text: content,
        icon: YAHOO.widget.SimpleDialog.ICON_INFO,
        constraintoviewport: true,
        buttons: [ {
            text:yestext,
            handler:handleYes,
            isDefault:true
        },

        {
            text:notext,
            handler:handleNo
        } ]
    } );

    dialog.setHeader(title);

    // Render the Dialog
    dialog.render('haloacl_notification'+YAHOO.haloacl.notification.counter);
    dialog.show();

    if(YAHOO.haloacl.debug)console.log("create dialog finished");

};

YAHOO.haloacl.notification.subscribeToElement = function(elementId, event, callback){
    YAHOO.util.Event.addListener($(elementId), event, callback);
};

YAHOO.haloacl.notification.showInlineNotification = function(content, targetdiv){
    if(YAHOO.haloacl.debug)console.log("trying to add notification to targetdiv:"+targetdiv);

    $(targetdiv).innerHTML = content;
}
YAHOO.haloacl.notification.hideInlineNotification = function(targetdiv){
    $(targetdiv).innerHTML = "";
}