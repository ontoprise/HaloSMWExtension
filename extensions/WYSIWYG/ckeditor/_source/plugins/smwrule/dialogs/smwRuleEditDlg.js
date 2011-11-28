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

CKEDITOR.dialog.add( 'SMWruleEdit', function( editor ) {

    return {
        title: editor.lang.smwrule.titleRuleEdit,

        minWidth: 600,
        minHeight:200,


        contents: [
        {
            id: 'tab1',
            label: 'Tab1',
            title: 'Tab1',
            elements : [
            {
                id: 'tagDefinition',
                type: 'textarea',
                label: editor.lang.smwrule.editRule,
                title: 'Edit semantic rule',
                className: 'swmf_class',
                style: 'border: 1px;'
            }
            ]
        }
        ],


        onOk: function() {
            var textarea = this.getContentElement( 'tab1', 'tagDefinition'),
            rule = textarea.getValue();

            rule = rule.Trim().replace(/\r?\n/, 'fckLR');
                        
            var html = '<span class="fck_smw_rule"';
            
            var ruleName = rule.match(/name\s*=\s*\"[\w\#\-;]+"/);
            var ruleType = rule.match(/type\s*=\s*\"[\w\#\-;]+"/);
            var ruleFormula = rule.match(/formula\s*=\s*\"[\w\#\-;]+"/);
            var variableSpec = rule.match(/variablespec\s*=\s*\"[\w\#\-;]+"/);
            var ruleContent = /(?!\-)>(.*?)<\/rule>/.exec(rule);
            if(ruleName)
                html += ' ' + ruleName;
            if(ruleType)
                html += ' ' + ruleType;
            if(ruleFormula)
                html += ' ' + ruleFormula;
            if(variableSpec)
                html += ' ' + variableSpec;
            html += '>';
            if(ruleContent && ruleContent.length)
                html += CKEDITOR.tools.htmlEncode(ruleContent[1]);
            html += '</span>';

            var element = CKEDITOR.dom.element.createFromHtml(html, editor.document);
            newFakeObj = editor.createFakeElement( element, 'FCK__SMWrule', 'span' );
            if ( this.fakeObj ) {
                newFakeObj.replace( this.fakeObj );
                editor.getSelection().selectElement( newFakeObj );
            } else
                editor.insertElement( newFakeObj );
        },
        onShow : function() {
            this.fakeObj = false;

            var editor = this.getParentEditor(),
            selection = editor.getSelection(),
            element = null;

            // Fill in all the relevant fields if there's already one item selected.
            if ( ( element = selection.getSelectedElement() ) && element.is( 'img' )
                && element.getAttribute( 'class' ) == 'FCK__SMWrule' )
            {
                this.fakeObj = element;
                element = editor.restoreRealElement( this.fakeObj );
                selection.selectElement( this.fakeObj );

                var content = '<rule';
                var ruleName = element.getAttribute('name');
                if(ruleName)
                    content += ' name="' + ruleName.htmlDecode() + '"';
                var ruleType = element.getAttribute('type');
                if(ruleType)
                    content += ' type="' + ruleType.htmlDecode() + '"';
                var ruleFormula = element.getAttribute('formula');
                if(ruleFormula)
                    content += ' formula="' + ruleFormula.htmlDecode() + '"';
                var variableSpec = element.getAttribute('variablespec');
                if(variableSpec)
                    content += ' variablespec="' + variableSpec.htmlDecode() + '"';
                var rule = element.getHtml().replace(/fckLR/g, '\r\n');
                rule = rule.htmlDecode().Trim();
                content += '>' + rule + '</rule>';
                
                var textarea = this.getContentElement( 'tab1', 'tagDefinition');
                textarea.setValue(content);
            }
        }
    };

} );
