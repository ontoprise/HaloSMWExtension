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
  var iri = '^[^<>:]+:[^<>:]+$';

  SPARQL.Validator = {    
    map: {
      'xsd:integer' : '^\\-?\\d+$',
      'xsd:string': '^[\\s\\S]*$',
      'xsd:double': '^\\-?\\d+(?:\\.?\\d+)?(?:e\\d+)?$',
      'xsd:number': '^\\-?\\d+(?:\\.?\\d+)?(?:e\\d+)?$',
      'xsd:boolean': '^(?:true|false)$',
      'xsd:dateTime': '^\\-?\\d{4}\\-\\d{2}\\-\\d{2}T\\d{2}:\\d{2}:\\d{2}(?:\\.\\d+)?$',
      'xsd:date': '^\\-?\\d{4}\\-\\d{2}\\-\\d{2}$',
//      'xsd:anyURI': '',
//      'tsctype:record': '',
      'tsctype:page' : '^[%!"$&\\()*,\\-.\\/0-9:;=?@A-Z\\^_`a-z~\\x80-\\xFF+]+$',
      'iri' : iri + '|' + '^<?' + iri + '>?$',

      'variable' : '^\\?[\\w\\s]+$'
    },
    get: function(key){
      return SPARQL.Validator.map[key];
    },
    validate: function(value, datatype){
      var result = true;
      var pattern = SPARQL.Validator.get(datatype);
      if(pattern){
        pattern = new RegExp(pattern);
        if(!pattern.test(value)){
          if(datatype === 'iri' && pattern.test('prefix:' + value)){
            return true;
          }
          SPARQL.showMessageDialog('Invalid value for ' + datatype + ': "' + SPARQL.escapeHtmlEntities(value) + '"', 'Validation failed');
          result = false;
        }
      }
      else{
        SPARQL.showMessageDialog('No validator defined for type "' + datatype + '"', 'Invalid validator');
        result = false;
      }

      return result;
    },

    validateAll: function(){
      var result = true;
      $('[validator]').each(function(index, value){
        if(!SPARQL.Validator.validate($(this).val(), $(this).attr('validator'))){
          $(this).addClass('failedValidation');
          result = false;
          return false;
        }
        else{
          $(this).removeClass('failedValidation');
        }
      });

      return result;
    },

    isIRI: function(string){
        var pattern = new RegExp(SPARQL.Validator.get('iri'));
        return pattern.test(string);
    },

    hasPointyBrackets: function(string){
      return (string.indexOf('<') === 0 && string.indexOf('>') === string.length - 1);
    }
  };

})(jQuery);

