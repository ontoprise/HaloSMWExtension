// Resize button for full screen mode of the FCK

var tbButton = new FCKToolbarButton( 'Fullscreen', 'Fullscreen', 'Fullscreen', null, true) ;
tbButton.IconPath = FCKConfig.PluginsPath + 'fullscreen/tb_icon_fullscreen.gif' ;
FCKToolbarItems.RegisterItem( 'Fullscreen', tbButton );

var FullscreenCommand = window.parent.Class.create();
FullscreenCommand.prototype = {
    initialize: function() {
        this.fullscreen = 0;
        this.fckiframe = window.parent.document.getElementsByTagName('iframe')[0];
        this.origStyle = this.fckiframe.getAttribute('style');
    },

    GetState: function() {
        return this.fullscreen;
    },

    Execute: function() {
	if (this.fullscreen) {
           this.fckiframe.setAttribute('style', this.origStyle);
        }
        else {
            this.fckiframe.style.left = '0px';
            this.fckiframe.style.top = '0px';
            this.fckiframe.style.height = '100%'
            this.fckiframe.style.width = '100%'
            this.fckiframe.style.position = 'fixed';
            this.fckiframe.style.zIndex = 11000;
        }
        this.fullscreen = 1 - this.fullscreen;
    }
}

fckFullscreen = new FullscreenCommand();

FCKCommands.RegisterCommand( 'Fullscreen', fckFullscreen) ;


