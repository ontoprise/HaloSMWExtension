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

var SM_Source = null, SM_Mapping = null;

Ext.ux.getSchemaTree = function(src) {
	var root = new Array();
	var form = null, template = null, field = null;
	if(src.forms) {
		for(var i=0;i<src.forms.length;++i) {
			form = {
				id:src.forms[i].id,
				text:src.forms[i].id,
				iconCls:"icon-form",
				cls:"form",
				children:[]
			};
			for(var j=0;j<src.forms[i].templates.length;++j) {
				template = {
					id:src.forms[i].id + '.' + src.forms[i].templates[j].id,
					text:src.forms[i].templates[j].id,
					iconCls:"icon-template",
					cls:"template",
					children:[]
				};
				for(var k=0;k<src.forms[i].templates[j].fields.length;++k) {
					field = {
						id:src.forms[i].id + '.' + src.forms[i].templates[j].id + '.' + src.forms[i].templates[j].fields[k].id,
						text:src.forms[i].templates[j].fields[k].id,
						iconCls:"icon-field",
						cls:"field",
						leaf:true
					};
					template.children.push(field);
				}
				form.children.push(template);
			}
			root.push(form);
		}
	} else if(src.templates) {
			for(var j=0;j<src.templates.length;++j) {
				template = {
					id:src.form + '.' + src.templates[j].id,
					text:src.templates[j].id,
					iconCls:"icon-template",
					cls:"template",
					children:[]
				};
				for(var k=0;k<src.templates[j].fields.length;++k) {
					field = {
						id:src.form + '.' + src.templates[j].id + '.' + src.templates[j].fields[k].id,
						text:src.templates[j].fields[k].id,
						iconCls:"icon-field",
						cls:"field",
						leaf:true
					};
					template.children.push(field);
				}
				root.push(template);
			}
	}
	return root;
}
Ext.ux.getMappedTree = function(mappedData, fid) {
	var data = {forms:[]};
	for(var x=0;x<mappedData.length;++x) {
		if(mappedData[x].src == fid) {
			var keys = mappedData[x].map.split('.');
			var form = null, template = null, field = null;
			for(var i=0;i<data.forms.length;++i){
				if(data.forms[i].id == keys[0]) {
					form = data.forms[i];
					break;
				}
			}
			if(form == null) {
				form = {id:keys[0], templates:[]};
				data.forms.push(form);
			}
			for(var i=0;i<form.templates.length;++i){
				if(form.templates[i].id == keys[1]) {
					template = form.templates[i];
					break;
				}
			}
			if(template == null) {
				template = {id:keys[1], fields:[]};
				form.templates.push(template);
			}
			for(var i=0;i<template.fields.length;++i){
				if(template.fields[i].id == keys[2]) {
					field = template.fields[i];
					break;
				}
			}
			if(field == null) {
				field = {id:keys[2]};
				template.fields.push(field);
			}
		}
	}
	return Ext.ux.getSchemaTree(data);
}

var sm_spot = null;
Ext.onReady(function(){
	Ext.QuickTips.init();
	Ext.get('loading').hide();
	sm_spot = new Ext.ux.Spotlight({
		easing: 'easeOut',
		duration: .3
	});
	
	var show = Ext.get('show_mapping');
	show.on('click', function(e){
		sm_spot.show(Ext.get('shade'));
		//if(sm_win != null) {
		//	sm_win.show(this);
		//	return;
		//}
		Ext.get('loading').show();
	
		// load data first
		var conn = new Ext.data.Connection();
		conn.request({
			url: SemanticConnector.form.ajaxUrl,
			method: 'POST',
			params: {
				'rsargs[]':['getMappingData', SemanticConnector.form.name],
				'action':'ajax',
				'rs':'smwf_sc_Access'
			},
			success: function(responseObject) {
				var o = Ext.util.JSON.decode(responseObject.responseText);
				SM_Source = o.source;
				SM_Mapping = o.mapping;
				Ext.ux.showform();
			},
			failure: function() {
				Ext.Msg.alert('Status', 'Unable to get mapped data.');
				Ext.get('loading').hide();
				sm_spot.hide();
			}
		});
		
		e.preventDefault();
	});
});

var sm_src = null, sm_mapping = null, sm_mapped = null, sm_win = null;
var sm_selected_field = null;

Ext.ux.refreshMappedTree = function() {
	sm_mapped.root.cascade(function(n){
		if(n.attributes.cls!='field'){
			n.draggable = false;
		}
	});
}
Ext.ux.refreshMappingTree = function() {
	var forms = new Array();
	sm_mapping.root.cascade(function(n){
		if(n.id == SM_Source.form){
			forms.push(n);
		}
		sm_mapped.root.cascade(function(n2){
			if(n2.id == n.id && n2.attributes.cls=='field'){
				forms.push(n);
			}
		});
		if(n.attributes.cls!='field'){
			n.draggable = false;
		}
	});
	for(var i=forms.length-1;i>=0;--i) {
		forms[i].remove();
	}
	// remove empty templates
	var etemplates = new Array();
	sm_mapping.root.cascade(function(n){
		if(n.attributes.cls!='field' && n.firstChild == null){
			etemplates.push(n);
		}
	});
	for(var i=etemplates.length-1;i>=0;--i)
		etemplates[i].remove();

	// remove empty forms
	var eforms = new Array();
	sm_mapping.root.cascade(function(n){
		if(n.attributes.cls!='field' && n.firstChild == null){
			eforms.push(n);
		}
	});
	for(var i=eforms.length-1;i>=0;--i)
		eforms[i].remove();
}
Ext.ux.onFieldDrop = function(dropEvent){
	if(dropEvent.dropNode.parentNode.id == dropEvent.target.id) {
		return true;
	}
	var form = null, template = null;
	dropEvent.tree.root.cascade(function(n){
		if(n.id == dropEvent.dropNode.parentNode.parentNode.id){
			form = n;
		}
	});
	dropEvent.tree.root.cascade(function(n){
		if(n.id == dropEvent.dropNode.parentNode.id){
			template = n;
		}
	});
	var attr;
	if(form == null){
		attr = Ext.ux.clone(dropEvent.dropNode.parentNode.parentNode.attributes);
		attr.children = null;
		form = dropEvent.tree.root.appendChild(new Ext.tree.TreeNode(attr));
		form.draggable = false;
		form.expand();
	}
	if(template == null){
		attr = Ext.ux.clone(dropEvent.dropNode.parentNode.attributes);
		attr.children = null;
		template = form.appendChild(new Ext.tree.TreeNode(attr));
		template.draggable = false;
		template.expand();
	}
	template.appendChild(
		new Ext.tree.TreeNode(dropEvent.dropNode.attributes)
	);
	
	var node = dropEvent.dropNode;
	if(node.parentNode.childNodes.length == 1) {
		node = node.parentNode;
		if(node.parentNode.childNodes.length == 1) {
			node = node.parentNode;
		}
		// tricky, this will drop an 'empty node'
		dropEvent.dropNode.remove();
	}
	node.remove();
	
	return true;
}
Ext.ux.showform = function() {
	// Panel for the west
	var form_source = new Ext.Panel({
		title: 'Form Fields',
		region: 'west',
		split: false,
		width: 250,
		collapsible: false,
		margins:'3 0 3 3',
		cmargins:'3 3 3 3'
	});
	// Panel for the center
	var form_mapped = new Ext.FormPanel({
		title: 'Mapped Fields',
		region: 'center',
		margins:'3 3 3 0', 
		cmargins:'3 3 3 3',
		defaults:{autoScroll:true},
		layout:'column'
	});
	// Panel for the east
	var form_mapping = new Ext.Panel({
		title: 'Available Mapping Fields',
		region: 'east',
		split: false,
		width: 250,
		collapsible: true,
		margins:'3 0 3 3',
		cmargins:'3 3 3 3'
	});
	sm_win = new Ext.Window({
		title: 'Schema Mapping Editor',
		closable:true,
		width:770,
		height:597,
		resizable:false,
		plain:true,
		layout: 'border',
		items: [form_source, form_mapped, form_mapping, 
			new Ext.BoxComponent({
				region: 'south',
				height: 30, // give north and south regions a height
				autoEl: {
					tag: 'div',
					style: 'margin-left:10',
					html:'<p>Please drag <b>FIELD</b> from "Available Mapping Fields" and drop to "Mapped Fields" to add schema mapping, or drag <b>FIELD</b> from "Mapped Fields" and drop to "Available Mapping Fields" to remove schema mapping.</p>'
				}
			})
		],
		listeners: {
			close : function( p ) {
				sm_win = null;
				sm_spot.hide();
			}
		}
	});
        
	var submit = sm_win.addButton({
		text: 'Save',
		handler: function(){
			var mapStr = '';
			for(var i=0;i<SM_Source.mappedData.length;++i) {
				mapStr += ',' + SM_Source.mappedData[i].src + '|' + SM_Source.mappedData[i].map;
			}
			form_mapped.getForm().submit({
				waitMsg:'Saving Data...',
		    url: SemanticConnector.form.ajaxUrl,
		    params: {
		    	'rsargs[]':['saveMappingData', SemanticConnector.form.name + '|' + mapStr],
					'action':'ajax',
					'rs':'smwf_sc_Access'
		    },
		    success: function(form, action) {
		       Ext.Msg.alert('Success', action.result.msg);
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
		}
	});
	// simple button add
	sm_win.addButton('Close', function(){
		sm_win.close();
		sm_spot.hide();
	});

	sm_src = Ext.ux.createTree('source', 'root', Ext.ux.getSchemaTree(SM_Source), form_source);
	sm_src.on({
		click: function( node, e ) {
			if(node.attributes.cls!='field') {
				sm_mapped.root.attributes.children = [];
				sm_mapping.root.attributes.children = [];
			} else {
				sm_mapped.root.attributes.children = Ext.ux.getMappedTree(SM_Source.mappedData, node.id);
				sm_mapping.root.attributes.children = Ext.ux.getSchemaTree(SM_Mapping);
			}
			sm_mapped.root.reload(Ext.ux.refreshMappedTree);
			sm_mapped.expandAll();
			sm_mapping.root.reload(Ext.ux.refreshMappingTree);
//			sm_mapping.expandAll();
			
			sm_selected_field = node;
		}
	});

	sm_mapped = Ext.ux.createTree('mapped', 'root', [], form_mapped);
	sm_mapped.enableDD = true;
	sm_mapped.dropConfig = {allowContainerDrop:true, appendOnly:true};
	sm_mapped.on({
		nodedragover : function( dragOverEvent ) {
			if(dragOverEvent.dropNode.getOwnerTree() != sm_mapping) {
				return false;
			}
			if(dragOverEvent.dropNode.parentNode.id == dragOverEvent.target.id || dragOverEvent.target.id == sm_mapped.root.id) {
				return true;
			}
			return false;
		},
		afterrender : function( tree ) {
			Ext.ux.refreshMappedTree();
		},
		beforenodedrop : function( dropEvent ) { 
			if(sm_selected_field.attributes.cls != 'field') {
				return false;
			}
			Ext.ux.onFieldDrop(dropEvent);
			SM_Source.mappedData.push({src:sm_selected_field.id, map:dropEvent.dropNode.id});
		}
	});
	
	sm_mapping = Ext.ux.createTree('mapping', 'root', [], form_mapping);
	sm_mapping.enableDD = true;
	sm_mapping.dropConfig = {allowContainerDrop:true, appendOnly:true};
	sm_mapping.on({
		nodedragover : function( dragOverEvent ) {
			if(dragOverEvent.dropNode.getOwnerTree() != sm_mapped) {
				return false;
			}
			if(dragOverEvent.dropNode.parentNode.id == dragOverEvent.target.id || dragOverEvent.target.id == sm_mapping.root.id) {
				return true;
			}
			return false;
		},
		afterrender : function( tree ) {
			sm_selected_field = sm_src.root;
		},
		beforenodedrop : function( dropEvent ) { 
			Ext.ux.onFieldDrop(dropEvent);
			for(var i=0;i<SM_Source.mappedData.length;++i) {
				if(SM_Source.mappedData[i].src==sm_selected_field.id && SM_Source.mappedData[i].map==dropEvent.dropNode.id) {
					SM_Source.mappedData.splice(i, 1);
					break;
				}
			}
		}
	});

	Ext.get('loading').hide();
	sm_win.show(this);
}

Ext.ux.createTree = function(id, text, data, container) {
	var tree = new Ext.tree.TreePanel({
		id:id,
		height: 435,
		border: false,
		bodyStyle:'padding:5px',
		rootVisible:false,
		autoScroll:true,
		animate: false,
		lines: false,
		loader: new Ext.tree.TreeLoader({
			preloadChildren: true,
			clearOnLoad: false
		}),
		root: new Ext.tree.AsyncTreeNode({
			text:text,
			id:id,
			iconCls: 'icon-root',
			draggable:false,
			children:data
		}),
		listeners: {
			afterrender: function(component) {
				tree.expandAll();
			}
		}
	});

	var hd = new Ext.Panel({
		border: false,
		height: 465,
		items: [new Ext.Toolbar({
			items:[ ' ',
				new Ext.form.TextField({
					width: 180,
					emptyText:'Find a field',
					listeners:{
						render: function(f){
							f.el.on('keydown', filterTree, f, {buffer: 350});
						}
					}
				}), ' ', ' ',
				{
					iconCls: 'icon-expand-all',
					tooltip: 'Expand All',
					handler: function(){ tree.root.expand(true); }
				}, '-',
				{
					iconCls: 'icon-collapse-all',
					tooltip: 'Collapse All',
					handler: function(){ tree.root.collapse(true); }
				}]
			})
		]
	});
	var filter = new Ext.tree.TreeFilter(tree, {
		clearBlank: true,
		autoClear: true
	});
	var hiddenPkgs = [];
	function filterTree(e){
		var text = e.target.value;
		Ext.each(hiddenPkgs, function(n){
			n.ui.show();
		});
		if(!text){
			filter.clear();
			return;
		}
		tree.expandAll();

		var filterStr = "";
		var re = new RegExp('^' + Ext.escapeRe(text), 'i');
		filter.filterBy(function(n){
			if(n.attributes.cls=='field' && re.test(n.text)) {
				filterStr += "," + n.id;
				return true;
			}
			return n.attributes.cls!='field';
		});

		// hide empty packages that weren't filtered
		hiddenPkgs = [];
		tree.root.cascade(function(n){
			if(n==tree.root) return;
			if(n.attributes.cls!='field' && filterStr.indexOf(","+n.id)<0){
				n.ui.hide();
				hiddenPkgs.push(n);
			}
		});
	}

	container.add(hd);
	hd.add(tree);

	return tree;
}