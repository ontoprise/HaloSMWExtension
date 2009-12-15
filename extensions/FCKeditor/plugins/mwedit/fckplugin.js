// Register our toolbar buttons.
var tbButton = new FCKToolbarButton( 'MW_Edit', 'StandardEditor', 'Switch to Wiki text editor', null, true) ;
tbButton.IconPath = FCKConfig.PluginsPath + 'mwedit/images/icon_terminal.png' ;
FCKToolbarItems.RegisterItem( 'MW_Edit', tbButton );

var StartStandardMwEditCommand = window.parent.Class.create();
StartStandardMwEditCommand.prototype = {
    initialize: function() {
        var pagename = window.parent.wgPageName;
        // possibly Semantic forms are working
        if (window.parent.wgNamespaceNumber == -1) {
            pagename = window.parent.location.href
            pagename = pagename.substr(pagename.lastIndexOf('/')+1)
            // still Special:blub in pagename, then original page is in param target
            if (pagename.indexOf(window.parent.wgCanonicalNamespace) == 0) {
                pagename=pagename.replace(/.*?target=([^&]*)(&.*)?/, '$1');
            } 
        }
        this.uri = window.parent.wgServer + window.parent.wgScriptPath + "/index.php?title=" + pagename + "&action=edit";
        this.ContextMenu = null;
    },

    GetState: function() {
        return FCK_TRISTATE_OFF; //we don't want the button to be toggled
    },

    Execute: function() {
        if (FCK.IsDirty()) {
            this.makeContainer();
        }
        else {
            this.redirect();
        }
	
    },

    makeContainer: function() {
            var x = (FCKBrowserInfo.IsIE) ? self.document.body.clientWidth : self.innerWidth;
            var y = (FCKBrowserInfo.IsIE) ? self.document.body.clientHeight: self.innerHeight;
            this.ContextMenu = new window.parent.ContextMenuFramework();
            this.ContextMenu.setPosition(parseInt(x / 2), parseInt(y / 2));
            this.ContextMenu.setContent(this.getHtml(), 'ANNOTATIONHINT', 'Save changes?');
            this.ContextMenu.showMenu();

    },

    getHtml: function() {
    return 'The editor content has changed. Do you want to save the changes?<br/>' +
           '<br/><br/><div style="text-align: center;">' +
           '<input type="submit" name="wgSave" value="yes" onClick="window.frames[0].switchToStandardEdit.save();" />&nbsp;' +
           '<input type="submit" name="dontSave" value="no" onClick="window.frames[0].switchToStandardEdit.redirect();" />&nbsp;' +
           '<input type="submit" name="cancel" value="cancel" onClick="window.frames[0].switchToStandardEdit.cancel();" />&nbsp;' +
           '</div>';
    },

    save: function() {
        var wpSave = document.createElement('input');
        wpSave.setAttribute('name', 'wpSave');
        wpSave.setAttribute('type', 'hidden');
        wpSave.setAttribute('value', '1');

        // normal window
        if (window.parent.document.getElementById('editform')) {
            window.parent.document.getElementById('editform').appendChild(wpSave);
            window.parent.document.getElementById('editform').submit();
        }
        // Semantic forms are active
        else if ((window.parent.wgAction == "formedit") &&
                 window.parent.document.getElementsByName('createbox').length > 0) {
            window.parent.document.getElementsByName('createbox')[0].appendChild(wpSave);
            window.parent.document.getElementsByName('createbox')[0].submit();
        }
        // no clue where the textarea might be
        else {
            alert('Error: article can\'t be saved');
            this.redirect();
        }
        //this.redirect();
    },

    redirect: function() {
        window.parent.location.href = this.uri;
    },

    cancel: function() {
        this.ContextMenu.remove();
    }
}

var switchToStandardEdit = new StartStandardMwEditCommand();
FCKCommands.RegisterCommand( 'MW_Edit', switchToStandardEdit) ;