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

CKEDITOR.dialog.add( 'MWSpecialTags', function( editor ) {
    {
        return {
            title : editor.lang.mwplugin.specialTagTitle,
            minWidth : 350,
            minHeight : 140,
            resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
            contents : [
            {
                id : 'mwSpecialTagDef',
                label : 'Special Tags label',
                title : 'Special Tags title',
                elements :
                [
                {
                    id: 'tagDefinition',
                    type: 'textarea',
                    label: editor.lang.mwplugin.specialTagDef,
                    title: 'Special Tag definition',
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
                spanClass = null,
                textarea = this.getContentElement( 'mwSpecialTagDef', 'tagDefinition'),
                content = textarea.getValue(),
                wgImgWikitags = ['source', 'ref', 'nowiki', 'html', 'gallery'],
                wgCKeditorMagicWords = window.parent.wgCKeditorMagicWords || window.parent.parent.wgCKeditorMagicWords;

                content.Trim();
                content = content.replace(/\r?\n/g, 'fckLR');
                
                // check for a tag
                if (el = content.match(/^<([\w-]+)>(.*?)<\/([\w-]+)>$/)) {
                    var inner = el[2] || '_';                    
                    spanClass = 'fck_mw_special';
                    className = 'FCK__MWSpecial';

                    if (el[1].InArray(wgImgWikitags)) {
                        spanClass = 'fck_mw_' + el[1];
                        className = 'FCK__MW' + el[1].substr(0, 1).toUpperCase() + el[1].substr(1);
                        
                        tag = '<span class="'+ spanClass +'" _fck_mw_customtag="true" _fck_mw_tagname="' + el[1] + '" _fck_mw_tagtype="t">'
                        + inner + '</span>';
                    }
                    else if(el[1].InArray(['noinclude', 'includeonly', 'onlyinclude'])){
                        tag = '<span class="fck_mw_' 
                          + el[1]
                          + '" _fck_mw_customtag="true" _fck_mw_tagname="'
                          + el[1]
                          + '" _fck_mw_tagtype="t">'
                          + inner + '</span>';
                        var element = CKEDITOR.dom.element.createFromHtml(tag, editor.document);
                        if(this.selectedElement && !this.selectedElement.is('body')){                            
                            element.replace(this.selectedElement);
                        }
                        else{
                            editor.insertElement( element );
                        }
                    }
                    else {
                      alert (editor.lang.mwplugin.invalidContent);
                      return false;
                  }
                }
                else if (el = content.match(/^__(.*?)__$/)) {
                    tag = '<span class="fck_mw_magic" _fck_mw_customtag="true" _fck_mw_tagname="' + el[1] + '" _fck_mw_tagtype="c">'
                    + '_</span>'
                    className = 'FCK__MWMagicWord';
                }
                else if (el = content.match(/^{{(#?[\w\d-]+):(.*?)}}$/)) {
                    var tagType = 'p';
                    if (el[1].InArray(wgCKeditorMagicWords.datevars)) tagType = 'v';
                    else if (el[1].InArray(wgCKeditorMagicWords.wikivars)) tagType = 'w';
                    var inner = el[2] || '_';
                    tag = '<span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="' + el[1] + '"' +
                    ' _fck_mw_tagtype="' + tagType + '">'
                    + inner + '</span>'
                    className = 'FCK__MWSpecial';
                }
                else if (el = content.match(/^{{([A-Z\d!]+)}}$/)) {
                    tagType = '';
                    if (el[1].InArray(wgCKeditorMagicWords.datevars)) tagType = 'v';
                    else if (el[1].InArray(wgCKeditorMagicWords.wikivars)) tagType = 'w';
                    if (tagType) {
                        tag = '<span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="' + el[1] + '"' +
                        ' _fck_mw_tagtype="' + tagType + '">_</span>'
                        className = 'FCK__MWSpecial';
                    }
                    else {
                        tag = '<span class="fck_mw_template">{{' + el[1] + '}}</span>';
                        className = 'FCK__MWTemplate';
                    }
                }
                else if ( wgCKeditorMagicWords.sftags && ( el = content.match(/^{{{([\w]+)((\|[^\|]+)*)}}}$/) ) ) {
                    if (el[1].InArray(wgCKeditorMagicWords.sftags)){
                      tagType = 'sf';
                    }
                    if (tagType) {
                        tag = '<span class="fck_mw_special" _fck_mw_customtag="true" _fck_mw_tagname="' + el[1] + '"' +
                        ' _fck_mw_tagtype="' + tagType + '">' + content + '</span>'
                        className = 'FCK__MWSpecial';
                    }
                    else {
                        tag = '<span class="fck_mw_template">{{{' + el[1] + '}}}</span>';
                        className = 'FCK__MWTemplate';
                    }
                }
                else if (el = content.match(/^{{[\w\d-]+(\|.*?)*}}$/)) {
                    tag = '<span class="fck_mw_template">' + el[0].substr(2, -2) + '</span>';
                    className = 'FCK__MWTemplate';
                }
                else {
                    alert (editor.lang.mwplugin.invalidContent);
                    return false;
                }
                element = CKEDITOR.dom.element.createFromHtml(tag, editor.document);
                var newFakeObj = editor.createFakeElement( element, className, 'span' );
                if ( this.fakeObj ) {
                    newFakeObj.replace( this.fakeObj );
                    editor.getSelection().selectElement( newFakeObj );
                }
                else{
                    editor.insertElement( newFakeObj );
                }
                return true;
            },

            onShow : function() {
                this.fakeObj = false;

                var editor = this.getParentEditor(),
                selection = editor.getSelection(),
                element = selection.getSelectedElement();
                if(!element){
                    element = selection.getStartElement();
                }    
                             
                this.selectedElement = element;
                
                // Fill in all the relevant fields if there's already one item selected.
                if ( element.is( 'img' ) && element.getAttribute( 'class' ).InArray( [
                        'FCK__MWSpecial',
                        'FCK__MWMagicWord',
                        'FCK__MWNowiki'
//                        'FCK__MWIncludeonly',
//                        'FCK__MWNoinclude',
//                        'FCK__MWOnlyinclude'
                        ])
                    )
                    {
                    this.fakeObj = element;
                    element = editor.restoreRealElement( this.fakeObj );
                    selection.selectElement( this.fakeObj );
                    var content = '',
                    inner = element.getHtml().replace(/_$/, '').replace(/fckLR/gi, '\r\n');
                    if ( element.getAttribute( 'class' ) == 'fck_mw_special' ) {
                        var tagName = element.getAttribute('_fck_mw_tagname') || '',
                        tagType = element.getAttribute('_fck_mw_tagtype') || '';
                            
                        if ( tagType == 't' ) {
                            content += '<' + tagName + '>' + inner + '</' + tagName + '>';
                        }
                        else if ( tagType == 'sf') {
                            content +=  inner;
                        }
                        else {
                            content += '{{' + tagName;
                            if (! tagType.IEquals('w', 'v'))
                                content += ':';
                            content += inner + '}}';
                        }
                    }
                    else if ( element.getAttribute( 'class' ) == 'fck_mw_magic' ) {
                        content += '__' + element.getAttribute('_fck_mw_tagname') + '__';
                    }
                    else {
                        content += '<' + element.getAttribute('_fck_mw_tagname') + '>' +
                        inner +
                        '</' + element.getAttribute('_fck_mw_tagname') + '>';
                    }
                    //editor.document.getById('tagDefinition').setHtml(content);
                    var textarea = this.getContentElement( 'mwSpecialTagDef', 'tagDefinition');
                    textarea.setValue(content);
                }
                //those 3 tags are represented in rich editor as spans and not fake objects as the rest
                else if ( element.getAttribute( 'class' ) &&
                    element.getAttribute( 'class' ).InArray( [                        
                        'fck_mw_noinclude',
                        'fck_mw_onlyinclude',
                        'fck_mw_includeonly'
                        ])
                    )
                    {
                        tagName = element.getAttribute( '_fck_mw_tagname' );
                        content = element.getHtml();
                        this.getContentElement( 'mwSpecialTagDef', 'tagDefinition').setValue('<' + tagName + '>' + content + '</' + tagName + '>');
                    }
            }
			
        }
    }
} );
