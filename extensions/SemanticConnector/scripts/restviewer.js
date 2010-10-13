SemanticConnector = {restviewer : {ajaxUrlPrefix : ''} };

Ext.ux.clone = function(o) {
	if(!o || 'object' !== typeof o) {
		return o;
	}
	var c = '[object Array]' === Object.prototype.toString.call(o) ? [] : {};
	var p, v;
	for(p in o) {
		if(o.hasOwnProperty(p)) {
			v = o[p];
			if(v && 'object' === typeof v) {
				c[p] = Ext.ux.clone(v);
			}
			else {
				c[p] = v;
			}
		}
	}
	return c;
}; // eo function clone

Ext.ux.copyText = function(text) {
	if (window.clipboardData) { // Internet Explorer
		window.clipboardData.setData("Text", ""+ text); // stupid IE... won't work without the ""+ ?!?!?
	} else if (window.netscape) { // Mozilla
		var error = 'Your browser does not allow clipboard access.\nThe RESTful command could not be copied to your clipboard.\nPlease copy the query manually.';
		try {
			netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
			var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
			if (!clip){
				alert(error);
				return;
			}
			var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
			if (!trans){
				alert(error);
				return;
			}
			trans.addDataFlavor('text/unicode');
			var str = new Object();
			var len = new Object();
			var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
			str.data=text;
			trans.setTransferData("text/unicode",str,text.length*2);
			var clipid=Components.interfaces.nsIClipboard;
			if (!clip){
				alert(error);
				return;
			}
			clip.setData(trans,null,clipid.kGlobalClipboard);
			alert('The RESTful command was successfully copied to your clipboard');
		}
		catch (e) {
			alert(error);
		}
	} else {
		return alert("Your browser does not support this feature");
	}
}

Ext.onReady(function(){

	var Command = Ext.data.Record.create([{
		name: 'command'
	}]);
	
	var store = new Ext.data.Store({
		reader: new Ext.data.ArrayReader({
			idIndex: 0  // id for each record will be the first element
		}, Command)
	});
	
/*
	var store = new Ext.data.Store({
		remoteSort: true,
		baseParams: {lightWeight:true,ext: 'js'},

		sortInfo: {field:'lastpost', direction:'DESC'},
		autoLoad: {params:{start:0, limit:20}},

		proxy: new Ext.data.ScriptTagProxy({
			url: 'http://extjs.com/forum/topics-browse-remote.php'
		}),

		reader: new Ext.data.JsonReader({
			root: 'topics',
			totalProperty: 'totalCount',
			idProperty: 'threadid',
			fields: [
				'title', 'forumtitle', 'forumid', 'author',
				{name: 'replycount', type: 'int'},
				{name: 'lastpost', mapping: 'lastpost', type: 'date', dateFormat: 'timestamp'},
				'lastposter', 'excerpt'
			]
		})
	});
*/

/*
	var grid = new Ext.grid.GridPanel({
		height:200,
		split: true,
		region: 'north',

		trackMouseOver:false,
		autoExpandColumn: 'cmdhistory',
		store: store,

		columns: [new Ext.grid.RowNumberer({width: 30}),{
			id: 'cmdhistory',
			header: "Command History",
			dataIndex: 'command',
			width: 420,
//			renderer: renderCommand,
			sortable:false
		}]
	});
	// define a template to use for the detail view
	var restTplMarkup = [
		'Return Code: <b>{retCode}</b><br/>',
		'Message: <b>{message}</b><br/>',
		'Result: <br/><p>{result}</p>'
	];
	var restTpl = new Ext.Template(restTplMarkup);
*/
	var lastCmd = "";
	var restful = new Ext.Panel({
		frame: true,
		width: 540,
		height: 200,
		region: 'north',
		layout: 'border',
		items: [
//			grid,
/*
			{
				id: 'helpPanel',
				region: 'center',
				bodyStyle: {
					background: '#ffffff',
					padding: '7px'
				},
				html: 'Please input RESTful command and Run it to view the result.'
			},
*/
			{
				id: 'commandPanel',
				xtype: 'textarea',
				region: 'center',
				emptyText: 'Please input RESTful command here ...',
				style: {
					background: '#ffffff',
					padding: '7px'
				}
			}
		],
		bbar: new Ext.Toolbar({
			items: [
//				{
//					text: 'Clean history',
//					handler: function(){
//						store.removeAll();
//						lastCmd = '';
//					}
//				},
//				'-',
				{
					text: 'Reset',
					handler: function(){
						var commandPanel = Ext.getCmp('commandPanel');
						commandPanel.reset();
					}
				},
				{
					text: 'Run',
					handler: function(){
						var commandPanel = Ext.getCmp('commandPanel');
						var cmd = commandPanel.getRawValue();
						if(cmd == "") return;
						
		var conn = new Ext.data.Connection();
		conn.request({
			url: SemanticConnector.restviewer.ajaxUrlPrefix + cmd,
			method: 'GET',
			success: function(responseObject) {
				var resultPanel = Ext.getCmp('resultPanel');
				try{
					var o = Ext.util.JSON.decode(responseObject.responseText);
					resultPanel.setRawValue(o.msg);
					if(o.success) {
						if(cmd != lastCmd && cmd != "") {
							store.add(new Command({command: cmd}));
							lastCmd = cmd;
						}
					}					
				} catch(err) {
					resultPanel.setRawValue('Wrong REST command, please check your command and retry.');
				}
			},
			failure: function() {
				Ext.Msg.alert('Error', 'Unable to run REST command.');
			}
		});
/*
					viewer.getForm().submit({
						waitMsg:'Running ...',
					    url: SemanticConnector.restviewer.ajaxUrlPrefix + cmd,
					    success: function(form, action) {
								var resultPanel = Ext.getCmp('resultPanel');
								resultPanel.setRawValue(action.result.msg);

								if(cmd != lastCmd && cmd != "") {
									store.add(new Command({command: cmd}));
									lastCmd = cmd;
								}
					    },
					    failure: function(form, action) {
					        switch (action.failureType) {
					            case Ext.form.Action.CLIENT_INVALID:
					                Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
					                break;
					            case Ext.form.Action.CONNECT_FAILURE:
					                Ext.Msg.alert('Failure', 'Ajax communication failed');
					                break;
					            case Ext.form.Action.SERVER_INVALID:
					               Ext.Msg.alert('Failure', action.result.msg);
					            default:
					               Ext.Msg.alert('Failure', 'Error happened when saving data');
					       }
					    }
			    	});
*/			    	
					}
				},
				'-',
				'<b>Please input RESTful command and Run it to view the result.</b>',
				'->',
				{
					text: 'Copy command to clipboard',
					handler: function(){
						var commandPanel = Ext.getCmp('commandPanel');
						var cmd = commandPanel.getRawValue();
						if(cmd != "") {
							Ext.ux.copyText(cmd);
						}
					}
				}
			]
		})
	});

	var viewer = new Ext.Panel({
		renderTo: 'restful-form',
		frame: true,
		title: 'Rest Viewer',
		width: 540,
		height: 700,
		layout: 'border',
		items: [
			restful,
			{
				id: 'resultPanel',
				xtype: 'textarea',
				region: 'center',
//				disabled: true,
				style: {
					background: '#ffffff',
//					color: '#000000',
					padding: '7px'
				}
			}
		]
	});

/*
	grid.getSelectionModel().on('rowselect', function(sm, rowIdx, r) {
		var commandPanel = Ext.getCmp('commandPanel');
		commandPanel.setRawValue(r.data.command);
	});
*/
/*
	// render functions
	function renderCommand(value, p, record){
		return record.data.command;
	}
*/
});