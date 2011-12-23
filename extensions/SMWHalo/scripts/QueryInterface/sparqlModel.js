/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

(function($){
  //Type enumerator
  TYPE = {
    VAR: 'VAR',
    IRI: 'IRI',
    LITERAL: 'LITERAL',
    ARRAY: [this.VAR, this.IRI, this.LITERAL]
  };
  
  //Model component. Takes care of sparql data structure manipulation
  SPARQL.Model = {
    data: {
      category_restriction: [],
      triple: [],
      filter: [],
      projection_var: [],
      namespace: [],
      order: []
    }
  };

  /**
   * SPARQL.Model.Term constructor. Creates new Term obejct.
   * @param type
   * @param value
   * @param datatype_iri
   * @param language
   * @exception when this method is called without 'new' keyword
   */
  SPARQL.Model.Term = function(value, type, datatype_iri, language){
    /**
     * Init SPARQL.Model.Term instance
     */
    this.init = function(value, type, datatype_iri, language){
      if(!this instanceof SPARQL.Model.Term){
        throw new Error("SPARQL.Model.Term constructor called as a function");
      }

      this.type = $.trim(type);
      this.value = $.trim(value) || '';
      this.datatype_iri = $.trim(datatype_iri);
      this.language = $.trim(language);

      //if ty is not defined then figure it out from the value
      if(!this.type){
        this.type = (this.value.indexOf('?') === 0 ? TYPE.VAR : TYPE.IRI);
      }

      //replace spaces by underscores in iri
      if(this.type === TYPE.IRI && this.fixIRI){
        this.value = this.fixIRI(value);
      }
      //remove ? from the beginning of a name
      else if(this.type === TYPE.VAR){
        this.value = this.value.replace(/^\?/, '');
      }
    };

    /**
   * Compare this Term object to another
   * @param term
   */
    this.isEqual = function(term){
      return SPARQL.objectsEqual(this, term);
    };

    //call init in the construstor
    this.init(value, type, datatype_iri, language);
  };


  SPARQL.Model.SubjectTerm = function(value, type, datatype_iri, language){
    this.fixIRI = function(value){
      return SPARQL.Model.assureFullyQualifiedIRI(value, 'instance');
    };
    SPARQL.Model.Term.call(this, value, type, datatype_iri, language);    
  };


  /**
   * SPARQL.Model.Triple constructor. Creates new Triple obejct.
   * @param subject Term representing subject of the triple
   * @param object Term representing object of the triple
   * @param predicate Term representing predicate of the triple
   * @param isOptional boolean indicating if this triple is optional
   * @exception when this method is called without 'new' keyword
   */
  SPARQL.Model.Triple = function(subject, predicate, object, isOptional){
    if(!this instanceof SPARQL.Model.Triple){
      throw new Error("SPARQL.Model.Triple constructor called as a function");
    }
    this.subject = subject;
    this.predicate = predicate;
    this.object = object;
    this.optional = isOptional;
  };

  SPARQL.Model.Triple.isEqual = function(anotherTriple){
    return SPARQL.objectsEqual(this, anotherTriple);
  };

  /**
   * SPARQL.Model.CategoryRestriction constructor. Creates new CategoryRestriction object.
   * @param subject Term representing the subject
   * @param categoryArray array of categories in fully qualified, short form or even plain names.
   * The method tries to fix the names to make them fully qualified iri
   */
  SPARQL.Model.CategoryRestriction = function(subject, categoryArray){
    if(!this instanceof SPARQL.Model.CategoryRestriction){
      throw new Error("SPARQL.Model.CategoryRestriction constructor called as a function");
    }
    this.subject = subject;
    //init from tree node text (category names separated by ' or ')
    if(typeof categoryArray === 'string'){      
      this.category_iri = categoryArray.split(' or ');
    }
    else{
      this.category_iri = categoryArray;
    }

    for(var i = 0; i < this.category_iri.length; i++){
      this.category_iri[i] = SPARQL.Model.assureFullyQualifiedIRI(this.category_iri[i], 'category');
    }


    /**
   * Compare this CategoryRestriction object to another.
   * @param anotherCategoryRestriction CategoryRestriction object to compare this one to
   * @return true if the objects are equal, false otherwise
   */
    this.isEqual = function(anotherCategoryRestriction){
      return SPARQL.objectsEqual(this, anotherCategoryRestriction);
    };

    /**
   * Get short representation of the iri.
   * Search for a matching namespace iri in the table, if found remove it from the given name.
   * If not found then return the string after last delimiter (/ : #)
   */
    this.getShortName = function(iri){
      var result = null;
      $.each(SPARQL.Model.data.namespace, function(index, namespace){
        if(iri.substr(0, namespace.namespace_iri.length) === namespace.namespace_iri){
          result = iri.replace(namespace.namespace_iri, '');
          return false;
        }
      });

      if(!result){
        iri = iri.split(/[\/:#]/);
        result = iri[iri.length - 1];
      }

      return result;
    };

    this.getShortNameArray = function(){
      var result = [];
      var that = this;
      $.each(this.category_iri, function(index, value){
        result.push(that.getShortName(value));
      });

      return result;
    };

    this.getString = function(){
      var result = '';
      var that = this;
      $.each(this.category_iri, function(index, value){
        result += that.getShortName(value);
        if(index < that.category_iri - 1){
          result += ' or ';
        }
      });

      return result;

    };

    /**
   * Delete category from categiry_iri array
   * @param categoryName string category name. If it's not a fully qualified iri then an attempt is made to transform it to one.
   */
    this.deleteCategory = function(categoryName){
      categoryName = SPARQL.Model.assureFullyQualifiedIRI(categoryName, 'category');
      for(var i = 0; i < this.category_iri.length; i++){
        if(this.category_iri[i] === categoryName){
          this.category_iri.splice(i, 1);
        }
      }
    };

    /**
   * Checks if the category_iri array is empty
   * @return true if categry_iri is empty, false otherwise
   */
    this.isEmpty = function(){
      return this.category_iri.length === 0;
    };
  };
  /**
   * Get namespace iri matching the given prefix
   * @param prefix string prefix
   * @return string matching namespace or empty string in case of failure
   */
  SPARQL.Model.getNamespace = function(prefix){
    var result = null;
    $.each(SPARQL.Model.data.namespace, function(index, namespace){
      if(namespace.prefix === prefix){
        result = namespace.namespace_iri;
        return false;
      }
    });

    return result;
  };

  /**
   * Make sure the given value is a fully qualified iri
   * @param value the iri string
   * @param prefix optional
   */
  SPARQL.Model.assureFullyQualifiedIRI = function(value, prefix){
    if(value){
      value = $.trim(value);
      var fullyQualifiedIRIPattern = /^http:\/\/:\w+[\.\/#]?\w+$/;
      var shortIRIPattern = /^(\w+):\w+$/;
      var match;

      //replace spaces by underscores
      value = value.replace(/\s+/, '_');

      //if value is of form 'http://xxx.yyy/zzzz' then do nothing, just return value
      if(fullyQualifiedIRIPattern.test(value)){
        return value;
      }
      //else if value is of form 'xxxx:yyyy' then replace the xxxx:' with a namespace
      else if((match = shortIRIPattern.exec(value))){
        var prfx = match[1];
        var namespace = SPARQL.Model.getNamespace(prfx);
        if(namespace){
          value = value.replace(prefix + ':', namespace);
          return value;
        }
      }
      //else get the namespace by given prefix and append it to value
      else if(prefix){
        namespace = SPARQL.Model.getNamespace(prefix);
        if(namespace){
          value = namespace + value;
        }
      }
    }
    return value;
  };

  

  /*
   *  Create new subject
   *  @param subjectName
   *  @param type
   */
  SPARQL.Model.createSubject = function(subjectName, type){
    var subject = new SPARQL.Model.SubjectTerm(subjectName, type);
    
    if(subject.type === TYPE.VAR 
      && subjectName
      && subjectName.length
      && $.inArray(subjectName, SPARQL.Model.data.projection_var) === -1)
      {
      SPARQL.Model.data.projection_var.push(subjectName); 
    }

    SPARQL.toTree();

    return subject;
  };
  
  /**
   * Update subject. Replace all occurences of the subject in the datamodel
   * @param subjectOld Term
   * @param subjectNew Term
   * @param inResults boolean
   */
  SPARQL.Model.updateSubject = function(subjectOld, subjectNew, inResults){    
    //do this only if inResults is defined
    if(typeof inResults !== 'undefined'){
      var projection_vars = SPARQL.Model.data.projection_var;
      var varInArray = $.inArray(subjectOld.value, projection_vars);
      //if new value should be in results
      if(inResults){
        //if old value is in results
        if(varInArray > -1){
          //change name
          projection_vars[varInArray] = subjectNew.value;
        }
        //if old value is NOT in results
        else{
          //add to array
          projection_vars.push(subjectNew.value);
        }
      }
      //if new value should NOT be in results
      else{
        if(varInArray > -1){
          //remove from array          
          projection_vars.splice(varInArray, 1);
        }
      }
    }
    if(subjectOld.isEqual(subjectNew)){
      return;
    }
    //go over triples, find this subject and change it
    var triples = SPARQL.Model.data.triple;
    for(var i = 0; i < triples.length; i++){
      var triple = triples[i];
      if(subjectOld.isEqual(triple.subject)){
        triple.subject = subjectNew;
      }
      if(subjectOld.isEqual(triple.object)){
        triple.object = subjectNew;
      }
    }
    var category_restriction = SPARQL.Model.data.category_restriction;
    //iterate over categories and change
    for(i = 0; i < category_restriction.length; i++){
      if(subjectOld.isEqual(category_restriction[i].subject)){
        category_restriction[i].subject = subjectNew;
      }
    }
    //iterate over filters
    var filters = SPARQL.Model.data.filter;
    for(i = 0; i < filters.length; i++){
      for(var j = 0; j < filters[i].expression.length; j++){
        for(var k = 0; k < filters[i].expression[j].argument.length; k++){
          if(subjectOld.isEqual(filters[i].expression[j].argument[k])){
            filters[i].expression[j].argument[k] = subjectNew;
          }
        }
      }
    }
    
    if(subjectOld.type === TYPE.VAR){
      var order = SPARQL.Model.data.order;
      for(i = 0; i < order.length; i++){
        if(subjectOld.value === order[i].by_var){
          if(subjectNew.type === TYPE.VAR){
            order[i].by_var = subjectNew.value;
            break;
          }
          else{
            order.splice(i, 1);
            break;
          }
        }
      }
    }
    
    SPARQL.toTree();
  };

  /**
   * Create new category
   * @param subject Term
   * @param categoryArray array of categories
   */
  SPARQL.Model.createCategory = function(subject, categoryArray){
    if(!subject){
      subject = SPARQL.Model.createSubject('newsubject');
    }

    if(typeof categoryArray === 'string'){
      categoryArray = [categoryArray];
    }
    
    var newCategoryRestriction = new SPARQL.Model.CategoryRestriction(subject, categoryArray);
      
    //check if this object already exists in the array
    var alreadyExists = false;
    $.each(SPARQL.Model.data.category_restriction, function(index, value){
      if(newCategoryRestriction.isEqual(value)){
        alreadyExists = true;
        return false;//break the loop
      }
    });
    if(!alreadyExists){
      SPARQL.Model.data.category_restriction.push(newCategoryRestriction);
      SPARQL.toTree();
    }
  };


  /**
   * Update category
   * @param oldCategoryRestriction CategoryRestriction old category
   * @param newCategories array of new category iri
   */
  SPARQL.Model.updateCategory = function(oldCategoryRestriction, newCategories){
    var category_restrictions = SPARQL.Model.data.category_restriction;
  
    for(var i = 0; i < category_restrictions.length; i++){
      if(oldCategoryRestriction.isEqual(category_restrictions[i])){
        //create category_iri from newCategories and replace old category with new one
        category_restrictions[i] = new SPARQL.Model.CategoryRestriction(oldCategoryRestriction.subject, newCategories);
        break;
      }
    }

    SPARQL.toTree();
  };

  /**
   *  Delete category from the specified subject.
   *  If category name is given then search in subject categories is performed (used for removing categories in OR relation).
   *  Otherwise the whole category_restriction object is removed (this is used when no OR relations defined)
   *  @param subject Term representing subject
   *  @param oldCategories array of categories
   *  @param categoryToDelete string category name
   */
  SPARQL.Model.deleteCategory = function(subject, oldCategories, categoryToDelete){
    var categoryRestriction = new SPARQL.Model.CategoryRestriction(subject, oldCategories);
    var category_restrictions = SPARQL.Model.data.category_restriction;
    for(var i = 0; i < category_restrictions.length; i++){
      if(categoryRestriction.isEqual(category_restrictions[i])){
        categoryRestriction.deleteCategory(categoryToDelete);
        if(categoryRestriction.isEmpty()){
          category_restrictions.splice(i, 1);
        }
        else{
          category_restrictions[i] = categoryRestriction;
        }
      }
      break;
    }
    SPARQL.toTree();
  };

  /**
   *  Update filters belonging to the given variable.
   *  Removes filters having the given var as argument then adds a new filters to array
   *  @param varTerm Term representing variable
   *  @param newFilters array of filters
   */
  SPARQL.Model.updateFilters = function(varTerm, newFilters){
    //iterate over filters
    var filters = SPARQL.Model.data.filter;
    for(i = 0; i < filters.length; i++){
      for(var j = 0; j < filters[i].expression.length; j++){
        for(var k = 0; k < filters[i].expression[j].argument.length; k++){
          if(SPARQL.objectsEqual(filters[i].expression[j].argument[k], varTerm)){
            filters.splice(i, 1);
            i--;
          }
        }
      }
    }
    if(newFilters && newFilters.length){
      filters.concat(newFilters);
    }
    
    SPARQL.View.updateFilters(varTerm);
  //    SPARQL.updateAllFromTree();
  };


  SPARQL.Model.addFilterAND = function(){
    SPARQL.View.addFilterAND('');
  };


  /**
   *  Check if given variable is in projection vars
   *  @param subject Term representing a variable
   */
  SPARQL.Model.isVarInResults = function(subject){
    var result = true;
    var projection_var = SPARQL.Model.data.projection_var;
    if(subject){
      result = (subject.type === 'VAR' && $.inArray(subject.value, projection_var) > -1);
    }

    return result;
  };

  /**
   *  Create new property.
   *  @param subject Term representing a subject
   *  @param propertyName string property name
   *  @param valueName string property value name
   *  @param optional boolean is this triple optional
   *  @param showInResults boolean is this var shown in results
   */
  SPARQL.Model.createProperty = function(subject, propertyName, valueName, optional, showInResults){
    if(!subject){
      subject = SPARQL.Model.createSubject('newsubject', 'VAR');
    }
    var propertyType = ($.trim(propertyName).indexOf('?') === 0 ? 'VAR' : 'IRI');
    var propertyValue = (propertyType === 'VAR' ? $.trim(propertyName).replace(/^\?/, '') : SPARQL.property_iri + SPARQL.iri_delim + propertyName);
    var valueType = ($.trim(valueName).indexOf('?') === 0 ? 'VAR' : 'IRI');
    var valueValue = (valueType === 'VAR' ? $.trim(valueName).replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + valueName);

    var triple  = {
      subject: subject,
      predicate: new SPARQL.Model.Term(propertyValue, propertyType),
      object: new SPARQL.Model.Term(valueValue, valueType),
      optional: optional
    };
    //@TODO check if this triple is already in the array
    //implement inArray method for objects
    SPARQL.Model.data.triple.push(triple);

    var index = $.inArray(triple.object.value, SPARQL.Model.data.projection_var);
    if(showInResults){
      if(triple.object.type === TYPE.VAR && index === -1){
        SPARQL.Model.data.projection_var.push(triple.object.value);
      }
    }
    else{
      if(index > -1){
        SPARQL.Model.data.projection_var.splice(index, 1);
      }
    }

    SPARQL.View.createProperty(triple);
  };

  /**
   *  Remove triple replresenting given property
   *  and remove the object var from projection vars, filters, order if it's not part of any other triple or category restriction
   *  @param dataEntity
   */
  SPARQL.Model.deleteProperty = function(dataEntity){
    //remove this property from triple
    var triples = SPARQL.Model.data.triple;
    var objectVarInUse = false;
    for(var i = 0; i < triples.length; i++){
      if(SPARQL.objectsEqual(dataEntity.value, triples[i])){
        triples.splice(i, 1);
      }
      else if(dataEntity.value.object.type === TYPE.VAR && SPARQL.objectsEqual(dataEntity.value.object, triples[i])){
        objectVarInUse = true;
      }
    }
    //check if this var is in categories
    if(!objectVarInUse){
      var category_restriction = SPARQL.Model.data.category_restriction;
      for(i = 0; i < category_restriction.length; i++){
        if(category_restriction[i].subject.isEqual(dataEntity.value.object)){
          objectVarInUse = true;
          break;
        }
      }
    }

    //if this var not in use then delete it from filters and order
    if(!objectVarInUse){
      var filters = SPARQL.Model.data.filter;
      for(i = 0; i < filters.length; i++){
        for(var j = 0; j < filters[i].expression.length; j++){
          for(var k = 0; k < filters[i].expression[j].argument.length; k++){
            if(filters[i].expression[j].argument.isEqual(dataEntity.value.object)){
              filters.splice(i, 1);
              break;
            }
          }
        }
      }

      var order = SPARQL.Model.data.order;
      for(i = 0; i < order.length; i++){
        if(order[i].by_var === dataEntity.value.object.value){
          order.splice(i, 1);
          break;
        }
      }
    }

    SPARQL.View.deleteProperty(dataEntity);
  //    SPARQL.updateAllFromTree();
  };

  /**
   * Delete subject. Delete all the triples having this argument as subject
   * and delete all categories having this argument as subject
   * and delete all filters if this argument does not appear as object in any triple
   * and delete it from projection vars
   * and delete it from order
   * @param subject Term
   */
  SPARQL.Model.deleteSubject = function(subject){    
    var category_restriction = SPARQL.Model.data.category_restriction;
    //remove from categories
    for(var i = 0; i < category_restriction.length; i++){
      if(subject.isEqual(category_restriction[i].subject)){
        category_restriction.splice(i, 1);
        i--;
      }
    }

    //remove from triple
    var triples = SPARQL.Model.data.triple;
    var occursAsObject = false;
    for(i = 0; i < triples.length; i++){
      if(subject.isEqual(triples[i].subject)){
        triples.splice(i, 1);
        i--;
      }
      else if(subject.isEqual(triples[i].object)){
        occursAsObject = true;
      }
    }


    //if this is var remove it from order
    if(!occursAsObject && subject.type === 'VAR'){
      var filters = SPARQL.Model.data.filter;
      for(i = 0; i < filters.length; i++){
        for(var j = 0; j < filters[i].expression.length; j++){
          for(var k = 0; k < filters[i].expression[j].argument.length; k++){
            if(subject.isEqual(filters[i].expression[j].argument[k])){
              filters[i].expression[j].splice(j, 1);
              j--;
            }
          }
        }
        if(filters[i].expression.length === 0){
          filters.splice(i, 1);
          i--;
        }
      }

      var order = SPARQL.Model.data.order;
      for(i = 0; i < order.length; i++){
        if(subject.value === order[i].by_var){
          order.splice(i, 1);
          break;
        }
      }

      //remove it from projection_var
      var projection_var = SPARQL.Model.data.projection_var;
      for(i = 0; i < projection_var.length; i++){
        if(subject.value === projection_var[i]){
          projection_var.splice(i, 1);
          break;
        }
      }
    }

    SPARQL.toTree();
  };

  /**
   * Reset the model to initial state: empty data, set default namespaces and query parameters
   */
  SPARQL.Model.reset = function(){
    SPARQL.tripleStoreGraph = window.parent.smwghTripleStoreGraph + SPARQL.iri_delim;
    SPARQL.category_iri = SPARQL.tripleStoreGraph + 'category';
    SPARQL.property_iri = SPARQL.tripleStoreGraph + 'property';
    SPARQL.instance_iri = SPARQL.tripleStoreGraph + 'instance';

    SPARQL.Model.data = {
      category_restriction: [],
      triple: [],
      filter: [],
      projection_var: [],
      namespace: [],
      order: []
    };

    SPARQL.queryString = null;
    SPARQL.queryParameters = {
      source: 'tsc',
      format: 'table'
    },

    SPARQL.Model.data.namespace = [
    {
      prefix: "tsctype",
      namespace_iri: "http://www.ontoprise.de/smwplus/tsc/unittype#"
    },
    {
      prefix: "xsd",
      namespace_iri: "http://www.w3.org/2001/XMLSchema#"
    },
    {
      prefix: "rdf",
      namespace_iri: "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    },
    {
      prefix: "category",
      namespace_iri: SPARQL.category_iri + SPARQL.iri_delim
    },
    {
      prefix: "property",
      namespace_iri: SPARQL.property_iri + SPARQL.iri_delim
    },
    {
      prefix: "instance",
      namespace_iri: SPARQL.instance_iri + SPARQL.iri_delim
    }];

    SPARQL.View.reset();
  };

  /**
   * Change the triple representing this property in the model.
   * Change also the object var if it's not part of any other triple
   * @param oldTriple old triple object
   * @param newTriple new triple object
   * @param valueInResults boolean indicating whether the object value should be shown in results or not
   *
   */
  SPARQL.Model.updateProperty = function(oldTriple, newTriple, valueInResults){
    //varExists indicates whether an object var of the old triple exists somewhere elso fo we have to update it also
    var varExists = false;
    if(SPARQL.objectsEqual(oldTriple, newTriple)){
      varExists = true;
    }
    else{
      //find old triple
      var triples = SPARQL.Model.data.triple;
      for(var i = 0; i < triples.length; i++){
        if(SPARQL.objectsEqual(oldTriple, triples[i])){
          //replace with the new triple
          triples[i] = newTriple;
        }
        else if(triples[i].object.isEqual(oldTriple.object)
          || triples[i].subject.isEqual(oldTriple.object)){
          varExists = true;
        }
      }
    }

    if(!varExists){
      var category_restrictions = SPARQL.Model.data.category_restriction;
      $.each(category_restrictions, function(index, category_restriction){
        if(category_restriction.subject.isEqual(oldTriple.object)){
          varExists = true;
          return false;//break the loop
        }
      });
    }

    var filters = SPARQL.Model.data.filter;
    if(newTriple.object.type === TYPE.VAR){
      $.each(filters, function(i, filter){
        $.each(filter.expression, function(j, expression){
          $.each(expression.argument, function(k, argument){
            if(argument.isEqual(oldTriple.object)){
              argument = newTriple.object;
            }
          })
        });
      });

      var order = SPARQL.Model.data.order;
      $.each(order, function(index, value){
        if(value.by_var === oldTriple.object.value){
          value.by_var = newTriple.object.value;
        }
      });
    }

    if(typeof valueInResults !== 'undefined'){
      var projection_var = SPARQL.Model.data.projection_var;
      if(!varExists){
        $.each(projection_var, function(index, variable){
          if(variable === oldTriple.object.value){
            projection_var.splice(index, 1);
            return false;//break the loop
          }
        });
      }
      if(valueInResults && $.inArray(newTriple.object.value, projection_var) === -1){
        projection_var.push(newTriple.object.value);
      }
      if(!valueInResults && $.inArray(newTriple.object.value, projection_var) > -1){
        for(i = 0; i < projection_var.length; i++){
          if(projection_var[i] === newTriple.object.value){
            projection_var.splice(i, 1);
            break;
          }
        }
      }
    }

    SPARQL.View.updateProperty(oldTriple, newTriple);
  //    SPARQL.updateAllFromTree();
  };




})(jQuery);


