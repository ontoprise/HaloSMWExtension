/**
 * Context sensitive help for SMW+
 *
 * It uses a DIV element with id 'smw_csh' to add a help label. If this is not
 * available the DIV will be created as the first child of the 'innercontent'
 * div, which means, it appears between the tab section and the main head line.
 */
var SMW_UserManual_CSH = Class.create();
SMW_UserManual_CSH.prototype = {
    initialize: function(label) {
        // get the container <div id="smw_csh"></div>
        var div = document.getElementById('smw_csh');
        if (! div) { // it's not in the skin. Create it
            div = document.createElement('div')
            div.id = 'smw_csh'
            div.style.textAlign = 'right'
            // and insert it below the <div id="content">
            var child = document.getElementById('content').firstChild
            if (child.nodeType == 1)
                document.getElementById('content').insertBefore(div, child)
            // we can't insert it before a text node, therefore look for the
            // first child which is an element node.
            else if (child.nodeType == 3) {
                while (child) {
                    child = child.nextSibling
                    if (child && child.nodeType == 1)
                        document.getElementById('content').insertBefore(div, child)
                }
            }
        }
        // set the link inside the div container
        div.innerHTML = '<a href="#" onclick="javascipt: smwCsh.loadPopup();">'
            + label + '</a>';
    },

    /**
     * fills the content box of the popup with some html received by an ajax call
     *
     * @param Object request
     */
    setContent: function(request) {
        var resObj = !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(request.responseText.replace(/"(\\.|[^"\\])*"/g, '')))
             && eval('(' + request.responseText + ')')
        if (resObj.selection)
            document.getElementById('smw_csh_selection').innerHTML=resObj.selection
        if (resObj.content)
            document.getElementById('smw_csh_answer').innerHTML=resObj.content
        document.getElementById('smw_csh_link_to_smw').innerHTML=resObj.link?resObj.link:''
        if (resObj.title)
            document.getElementById('smw_csh_answer_head').innerHTML=resObj.title
        // resize the smw_csh_content div if visible
        if (document.getElementById('smw_csh_answer').style.display != 'none') {
            var dim = this.getContentSize()
            document.getElementById('smw_csh_answer').style.width = dim[0]+'px'
            document.getElementById('smw_csh_answer').style.height = dim[1]+'px'
            document.getElementById('smw_csh_selection').getElementsByTagName('select')[0].style.width=dim[0]+'px'
        }
    },

    switchTab: function(td){
        if (td && td.className == "cshTabInactive") {
            if (td.parentNode.childNodes[5] == td) {
                td.parentNode.childNodes[2].className = "cshTabInactive"
                document.getElementById('smw_csh_answer').parentNode.style.display='none'
                document.getElementById('smw_csh_feedback').parentNode.style.display='block'
            } else {
                td.parentNode.childNodes[5].className = "cshTabInactive"
                document.getElementById('smw_csh_answer').parentNode.style.display='block'
                document.getElementById('smw_csh_feedback').parentNode.style.display='none'
            }
            td.className="cshTabActive"
        }
    },

    setHeadline: function(label) {
        this.headline = label
    },

    /**
     * calls the popup, makes it visible to the user. Also two event handlers
     * mousedown and mouseup are registered, which toggle the dragging (i.e. after
     * a mousedown event and before receiving the mouseup event the mouse key is
     * pressed and the window can be dragged.
     */
    loadPopup: function() {
        var setContent=0
        if (!this.popup) {
            this.popup = new DndPopup('smw_csh_popup', this.headline, umegPopupWidth+'px', umegPopupHeight+'px')
            setContent=1
        }
        this.popup.preserveContent=1
        this.popup.closeImage=DND_POPUP_DIR+'skin/close.gif'
        this.popup.actionOnClose="smwCsh.closeBox();"
        this.popup.attachTo=document.getElementById('content')

        // clicking on the help link again will hide the popup
        if (this.popup.isVisible()){
            this.popup.close()
            return
        }
        this.popup.open()
        if (setContent) {
            var cont = document.getElementById('smw_csh_rendered_boxcontent')
            this.popup.setHtmlContent(cont.innerHTML)
            cont.parentNode.removeChild(cont)
        }
            
        var ds = this.getDiscourseState()
        sajax_do_call('wfUmeAjaxGetArticleList', ds, this.setContent.bind(this))
    },

    /**
     * hides the help popup and releases the drag and drop events.
     */
    closeBox: function(){
        this.popup.close()
    },

    /**
     * Ajax call for getting the rendered HTML page of a help article
     * @param string page name
     */
    getPageContent: function(page) {
        sajax_do_call('wfUmeAjaxGetArticleHtml', [page], this.setContent.bind(this))
    },

    /**
     * The div box that contains the page content of a help page need width and
     * height to be set so that scrolling works. Relative size parameters (in %)
     * don't work. Therefore calculate the space that's left for the content box
     * @return array(int) width, height
     */
    getContentSize: function() {
        var node = document.getElementById('smw_csh_answer')
        var dim=[0,0]
        while (node) {
            if (node.id == 'smw_csh_popup') break
            dim[0]+=node.offsetLeft
            dim[1]+=node.offsetTop
            node=node.parentNode
        }
        dim[1]+=document.getElementById('smw_csh_link_to_smw').offsetHeight-10
        return [umegPopupWidth-dim[0],umegPopupHeight-dim[1]]
    },

    /**
     * if a radio input type is clicked for rating, a textbox automatically
     * appears below the radio inputs. This is done here
     */
    openRatingBox: function(){
        var obj=document.getElementById('smw_csh_rating_box')
        if (obj && obj.style.display=='none') {
            obj.style.display=null
            var arrow=document.getElementById('smw_csh_rating').getElementsByTagName('img')[0]
            arrow.src=arrow.src.replace(/right\.png/,'down.png')
        }
        else this.hideRatingBox()
    },

    /**
     * send the rating text inlcuding the rating itself to the smw Forum
     */
    sendRating: function(){
        this.hideRatingBox()
    },

    /**
     * uncheck any value in the radio input and empty any comment in the rating
     * textarea
     */
    resetRating: function(){
        document.getElementById('smw_csh_rating_box').getElementsByTagName('textarea')[0].value='';
        document.getElementsByName('smw_csh_did_it_help')[0].checked=null;
        document.getElementsByName('smw_csh_did_it_help')[1].checked=null;
        this.hideRatingBox()
    },

    /**
     * hide the rating box below the radio input
     */
    hideRatingBox: function(){
        var obj=document.getElementById('smw_csh_rating_box')
        if (obj && obj.style.display!='none') {
            obj.style.display='none'
            var arrow=document.getElementById('smw_csh_rating').getElementsByTagName('img')[0]
            arrow.src=arrow.src.replace(/down\.png/,'right.png')
        }
    },

    /* the following function work on the feedback tab */

    /**
     * opens the feedback box for sending a bug report, general comment or
     * ask you own question. This function gets called when clicking on the
     * headline of the comment box (launched by the onclick event which is in
     * the page html defined). Closing stuff goes in an exta method because
     * this happens also if someone clicks on the submit buttons below the
     * textarea in an open comment box.
     *
     * @param DomNode tr of the current comment box
     */
    openCommentBox: function(el){
        if (!el) return
        var arrow=el.getElementsByTagName('img')[0]
        if (arrow.src.indexOf('right.png')!= -1) {
            // close any other box that might be open
            var boxes=document.getElementById('smw_csh_feedback').getElementsByTagName('table')
            for (var i=0; i<boxes.length; i++)
                this.closeCommentBox(boxes[i].getElementsByTagName('tr')[0])
            // now open the box that we want
            arrow.src=arrow.src.replace(/right\.png/, 'down.png')
            el.getElementsByTagName('td')[0].style.fontWeight='bold'
            el.parentNode.style.backgroundColor='#EFEFEF'
            this.getCommentBox(el)
        }
        else
            this.closeCommentBox(el)
    },

    /**
     * create the comment box (textarea and buttons for sending and reset) below
     * the comment headline inside the box
     * @param DomNode tr of the current comment box
     */
    getCommentBox: function(el){
        var tr=document.createElement('tr');
        var td=document.createElement('td');
        var textarea=document.createElement('textarea');
        textarea.rows=3
        textarea.style.width='98%'
        td.appendChild(textarea)
        tr.appendChild(td)
        el.parentNode.appendChild(tr)

        tr=document.createElement('tr');
        td=document.createElement('td');
        var button=document.createElement('input');
        button.type='submit'
        button.name='cshreset'
        button.value='Reset'
        button.addEventListener('click', smwCsh.sendCommentBox.bindAsEventListener(this), false)
        td.appendChild(button)
        
        var button=document.createElement('input');
        button.type='submit'
        button.name='cshsend'
        button.value='Submit feedback'
        button.style.textAlign="right"
        button.addEventListener('click', smwCsh.sendCommentBox.bindAsEventListener(this), false)
        td.appendChild(button)
        tr.appendChild(td)
        el.parentNode.appendChild(tr)
    },

    /**
     * send comment (or just close the box) after hitting one of the buttons
     * below the content box
     * @param Object Event
     */
    sendCommentBox: function(e){
        if (!e) e=window.event
        var eL = e.srcElement ? e.srcElement : e.target ? e.target : e.currentTarget
        if (eL.name=='cshsend') {
            var tbody=eL.parentNode.parentNode.parentNode
            var type=tbody.firstChild.firstChild.getElementsByTagName('img')[1].src
            type=type.substr(type.lastIndexOf('/')+1)
            var txt=tbody.getElementsByTagName('textarea')[0].value
            alert('send ' + type + ' and text ' + txt)
        }
        this.closeCommentBox(eL)
    },

    /**
     * closes the comment box. This happens when the header (textlabel) is clicked
     * or one of the buttons is clicked.
     * @param DomNode clickedElement node (input or tr)
     */
    closeCommentBox: function(n){
        if (n.tagName=='INPUT')
            n=n.parentNode.parentNode.parentNode
        else
            n=n.parentNode
        if (n.childNodes.length<3) return;
        n.removeChild(n.lastChild)
        n.removeChild(n.lastChild)
        n.getElementsByTagName('img')[0].src=
            n.getElementsByTagName('img')[0].src.replace(/down\.png/, 'right.png')
        n.getElementsByTagName('td')[0].style.fontWeight=null
        n.style.backgroundColor=null
    },
    /* function for the feedback tab end here */

    /**
     * check pages, elements and variables in the current page to guess
     * the current discourse states. All which apply will be returned in an
     * array
     * @return Array ds
     */
    getDiscourseState: function(){
        // available disource states: http://dmwiki.ontoprise.com:8888/dmwiki/index.php/Help_articles_structure
        var ds = new Array()
        // check the action parameter
        switch (wgAction) {
            case 'edit': ds.push('edit'); break
            case 'formedit': ds.push('SemanticForms'); break
        }
        // check any special page that we know of
        switch (wgCanonicalSpecialPageName) {
            case 'OntologyBrowser': ds.push('OntologyBrowser'); break
            case 'QueryInterface': ds.push('QueryInterface'); break
            case 'AddData':
            case 'EditData': ds.push('QueryInterface'); break
            case 'DataImportRepository':
            case 'DefineWebService':
            case 'UseWebService': ds.push('Webservice'); break
            case 'Gardening':
            case 'GardeningLog': ds.push('Gardening'); break
            case 'SemanticNotifications': ds.push('SemanticNotification'); break
            case 'Search': if (document.getElementById('us_searchform') &&
                               document.getElementById('us_searchfield'))
                               ds.push('UnifiedSearch');
                           break

        }
        // FCKeditor object exists?
        if (typeof FCKeditor != "undefined") ds.push('wysiwyg')
        // Semantic Toolbar div exists?
        if (document.getElementById('semtoolbar')) ds.push('SemanticToolbar')
        // any input element with class wick (ignore the search field)
        if (this.elementsWithHaloAc()) ds.push('HaloAutoCompletion')

        return this.uniqueDs(ds)
    },

    /**
     * array_uniq function for discourseState array
     * @param Array ds
     * @return Array uds
     */
    uniqueDs: function(ds){
        var uds = new Array()
        while (e = ds.shift()) {
            var f=0
            for (var i = 0; i < uds.length; i++) {
                if (e == uds[i]) {
                    f=1
                    break
                }
            }
            if (!f) uds.push(e)
        }
        return uds
    },

    /**
     * Check if in the page are any <input> or <textareas> that make use of
     * the Halo autocompletion. In this case, the class name consists of some
     * wickEnabled string. If there are such elements return true. The search
     * field, which is always on the page, will be ignored.
     * @return Boolean true / false
     */
    elementsWithHaloAc: function() {
        var input = document.getElementsByTagName('input')
        for (i = 0; i < input.length; i++) {
            if (input.id == 'searchInput') continue
            var cn=input.item(i).className
            if (cn && cn.indexOf('wickEnabled') != -1) return true
        }
        input = document.getElementsByTagName('textarea')
        for (i = 0; i < input.length; i++) {
            cn=input.item(i).className
            if (cn && cn.indexOf('wickEnabled:MWFloater') != -1) return true
        }
        return false
    }

}
