// Register our toolbar buttons.
var tbButton = new FCKToolbarButton( 'MW_Edit', 'StandardEditor', 'Switch to standard edit' ) ;
tbButton.IconPath = FCKConfig.PluginsPath + 'mwedit/images/icon_terminal.png' ;
FCKToolbarItems.RegisterItem( 'MW_Edit', tbButton );

var StartStandardMwEditCommand = window.parent.Class.create();
StartStandardMwEditCommand.prototype = {
    initialize: function() {
        this.uri = window.parent.wgServer + window.parent.wgScriptPath + "/index.php?title=" + window.parent.wgPageName + "&action=edit";
        this.ContextMenu = null;
    },

    GetState: function() {
        return FCK_TRISTATE_OFF; //we dont want the button to be toggled
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
            this.ContextMenu = new window.parent.ContextMenuFramework();
            this.ContextMenu.setPosition(parseInt(self.innerWidth / 2),
                                         parseInt(self.innerHeight / 2));
            this.ContextMenu.setContent(this.getHtml(), 'ANNOTATIONHINT', 'Save changes?');
            this.ContextMenu.showMenu();

    },

    getHtml: function() {
    return 'The editor content has changed. Do you want to save the changes?<br/>' +
           '<br/><br/>' +
           '<input type="submit" name="wgSave" value="Save changes" onClick="window.frames[0].switchToStandardEdit.save();" />&nbsp;' +
           '<input type="submit" name="dontSave" value="Dont save" onClick="window.frames[0].switchToStandardEdit.redirect();" />&nbsp;' +
           '<input type="submit" name="cancel" value="Cancel" onClick="window.frames[0].switchToStandardEdit.cancel();" />&nbsp;';
    },

    save: function() {
        var wpSave = document.createElement('input');
        wpSave.setAttribute('name', 'wpSave');
        wpSave.setAttribute('type', 'hidden');
        wpSave.setAttribute('value', '1');
        window.parent.document.getElementById('editform').appendChild(wpSave);
        window.parent.document.getElementById('editform').submit();
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