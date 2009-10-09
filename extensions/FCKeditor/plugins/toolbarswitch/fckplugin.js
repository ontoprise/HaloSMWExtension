// Resize button for full screen mode of the FCK

var tbButton = new FCKToolbarButton( 'ToolbarSwitch', 'Switch toolbar', 'Switch toolbar', null, true) ;
tbButton.IconPath = FCKConfig.PluginsPath + 'toolbarswitch/tb_icon_tools.png' ;
FCKToolbarItems.RegisterItem( 'ToolbarSwitch', tbButton );

var ToolbarSwitchCommand = window.parent.Class.create();
ToolbarSwitchCommand.prototype = {
    initialize: function() {
        this.tbType = 0;
        this.tbNames = ['Wiki', 'WikiEnhanced'];
    },

    GetState: function() {
        return this.tbType;
    },

    Execute: function() {
        this.tbType = 1 - this.tbType;
        FCK.ToolbarSet.Load(this.tbNames[this.tbType]);
    },

    Restore: function() {
        FCK.ToolbarSet.Load(this.tbNames[this.tbType]);
    }
}

fckToolbarSwitch= new ToolbarSwitchCommand();

FCKCommands.RegisterCommand( 'ToolbarSwitch', fckToolbarSwitch) ;


