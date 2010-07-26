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
        this.uri = window.parent.wgServer + window.parent.wgScriptPath + "/index.php?title=" + encodeURIComponent(pagename) + "&action=edit";
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
        var ContextMenu = window.parent.document.createElement('div');
        //document.getElementById(iframeId).contentWindow.document
        ContextMenu.style.position="absolute";
        ContextMenu.style.top=parseInt(y / 2)+'px';
        ContextMenu.style.left=parseInt(x / 2)+'px';
        ContextMenu.style.zIndex=1000;
        ContextMenu.style.visability='visible';
        ContextMenu.style.backgroundColor = "#E3E3C7";
        ContextMenu.id = "fckMweditSaveChanges";
        ContextMenu.innerHTML = this.getHtml();
        var el = window.parent.document.getElementById('globalWrapper');
        el.appendChild(ContextMenu);
    },

    getHtml: function() {
        var frame = 0;
        if (FCKBrowserInfo.IsIE) {
            for (i=0; i<window.parent.frames.length; i++) {
                if (window.parent.frames(i).document.title == "FCKeditor") {
                    frame = i;
                    break;
                }
            }
        }
    
        return '<span style="padding: 3px 10px; font-weight:bold; color:#737357; font-size: 14pt">Save changes?</span><br/>' +
           'The editor content has changed.<br/>Do you want to save the changes?<br/>' +
           '<br/><br/><div style="text-align: center;">' +
           '<input type="submit" name="wgSave" value="yes" onClick="window.frames['+frame+'].switchToStandardEdit.save();" />&nbsp;' +
           '<input type="submit" name="dontSave" value="no" onClick="window.frames['+frame+'].switchToStandardEdit.redirect();" />&nbsp;' +
           '<input type="submit" name="cancel" value="cancel" onClick="window.frames['+frame+'].switchToStandardEdit.cancel();" />&nbsp;' +
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
        var el = window.parent.document.getElementById('fckMweditSaveChanges');
        el.parentNode.removeChild(el);
    }
}

var switchToStandardEdit = new StartStandardMwEditCommand();
FCKCommands.RegisterCommand( 'MW_Edit', switchToStandardEdit) ;