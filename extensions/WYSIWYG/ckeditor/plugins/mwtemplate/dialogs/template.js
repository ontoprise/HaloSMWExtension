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

CKEDITOR.dialog.add( 'MWTemplate', function( editor ) {
{
  return {
    title : editor.lang.mwtemplateplugin.title,
    minWidth : 350,
    minHeight : 140,
    resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
    contents : [
    {
      id : 'mwTemplateTagDef',
      label : 'Special Tags label',
      title : 'Special Tags title',
      elements :
      [
      {
        id: 'tagDefinition',
        type: 'textarea',
        label: editor.lang.mwtemplateplugin.defineTmpl,
        title: 'Template Tag definition',
        className: 'swmf_class',
        style: 'border: 1px;'
      }
      ]
    }
    ],
    onOk : function() {
      var tag = null,
      className = null,
      el = null,
      textarea = this.getContentElement( 'mwTemplateTagDef', 'tagDefinition'),
      content = textarea.getValue();

      content.Trim();      
      // check for a tag
      if (content.match(/^{{#?[!\w\d-]+:?[\s\S]*?}}|{{{\w+}}}$/)) {
        content = content.replace(/\r?\n/g, 'fckLR');
        tag = '<span class="fck_mw_template">' + content + '</span>';
        className = 'FCK__MWTemplate';      
        var element = CKEDITOR.dom.element.createFromHtml(tag, editor.document),
        newFakeObj = editor.createFakeElement( element, className, 'span' );
        if ( this.fakeObj ) {
          newFakeObj.replace( this.fakeObj );
          editor.getSelection().selectElement( newFakeObj );          
        }
        else{
          editor.insertElement( newFakeObj );          
        }
        return true;
      }
      else {
        alert ('invalid content');
        return false;
      }
    },

    onShow : function() {
      this.fakeObj = false;

      var editor = this.getParentEditor(),
      selection = editor.getSelection(),
      element = null;

      // Fill in all the relevant fields if there's already one item selected.
      if ( ( element = selection.getSelectedElement() ) && element.is( 'img' )
        && element.getAttribute( 'class' ) == 'FCK__MWTemplate' )
        {
        this.fakeObj = element;
        element = editor.restoreRealElement( this.fakeObj );
        selection.selectElement( this.fakeObj );
        var content = element.getHtml().replace(/_$/, '').replace(/fckLR/gi, '\r\n');                   
        var textarea = this.getContentElement( 'mwTemplateTagDef', 'tagDefinition');
        textarea.setValue(content);
      }
    }

  }
}
} );
