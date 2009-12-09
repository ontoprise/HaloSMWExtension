var UltraPedia = {tabWidgets : []};
Ext.onReady(function(){
	for(var i=0;i<UltraPedia.tabWidgets.length;++i) {
	    new Ext.TabPanel({
	        renderTo : UltraPedia.tabWidgets[i].id,
	        activeTab : 0,
	        width : (UltraPedia.tabWidgets[i].width>0?UltraPedia.tabWidgets[i].width:600),
	        height : (UltraPedia.tabWidgets[i].height>0?UltraPedia.tabWidgets[i].height:250),
	        plain : true,
	        defaults : {autoScroll: true},
	        items : UltraPedia.tabWidgets[i].items
	    });
    }
});