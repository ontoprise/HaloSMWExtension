/*  Copyright 2010, ontoprise GmbH
*  This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
var counter=0;

function newParameter() {
    counter++;
    var newFields = document.getElementById('tpee_parameter_template').cloneNode(true);
    newFields.id = '';
    newFields.style.display = 'block';
    var newField = newFields.childNodes;
    for (var i=0;i<newField.length;i++) {
        var theName = newField[i].name
        if (theName)
            newField[i].name = theName + counter;
    }
    var insertHere = document.getElementById('tpee_parameter_insert');
    insertHere.parentNode.insertBefore(newFields, insertHere);
}

function removeOrNot(id, linkYes, linkNo) {
    if(confirm('Remove '+id+"?")) {
        window.location = linkYes;
    } else {
        windows.location = linkNo;
    }
}

var editArea;

function init(){
    editArea = document.getElementById('tpee_pattern');
}

function insertText(editField, insertText) {
    // IE
    if (document.selection) {
        editField.focus();
        selected = document.selection.createRange();
        selected.text = insertText;
        return;
    }
    // MOZ
    var length =  editField.value.length;
    var start = editField.selectionStart;
    var end = editField.selectionEnd;

    if ((start != 0) && (end != length)) {
        var before = editField.value.substring(0, start);
        var after = editField.value.substring(end, length);
        editField.value = before + insertText + after;
    } else {
        editField.value += insertText;
    }
}

function insertHere(text){
    insertText(editArea, text + "\n");
}

function insertAtTop(editField, insertText) {
    // IE - to do
    if (document.selection) {
        insertText(editField, insertText);
    }

    // MOZ
    editField.value = insertText+editField.value;
}

function insertAtStart(insertText) {
    insertAtTop(editArea, insertText + "\n");
}

// might not work with IE
function inset(editField, insetString) {
    var start = editField.selectionStart;
    var end = editField.selectionEnd;

    for(var c=start;c<end;c++){
        var length =  editField.value.length;
        var before = editField.value.substring(0, c);
        var after = editField.value.substring(c, length);
        if(editField.value.substring(c-1, c) == "\n") {
            editField.value = before + insetString + after;
            end = end+insetString.length;
        }
    }
}

function commentOut(editField) {
    inset(editField, "#");
}

function comment(){
    commentOut(editArea);
}

function indentArea(editField){
    inset(editField, "    ");
}

function indent(){
    indentArea(editArea);
}

// might not work with IE
function makeOptional(editField) {

    var length =  editField.value.length;

    var start = editField.selectionStart;
    var end = editField.selectionEnd;

    var before = editField.value.substring(0, start);
    var after = editField.value.substring(end, length);

    var selection = editField.value.substring(start, end);

    editField.value = before + "\nOPTIONAL\n{\n" + selection + "\n}\n"+after;
}


// quick and dirty - wipes all # in selected region
function unCommentRegion(editField) {
    var length =  editField.value.length;
    var start = editField.selectionStart;
    var end = editField.selectionEnd;

    for(var c=start;c<end;c++){
        var before = editField.value.substring(0, c-1);
        var after = editField.value.substring(c, length);
        if(editField.value.substring(c-1, c) == "#") {
            editField.value = before + after;
        }
    }
}

function unComment(){
    unCommentRegion(editArea);
}

function clearArea(editField){
    editField.value = "";
}

function clearAll(){
    clearArea(editArea);
}

function optional(){
    makeOptional(editArea);
}

// Styling on mouse events - needs fixing
function mouseOver(ctrl){
//	ctrl.style.borderColor = '#000000';
//	ctrl.style.backgroundColor = '#BBBBBB';
}

function mouseOut(ctrl){
//	ctrl.style.borderColor = '#ccc';
//	ctrl.style.backgroundColor = '#CCCCCC';
}

function mouseDown(ctrl){
//	ctrl.style.backgroundColor = '#8492B5';
}

function mouseUp(ctrl){
//  	ctrl.style.backgroundColor = '#B5BED6';
}