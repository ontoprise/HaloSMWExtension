/*
 * Copyright (C) ontoprise GmbH
 *
 * Vulcan Inc. (Seattle, WA) and ontoprise GmbH (Karlsruhe, Germany)
 * expressly waive any right to enforce any Intellectual Property
 * Rights in or to any enhancements made to this program.
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

var SMW_HALO_VERSION = 'SMW_HALO_VERSION';
if (SMW_HALO_VERSION.InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {

  /**
   * Retrieve HTML presentation of the current selected range, require editor
   * to be focused first.
   */
  CKEDITOR.editor.prototype.getSelectedHtml = function()
  {
    var selection = this.getSelection();
    if( selection )
    {
      var bookmarks = selection.createBookmarks(),
      range = selection.getRanges()[ 0 ],
      fragment = range.clone().cloneContents();

      selection.selectBookmarks( bookmarks );

      var retval = "",
      childList = fragment.getChildren(),
      childCount = childList.count();
      for ( var i = 0; i < childCount; i++ )
      {
        var child = childList.getItem( i );
        retval += ( child.getOuterHtml?
          child.getOuterHtml() : child.getText() );
      }
      return retval;
    }
  };

  (function(){
    /**
     * Class which has the same functionality as SMWEditInterface except for the fact
     * that this must work for the CKEditor.
     * This class provides access to the edited text, returns a selection, changes
     * the content etc. The class is used in the semantic toolbar.
     */
    // global variable for the edit interface (connector class for the STB to the
    // current used editor)
    var gEditInterface;

    // these variables are used in the CKeditInterface class only. They should go
    // inside the class but for some reason the class is reinitiated and the data
    // in the member variables is lost
    // if set, contains the new text that will be inserted in the editor area
    var gEnewText = '',
    // the selection in the editor window may be null if selection is invalid.
    // When set, at least element 0 is set. All other are optional depending
    // on the selected element.
    // 0 => the selected text
    // 1 => namespace number (14 => Category, 102 => Property)
    // 2 => name of the property/category
    // 3 => value of the property
    // 4 => representation of the property
    gEselection = Array(),
    // the HTML element that contains the selected text
    gEselectedElement = null,
    // start and end for selection when in wikitext mode
    gEstart = -1,
    gEend = -1,
    // store here error message if selection can't be annotated
    gEerrMsgSelection = '',
    // puffer output before changing FCKtext
    gEoutputBuffering = false,
    gEeditor,
    gEflushedOnce;
	
    var gElastMouseUpEvent = null;
	
    var CKeditInterface = function( editor ) {
      gEeditor = editor;
    };
    CKeditInterface.prototype = {

      /**
       * Sets the annotation element (i.e. the span element that contains a
       * category or property annotation) that is currently being edited.
       *
       * @param {Object} annotationElement
       * 		The DOM element for the annotation.
       */
      setEditAnnotation: function (annotationElement) {
        this.editAnnotation = annotationElement;
      },
		
      /**
       * gets the selected string. This is the  simple string of a selected
       * text in the editor arrea.
       *
       * @access public
       * @return string selected text or null
       * */
      getSelectedText: function() {
        this.getSelectionAsArray();
        return (gEselection.length > 0) ? gEselection[0] : null;
      },

      /**
       * returns the error message, if a selection cannot be annotated
       *
       * @access public
       * @return string error message
       */
      getErrMsgSelection: function() {
        return gEerrMsgSelection;
      },
	  
      /**
       * Get the current selection of the FCKeditor and replace it with the
       * annotated value. This works on a category or property annotation only.
       * All other input is ignored and nothing will be replaced.
       *
       * @access public
       * @param  string text wikitext
       */
      setSelectedText: function(text) {
        // get the current editor instance
        var ckeditor = window.parent.wgCKeditorInstance;
        // check if start and end are set, then simply replace the selection
        // in the textarea
        if (ckeditor.mode != 'wysiwyg' && gEstart != -1 && gEend != -1) {
          var txtarea = ckeditor.getData();
          var newtext = txtarea.substr(0, gEstart) + text + txtarea.substr(gEend);
          this.clearSelection();
          HideContextPopup();
          this.setValue(newtext);
          return;
        }

        // WYSIWYG mode: Replace the existing annotation
        var oSpan = this.editAnnotation;
        var replaceElement = false;
        if (!oSpan) {
          // We are not changing an existing annotation.
          // The current selection will be annotated.
          oSpan = new CKEDITOR.dom.element( 'span', ckeditor.document );
          replaceElement = true;
        }
        this.editAnnotation = null;

        // check the param text, if it's valid wiki text for a property or
        // category information.
        // check property first
        var regex = new RegExp('^\\[\\[(.*?)::(.*?)(\\|(.*?))?\\]\\]$');
        var match = regex.exec(text);
        if (match) {
          oSpan.addClass('fck_mw_property');
          if (match[4]) {
            oSpan.setAttribute( 'property',  match[1] + '::' + match[2] );
            oSpan.setHtml( match[4] );
          }
          else {
            oSpan.setAttribute( 'property',  match[1] );
            oSpan.setHtml( match[2] );
          }
          if (oSpan.getHtml().length === 0)
            oSpan.setHtml('&nbsp;');
          // no match for property, check category next
        }
        else {
          regex = new RegExp('^\\[\\[' + window.parent.gLanguage.getMessage('CATEGORY_NS') + '(.*?)(\\|(.*?))?\\]\\]$');
          match = regex.exec(text);

          if (match) {
            oSpan.addClass('fck_mw_category') ;
            oSpan.setHtml(match[1]);
          }
          // no category neighter, something else (probably garbage) was in
          // the wikitext, then quit and do not modify the edited wiki page
          else
            return;
        }
        if (replaceElement) {
          if (ckeditor.mode == 'wysiwyg') {
            ckeditor.insertElement(oSpan);
          } else {
            ckeditor.insertElement(text);
          }
        }
		
        //        HideContextPopup();
      },
	
      setEditAreaName: function (ean) {
        // not needed in this implementation
      },

      /**
       * returns the text of the edit window. This is wiki text.
       * If gEnewText is set, then something on the text has changed but the
       * editarea is not yet updated with the new value. Therefore return this
       * instead of fetching the text (with still the old value) from the editor
       * area
       *
       * @access public
       * @return string wikitext of the editors textarea
       */
      getValue: function() {
        return (gEnewText) ? gEnewText : gEeditor.getData();
      },

      /**
       * set the text in the editor area completely new. The text in the function
       * argument is wiki text. Therefore an Ajax call must be done, to transform
       * this text into proper html what is needed by the FCKeditor. To parse the
       * wiki text, the parser of the FCK extension is used (the same when the
       * editor is started and when switching between wikitext and html).
       *
       * After parsing the text can be set in the editor area. This is done with
       * FCK.SetData(). When doing this all Event listeners are lost. Therefore
       * these must be added again. Also the variable gEnewText which contains
       * the new text (runtime issues), can be flushed again.
       * In this case the global variable gEditInterface.newText is used to get
       * the correct instance of the class.
       *
       * Since the Semantic toolbar changes text quite frequently, we enable some
       * kind of output buffering. If this is set (makes sence in the WYSIWYG mode
       * only) then the text is saved in an internal variable. When output
       * buffering is not selected, then the text is imediately written to the
       * editor.
       *
       * @access public
       * @param  string text with wikitext
       */
      setValue: function(text) {
        if (text) {
          if (gEeditor.mode == 'wysiwyg') {
            gEnewText = text;
            if (!gEoutputBuffering)
              this.flushOutputBuffer();
          }
          else {
            gEeditor.setData(text);
          }
        }
      },

      /**
       * returns the element were the selection is in.
       *
       * @access private
       * @return HtmlNode
       */
      getSelectedElement: function() {
        return gEselectedElement;
      },

      /**
       * gets the selected text of the current selection from the FCK
       * and fill up the member variable selection. This is an array of
       * maximum 4 elements which are:
       * 0 => selected text
       * 1 => namespace (14 = category, 102 = property) not existend otherwise
       * 2 => name of property or not set
       * 3 => actual value of property if sel. text is representation only not
       *      existend otherwise
       * If the selection is valid at least gEselection[0] must be set. The
       * selection is then returned to the caller
       *
       * @access public
       * @return Array(mixed) selection
       */
      getSelectionAsArray: function() {

        function handleSelectedSpan(gEeditor, element, selectedText){
          var resultArray = [],
          classAttribute = element.getAttribute('class');
          if(classAttribute == 'fck_mw_property'){
            //found property
            resultArray[0] = selectedText.toString();
            resultArray[1] = 102;
            var property = element.getAttribute('property');
            if (property.indexOf('::') > -1) {
              resultArray[2] = property.substring(0, property.indexOf('::'));
              resultArray[3] = property.substring(property.indexOf('::') +2);
            }
            else{
              resultArray[2] = property;
            }
          }

          else if(classAttribute == 'fck_mw_category'){
            //found category
            resultArray[0] = selectedText.toString();
            resultArray[1] = 14;
          }
         
          return resultArray;
        }

        // flush the selection array
        gEselection = [];
        // remove any previously set error messages
        gEerrMsgSelection = '';

        // (partly) selected text within these elements can be annotated.
        var goodNodes = ['P', 'B', 'I', 'U', 'S', 'LI', 'DT', 'DIV', 'SPAN'];

        // if we are in wikitext mode, return the selection of the textarea
        if (gEeditor.mode != 'wysiwyg' ) {
          this.getSelectionWikitext();
          return gEselection;
        }

        // selection text only without any html mark up etc.
        var selection = gEeditor.getSelection();
        if (selection) {
   
          var selectedHtml = gEeditor.getSelectedHtml();
          if (CKEDITOR.env.ie) {
            selection.unlock(true);
            var selectedText = selection.getNative().createRange().text;
          } else {
            var selectedText = selection.getNative();
          }

          if(selectedHtml && selectedText){
            if(selection.getSelectedElement()){
              //selected one element with no children e.g. <img/>
              gEselection[0] = '';
              return gEselection[0];
            }
            else{
              var element = CKEDITOR.dom.element.createFromHtml(selectedHtml);
              if(element && element.getOuterHtml && CKEDITOR.tools.trim(element.getOuterHtml()) === CKEDITOR.tools.trim(selectedHtml)){
                if(element.is('span')){
                  gEselection = handleSelectedSpan(gEeditor, element, CKEDITOR.tools.trim(selectedText.toString()));
                  return gEselection;
                }
              }
              if(selectedHtml == selectedText){
                //check if selected text is inside an annotation and if yes - select the whole annotation element
                if(element.type == CKEDITOR.NODE_TEXT && gEeditor.getSelection().getStartElement().is('span')){
                  element = gEeditor.getSelection().getStartElement();
                  gEselection = handleSelectedSpan(gEeditor, element, element.getText());
                  gEeditor.getSelection().selectElement(element);
                  return gEselection;
                }
                //allow only text inside specific elements to be annotated
                else if(element.type == CKEDITOR.NODE_TEXT && gEeditor.getSelection().getStartElement().getName().toUpperCase().InArray(goodNodes)){
                  gEselection[0] = CKEDITOR.tools.trim(selectedText.toString());
                  return gEselection;
                }                
              }

              gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_SELECTION_OVER_FORMATS');
              gEerrMsgSelection = gEerrMsgSelection.replace('$1', '').replace(/:\s+$/, '!');
              return null;
            }
          }
        }
      },
        
      

      //
      //
      //
      //
      //        // if the parent node is <a> or a <span> (property, category) then
      //        // we automatically select *all* of the inner html and the annotation
      //        // works for the complete node content (this is a must for these nodes)
      //        if (parent.$.nodeName.toUpperCase() == 'A') {
      //          gEselection[0] = parent.getText();
      //          gEselectedElement = parent;
      //          return gEselection;
      //        }
      //        // check category and property that might be in the <span> tag,
      //        // ignore all other spans that might exist as well
      //        if (parent.$.nodeName.toUpperCase() == 'SPAN') {
      //          if (parent.hasClass('fck_mw_property')) {
      //            gEselectedElement = parent;
      //            gEselection[0] = parent.getText().replace(/&nbsp;/gi, ' ');
      //            gEselection[1] = 102;
      //            var val = parent.getAttribute('property');
      //            // differenciation between displayed representation and
      //            // actual value of the property
      //            if (val.indexOf('::') != -1) {
      //              gEselection[2] = val.substring(0, val.indexOf('::'));
      //              gEselection[3] = val.substring(val.indexOf('::') +2);
      //            } else
      //              gEselection[2] = val;
      //            return gEselection;
      //          }
      //          else if (parent.hasClass('fck_mw_category')) {
      //            gEselectedElement = parent;
      //            gEselection[0] = parent.getText().replace(/&nbsp;/gi, ' ');
      //            gEselection[1] = 14;
      //            return gEselection;
      //          }
      //          gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_SELECTION_OVER_FORMATS');
      //          gEerrMsgSelection = gEerrMsgSelection.replace('$1', '&lt;span&gt;');
      //          return null;
      //        }
      //        // just any text was selected, use this one for the selection
      //        // if it was encloded between the "good nodes"
      //        for (var i = 0; i < goodNodes.length; i++) {
      //          if (parent.$.nodeName.toUpperCase() == goodNodes[i]) {
      //            gEselectedElement = parent;
      //            gEselection[0] = selTextCont.replace(/&nbsp;/gi, ' ');
      //            return gEselection;
      //          }
      //        }
      //        // selection is invalid
      //        gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_SELECTION_OVER_FORMATS');
      //        gEerrMsgSelection = gEerrMsgSelection.replace('$1', '&lt;' + parent.$.nodeName + '&gt;');
      //        return null;
      //
      //        // the selection is exactly one tag that encloses the selected text
      //        var ok = html.match(/^<[^>]*?>[^<>]*<\/[^>]*?>$/g);
      //        if (ok && ok.length == 1) {
      //          var tag = html.replace(/^<(\w+) .*/, '$1').toUpperCase();
      //          var cont = html.replace(/^<[^>]*?>([^<>]*)<\/[^>]*?>$/, '$1');
      //          // anchors are the same as formating nodes, we use the selected
      //          // node content as the value.
      //          goodNodes.push('A');
      //          for (i = 0; i < goodNodes.length; i++) {
      //            if (tag == goodNodes[i]) {
      //              this.MatchSelectedNodeInDomtree(parent, tag, cont);
      //              gEselection[0] = cont.replace(/&nbsp;/gi, ' ');
      //              return gEselection;
      //            }
      //          }
      //          // there are several span tags, we need to find categories and properties
      //          if (tag == 'SPAN') {
      //            if (html.indexOf('class="fck_mw_property"') != -1 ||
      //              html.indexOf('class=fck_mw_property') != -1)  // IE has class like this
      //              {
      //              this.MatchSelectedNodeInDomtree(parent, tag, cont);
      //              gEselection[0] = cont.replace(/&nbsp;/gi, ' ');
      //              gEselection[1] = 102;
      //              val = html.replace(/.*property="(.*?)".*/, '$1');
      //              if (val.indexOf('::') != -1) {
      //                gEselection[2] = val.substring(0, val.indexOf('::'));
      //                gEselection[3] = val.substring(val.indexOf('::') +2);
      //              } else {
      //                gEselection[2] = val;
      //              }
      //              return gEselection;
      //            }
      //            if (html.indexOf('class="fck_mw_category"') != -1 ||
      //              html.indexOf('class=fck_mw_property') != -1) {
      //              this.MatchSelectedNodeInDomtree(parent, tag, cont);
      //              gEselection[0] = cont.replace(/&nbsp;/gi, ' ');
      //              gEselection[1] = 14;
      //              return gEselection;
      //            } // below here passing all closing brakets means that the selection
      //          // was invalid
      //          }
      //          gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_SELECTION_OVER_FORMATS');
      //          gEerrMsgSelection = gEerrMsgSelection.replace('$1', '&lt;' + tag + '&gt;');
      //          return null;
      //        }
      //        gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_SELECTION_OVER_FORMATS');
      //        gEerrMsgSelection = gEerrMsgSelection.replace('$1', '').replace(/:\s+$/, '!');
      //        return null;
      //      },

      /**
       * from the parent node go over the child nodes and
       * select the appropriate child based on the string match that was
       * done before
       *
       * @access private
       * @param  DOMNode parent html element of the node (defined by name and value)
       * @param  string nodeName tag name
       * @param  string nodeValue content text of
       */
      MatchSelectedNodeInDomtree: function (parent, nodeName, nodeValue) {
        for(var i = 0; parent.childNodes && i < parent.childNodes.length; i++) {
          if (parent.childNodes[i].nodeType == 1 &&
            parent.childNodes[i].nodeName.toUpperCase() == nodeName &&
            parent.childNodes[i].innerHTML.replace(/^\s*/, '').replace(/\s*$/, '') == nodeValue) {
            gEselectedElement = parent.childNodes[i];
            return;
          }
        }
      },

      /**
       * Checks the current selection and returns the html content of the
       * selection.
       * i.e. select: "property value show" and the returned string would be:
       * <span class="fck_mw_property property="myName">property value shown</span>
       *
       * @access private
       * @return string html selection including surounding html tags
       */
      getSelectionHtml: function() {

        var ckSel = gEeditor.getSelection(),
        selection = ckSel.getNative();
        if(selection.createRange) {
          var range = selection.createRange();
          var html = range.htmlText;
        }
        else {
          range = selection.getRangeAt(selection.rangeCount - 1).cloneRange();
          var clonedSelection = range.cloneContents();
          var div = document.createElement('div');
          div.appendChild(clonedSelection);
          html = div.innerHTML;
          // check if selection contains a html tag of span or a
          // i.e. link, property, category, because in these cases
          // we must select all of the inner content.
          var lowerTags = html.toLowerCase();
          if (lowerTags.indexOf('<span') === 0 || lowerTags.indexOf('<a') === 0) {
            // even though in the original the tag look like <span property...>This is my property rep</span>
            // the selected html might contain <span property...>property rep</span> only. To select all make
            // a text match of the content of the ancestor tag content.
            var parentContent = selection.getRangeAt(selection.rangeCount -1).commonAncestorContainer.innerHTML;
            // build a pattern of <span property...>property rep</span>
            var pattern = html.replace(/([().|?*{}\/])/g, '\\$1');
            pattern = pattern.replace('>', '>.*?');
            pattern = pattern.replace('<\\/', '.*?<\\/');
            pattern = '(.*)(' + pattern + ')(.*)';
            // the pattern is now: (.*)(<span property\.\.\.>.*?property rep.*?<\/span>)(.*)
            var rex = new RegExp(pattern);
            if (rex instanceof RegExp)
              html = parentContent.replace(rex, '$2');
          }
        }
        return html.replace(/^\s*/, '').replace(/\s*$/, ''); // trim the selected text
      },

      /**
       * If in wikitext mode, this function gets the seleced text and must also
       * select the complete annotation if the selection is inside of [[ ]]. Also
       * selections inside templates etc. must be ignored.
       * Evaluated parameter will be stored in variable gEselection.
       *
       * @access private
       * @see    getSelectionAsArray() for details on the selection
       */
      getSelectionWikitext: function() {
        // selected text by the user in the textarea
        var selection = this.getSelectionFromTextarea();
        if (selection.length === 0) { // nothing selected
          gEselection[0] = "";
          return null;
        }

        // complete text from the editing area
        var txt = gEeditor.getData();

        var p; // position marker for later

        var currChar; // current character that is observed

        // start look at the left side of the selection for any special character
        var currPos = gEstart;
        var stopper = -1;
        while (currPos > 0) {
          // go back one position in text area string.
          currPos--;
          currChar = txt.substr(currPos, 1);
          // "[" found, move the selection start there if there are two of them
          if (currChar == '[') {
            // one [ is in the selection and we didn't run over ] yet
            if (selection.substr(0, 1) == '[' && stopper < currPos) {
              gEstart = currPos;        // is at position, stop here
            }
            else if (currPos > 0 &&
              txt.substr(currPos -1, 1) == '[' &&
              stopper < currPos) {         // previous pos
              gEstart = currPos - 1;         // also contains a [
              currPos--;
            }
          }
          // "]" found, stop looking further if there are two of them
          if (currChar == ']' && currPos > 0 && txt.substr(currPos -1, 1) == ']') {
            stopper = currPos;
            currPos--;
          }
          // ">" found, check it's the end of a tag, and if so, which type of tag
          if (currChar == '>') {
            // look for the corresponding <
            p = txt.substr(0, currPos).lastIndexOf('<');
            if (p == -1) continue; // no < is there, just go ahead
            var tag = txt.substr(p, currPos - p + 1);
            if (tag.match(/^<[^<>]*>$/)) { // we really found a tag
              if (tag[1] == '/')  // we are at the end of a closing tag
                break;          // stop looking any further back
              else if (tag.match(/\s*>$/)) // it's a <tag />, stop here as well
                break;
            }
          }
          // maybe we are inside a tag and found it's begining. Check that
          if (currChar == '<') {
            // look for the corresponding >
            p = selection.indexOf('>');
            if (p == -1) continue;
            tag = txt.substr(currPos, gEstart + p + 1 - currPos);
            if (tag.match(/^<[^<>]*>$/)) { // we really found a tag
              gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_NOT_IN_TAG');
              gEerrMsgSelection = gEerrMsgSelection.replace('$1', txt.substr(gEstart, gEend - gEstart));
              gEerrMsgSelection = gEerrMsgSelection.replace('$2', tag);
              return this.clearSelection();
            }
          }
          // we are inside a template or parser function or whatever
          if (currChar == '{' && currPos > 0 && txt.substr(currPos - 1, 1) == '{') {
            gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_NOT_IN_TEMPLATE');
            gEerrMsgSelection = gEerrMsgSelection.replace('$1', txt.substr(gEstart, gEend - gEstart));
            return null;
          }
          // end of a template or parser function found, stop here
          if (currChar == '}' && currPos > 0 && txt.substr(currPos - 1, 1) == '}')
            break;
        }

        // adjust selection if we moved the start position
        selection = txt.substr(gEstart, gEend - gEstart);

        // look for any special character at the right side of the selection
        currPos = gEend - 1;
        stopper = txt.length;
        while (currPos < txt.length - 2) {
          // move the possition one step forward in the string
          currPos++;
          currChar = txt.substr(currPos, 1);
          // if we find an open braket, move the selection start there
          if (currChar == ']') {
            if (selection.substr(selection.length - 1, 1) == ']' && stopper > currPos) {
              gEend = currPos + 1;
            }
            else if (currPos < txt.length - 1 && txt.substr(currPos + 1, 1) == ']' && stopper > currPos) {
              currPos++;
              gEend = currPos + 1;
            }
          }
          // "[" found, stop looking further if there are two of them
          if (currChar == '[' && currPos < txt.length - 1 && txt.substr(currPos + 1, 1) == '[') {
            currPos++;
            stopper = currPos;
          }
          // we are inside a template or parser function or whatever
          if (currChar == '}' && currPos < txt.length - 1 && txt.substr(currPos + 1, 1) == '}') {
            gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_NOT_IN_TEMPLATE');
            gEerrMsgSelection = gEerrMsgSelection.replace('$1', txt.substr(gEstart, gEend - gEstart));
            return null;
          }
          // we are facing the begining of a template or parser function, stop here
          if (currChar == '{' && currPos < txt.length - 1 && txt.substr(currPos + 1, 1) == '{')
            break;

          // "<" found, check it's the start of a tag, and if so, which type of tag
          if (currChar == '<') {
            // look for the corresponding >
            p = txt.substr(currPos).indexOf('>');
            if (p == -1) continue; // no > is there, just go ahead
            tag = txt.substr(currPos, p + 1);
            if (tag.match(/^<[^<>]*>$/)) { // we really found a tag
              if (tag.substr(1, 1) == '/') {  // we are at the end of a closing tag
                gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_NOT_IN_TAG');
                gEerrMsgSelection = gEerrMsgSelection.replace('$1', txt.substr(gEstart, gEend - gEstart));
                gEerrMsgSelection = gEerrMsgSelection.replace('$2', tag);
                return this.clearSelection(); // stop looking any further and quit
              }
              else if (tag.match(/\s*>$/)) // it's a <tag />, stop here as well
                break;
            }
          }
          // maybe we are inside a tag and found it's end. Check that
          if (currChar == '>') {
            // look for the corresponding <
            p = selection.lastIndexOf('<');
            if (p == -1) continue;
            tag = txt.substr(gEstart + p, currPos - gEstart - p + 1);
            if (tag.match(/^<[^<>]*>$/)) { // we really found a tag
              gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_NOT_IN_TAG');
              gEerrMsgSelection = gEerrMsgSelection.replace('$1', txt.substr(gEstart, gEend - gEstart));
              gEerrMsgSelection = gEerrMsgSelection.replace('$2', tag);
              return this.clearSelection();
            }
          }
        }
        // adjust selection if we moved the end position
        selection = txt.substr(gEstart, gEend - gEstart);

        // trim the selection
        selection = selection.replace(/^\s*/, '').replace(/\s+$/, '');
        gEselection[0] = selection;

        // now investigate the selected text and fill up the gEselection array

        // check for a property
        var regex = new RegExp('^\\[\\[(.*?)::(.*?)(\\|(.*?))?\\]\\]$');
        var match = regex.exec(selection);
        if (match) {
          gEselection[0] = match[2];
          gEselection[3] = match[2];
          gEselection[1] = 102;
          gEselection[2] = match[1];
          if (match[4])
            gEselection[0] = match[4];
          return null;
        }
        // check for a category
        regex = new RegExp('^\\[\\[' + window.parent.gLanguage.getMessage('CATEGORY_NS') + '(.*?)(\\|(.*?))?\\]\\]$');
        match = regex.exec(selection);
        if (match) {
          gEselection[1] = 14;
          gEselection[0] = match[1];
          return null;
        }
        // link
        regex = new RegExp('^\\[\\[:?(.*?)(\\|(.*?))?\\]\\]$');
        match = regex.exec(selection);
        if (match) {
          gEselection[0] = match[1];
          return null;
        }
        // check if there are no <tags> in the selection
        if (selection.match(/.*?(<\/?[\d\w:_-]+(\s+[\d\w:_-]+="[^<>"]*")*\s*(\/\s*)?>)+.*?/)) {
          gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_SELECTION_OVER_FORMATS');
          gEerrMsgSelection = gEerrMsgSelection.replace('$1', '').replace(/:\s+$/, '!');
          return this.clearSelection();
        }
        // if there are still [[ ]] inside the selection then more that a
        // link was selected making this selection invalid.
        if (selection.indexOf('[[') != -1 || selection.indexOf(']]') != -1 ) {
          gEerrMsgSelection = window.parent.gLanguage.getMessage('CAN_NOT_ANNOTATE_SELECTION');
          return this.clearSelection();
        }
        // if there are still {{ }} inside the selection then template or parser function
        // is inside the selection, make it invalid
        if (selection.indexOf('{{') != -1 || selection.indexOf('}}') != -1 ) {
          gEerrMsgSelection = window.parent.gLanguage.getMessage('WTP_NOT_IN_TEMPLATE');
          gEerrMsgSelection = gEerrMsgSelection.replace('$1', txt.substr(gEstart, gEend - gEstart));
          return this.clearSelection();
        }



        // finished, assuming the selection is good without any further modifying.
        return null;
      },

      /**
       * Retrieve the selected text from the textarea when in wikitext mode.
       * If nothing is selected an empty string will be returned. Return value is
       * the selected text within the text area. If selection is not empty, then
       * gEstart and gEend are not -1.
       *
       * @access private
       * @return string selection
       */
      getSelectionFromTextarea: function() {

        var myArea = CKEditorTextArea( gEeditor ),
        selection = '';

        if ( CKEDITOR.env.ie ) {
          if (document.selection) {
            // The current selection
            var range = document.selection.createRange();
            // We'll use this as a 'dummy'
            var stored_range = range.duplicate();
            // Select all text
            stored_range.moveToElementText( myArea );
            // Now move 'dummy' end point to end point of original range
            stored_range.setEndPoint( 'EndToEnd', range );
            // Now we can calculate start and end points
            myArea.selectionStart = stored_range.text.length - range.text.length;
            myArea.selectionEnd = myArea.selectionStart + range.text.length;
          }
        }
        if (myArea.selectionStart != undefined) {
          gEstart = myArea.selectionStart;
          gEend = myArea.selectionEnd;
          selection = myArea.value.substr(gEstart, gEend - gEstart);
        }
        return selection;
      },

      /**
       * Make a previously selected text invalid, remove all markers in the
       * variables
       *
       * @access private
       */
      clearSelection: function() {
        gEstart = -1;
        gEend = -1;
        gEselection = Array();
        return null;
      },

      // not needed but exists for compatiblity reasons
      setSelectionRange: function(start, end) {},
      // not needed but exists for compatiblity reasons
      getTextBeforeCursor: function() {},
      // not needed but exists for compatiblity reasons. The handling of selecting
      // the complete annotation is done in the getSelectionAsArray()
      selectCompleteAnnotation: function() {},

      focus: function() {
        gEeditor.focus();
      },

      /**
       *  enable output buffering. Text is not imediately written to the text
       *  area of the editor window. Changes are collected in the newText variable
       *  and then written once only to the editor area.
       *
       *  @access public
       */
      setOutputBuffer: function() {
        gEoutputBuffering = true;
      },


      /**
       *  flush the output buffer. Text is now written to the text area of the
       *  FCK editor. See the documentation of setValue() for a detailed
       *  documentation of the whole process.
       *
       *  @access public
       */
      flushOutputBuffer: function() {
        gEflushedOnce= false;
        if (gEflushedOnce) {
          function ajaxResponseSetHtmlText(request) {
            if (request.status == 200) {
              // success => store wikitext as FCK HTML
              gEeditor.setData(request.responseText);

            }
            gEditInterface.newText = '';
            gEditInterface.outputBuffering = false;
            gEoutputBuffering = false;
          }
          window.parent.sajax_do_call('wfSajaxWikiToHTML', [gEnewText],
          ajaxResponseSetHtmlText);
          return;
        }
        gEflushedOnce = true;
        gEeditor.setData(gEnewText);
        gEditInterface.outputBuffering = false;
        gEoutputBuffering = false;
      }

    };


    // global variable for the context menu itself
    var ckePopupContextMenu;

    /**
     * Create a new context menu for annotating a selection that is not yet annotated.
     * Both property and category container will be shown.
     *
     * @param Event event
     * @param string value selected text
     */
    ShowNewToolbar = function(event, value) {
      var wtp = new window.parent.WikiTextParser();
      ckePopupContextMenu = new window.parent.ContextMenuFramework();
      if (!ckePopupContextMenu.wasDragged()) {
        var pos = CalculateClickPosition(event);
        ckePopupContextMenu.setPosition(pos[0], pos[1]);
      }
      //        var relToolBar = new window.parent.RelationToolBar();
      var relToolBar = window.parent.relToolBar;
      //        var catToolBar = new window.parent.CategoryToolBar();
      var catToolBar = window.parent.catToolBar;
      //        window.contextMenuRelToolBar = relToolBar;
      //        window.catToolBar = new window.parent.CategoryToolBar();
      relToolBar.setWikiTextParser(wtp);
      catToolBar.setWikiTextParser(wtp);
      relToolBar.createContextMenu(ckePopupContextMenu, value, value, value, HideContextPopup);
      catToolBar.createContextMenu(ckePopupContextMenu, value, false, HideContextPopup);
      ckePopupContextMenu.showMenu();
    };

    /**
     * Create a new context menu for annotating a property.
     * Only the property container will be shown.
     * The selected text is the representation at least. If value and represenation
     * are equal then the selected text is the value as well.
     *
     * @param Event event
     * @param string name of the property
     * @param string value of the property
     * @param string representation of the property
     */
    ShowRelToolbar = function(event, name, value, show) {
      var wtp = new window.parent.WikiTextParser();
      ckePopupContextMenu = new window.parent.ContextMenuFramework();
      if (!ckePopupContextMenu.wasDragged()) {
        var pos = CalculateClickPosition(event);
        ckePopupContextMenu.setPosition(pos[0], pos[1]);
      }
      var toolBar = window.parent.relToolBar;
      toolBar.setWikiTextParser(wtp);
      toolBar.createContextMenu(ckePopupContextMenu, value, show, name, HideContextPopup);
      ckePopupContextMenu.showMenu();
    };

    ShowRelToolbarByOffset = function(element, propertyName, propertyValue, displayedText){
      var wtp = new window.parent.WikiTextParser();
      ckePopupContextMenu = new window.parent.ContextMenuFramework();
      if (!ckePopupContextMenu.wasDragged()) {
        var pos = CalculateClickPosition(event);
        ckePopupContextMenu.setPosition(pos[0], pos[1]);
      }
      var toolBar = window.parent.relToolBar;
      toolBar.setWikiTextParser(wtp);
      toolBar.createContextMenu(ckePopupContextMenu, propertyValue, displayedText, propertyName);
      ckePopupContextMenu.showMenu();
    };

    /**
     * Create a new context menu for annotating a category.
     * Only the category container will be shown.
     * The selected text is the category name.
     *
     * @param Event event
     * @param string name selected text
     * @param bool editCategory
     * 	If true, an existing category is to be edited
     */
    ShowCatToolbar = function(event, name, editCategory) {
      var wtp = new window.parent.WikiTextParser();
      ckePopupContextMenu = new window.parent.ContextMenuFramework();
      if (!ckePopupContextMenu.wasDragged()) {
        var pos = CalculateClickPosition(event);
        ckePopupContextMenu.setPosition(pos[0], pos[1]);
      }
      var toolBar = window.parent.catToolBar;
      toolBar.setWikiTextParser(wtp);
      toolBar.createContextMenu(ckePopupContextMenu, name, editCategory, HideContextPopup);
      ckePopupContextMenu.showMenu();
    };

    ShowCatToolbarByOffset = function(element, name) {
      var wtp = new window.parent.WikiTextParser();
      ckePopupContextMenu = new window.parent.ContextMenuFramework();
      if (!ckePopupContextMenu.wasDragged()) {
        var pos = CalculateClickPosition(event);
        ckePopupContextMenu.setPosition(pos[0], pos[1]);
      }
      var toolBar = window.parent.catToolBar;
      toolBar.setWikiTextParser(wtp);
      toolBar.createContextMenu(ckePopupContextMenu, name);
      ckePopupContextMenu.showMenu();
    };




    /**
     * Calculate correct x and y coordinates of event in browser window
     *
     * @param Event event
     * @return Array(int, int) coordinates x, y
     */
    CalculateClickPosition = function(event) {
      var offset = GetOffsetFromOuterHtml();
      var pos = [];

      pos[0] = offset[0] + event.clientX;
      pos[1] = offset[1] + event.clientY;

      var sx;
      var sy;
      if (CKEDITOR.env.ie) {
        sx = (window.parent.document.documentElement.scrollLeft) ? window.parent.document.documentElement.scrollLeft : window.parent.document.body.scrollLeft;
        sy = (window.parent.document.documentElement.scrollTop) ? window.parent.document.documentElement.scrollTop : window.parent.document.body.scrollTop;
      }
      else {
        sx = window.parent.pageXOffset;
        sy = window.parent.pageYOffset;
      }
      if (sx > 0 && sx < pos[0]) pos[0] -= sx;
      if (sy > 0 && sy < pos[1]) pos[1] -= sy;

      return pos;
    };


    CalculateElementPosition = function(element) {
      var offset = GetOffsetFromOuterHtml();
      var pos = [0, 0];
    
      if(element.$){
        pos[0] = offset[0] + element.$.offsetTop;
        pos[1] = offset[1] + element.$.offsetLeft;
      }
      else if(jQuery(element).offset()){
        pos[0] = offset[0] + jQuery(element).offset().top;
        pos[1] = offset[1] + jQuery(element).offset().left;
      }

      var sx;
      var sy;
      if (CKEDITOR.env.ie) {
        sx = (window.parent.document.documentElement.scrollLeft) ? window.parent.document.documentElement.scrollLeft : window.parent.document.body.scrollLeft;
        sy = (window.parent.document.documentElement.scrollTop) ? window.parent.document.documentElement.scrollTop : window.parent.document.body.scrollTop;
      }
      else {
        sx = window.parent.pageXOffset;
        sy = window.parent.pageYOffset;
      }
      if (sx > 0 && sx < pos[0]) pos[0] -= sx;
      if (sy > 0 && sy < pos[1]) pos[1] -= sy;

      return pos;
    };

    /**
     * get offset from elements around the iframe
     *
     * @access public
     * @return array(int, int) offsetX, offsetY
     */
    GetOffsetFromOuterHtml = function() {
      var id = (window.parent.wgAction == "formedit") ? 'cke_free_text' : 'editform';
      var el = window.parent.document.getElementById(id);
      var offset = [];
      var editorName = window.parent.wgCKeditorInstance.name;
      offset[0] = 0; // x coordinate
      // y ccordinate gets hight of CKeditor toolbar added
      offset[1] = document.getElementById('cke_top_'+editorName).offsetHeight;
      offset[1] += 1;

      if (el.offsetParent) {
        do {
          offset[0] += el.offsetLeft;
          offset[1] += el.offsetTop;
        } while ((el = el.offsetParent));
      }
      return offset;
    };

    /**
     * reomove the context menu from the DOM tree
     */
    HideContextPopup = function() {
      if (ckePopupContextMenu) {
        ckePopupContextMenu.remove();
        ckePopupContextMenu = null;
      }
      // AC selection if there, remove it as well
      if (window.parent.autoCompleter) {
        window.parent.autoCompleter.hideSmartInputFloater();
      }
      window.parent.smwhgAnnotationHints.hideHints();
    };

    /**
     * Get the current frame with the wikipage. Skip the iframe from YUI (this
     * one has set an id).
     * This function should be more robust but's working for now.
     */
    GetWindowOfEditor = function() {
      var frame;
      for (var i = 0; i < window.frames.length; i++) {
        if (window.frames[i].frameElement.id)
          continue;
        frame = window.frames[i];
        break;
      }
      return frame;
    };

    /**
     * Invokes the tagging floatbox with the current selection for adding a
     * property annotation.
     *
     * @param editor
     * 		The CKeditor object
     */
    AddPropertyAnnotation = function (editor) {
      if (!gEditInterface) {
        plugin.createEditInterface(editor);
      }
      var selection = gEditInterface.getSelectionAsArray();
      if (selection && selection.length >= 1) {
        var show = selection[0];
        var val = show;
        ShowRelToolbar(plugin.mLastMoveEvent, '', val, show);
      }
		
    }

    /**
     * Invokes the tagging floatbox for a property annotation.
     *
     * @param editor
     * 		The CKeditor object
     * @param {Object} annotationElement
     * 		This DOM element contains the annotation.
     */
    EditPropertyAnnotation = function (editor, annotationElement) {
      var aclass = annotationElement.getAttribute('class');
      if (aclass !== 'fck_mw_property') {
        // invalid element
        return;
      }
      if (!gEditInterface) {
        plugin.createEditInterface(editor);
      }
      gEditInterface.setEditAnnotation(annotationElement);
      var property = annotationElement.getAttribute('property');
      // The property may consist of property and value if separated by ::
      if (property.indexOf('::') > 0) {
        // Property and value
        var propVal = property.split('::');
        var propertyName = propVal[0];
        var value = propVal[1];
        var show = annotationElement.getText();
        ShowRelToolbar(plugin.mLastMoveEvent, propertyName, value, show);
      } else {
        // 'property' contains only the property name
        var propertyName = property;
        var value = annotationElement.getText();
        ShowRelToolbar(plugin.mLastMoveEvent, propertyName, value, value);
      }
		
    }
	
    /**
     * Invokes the tagging floatbox with the current selection for adding a
     * category annotation.
     *
     * @param editor
     * 		The CKeditor object
     */
    AddCategoryAnnotation = function (editor) {
      if (!gEditInterface) {
        plugin.createEditInterface(editor);
      }
      var selection = gEditInterface.getSelectionAsArray();
      if (selection && selection.length >= 1) {
        ShowCatToolbar(plugin.mLastMoveEvent,  selection[0], false);
      }
		
    }

    /**
     * Invokes the tagging floatbox for a category annotation.
     *
     * @param editor
     * 		The CKeditor object
     * @param {Object} annotationElement
     * 		This DOM element contains the annotation.
     */
    EditCategoryAnnotation = function (editor, annotationElement) {
      var aclass = annotationElement.getAttribute('class');
      if (aclass !== 'fck_mw_category') {
        // invalid element
        return;
      }
      if (!gEditInterface) {
        plugin.createEditInterface(editor);
      }
				
      var cat = annotationElement.getText();
      gEditInterface.setEditAnnotation(annotationElement);
      ShowCatToolbar(plugin.mLastMoveEvent, cat, true);
    }

    var CKEditorTextArea = function(editor) {
      //      return document.getElementById('cke_contents_' + editor.name).getElementsByTagName('textarea')[0];
      return jQuery('#cke_contents_' + editor.name).find('textarea').first();

    };

    CKEDITOR.plugins.smwtoolbar = {
      stbIsActive : false,
      stbEditorText : '',
      mLastMoveEvent : null,
      EnableAnnotationToolbar : function( editor ) {
        this.stbIsActive = true;
        window.parent.stb_control.initialize();
        window.parent.stb_control.initToolbarFramework();
        window.parent.stb_control.onCloseButtonClick('wgCKeditorInstance.execCommand(\'SMWtoolbar\')');
        // enable draging
        window.parent.smwhg_dragresizetoolbar.draggable=null;
        window.parent.smwhg_dragresizetoolbar.callme();
        this.SetEventHandler4AnnotationBox( editor );
        editor.getCommand('SMWtoolbar').setState(CKEDITOR.TRISTATE_ON);
      },
      DisableAnnotationToolbar: function( editor ) {
        this.stbIsActive = false;
        HideContextPopup();
        window.parent.stb_control.closeToolbar();
        this.ClearEventHandler4AnnotationBox(editor);
        editor.getCommand('SMWtoolbar').setState(CKEDITOR.TRISTATE_OFF);
      },
      EditorareaChanges : function() {
        if (! this.stbIsActive) return;
        var editorData = this.editor.getData();
        if (this.stbEditorText != editorData) {
          window.parent.relToolBar.fillList();
          window.parent.catToolBar.fillList();
          this.stbEditorText = editorData;
        }
      },
      SetEventHandler4AnnotationBox : function (editor) {
        //        var element = CKEDITOR.document.getById('cke_contents_' + editor.name);
        this.editor = editor;
        if ( editor.mode == 'wysiwyg' ) {
          var frame = GetWindowOfEditor();
          if (CKEDITOR.env.ie) {
            var iframe = frame;
            var iframeDocument = iframe.document || iframe.contentDocument;
            iframeDocument.onkeyup = this.EditorareaChanges.bind(this);
          } else {
            window.parent.Event.observe(frame, 'keyup', this.EditorareaChanges.bind(this));
          }
          //            window.parent.obContributor.activateTextArea(frame);
        } else {
          var Textarea = CKEditorTextArea(editor);
          window.parent.Event.observe(Textarea, 'keyup', this.EditorareaChanges.bind(this));
          window.parent.obContributor.activateTextArea(Textarea);
        }
      },
      ClearEventHandler4AnnotationBox : function(editor) {
        this.editor = editor;
        if ( editor.mode == 'wysiwyg' ) {
          if (CKEDITOR.env.ie) {
            var iframe = window.frames[0];
            var iframeDocument = iframe.document || iframe.contentDocument;
            iframeDocument.onkeyup = null;
            iframeDocument.onmouseup = null;
            iframeDocument.onmousedown = null;
            iframeDocument.onmousemove = null;
          } else {
            window.parent.Event.stopObserving(window.frames[0], 'keyup', this.EditorareaChanges);
          }
        } else {
          var Textarea = CKEditorTextArea(editor);
          window.parent.Event.stopObserving(Textarea, 'keyup', this.EditorareaChanges);
        }
      },
      RegisterMouseTracker: function (editor) {
        // In the WYSIWYG mode we have to keep track of the current mouse position.
        // The last mouse move event is stored in plugin.mLastMoveEvent
        if (editor.mode == 'wysiwyg') {
          var iframe = GetWindowOfEditor();
          if (CKEDITOR.env.ie) {
            var iframeDocument = iframe.document || iframe.contentDocument;
            iframeDocument.onmousemove = function(){
              plugin.mLastMoveEvent = iframe.event;
            };
          }
          else {
            window.parent.Event.observe(iframe, 'mousemove', function(event){
              plugin.mLastMoveEvent = event;
            });
          }
        }
      },
      loadToolbar : function ( editor ) {
        if (!this.stbIsActive) {          
          this.createEditInterface(editor);
          this.EnableAnnotationToolbar(editor);
        }
      },
      closeToolbar: function(editor){
        if (this.stbIsActive) {
          if (CKEDITOR.env.ie) {
            window.parent.gEditInterface = null;
            gEditInterface = null;
          }
          else {
            delete gEditInterface;
            delete window.parent.gEditInterface;
          }
          this.DisableAnnotationToolbar(editor);
        }
      },
      toggleToolbar: function(editor){
        if (this.stbIsActive) {
          this.closeToolbar(editor);
        }
        else{
          this.loadToolbar(editor);
        }
      },
      createEditInterface : function(editor) {
        gEditInterface = new CKeditInterface(editor);
        window.parent.gEditInterface = gEditInterface;
      }

    };

    var plugin = CKEDITOR.plugins.smwtoolbar;
    var commandDefinition = {
      preserveState : false,
      editorFocus : false,
      canUndo : false,
      modes : {
        wysiwyg : 1,
        source : 1
      },

      exec: function( editor )
      {
        plugin.toggleToolbar( editor );
      }
    };
    
    var openCommandDefinition = {
      preserveState : false,
      editorFocus : false,
      canUndo : false,
      modes : {
        wysiwyg : 1,
        source : 1
      },

      exec: function( editor )
      {
        plugin.loadToolbar( editor );
      }
    };

    var closeCommandDefinition =
      {
      preserveState : false,
      editorFocus : false,
      canUndo : false,
      modes : {
        wysiwyg : 1,
        source : 1
      },

      exec: function( editor )
      {
        plugin.closeToolbar( editor );
      }
    };

    CKEDITOR.plugins.add('smwtoolbar', {

      requires : [ 'mediawiki', 'editingblock' ],

      beforeInit : function( editor ) {
        // disable STB by default when loading the editor
        if (window.parent.stb_control) {
          window.parent.stb_control.closeToolbar();
        }
      },

      init : function( editor )
      {
        if (editor.contextMenu)
        {
          var editPropertyCommmand =
            {
            preserveState : false,
            editorFocus : true,
            canUndo : true,
            modes : {
              wysiwyg : 1,
              source : 1
            },

            exec: function( editor )
            {
              EditPropertyAnnotation(editor, editPropertyCommmand.element);
            }
          };
			
          var editCategoryCommmand =
            {
            preserveState : false,
            editorFocus : true,
            canUndo : true,
            modes : {
              wysiwyg : 1,
              source : 1
            },

            exec: function( editor )
            {
              EditCategoryAnnotation(editor, editCategoryCommmand.element);
            }
          };
          var addPropertyCommmand =
            {
            preserveState : false,
            editorFocus : true,
            canUndo : true,
            modes : {
              wysiwyg : 1,
              source : 1
            },
        		  
            exec: function( editor )
            {
              AddPropertyAnnotation(editor);
            }
          };
          
          var addCategoryCommmand =
            {
            preserveState : false,
            editorFocus : true,
            canUndo : true,
            modes : {
              wysiwyg : 1,
              source : 1
            },
        		  
            exec: function( editor )
            {
              AddCategoryAnnotation(editor);
            }
          };
          var removePropertyCommmand =
            {
            preserveState : false,
            editorFocus : true,
            canUndo : true,
            modes : {
              wysiwyg : 1,
              source : 1
            },

            exec: function( editor )
            {
              removePropertyCommmand.element.remove();
              plugin.EditorareaChanges();
            }
          };
			
          var removeCategoryCommmand =
            {
            preserveState : false,
            editorFocus : true,
            canUndo : true,
            modes : {
              wysiwyg : 1,
              source : 1
            },

            exec: function( editor )
            {
              removeCategoryCommmand.element.remove();
              plugin.EditorareaChanges();
            }
          };
          editor.addCommand( 'editProperty', editPropertyCommmand);
          editor.addCommand( 'editCategory', editCategoryCommmand);
          editor.addCommand( 'removeProperty', removePropertyCommmand);
          editor.addCommand( 'removeCategory', removeCategoryCommmand);
          editor.addMenuGroup( 'mediawiki' );
          editor.addMenuItem( 'editPropertyItem',
          {
            label : 'Edit Property',
            command : 'editProperty',
            group : 'mediawiki'
          });
			
          editor.addMenuItem( 'editCategoryItem',
          {
            label : 'Edit Category',
            command : 'editCategory',
            group : 'mediawiki'
          });
          editor.addMenuItem( 'removePropertyItem',
          {
            label : 'Remove Property',
            command : 'removeProperty',
            group : 'mediawiki'
          });
			
          editor.addMenuItem( 'removeCategoryItem',
          {
            label : 'Remove Category',
            command : 'removeCategory',
            group : 'mediawiki'
          });
			
          editor.contextMenu.addListener( function( element )
          {
            if (element.getAttribute('class') === 'fck_mw_category'){
              editCategoryCommmand.categoryName = element.getAttribute('sort');
              editCategoryCommmand.element = element;
              removeCategoryCommmand.element = element;
              return {
                removeCategoryItem: CKEDITOR.TRISTATE_ON,
                editCategoryItem  : CKEDITOR.TRISTATE_ON
              };
            }
            else if (element.getAttribute('class') === 'fck_mw_property'){
              editPropertyCommmand.element = element;
              removePropertyCommmand.element = element;
              return {
                removePropertyItem: CKEDITOR.TRISTATE_ON,
                editPropertyItem  : CKEDITOR.TRISTATE_ON
              };
            }
            return null;
          });

        }
		
        if ( editor.ui.addButton ) {
          editor.addCommand( 'SMWtoolbarOpen', openCommandDefinition);
          editor.addCommand( 'SMWtoolbarClose', closeCommandDefinition);
          editor.addCommand( 'SMWtoolbar', commandDefinition);
        	
          editor.ui.addButton( 'SMWtoolbar',
          {
            label : 'Data Toolbar',
            command : 'SMWtoolbar',
            icon: this.path + 'images/icon_STB.gif',
            title: 'Data Toolbar'
          });
          editor.getCommand('SMWtoolbar').setState(CKEDITOR.TRISTATE_OFF);
        
          editor.addCommand( 'SMWAddProperty', addPropertyCommmand);
          editor.ui.addButton( 'SMWAddProperty',
          {
            label : 'Add Property',
            command : 'SMWAddProperty',
            icon: this.path + 'images/icon_property.gif',
            title: 'Add Property'
          });
          editor.getCommand('SMWAddProperty').setState(CKEDITOR.TRISTATE_OFF);
        	
          editor.addCommand( 'SMWAddCategory', addCategoryCommmand);
          editor.ui.addButton('SMWAddCategory', {
            label : 'Add Category',
            command : 'SMWAddCategory',
            icon : this.path + 'images/icon_category.gif',
            title : 'Add Category'
          });
          editor.getCommand('SMWAddCategory').setState(CKEDITOR.TRISTATE_OFF);
        	
        }
        
        
        // disable toolbar when switching mode
        editor.on( 'beforeCommandExec', function( ev ) {
          if ( !plugin.stbIsActive )
            return;
				
          if ( ( ev.data.name == 'source' || ev.data.name == 'newpage' ) && editor.mode == 'wysiwyg' ) {
            plugin.DisableAnnotationToolbar( editor );
          }
          if ( ( ev.data.name == 'wysiwyg' || ev.data.name == 'newpage' ) && editor.mode == 'source' ) {
            plugin.DisableAnnotationToolbar( editor );
          }
        });

        editor.on("dataReady", function(event) {
          if (plugin.stbIsActive) {
            gEnewText='';
            delete gEditInterface;
            if (CKEDITOR.env.ie) {
              window.parent.gEditInterface = null;
            } else {
              delete window.parent.gEditInterface;
            }
            gEditInterface = new CKeditInterface(editor);
            window.parent.gEditInterface = gEditInterface;
            plugin.SetEventHandler4AnnotationBox(editor);
          }
          // Always keep track of the last mouse position for the annotation
          // context menu
          plugin.RegisterMouseTracker(editor);
            
        });
        editor.on("resize", function(event) {
          if (plugin.stbIsActive) {
            var ontomenu = window.parent.document.getElementById('ontomenuanchor');
            // I have no clue how to know in which mode we are, so just set the z-index to some
            // value that works in both modes
            ontomenu.style.zIndex = editor.config.baseFloatZIndex + 10;
          }
        });

      }
    });
  })();
} else {
  // SMWHalo not installed
  CKEDITOR.plugins.add( 'smwtoolbar',
  {
    requires : [ 'dialog' ],
    init : function( editor )
    {
      var command = editor.addCommand( 'SMWtoolbar', new CKEDITOR.dialogCommand( 'SMWtoolbar' ) );
      command.canUndo = false;

      editor.ui.addButton( 'SMWtoolbar',
      {
        label : 'Semantic Toolbar',
        title : 'Semantic Toolbar',
        command : 'SMWtoolbar',
        icon: this.path + 'images/icon_STB.gif'
      });

      CKEDITOR.dialog.add( 'SMWtoolbar', this.path + 'dialogs/teaser.js' );
    }
  });

}
