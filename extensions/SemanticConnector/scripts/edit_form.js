SemanticConnector = {
	edit: {
		currentForm: '',
		imagePath: '',
		possible: [],
		applicable: []
	},
	selector: {}
};

Ext.onReady(function(){
	Ext.QuickTips.init();
	var related = new Ext.data.ArrayStore({
		data: SemanticConnector.edit.possible,
		fields: ['value', 'text'],
		sortInfo: {
			field: 'value',
			direction: 'ASC'
		}
	});
	var mapped = SemanticConnector.edit.applicable;

	SemanticConnector.selector = new Ext.ux.form.ItemSelector({
		name: 'itemselector',
		delimiter:'|',
		fieldLabel: '<b style="color:#ff0000">BE CAREFUL!</b><br/><br/>Be sure to add or remove applicable forms to this page',
		imagePath: SemanticConnector.edit.imagePath,
		multiselects: [{
			legend: 'All Possible Forms',
			width: 250,
			height: 200,
			store: related,
			displayField: 'text',
			valueField: 'value'
		},{
			legend: 'Applicable Forms',
			width: 250,
			height: 200,
			store: mapped
		}]
	});
	var mapped_forms = new Ext.FormPanel({
		frame:false,
		bodyStyle:'padding:5px 5px 0',
		items: [
			new Ext.BoxComponent({
			autoEl: {
				tag: 'div',
				style: 'margin-left:5; font-family:tahoma,arial,helvetica,sans-serif; font-size:14px',
				html:'<p>Current editing form is <b>' + SemanticConnector.edit.currentForm + '</b>.</p>'
			}
		}), {
			xtype:'fieldset',
			checkboxToggle:true,
			title: 'Related Forms (Check to view)',
			autoHeight:true,
			defaultType: 'textfield',
			collapsed: true,
			items :[
				SemanticConnector.selector,
				new Ext.BoxComponent({
					autoEl: {
						tag: 'div',
						style: 'margin-left:5; font-family:tahoma,arial,helvetica,sans-serif; font-size:11px',
						html:'<p>Double click item in <b>Applicable Forms</b> to edit page with select form.</p>'
					}
				})
			]
		}]
	});

	mapped_forms.render(Ext.get('valid_forms'));
});
SemanticConnector.getHttpRequest = function() {
	var activeX = ['MSXML2.XMLHTTP.3.0',
		'MSXML2.XMLHTTP',
		'Microsoft.XMLHTTP'];
	var http;
	try {
		http = new XMLHttpRequest();
	} catch(e) {
		for (var i = 0; i < activeX.length; ++i) {
			try {
				http = new ActiveXObject(activeX[i]);
				break;
			} catch(e) {}
		}
	} finally {
		return http;
	}
};
SemanticConnector.saveEnabledForm = function() {
	var mfs = '';
	if (!SemanticConnector.selector.rendered) {
		var store = SemanticConnector.edit.applicable;
		var values = [];
		for (var i=0; i<store.length; i++) {
			values.push(store[i][0]);
		}
	    mfs = values.join(SemanticConnector.selector.delimiter);
	} else {
		mfs = SemanticConnector.selector.getValue();
	}
	var params = {
		'rsargs[]':['saveEnabledForms',
			SemanticConnector.form.page_name + ',' +
			SemanticConnector.form.form_name + ',' +
			mfs],
		'action':'ajax',
		'rs':'smwf_sc_Access'
	};
	
	try {
		var conn = SemanticConnector.getHttpRequest();
		conn.open('POST', SemanticConnector.form.ajaxUrl, false);
		conn.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		conn.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		conn.send(Ext.urlEncode(params));
		return true;
	} catch(e) {
		Ext.Msg.alert('Failure', 'Error when saving applicable forms, please retry later.');
		return false;
	}
	
/*
	var conn = new Ext.data.Connection();
	conn.request({
		url: SemanticConnector.form.ajaxUrl,
		method: 'POST',
		params: {
			'rsargs[]':['saveEnabledForms', 
				SemanticConnector.form.page_name + ',' + 
				SemanticConnector.form.form_name + ',' +
				mfs],
			'action':'ajax',
			'rs':'smwf_sc_Access'
		},
		success: function(responseObject) {
			document.getElementsByName('createbox')[0].submit();
		},
		failure: function() {
			Ext.Msg.alert('Failure', 'Error when saving applicable forms, please retry later.');
		}
	});
	return false;
*/
};

SemanticConnector.switchForm = function(vw, index, node, e) {
	// switch to form
	Ext.Msg.show({
		title:'Make sure your changes have been saved!',
		msg: 'You are about to leave this form and switch to another form.<br/>Please save your changes first!<br/><b style="color:#ff0000">Have you saved your changes?</b>',
		buttons: Ext.Msg.YESNO,
		fn: function(buttonId, text, opt){
			if (buttonId == 'yes'){
				window.location.href = SemanticConnector.form.switchFormPrefix + vw.store.getAt(index).data.value + SemanticConnector.form.switchFormSuffix;
			}
		},
		icon: Ext.MessageBox.QUESTION
	});
}
