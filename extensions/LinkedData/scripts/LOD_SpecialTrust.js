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

var counter=0;

function removeOrNot(id, linkYes, linkNo) {
    if(confirm('Remove '+id+"?")) {
        window.location = linkYes;
    } else {
        windows.location = linkNo;
    }
}

var submitted = 0;
function validate() {
    if(submitted) {
        alert("Form already submitted, please be patient");
        return false;
    }
    var value = document.getElementById('tpee_selected_heuristic').value;
    if(value == '000') {
        alert("Please select a heuristic");
        return false;
    }
    return true;
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

// copy the parameters from a table row to an editor form
function editParameter(row) {
    var form = jQuery('#tpee_parameter_form').clone();
    var count = row.id.match('\\d+$');
    row = jQuery(row);
    form.attr('id', 'tpee_parameter_form_'+count);
    form.css({
        display: 'block'
    });
    form.find(':text,textarea').each(function(i, input) {
        if(input.type != "button") {
            input.value = row.find('#'+input.name+count).text();
        }
    });
    var insertHere = jQuery('#tpee_parameter_insert');
    form.insertAfter(insertHere);
}

// copy the parameters from an editor form to the table row
function setParameter(form) {
    var count = form.id.match('\\d+$');
    var row = jQuery('#tpee_parameter_'+count);
    var jform = jQuery(form);
    jform.find(':text,textarea').each(function(i, input) {
        var value = input.value;
        row.find('input[name^="'+input.name+'"]').val(value);
        row.find('td[id^="'+input.name+'"]').text(value);
    });
    form.parentNode.removeChild(form);
}

// create a new parameter row
function newParameter() {
    counter = jQuery('input[name="lastcount"]').val();
    counter++;
    var newRow = jQuery('#tpee_parameter_template').clone();
    newRow.attr('id', 'tpee_parameter_'+counter);
    newRow.css({
        display: ''
    });
    newRow.find('td').each(function(i, td) {
        td = jQuery(td);
        td.attr('id', td.attr('id')+counter);
    });
    newRow.find('input').each(function(i, input){
        if(input.type != "button") {
            input.name =input.name+counter;
        }
    });
    // create a new URI for the parameter
    var policyURI = jQuery('input[name="uri"]').val();
    policyURI = policyURI.substr(policyURI.lastIndexOf('/')+1);
    newRow.find('input[name="uri_'+counter+'"]').val('http://www.example.org/smw-lde/smwTrustPolicies/Par_'+policyURI+'_'+counter);
    var insertHere = jQuery('#tpee_parameter_template');
    newRow.insertBefore(insertHere);
    jQuery('input[name="lastcount"]').val(counter);
}
