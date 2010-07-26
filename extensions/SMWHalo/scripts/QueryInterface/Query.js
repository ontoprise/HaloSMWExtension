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
* Creates HTML string for the query tree representation. The tree representation is
* laid out like a file browser with folders and leafs.
*/
    updateTree:function(){
        if (this.id == 0 && this.isEmpty()) {
            return gLanguage.getMessage('QI_EMPTY_QUERY');
        }
        var tree = '<table class="qiTree">';
        if (this.id == 0)
            tree += '<tr><td colspan="2">'
                + '<a href="javascript:void(0);" onclick="qihelper.setActiveQuery(0)">'
                + gLanguage.getMessage('QI_MAIN_QUERY_NAME') + '</a></td></tr>';
        for(var i=0; i<this.categories.length; i++){
			tree += '<tr><td width="16"><img src="'+qihelper.imgpath+'../../concept.gif"/></td><td> ';
			for(var j=0, js = this.categories[i].length; j < js; j++) {
					tree += '<a href="javascript:void(0)" onclick="qihelper.selectNode(this, \'category-'+this.id+'-'+i+'-'+j+'\')">'
                        + this.categories[i][j] + '</a>';
                    if (j < (js - 1) )
                        tree += ' <span style="font-weight:bold">' + gLanguage.getMessage('QI_OR') + '</span> ';
			}
			tree += '</td></tr>';
		}
        for(var i=0; i<this.instances.length; i++){
			tree += '<tr><td width="16"><img src="'+qihelper.imgpath+'../../instance.gif"/></td><td> ';
			for(var j=0, js = this.instances[i].length; j < js; j++) {
					tree += '<a href="javascript:void(0)" onclick="qihelper.selectNode(this, \'instance-'+this.id+'-'+i+'-'+j+'\')">'
                        + this.instances[i][j] + '</a>';
                    if (j < (js - 1) )
                        tree += ' <span style="font-weight:bold">' + gLanguage.getMessage('QI_OR') + '</span> ';
			}
			tree += '</td></tr>';
		}
		for(var i=0; i<this.properties.length; i++){
			tree += '<tr><td width="16"><img src="'+qihelper.imgpath+'../../property.gif"/></td><td> '
                + '<a href="javascript:void(0);" onclick="qihelper.selectNode(this, \'property-'+this.id+'-'+i+'\')">'
                + this.properties[i].getName() + '</a></td></tr>'
                + '<tr><td><img src="'+qihelper.imgpath+'lastlink.gif"/></td><td>';
			propvalues = this.properties[i].getValues();
			for(var j=0, js= propvalues.length; j < js; j++){
                
				if(propvalues[j][0] == "subquery")
					tree += '<img src="'+qihelper.imgpath+'subquery.png"/> '
                        + '<a href="javascript:void(0);" onclick="qihelper.setActiveQuery(' + propvalues[j][2] + ')">'
                        + gLanguage.getMessage('QI_SUBQUERY') + ' ' + propvalues[j][2]
                        + '</a></td></tr><tr><td> </td><td>___SUBQUERY_'+propvalues[j][2]+'___';
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
					var pvalue = propvalues[j][2];
					
                    res = ((this.properties[i].getArity() > 2 ) ? propvalues[j][0] : "")
                        + " " + res + " ";
                    if (propvalues[j][2] == '*')
                        res += '<i>'+gLanguage.getMessage('QI_ALL_VALUES')+'</i>'
                    else res += propvalues[j][2];
                    if (propvalues[j][3]) res += ' ' + propvalues[j][3];
					tree += res;
                    if (j < (js - 1) )
                        tree += ' <span style="font-weight:bold">' + gLanguage.getMessage('QI_OR') + '</span> ';
				}
			}
		}
        tree += '</table>';
        return tree;
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
			if(this.properties[i].isShown() && this.properties[i].getArity() == 2){ // "Show in results" checked?
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
		for(var i=0; i<this.properties.length; i++){
            // add this only if there is no special value asked for
			if(this.properties[i].mustBeSet() && 
                           this.properties[i].getValues().length == 1 &&
                           this.properties[i].getValues()[0][2] == '*') {
				asktext += "[[" + this.properties[i].getName().unescapeHTML() + "::+]]";
			}
			if(this.properties[i].getArity() > 2){ // always special treatment for arity > 2
                var valueSet = false;
                var tmpAsk = "[[" + this.properties[i].getName().unescapeHTML() + "::";
                var vals = this.properties[i].getValues();
                for(var j=0; j<vals.length; j++){
                    if(j!=0)
        				tmpAsk += ";"; // connect values with semicolon
            		if (vals[j][1] != "=") //add operator <, >, ! if existing
                        // normal ask makes no difference between > and >= the TSC does
                        tmpAsk += ($('usetriplestore') && $('usetriplestore').checked &&
                                   (vals[j][1].charAt(0) == '>' || vals[j][1].charAt(0) == '<') )
                            ? vals[j][1]
                            : vals[j][1].substring(0,1);
                    // value set?
                    if (vals[j][2] != '*') {
                        tmpAsk += vals[j][2].unescapeHTML();
                        valueSet = true;
                    }
                }
                tmpAsk += "]]";
                if (valueSet) asktext += tmpAsk;
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
                        if (vals[j][3] && vals[j][3].length > 0)
                            asktext += ' '+vals[j][3].unescapeHTML();
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
			if(this.properties[i].isShown()) { // "Show in results" checked?
                var prop = this.properties[i].getName();
                var pname = prop;
                if (this.properties[i].getShowUnit())
                    prop += ' #' + this.properties[i].getShowUnit();
                if (! this.properties[i].getColName())
                    prop += ' = ';
                else if (pname != this.properties[i].getColName())
                    prop += ' = ' + this.properties[i].getColName();
                        
                // do not show the same display statement twice
                if (displayStatements.inArray(prop)) continue;
                displayStatements.push(prop);
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