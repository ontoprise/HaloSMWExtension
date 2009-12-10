/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var UP_RatingPopup = Class.create();
UP_RatingPopup.prototype = {

    initialize: function() {
        this.id='up_rating_popup'
        this.height=uprgPopupHeight+'px'
        this.width=uprgPopupWidth+'px'
        this.Ultrapedia=uprgUltrapediaAPI
        this.Wikipedia=uprgWikipediaAPI
    },

    cellRating: function(cell, uri) {
        // cell is usually the span element, set it to the real <td> element
        this.cell=cell
        while (this.cell.tagName != 'TD')
            this.cell=this.cell.parentNode

        this.initPopup()
        
        this.cellIdentifier='' // set in applyCellLabels
        this.tableIdentifier=1 // set default value but fetch it correctly now
        var n=this.cell
        while (n.tagName!='TABLE')
            n=n.parentNode
        if (n.id.indexOf('querytable')!=-1)
            this.tableIdentifier=parseInt(n.id.replace(/querytable/, ''))
        // provenance URI
        this.provenanceUri=''
        var uriArgs=uri.split('&');
        for (var i=0; i<uriArgs.length; i++) {
            this.provenanceUri+=uriArgs[i]+(i==0 ? '?':'&')
        }
        this.provenanceUri+='redirect-after-edit='+(wgServer+wgScriptPath).replace(/:/, '%3A').replace(/\//g, '%2F')
        // set the static html stuff
        this.popup.setHtmlContent(this.cellRatingHtml())
        this.applyCellLabels()
        var cellIdent=this.cellIdentifier.replace(/ \| /, ',')
        
        sajax_do_call('wfUpGetCellRating', [wgPageName, this.tableIdentifier, cellIdent], this.setComments.bind(this))
    },

    tableRating: function(num) {
        // get the table identifier
        this.tableIdentifier=num
        // flush cell identifier, not needed here
        this.cellIdentifier=''

        this.initPopup()
        // set the static html stuff
        this.popup.setHtmlContent(this.tableRatingHtml())
        sajax_do_call('wfUpGetTableRating', [wgPageName, this.tableIdentifier], this.setComments.bind(this))
    },

    applyCellLabels: function() {
        // get the <td> of the clicked element
        var n=this.cell
        while(n.tagName!='TD')
            n=n.parentNode
        document.getElementById('up_data_table_value').value=this.getCellContent(n)
        // go back to all previous <td> to find the first one
        var np
        var c=-1
        while (n) {
            if (n.nodeType==1){
                np=n
                c++
            }
            n=n.previousSibling
        }
        // label of the first col in the current row
        this.cellIdentifier=this.getCellContent(np)
        // get the <tr> of the clicked element
        n=this.cell.parentNode
        while (n.tagName!='TR')
            n=n.parentNode
        // go back to all previous <tr>
        while(n){
            if (n.nodeType==1) np=n
            n=n.previousSibling
        }
        // get now the cells in the first row
        var rh=np.getElementsByTagName('th')
        if (rh.length==0) rh=np.getElementsByTagName('td')
        if (rh.length==0) return
        var firstColHeader=this.getCellContent(rh[0])
        document.getElementById('up_data_table_row').innerHTML=
            (firstColHeader.length>0 ? firstColHeader+': ':'')+this.cellIdentifier
        if (rh.length > c) {
            document.getElementById('up_data_table_col').innerHTML=this.getCellContent(rh[c])
            this.cellIdentifier=document.getElementById('up_data_table_col').innerHTML
                +' | '
                +this.cellIdentifier
        }
    },

    getCellContent: function(cell){
        var a=cell.getElementsByTagName('a')
        for (var i=0; i<a.length; i++) {
            if (a[i].href && a[i].href.indexOf(wgScript)!=-1 && a[i].className.indexOf('sort')==-1)
                return this.trim(a[i].innerHTML)
        }
        var res=''
        var c=new Array()
        for (var i=0; i<cell.childNodes.length; i++)
           c.push(cell.childNodes[i])
        var cl=c.length
        for (var i=0; i<cl; i++) {
            if (c[i].nodeType==3 && c[i].parentNode.className.indexOf('sort')==-1)
                res+=c[i].nodeValue
            for(var j=0; j<c[i].childNodes.length; j++) {
                c.push(c[i].childNodes[j])
                cl++
            }
        }
        return this.trim(res)
    },

    setComments: function(request) {
        var resObj = !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(request.responseText.replace(/"(\\.|[^"\\])*"/g, '')))
             && eval('(' + request.responseText + ')')
        if (resObj.html) {
            var cont=document.getElementById('uprComments')
            cont.innerHTML=resObj.html
            // resize the content div
            var dim = this.getContentSize()
            cont.style.width = dim[0]+'px'
            cont.style.height = dim[1]+'px'
        }
    },

    getContentSize: function() {
        var node = document.getElementById('uprComments')
        var row
        var dim=[0,0]
        while (node) {
            if (node.id == this.id) break
            dim[0]+=node.offsetLeft
            dim[1]+=node.tagName!="TR" ? node.offsetTop :0
            node=node.parentNode
        }
        return [parseInt(this.width)-dim[0],parseInt(this.height)-dim[1]]
    },

    trim: function(txt){
        return txt.replace(/^\s*/,'').replace(/\s*$/,'')
    },

    initPopup: function() {
        this.popup = new DndPopup(this.id, UP_RatingPopupLang.smw_ume_reset , this.width, this.height)
        this.popup.closeImage=DND_POPUP_DIR+'/skins/close.gif'
        this.popup.actionOnClose="uprgPopup.closeBox();"
        this.popup.attachTo=document.getElementById('content')
        this.popup.open()
    },

    closeBox: function(){
        this.popup.close()
    },

    reset: function(res, err){
        if (res != null && res == 0) {
            alert(UP_RatingPopupLang.err_comment_up.replace(/%s/, err))
            return
        }
        else if (res) {
            if (this.cellIdentifier) {
                var origUri=this.provenanceUri.replace(/\?/, '&')
                origUri=origUri.substring(0, origUri.lastIndexOf('&'))
                this.cellRating(this.cell, origUri)
            }
            else
                this.tableRating(this.tableIdentifier)
        }
        document.getElementsByName('up_data_correct_c')[0].value='';
        document.getElementsByName('up_data_correct')[0].checked=null;
        document.getElementsByName('up_data_correct')[1].checked=null;
        
    },

    send: function(){
        var comment=this.trim(document.getElementsByName('up_data_correct_c')[0].value)
        var commentOnTalkP=comment
        var rating;
        if (document.getElementsByName('up_data_correct')[0].checked)
            rating=1
        else if (document.getElementsByName('up_data_correct')[1].checked)
            rating=2
        if (!rating) {
            alert(UP_RatingPopupLang.fieldsempty)
            return
        }
        comment+= '[['+uprgPropertyReferingPage+'::'+wgPageName+'| ]]'+
                  '[['+uprgPropertyReferingSection+'::| ]]'+
                  '[['+uprgPropertyTable+'::'+this.tableIdentifier+'| ]]'+
                  '[['+uprgPropertyRating+'::'+(rating == 2 ? 'false' : 'true')+'| ]]'
        if (this.cellIdentifier)
            comment+='[['+uprgPropertyCell+'::'+this.cellIdentifier.replace(/ \| /,',')+'| ]]'

        // create a new rating page in Ultrapedia and reload the popup afterwards
        var upapi = new MW_API_Access(this.Ultrapedia)
        var pagename=uprgRatingNamespace+':'+new Date().getTime()
        upapi.createPage(pagename, comment, this.reset.bind(this))
        // add the comment to the talk page in wikipedia
        if (this.Wikipedia.length > 0) {
            var wpapi = new MW_API_Access(this.Wikipedia)
            var pagename='Talk%3A'+wgTitle
            commentOnTalkP= UP_RatingPopupLang.rating_on_talkp+': '+
                        (rating==2 ? UP_RatingPopupLang.data_invalid : UP_RatingPopupLang.data_correct)+
                        ', '+commentOnTalkP
            var section='Rating from Ultrapedia; table '+this.tableIdentifier
            wpapi.addCommentOnTalkpage(pagename, section, this.cellIdentifier, commentOnTalkP)
        }
    },

    tableRatingHtml: function(){
        return '<table style="width:100%; height:100%; border-collapse:collapse;empty-cells:show">'+
            '<tr><td><strong>'+UP_RatingPopupLang.rate_table+':</strong></td></tr>'+
            '<tr><td><hr class="uprSpacer"/></td></tr>'+
            '<tr>'+this.tdFeedbackHtml()+'</tr>'+
            '<tr><td><hr class="uprSpacer"/></td></tr>'+
            '<tr><td><strong>'+UP_RatingPopupLang.comments+':</strong>'+
            '<div id="uprComments"></div>'+
            '</td></tr>'+
            '</table>'
    },

    cellRatingHtml: function(){
        return '<table style="width:100%; height:100%; border-collapse:collapse;empty-cells:show">'+
            '<tr>'+
            '<td class="uprColLeft"><strong>'+UP_RatingPopupLang.data+':</strong></td>'+
            '<td class="uprColRight"><input id="up_data_table_value" width="100%" readonly="readonly"/><br/>'+
            '<div id="up_data_table_col"></div>'+
            '<div id="up_data_table_row"></div></span>'+
            '</td>'+
            '</tr><tr><td colspan="2"><hr class="uprSpacer"/></td></tr><tr>'+
            '<td class="uprColLeft">'+UP_RatingPopupLang.source+':</td>'+
            '<td class="uprColRight">'+UP_RatingPopupLang.dbpedia+'  ('+UP_RatingPopupLang.read_only+')</td>'+
            '</tr><tr><td colspan="2"><hr class="uprSpacer"/></td></tr><tr>'+
            '<td class="uprColLeft">'+UP_RatingPopupLang.edit+':</td>'+
            '<td class="uprColRight"><a href="'+this.provenanceUri+'">'+UP_RatingPopupLang.editlink+'</a></td>'+
            '</tr><tr><td colspan="2"><hr class="uprSpacer"/></td></tr><tr>'+
            '<td class="uprColLeft">'+UP_RatingPopupLang.feedback+':</td>'+
            this.tdFeedbackHtml()+
            '</tr><tr><td colspan="2"><hr class="uprSpacer"/></td></tr><tr>'+
            '<td class="uprColLeft">'+UP_RatingPopupLang.comments+':</td>'+
            '<td class="uprColRight"><div id="uprComments"></div></td>'+
            '</tr>'+
            '</table>'
    },

    tdFeedbackHtml: function(){
        return '<td'+(this.cell ? ' class="uprColRight"':'')+'>'+
            UP_RatingPopupLang.data_correct+
            '<input type="radio" name="up_data_correct" value="1"/>'+UP_RatingPopupLang.yes+
            '<input type="radio" name="up_data_correct" value="0"/>'+UP_RatingPopupLang.no+
            '<br/>'+
            '<textarea name="up_data_correct_c" width="100%" rows="4"></textarea>'+
            '<hr class="uprSpacer" />'+
            '<table width="100%" cellspacing="0" cellpadding="0"><tr><td width="50%">'+
            '<input type="submit" value="'+UP_RatingPopupLang.reset_feedback+'" onclick="uprgPopup.reset()"/>'+
            '</td><td width="50%" align="right">'+
            '<input type="submit" value="'+UP_RatingPopupLang.submit_feedback+'" onclick="uprgPopup.send()"/>'+
            '</td></tr></table></td>'
    }
}

var uprgPopup = new UP_RatingPopup()