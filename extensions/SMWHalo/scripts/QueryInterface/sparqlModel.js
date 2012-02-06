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
  if(typeof SPARQL === 'undefined'){
    return;
  }
  
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

  SPARQL.Model.removeNamespaceDuplicates = function(namespaceArray){
    if(!(namespaceArray && namespaceArray.length)){
      return [];
    }

    var result = $.map(namespaceArray, function(value, index){
      for(var i = index + 1; i < namespaceArray.length; i++){
        if(value.prefix === namespaceArray[i].prefix){
          mw.log('remove ' + value.prefix + ' : ' + value.namespace_iri);
          return null;
        }
      }      
      return value;
    });

    return result;
  };

  /**
   * Populate model from the sparql query json structure
   * @param data json object representing the sparql query
   */
  SPARQL.Model.init = function(data){
    //reset the model to initial values
    SPARQL.Model.data.category_restriction = [];
    SPARQL.Model.data.triple = [];
    SPARQL.Model.data.filter = [];

    //init static data
    SPARQL.Model.data.projection_var = data.projection_var || [];
    SPARQL.Model.data.order = data.order || [];
    SPARQL.Model.data.namespace = SPARQL.Model.removeNamespaceDuplicates(data.namespace);

    //init category_restriction
    data.category_restriction = data.category_restriction || [];
    $.each(data.category_restriction, function(index, category){
      var subject = new SPARQL.Model.SubjectTerm(category.subject.value, category.subject.type);
      SPARQL.Model.data.category_restriction.push(new SPARQL.Model.CategoryRestriction(subject, category.category_iri));
    });
    //init triple
    data.triple = data.triple || [];
    $.each(data.triple, function(index, triple){
      var subject = new SPARQL.Model.SubjectTerm(triple.subject.value, triple.subject.type);
      var predicate = new SPARQL.Model.PredicateTerm(triple.predicate.value, triple.predicate.type);
      var object = new SPARQL.Model.ObjectTerm(triple.object.value, triple.object.type);
      SPARQL.Model.data.triple.push(new SPARQL.Model.Triple(subject, predicate, object, triple.optional, triple.optional));
    });
    //init filter
    data.filter = data.filter || [];
    $.each(data.filter, function(i, filter){
      var expressions = {
        expression: []
      };
      $.each(filter.expression, function(j, expression){
        var argument0 = new SPARQL.Model.Term(expression.argument[0].value, expression.argument[0].type, expression.argument[0].datatype_iri, expression.argument[0].language);
        var argument1 = new SPARQL.Model.Term(expression.argument[1].value, expression.argument[1].type, expression.argument[1].datatype_iri, expression.argument[1].language);
        expressions.expression.push(new SPARQL.Model.FilterExpression(expression.operator, argument0, argument1));
      });
      SPARQL.Model.data.filter.push(expressions);
    });
  };

  SPARQL.Model.moveOptionalTriplesToEnd = function(){
    if(!(SPARQL.Model.data.category_restriction && SPARQL.Model.data.category_restriction.length)
      && SPARQL.Model.data.triple && SPARQL.Model.data.triple.length && SPARQL.Model.data.triple[0].optional){
      var optionalLast = [];
      for(var i = 0; i < SPARQL.Model.data.triple.length; i++){
        if(SPARQL.Model.data.triple[i].optional){
          optionalLast.push(SPARQL.Model.data.triple.splice(i, 1)[0]);
          i--;
        }
      }
      if(optionalLast.length){
        SPARQL.Model.data.triple = $.merge(SPARQL.Model.data.triple, optionalLast);
      }
    }
  };

  

  /**
   * Check if given variable is already a part of a model
   * @term Term representing a variable
   * @return true is given variable is a part of triples or categories, false otherwise
   */
  SPARQL.Model.isTermInModel = function(term){
    var result = false;
    $.each(SPARQL.Model.data.triple, function(index, triple){
      if(triple.subject.isEqual(term) || triple.predicate.isEqual(term) || triple.object.isEqual(term)){
        result = true;
        return false;
      }
    });

    if(!result){
      $.each(SPARQL.Model.data.category_restriction, function(index, category_restriction){
        if(category_restriction.subject.isEqual(term)){
          result = true;
          return false;
        }
      });
    }

    return result;
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
      this.value = ($.trim(value) || '').replace(/\s+/g, '_');
      this.datatype_iri = datatype_iri;
      this.language = language;      

      //if ty is not defined then figure it out from the value
      if(!this.type){
        this.type = (this.value.indexOf('?') === 0 ? TYPE.VAR : TYPE.IRI);
      }

      //replace spaces by underscores in iri
      if(this.type === TYPE.IRI && this.fixIRI){
        this.value = this.fixIRI(this.value);
      }
      //remove ? from the beginning of a name
      else if(this.type === TYPE.VAR){
        this.value = this.value.replace(/^\?/, '');
      }
    };

    this.getId = function(){
      return this.value.replace(/[:\?\/]/g, '-');
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

    /**
     * Get short representation of the iri.
     * Search for a matching namespace iri in the table, if found and the namespace prefix is equal to the given one,
     * then remove the namespace iri from the name, else if the prefixes are not equal, then replace the namespace iri
     * with the namespace prefix.
     * If not found then return the string after last delimiter (/,#)
     */
    this.getShortName = function(prefix){
      if(this.type !== TYPE.IRI){
        return this.value;
      }
      var result = null;
      var that = this;

      $.each(SPARQL.Model.data.namespace, function(index, namespace){
        if(that.value.substr(0, namespace.namespace_iri.length) === namespace.namespace_iri){
          if(prefix === namespace.prefix){
            result = that.value.replace(namespace.namespace_iri, '');
          }
          else{
            result = that.value.replace(namespace.namespace_iri, namespace.prefix + ':');
          }
          return false;
        }
      });

      if(!result){
        var value = this.value.length ? '<' + this.value + '>' : this.value;
        result = value;
      }

      return result;
    };
  };


  SPARQL.Model.SubjectTerm = function(value, type, datatype_iri, language){
    this.fixIRI = function(value){
      return SPARQL.Model.assureFullyQualifiedIRI(value, 'a');
    };
    SPARQL.Model.Term.call(this, value, type, datatype_iri, language);    
  };


  SPARQL.Model.PredicateTerm = function(value, type, datatype_iri, language){
    this.fixIRI = function(value){
      return SPARQL.Model.assureFullyQualifiedIRI(value, 'property');
    };
    SPARQL.Model.Term.call(this, value, type, datatype_iri, language);
  };

  SPARQL.Model.ObjectTerm = function(value, type, datatype_iri, language){
    this.fixIRI = function(value){
      return SPARQL.Model.assureFullyQualifiedIRI(value, 'a');
    };
    SPARQL.Model.Term.call(this, value, type, datatype_iri, language);
  };

  SPARQL.Model.FilterArgumentTerm = function(value, type, datatype_iri, language){
    this.fixIRI = function(value){
      return SPARQL.Model.assureFullyQualifiedIRI(value, 'a');
    };
    SPARQL.Model.Term.call(this, value, type, datatype_iri, language);
  };

  /**
     * SPARQL.Model.Triple constructor. Creates new Triple obejct.
     * @param subject Term representing subject of the triple
     * @param object Term representing object of the triple
     * @param predicate Term representing predicate of the triple
     * @param optional boolean indicating if this triple is optional
     * @exception when this method is called without 'new' keyword
     */
  SPARQL.Model.Triple = function(subject, predicate, object, optional){
    if(!this instanceof SPARQL.Model.Triple){
      throw new Error("SPARQL.Model.Triple constructor called as a function");
    }
    if(typeof predicate === 'string'){
      //init from node label
      var params = predicate.split(' ');
      this.subject = subject;
      this.predicate = new SPARQL.Model.PredicateTerm(params[0]);
      this.object = new SPARQL.Model.ObjectTerm(params[1]);
      this.optional = SPARQL.Model.isOptional(this);
    }
    else{
      this.subject = subject;
      this.predicate = predicate;
      this.object = object;
      this.optional = optional || false;
    }

    this.getId = function(){
      var id = this.subject.value + '-' + this.predicate.value + '-' + this.object.value;
      return id.replace(/[:\?\/]/g, '-');
    };

    this.isEqual = function(anotherTriple){
      return SPARQL.objectsEqual(this, anotherTriple);
    };
  };

  SPARQL.Model.isOptional = function(triple){
    var result = false;
    $.each(SPARQL.Model.data.triple, function(index, value){
      if(value.subject.isEqual(triple.subject)
        && value.predicate.isEqual(triple.predicate)
        && value.object.isEqual(triple.object))
        {
        result = value.optional;
        return false;
      }
    });

    return result;
  }

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

    this.getId = function(){
      var id = this.subject.value + '-' + this.category_iri.join('-');
      return id.replace(/[:\?\/]/g, '-');
    };


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
     * Search for a matching namespace iri in the table, if found and the namespace prefix is equal to the given one,
     * then remove the namespace iri from the name, else if the prefixes are not equal, then replace the namespace iri
     * with the namespace prefix.
     * If not found then return the string after last delimiter (/,#)
     */
    this.getShortName = function(iri, prefix){
      if(this.type === TYPE.VAR){
        return this.value;
      }
      var result = null;
      $.each(SPARQL.Model.data.namespace, function(index, namespace){
        if(iri.substr(0, namespace.namespace_iri.length) === namespace.namespace_iri){
          if(prefix === namespace.prefix){
            result = iri.replace(namespace.namespace_iri, '');
          }
          else{
            result = iri.replace(namespace.namespace_iri, namespace.prefix + ':');
          }
          return false;
        }
      });

      if(!result){
        var value = iri.length ? '<' + iri + '>' : iri;
        result = value;
      }

      return result;
    };    

    this.getShortNameArray = function(){
      var result = [];
      var that = this;
      $.each(this.category_iri, function(index, value){
        result.push(that.getShortName(value, 'category'));
      });

      return result;
    };

    this.getString = function(){
      var result = '';
      var that = this;
      $.each(this.category_iri, function(index, value){
        result += that.getShortName(value, 'category');
        if(index < that.category_iri.length - 1){
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


  SPARQL.Model.FilterExpression = function(operator, argument1, argument2){
    if(!this instanceof SPARQL.Model.FilterExpression){
      throw new Error("SPARQL.Model.FilterExpression constructor called as a function");
    }

    this.operator = operator;
    this.argument = [argument1, argument2];

    /**
       * Check if given argument is a part of this filter expression
       * @param term Term
       * @return true if this filter expression contains such an argument, false otherwise
       */
    this.hasTerm = function(term){
      var result = false;
      var that = this;
      $.each(that.argument, function(index, argument){
        if(argument.isEqual(term)){
          result = true;
          return false;
        }
      });

      return result;
    };

    this.getId = function(){
      var id = this.operator + '-' + this.argument[0].value + '-' + this.argument[1].value;
      return id.replace(/[:\?\/]/g, '-');
    };

    /**
       * Compare this expression to the given one
       * @param anotherExpression FilterExprerssion
       * @return true if these filter expressions are equal, false otherwise
       */
    this.isEqual = function(anotherExpression){
      return SPARQL.objectsEqual(this, anotherExpression);
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

      //replace spaces by underscores
      value = value.replace(/\s+/g, '_');

      //if value is in pointy brackets then do nothing, just return it
      if(SPARQL.Validator.hasPointyBrackets(value)){
        return value.replace(/^</, '').replace(/>$/, '');
      }
      //else if iri contains colon then try to replace the prefix with
      //a matching namespace
      var colonIndex = value.indexOf(':');
      if(colonIndex > -1){
        var prfx = value.substring(0, colonIndex);
        var namespace = SPARQL.Model.getNamespace(prfx);
        if(namespace){
          value = value.replace(prfx + ':', namespace);
          return value;
        }
      }
      //else default prefix is specified then get namespace for this prefix and append it to value
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
     *  @param subjectName string
     *  @param type string
     */
  SPARQL.Model.createSubject = function(subjectName, type){
    if(!subjectName){
      subjectName = '?variable_' + SPARQL.getNextUid();
    }
    var subject = new SPARQL.Model.SubjectTerm(subjectName, type);
    
    if(subject.type === TYPE.VAR
      && subject.value
      && subject.value.length
      && $.inArray(subject.value, SPARQL.Model.data.projection_var) === -1)
      {
      SPARQL.Model.data.projection_var.push(subject.value);
    }

    SPARQL.toTree(null, subject.getId(), true);

    return subject;
  };
  
  /**
     * Update subject. Replace all occurences of the subject in the datamodel
     * @param subjectOld Term
     * @param subjectNew Term
     * @param inResults boolean
     */
  SPARQL.Model.updateSubject = function(subjectOld, subjectNew, inResults){
    if(subjectNew.type === TYPE.IRI && SPARQL.Model.data.triple.length === 0){
      SPARQL.showMessageDialog('Can\'t add new subject of type "IRI".\nUse variable instead.', 'Operation failed', 'modelUpdateSubjectMsg');
      return;
    }
    //do this only if inResults is defined
    if(typeof inResults !== 'undefined'){
      var projection_vars = SPARQL.Model.data.projection_var || [];
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
//    if(subjectOld.isEqual(subjectNew)){
//      if(SPARQL.validateQueryTree()){
//        SPARQL.toTree(null, subjectNew.getId());
//      }
//      return;
//    }
    //go over triples, find this subject and change it
    var triples = SPARQL.Model.data.triple || [];
    for(var i = 0; i < triples.length; i++){
      var triple = triples[i];
      if(subjectOld.isEqual(triple.subject)){
        triple.subject = subjectNew;
      }
      if(subjectOld.isEqual(triple.object)){
        triple.object = subjectNew;
      }
    }
    var category_restriction = SPARQL.Model.data.category_restriction  || [];
    //iterate over categories and change
    for(i = 0; i < category_restriction.length; i++){
      if(subjectOld.isEqual(category_restriction[i].subject)){
        category_restriction[i].subject = subjectNew;
      }
    }
    //iterate over filters
    var filters = SPARQL.Model.data.filter || [];
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
      var order = SPARQL.Model.data.order || [];
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
    
    SPARQL.toTree(null, subjectNew.getId());
  };

  /**
     * Create new category
     * @param subject Term
     * @param categoryArray array of categories
     */
  SPARQL.Model.createCategory = function(subject, categoryArray){
    subject = subject || SPARQL.Model.createSubject();
//    categoryArray = categoryArray || 'category' + SPARQL.getNextUid();
    categoryArray = categoryArray || '';

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
      SPARQL.toTree(null, newCategoryRestriction.getId());
    }

    SPARQL.toTree(null, newCategoryRestriction.getId());
  };


  /**
     * Update category
     * @param oldCategoryRestriction CategoryRestriction old category
     * @param newCategories array of new category iri
     */
  SPARQL.Model.updateCategory = function(oldCategoryRestriction, newCategories){
    var category_restrictions = SPARQL.Model.data.category_restriction;
    var newCategory;
    for(var i = 0; i < category_restrictions.length; i++){
      if(oldCategoryRestriction.isEqual(category_restrictions[i])){
        //create category_iri from newCategories and replace old category with new one
        newCategory = new SPARQL.Model.CategoryRestriction(oldCategoryRestriction.subject, newCategories);
        category_restrictions[i] = newCategory;
        break;
      }
    }

    SPARQL.toTree(null, newCategory ? newCategory.getId() : null);
  };


//  SPARQL.Model.addCategory = function(categoryRestriction){
//    SPARQL.Model.data.category_restriction.push(categoryRestriction);
//
//    SPARQL.toTree(null, categoryRestriction.getId());
//  };


  /**
     *  Delete category from the specified subject.
     *  If category name is given then search in subject categories is performed (used for removing categories in OR relation).
     *  Otherwise the whole category_restriction object is removed (this is used when no OR relations defined)
     *  @param categoryRestriction CategoryRestriction object
     *  @param categoryToDelete string name of category to delete
     */
  SPARQL.Model.deleteCategory = function(categoryRestriction, categoryToDelete){
    var category_restrictions = SPARQL.Model.data.category_restriction;
    for(var i = 0; i < category_restrictions.length; i++){
      if(categoryRestriction.isEqual(category_restrictions[i])){
        if(categoryToDelete){
          categoryRestriction.deleteCategory(categoryToDelete);
          if(categoryRestriction.isEmpty()){
            category_restrictions.splice(i, 1);
          }
          else{
            category_restrictions[i] = categoryRestriction;
          }
        }
        else{
          category_restrictions.splice(i, 1);
        }

        break;
      }      
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
    for(var i = 0; i < filters.length; i++){
      for(var j = 0; j < filters[i].expression.length; j++){
        if(filters[i].expression[j].hasTerm(varTerm)){
          filters.splice(i, 1);
          i--;
          break;
        }
      }
    }
    if(newFilters && newFilters.length){
      SPARQL.Model.data.filter = filters.concat(newFilters);
    }
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
    subject = subject || SPARQL.Model.createSubject();
//    propertyName = propertyName || 'property' + SPARQL.getNextUid();
    propertyName = propertyName || '';
//    valueName = valueName || '?value' + SPARQL.getNextUid();
    valueName = valueName || '?variable_' + SPARQL.getNextUid();
    optional = optional || false;
    showInResults = showInResults || true;

    var predicate = new SPARQL.Model.PredicateTerm(propertyName);
    var object = new SPARQL.Model.ObjectTerm(valueName);

    //create new Triple object
    var newTriple = new SPARQL.Model.Triple(subject, predicate, object, optional);

    //if it's not already in SPARQL.Model.data.triple then add it
    if(!SPARQL.isObjectInArray(newTriple, SPARQL.Model.data.triple)){
      SPARQL.Model.data.triple.push(newTriple);
      if(showInResults && newTriple.object.type === TYPE.VAR && $.inArray(newTriple.object.value, SPARQL.Model.data.projection_var) === -1){
        SPARQL.Model.data.projection_var.push(newTriple.object.value);
      }
      SPARQL.toTree(null, newTriple.getId());
    }
  };

  /**
     *  Remove triple replresenting given property
     *  also remove the object var from projection vars, filters, order if it's not part of any other triple or category restriction
     *  @param triple Triple replresenting given property
     */
  SPARQL.Model.deleteProperty = function(triple){
    //remove this property from triple
    var triples = SPARQL.Model.data.triple || [];
    var objectVarInUse = false;
    for(var i = 0; i < triples.length; i++){
      if(triple.isEqual(triples[i])){
        triples.splice(i, 1);
        break;
      }
      else if(triple.object.type === TYPE.VAR && triple.object.isEqual(triples[i].object)){
        objectVarInUse = true;
      }
    }
    //check if this var is in categories
    if(!objectVarInUse){
      var category_restriction = SPARQL.Model.data.category_restriction || [];
      for(i = 0; i < category_restriction.length; i++){
        if(category_restriction[i].subject.isEqual(triple.object)){
          objectVarInUse = true;
          break;
        }
      }
    }

    //if this var not in use then delete it from projection_var, filters and order
    if(!objectVarInUse){
      var index = $.inArray(triple.object.value, SPARQL.Model.data.projection_var);
      if(index > -1){
        SPARQL.Model.data.projection_var.splice(index, 1);
      }
      
      var filters = SPARQL.Model.data.filter || [];
      for(i = 0; i < filters.length; i++){
        for(var j = 0; j < filters[i].expression.length; j++){
          if(filters[i].expression[j].hasTerm(triple.object)){
            filters.splice(i, 1);
            break;
          }
        }
      }

      var order = SPARQL.Model.data.order || [];
      for(i = 0; i < order.length; i++){
        if(order[i].by_var === triple.object.value){
          order.splice(i, 1);
          break;
        }
      }
    }
    SPARQL.toTree();

  };

  /**
   * Delete variables that appear only in projection_var in the model
   * 
   */
  SPARQL.Model.cleanup = function(){
    SPARQL.Model.removeOrphanVars();
    SPARQL.Model.removeOrphanFilters();
    SPARQL.Model.removeOrphanOrders();    
  };
  

  SPARQL.Model.removeOrphanVars = function(){
    var projection_var = SPARQL.Model.data.projection_var || [];
    for(var i = 0; i < projection_var.length; i++){
      if(!SPARQL.Model.isTermInModel(new SPARQL.Model.Term(projection_var[i], TYPE.VAR))){
        projection_var.splice(i, 1);
        i--;
      }
    }
  };

  
  SPARQL.Model.removeOrphanFilters = function(){
    var filter = SPARQL.Model.data.filter || [];
    for(var i = 0; i < filter.length; i++){
      for(var j = 0; j < filter[i].expression.length; j++){
        var argument0 = filter[i].expression[j].argument[0]; 
        var argument1 = filter[i].expression[j].argument[1];

        if(argument0.type === TYPE.VAR && !SPARQL.Model.isTermInModel(argument0)
          || argument1.type === TYPE.VAR && !SPARQL.Model.isTermInModel(argument1))
        {
          filter[i].expression.splice(i, 1);
          i--;
        }
      }
    }
  };


  SPARQL.Model.removeOrphanOrders = function(){
    var order = SPARQL.Model.data.order || [];
    for(var i = 0; i < order.length; i++){
      if(!SPARQL.Model.isTermInModel(new SPARQL.Model.Term(order[i], TYPE.VAR))){
        order.splice(i, 1);
        i--;
      }
    }
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
    var category_restriction = SPARQL.Model.data.category_restriction || [];
    //remove from categories
    for(var i = 0; i < category_restriction.length; i++){
      if(subject.isEqual(category_restriction[i].subject)){
        category_restriction.splice(i, 1);
        i--;
      }
    }

    //remove from triple
    var triples = SPARQL.Model.data.triple || [];
    var occursAsObject = false;
    for(i = 0; i < triples.length; i++){
      if(subject.isEqual(triples[i].subject)){
        triples.splice(i, 1);
        i--;
      }
    }    
  
    SPARQL.Model.cleanup();
    SPARQL.toTree();
  };



  /**
     * Reset the model to initial state: empty data, set default namespaces and query parameters
     */
  SPARQL.Model.reset = function(){  

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
    //varExists indicates whether an object var of the old triple exists somewhere in the model so we have to update it also
    var varExists = false;
    if(oldTriple.isEqual(newTriple)){
      varExists = true;
    }
    else{
      //find old triple
      var triples = SPARQL.Model.data.triple || [];
      for(var i = 0; i < triples.length; i++){
        if(oldTriple.isEqual(triples[i])){
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
      var category_restrictions = SPARQL.Model.data.category_restriction || [];
      $.each(category_restrictions, function(index, category_restriction){
        if(category_restriction.subject.isEqual(oldTriple.object)){
          varExists = true;
          return false;//break the loop
        }
      });
    }

    var filters = SPARQL.Model.data.filter || [];
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

      var order = SPARQL.Model.data.order || [];
      $.each(order, function(index, value){
        if(value.by_var === oldTriple.object.value){
          value.by_var = newTriple.object.value;
        }
      });
    }

    if(typeof valueInResults !== 'undefined'){
      var projection_var = SPARQL.Model.data.projection_var || [];
      if(!varExists){
        $.each(projection_var, function(index, variable){
          if(variable === oldTriple.object.value){
            projection_var.splice(index, 1);
            return false;//break the loop
          }
        });
      }
      if(newTriple.object.type === TYPE.VAR){
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
    }

    SPARQL.toTree(null, newTriple.getId());
  };




})(jQuery);


