/**
* @file
* @ingroup SMWHaloSpecials
* @ingroup SMWHaloQueryInterface
* 
* 
* Query.js
* Query object representing a single query. Subqueries are
* seperate objects which are referenced by an ID.
* @author Markus Nitsche [fitsch@gmail.com]
*/

var Query = Class.create();
Query.prototype = {

/**
* Initialize a new query
* @param id ID
* @param parent parentID
* @param name QueryName
*/
	initialize:function(id, parent, name){
		this.id = id; //id of this query
		this.parent = parent; //parent of this query, null if root
		this.name = name; //name of the property referencing on this query
		this.hasSubquery = false; //has it subqueries?
		this.categories = Array(); //All categories
		this.instances = Array(); //All Instances
		this.properties = Array(); //All properties
		this.subqueryIds = Array(); //IDs of subqueries
	},

/**
* Add a category or a gourp of or-ed
* categories to the query
* @param cat CategoryGroup
* @param oldid null if new, otherwise ID of an existing
* category group which will be overwritten
*/
	addCategoryGroup:function(cat, oldid){
		if(oldid==null)
			this.categories.push(cat);
		else
			this.categories[oldid] = cat;
	},

/**
* Add a instance or a gourp of or-ed
* instances to the query
* @param ins InstanceGroup
* @param oldid null if new, otherwise ID of an existing
* instance group which will be overwritten
*/
	addInstanceGroup:function(ins, oldid){
		if(oldid==null)
			this.instances.push(ins);
		else
			this.instances[oldid] = ins;
	},

/**
* Add a property or a gourp of or-ed
* properties to the query
* @param pgroup PropertyGroup
* @param subIds IDs of subqueries that are referenced within
* this property group
* @param oldid null if new, otherwise ID of an existing
* property group which will be overwritten
*/
	addPropertyGroup:function(pgroup, subIds, oldid){
		if(oldid == null)
			this.properties.push(pgroup);
		else
			this.properties[oldid] = pgroup;
		if (subIds.length > 0){
			this.hasSubquery = true;
			for(var i=0; i<subIds.length; i++){
				this.subqueryIds.push(subIds[i]);
			}
		}
	},

	hasSubqueries:function(){
		return this.hasSubquery;
	},
/**
* Creates XML string for the query tree representation. The tree representation is
* laid out like a file browser with folders and leafs.
*/
	updateTreeXML:function(){
		var treexml = '<?xml version="1.0" encoding="UTF-8"?>';
		treexml += '<treeview title=" Query"><folder title=" ' + this.name + '" code="root" expanded="true" img="question.gif">';
		for(var i=0; i<this.categories.length; i++){
			treexml += '<folder title="' + gLanguage.getMessage('QI_CATEGORIES') + '" code="categories' + i +'" expanded="true" img="category.gif">';
			for(var j=0; j<this.categories[i].length; j++){
					treexml += '<leaf title=" ' + this.categories[i][j] + '" code="category' + i + '-' + j + '" img="blue_ball.gif"/>';
			}
			treexml += '</folder>';
		}
		for(var i=0; i<this.instances.length; i++){
			treexml += '<folder title="' + gLanguage.getMessage('QI_INSTANCES') + '" code="instances' + i +'" expanded="true" img="instance.gif">';
			for(var j=0; j<this.instances[i].length; j++){
				treexml += '<leaf title=" ' + this.instances[i][j] + '" code="instance' + i + '-' + j + '" img="red_ball.gif"/>';
			}
			treexml += '</folder>';
		}
		for(var i=0; i<this.properties.length; i++){
			treexml += '<folder title=" ' + this.properties[i].getName() + '" code="properties' + i +'" expanded="true" img="property.gif">';
			propvalues = this.properties[i].getValues();
			for(var j=0; j<propvalues.length; j++){
				if(propvalues[j][0] == "subquery")
					treexml += '<leaf title=" ' + gLanguage.getMessage('QI_SUBQUERY') + ' ' + propvalues[j][2] + '" code="subquery' + propvalues[j][2] + '" img="subquery.png" class="treesub"/>';
				else {
					var res = ""; //restriction for numeric values. Encode for HTML display
					switch(propvalues[j][1]){
						case "<=":
							res = "&lt;=";
							break;
						case ">=":
							res = "&gt;=";
							break;
						default:
							res = propvalues[j][1];
							break;
					}
					treexml += '<leaf title=" ' + propvalues[j][0] + " " + res + " " + propvalues[j][2] + '" code="property' + i + '-' + j + '" img="yellow_ball.gif"/>';
				}
			}
			treexml += '</folder>';
		}
		treexml += '</folder></treeview>';
		updateQueryTree(treexml);
	},

/**
* Create the syntax for the ask query of this object. Subqueries are not resolved
* but marked with "Subquery:[ID]:". Recursive resolving of all subqueries is done
* within QIHelper.js
* @return asktext string containing the ask syntax
*/
	getAskText:function(){
		var asktext = "";
		for(var i=0; i<this.categories.length; i++){
			asktext += "[[" + gLanguage.getMessage('CATEGORY_NS');
			for(var j=0; j<this.categories[i].length; j++){
				asktext += this.categories[i][j].unescapeHTML();
				if(j<this.categories[i].length-1){ //add disjunction operator
					asktext += "||";
				}
			}
			asktext += "]]";
		}
		for(var i=0; i<this.instances.length; i++){
			asktext += "[[";
			for(var j=0; j<this.instances[i].length; j++){
				asktext += this.instances[i][j].unescapeHTML();
				if(j<this.instances[i].length-1){ //add disjunction operator
					asktext += "||";
				}
			}
			asktext += "]]";
		}
		for(var i=0; i<this.properties.length; i++){
			if(this.properties[i].isShown()){ // "Show in results" checked?
				asktext += "[[" + this.properties[i].getName() + "::*]]"; // Display statement
			}
                        // add this only if there is no special value asked for
			if(this.properties[i].mustBeSet() &&
                           this.properties[i].getValues().length == 1 &&
                           this.properties[i].getValues()[0][2] == '*') {
				asktext += "[[" + this.properties[i].getName() + "::+]]";
			}
			
			if(this.properties[i].getArity() > 2){ // always special treatment for arity > 2
				asktext += "[[" + this.properties[i].getName().unescapeHTML() + "::";
				var vals = this.properties[i].getValues();
				for(var j=0; j<vals.length; j++){
					if(j!=0)
						asktext += ";"; // connect values with semicolon
					if(vals[j][1]!="=") //add operator <, >, ! if existing
                        // normal ask makes no difference between > and >= the TSC does
                        asktext += ($('usetriplestore') && $('usetriplestore').checked &&
                                    (vals[j][1].charAt(0) == '>' || vals[j][1].charAt(0) == '<') )
                            ? vals[j][1]
                            : vals[j][1].substring(0,1);
                    asktext += (vals[j][2] == '*') ? '' : vals[j][2].unescapeHTML();
				}
			} else { //binary property
				var vals = this.properties[i].getValues();
				if (vals.length == 1 && vals[0][2] == "*"){}
				else{
					asktext += "[[" + this.properties[i].getName().unescapeHTML() + "::";
					for(var j=0; j<vals.length; j++){
						if(j!=0) //add disjunction operator
							asktext += "||";
						if(vals[j][1]!= "=")
                            // normal ask makes no difference between > and >= the TSC does
                            asktext += ($('usetriplestore') && $('usetriplestore').checked &&
                                        (vals[j][1].charAt(0) == '>' || vals[j][1].charAt(0) == '<') )
                                ? vals[j][1]
                                : vals[j][1].substring(0,1);
						if(vals[j][0] == "subquery") // Mark ID of subqueries so they can easily be parsed
							asktext += "Subquery:" + vals[j][2] + ":";
						else
							asktext += vals[j][2].unescapeHTML();
					}
				asktext += "]]";
				}
			}
			
		}
		return asktext;
	},
	
/**
* Create the syntax for the ask query of this object in the ask parser syntax
* which was introduced with SMW 1.0. Subqueries are not resolved
* but marked with "Subquery:[ID]:". Recursive resolving of all subqueries is done
* within QIHelper.js
* @return asktext string containing the ask parser syntax
*/
	getParserAsk:function(){
		var asktext = "";
		for(var i=0; i<this.categories.length; i++){
			asktext += "[[" + gLanguage.getMessage('CATEGORY_NS');
			for(var j=0; j<this.categories[i].length; j++){
				asktext += this.categories[i][j].unescapeHTML();
				if(j<this.categories[i].length-1){ //add disjunction operator
					asktext += "||";
				}
			}
			asktext += "]]";
		}
		for(var i=0; i<this.instances.length; i++){
			asktext += "[[";
			for(var j=0; j<this.instances[i].length; j++){
				asktext += this.instances[i][j].unescapeHTML();
				if(j<this.instances[i].length-1){ //add disjunction operator
					asktext += "||";
				}
			}
			asktext += "]]";
		}
		var displayStatements = new Array();
		for(var i=0; i<this.properties.length; i++){
			if(this.properties[i].isShown()){ // "Show in results" checked?
				displayStatements.push(this.properties[i].getName().unescapeHTML());
			}
                        // add this only if there is no special value asked for
			if(this.properties[i].mustBeSet() && 
                           this.properties[i].getValues().length == 1 &&
                           this.properties[i].getValues()[0][2] == '*') {
				asktext += "[[" + this.properties[i].getName().unescapeHTML() + "::+]]";
			}
			if(this.properties[i].getArity() > 2){ // always special treatment for arity > 2
				asktext += "[[" + this.properties[i].getName().unescapeHTML() + "::";
				var vals = this.properties[i].getValues();
				for(var j=0; j<vals.length; j++){
					if(j!=0)
						asktext += ";"; // connect values with semicolon
					if (vals[j][1] != "=") //add operator <, >, ! if existing
                        // normal ask makes no difference between > and >= the TSC does
                        asktext += ($('usetriplestore') && $('usetriplestore').checked &&
                                    (vals[j][1].charAt(0) == '>' || vals[j][1].charAt(0) == '<') )
                            ? vals[j][1]
                            : vals[j][1].substring(0,1);
					asktext += (vals[j][2] == '*') ? '' : vals[j][2].unescapeHTML();
				}
				asktext += "]]";
			} else { //binary property
				var vals = this.properties[i].getValues();
				if (vals.length == 1 && vals[0][2] == "*"){}
				else{
					asktext += "[[" + this.properties[i].getName().unescapeHTML() + "::";
					for(var j=0; j<vals.length; j++){
						if(j!=0) //add disjunction operator
							asktext += "||";
						if(vals[j][1] != "=")
                          // normal ask makes no difference between > and >= the TSC does
                            asktext += ($('usetriplestore') && $('usetriplestore').checked &&
                                        (vals[j][1].charAt(0) == '>' || vals[j][1].charAt(0) == '<') )
                                ? vals[j][1]
                                : vals[j][1].substring(0,1);
						if(vals[j][0] == "subquery") // Mark ID of subqueries so they can easily be parsed
							asktext += "Subquery:" + vals[j][2] + ":";
						else
							asktext += vals[j][2].unescapeHTML();
					}
					asktext += "]]";
				}
			}
		}
		return asktext;
	},
	
	getDisplayStatements:function(){
		var displayStatements = new Array();
		for(var i=0; i<this.properties.length; i++){
			if(this.properties[i].isShown()){ // "Show in results" checked?
				displayStatements.push(this.properties[i].getName());
			}
		}
		return displayStatements;
	},

	isEmpty:function(){
		if(this.categories.length == 0 && this.instances.length == 0 && this.properties.length == 0){
			return true;
		} else {
			return false;
		}
	},

	getName:function(){
		return this.name;
	},

	getSubqueryIds:function(){
		return this.subqueryIds;
	},

	getParent:function(){
		return this.parent;
	},

	getCategoryGroup:function(id){
		return this.categories[id];
	},

	getInstanceGroup:function(id){
		return this.instances[id];
	},

	getPropertyGroup:function(id){
		return this.properties[id];
	},

	getAllProperties:function(){
		return this.properties;
	},

	removeCategoryGroup:function(id){
		if(id < this.categories.length-1)
			this.categories[id]= this.categories.pop();
		else
			this.categories.pop();
	},

	removeInstanceGroup:function(id){
		if(id < this.instances.length-1)
			this.instances[id]= this.instances.pop();
		else
			this.instances.pop();
	},

	removePropertyGroup:function(id){
		if(id < this.properties.length-1){
			this.properties[id]= this.properties.pop();
			}
		else{
			this.properties.pop();
		}
	}


};