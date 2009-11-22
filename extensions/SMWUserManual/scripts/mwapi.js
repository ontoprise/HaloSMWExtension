/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var MW_API_Access = Class.create();
MW_API_Access.prototype = {
    initialize: function(url) {
        this.url=url
        this.callParamsArray = new Array()
        this.callTargetArray = new Array()
        this.callActionArray = new Array()
        this.inCall = false

        setInterval(this.callQueue.bind(this), 100);
        
        // create the HTTP request object
        // Mozilla, Safari and other browsers
        if (window.XMLHttpRequest)
            this.httpRequest = new XMLHttpRequest()
        // IE
        else if (window.ActiveXObject) {
        	try {
                this.httpRequest = new ActiveXObject("Msxml2.XMLHTTP")
            }
            catch (e) {
                try {
                    this.httpRequest = new ActiveXObject("Microsoft.XMLHTTP")
                }
                catch (e) {
                    alert('http request object not found')
                }
            }
        }
    },

    createPage: function(page, text, func){
        this.page=page.replace(/ /g, '_')
        this.text=this.URLEncode(text)
        this.returnFunction=func
        this.getEditToken()
        this.callApi('action=edit&title='+this.page+'&createonly=1&text='+this.text+'&format=json', this.pageCreated, 'createPage')
    },

    getPageContent: function(page, func) {
        this.returnFunction=func
        this._getPageContent(page, this.returnContent)
    },

    _getPageContent: function(page, func) {
        this.page=page.replace(/ /g, '_')
        this.callApi('action=query&titles='+this.page+'&prop=revisions|info&rvlimit=1&rvprop=content|timestamp&intoken=edit&format=xml', func)
    },

    addCommentOnTalkpage: function(page, section, cell, text, func) {
        this.section=section
        this.cell=cell
        this.text=(text.charAt(text.length-1)=='\n')?text:text+'\n'
        this.returnFunction=func
        this._getPageContent(page, this.mergeSections)
    },

    returnContent: function(response){
        var node=this.getDomFromResponse(response)
        var text = node.getElementsByTagName('rev')[0].firstChild.nodeValue
        if (typeof(this.returnFunction) == 'function') {
            this.returnFunction(text?1:0, text)
            this.returnFunction=null
        }
    },

    mergeSections: function(response){
        var node=this.getDomFromResponse(response)
        var text = node.getElementsByTagName('rev')[0].firstChild.nodeValue
        
        if (text) {
            var sections=new Array()
            var lines=text.split('\n')
            for (var i=0; i<lines.length; i++) {
                if (lines[i].match(/^\s*(={2,})[^=]*\1\s*$/)) {
                    var n= lines[i].replace(/=/g,'')
                    var l = (lines[i].length - n.length)/2
                    sections.push([this.trim(n), l, lines[i]])
                }
            }
            var insertLevel = this.cell?3:2
            var pSection
            var pCell
            var insertBefore
            for (var i=0; i<sections.length; i++) {
                if (sections[i][0] == this.section && sections[i][1] ==2)
                    pSection= i
                if (sections[i][0] == this.cell && sections[i][1] ==3)
                    pCell= i
            }
            if (this.cell) {
                if (pCell != null && pSection != null) {
                    if (sections.length > pCell+1)
                        insertBefore=sections[pCell+1][2]
                } else {
                    this.text = '=== '+this.cell+' ===\n'+this.text
                }
            }
            if (pSection != null) {
                if (sections.length > pSection+1)
                    if (!insertBefore && !this.cell) insertBefore=sections[pSection+1][2]
            } else {
                this.text='== '+this.section+' ==\n'+this.text
            }
            if (insertBefore)
                this.text=text.replace(insertBefore, this.text+insertBefore)
            else
                this.text=text+this.text
            alert(this.text)
        }
    },

    pageCreated: function(res) {
        var response= this.getJsonResponse(res)
        if (typeof(this.returnFunction) == 'function') {
            this.returnFunction(response.error?0:1, response.error.info)
            this.returnFunction=null
        }
    },

    getEditToken: function(){
        if (!this.editToken) {
            this.callApi('action=query&prop=info|revisions&intoken=edit&titles='+this.page+'&format=json', this.setEditToken)
        }
    },

    setEditToken: function(res){
        var response= this.getJsonResponse(res)
        if (response.query) {
            this.editToken=response.query.pages[-1].edittoken
        }
    },

    getJsonResponse: function(res){
        return !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(res.replace(/"(\\.|[^"\\])*"/g, '')))
               && eval('(' + res + ')')
    },

    getDomFromResponse: function(xml){
        var xmlDoc
        //for IE
        if (window.ActiveXObject) {
            xmlDoc = new ActiveXObject('Microsoft.XMLDOM')
            xmlDoc.async = false
            xmlDoc.loadXML(xml)
        }
        //for Mozilla, Firefox, Opera, etc.
        else if (document.implementation && document.implementation.createDocument){
            var parser = new DOMParser();
            xmlDoc = parser.parseFromString(xml,'text/xml');
        }
        //var h = xmlDoc.evaluate( xpath, xmlDoc, null, XPathResult.ANY_TYPE, null );
        return xmlDoc
    },

    trim: function(txt){
        return txt.replace(/^\s*/,'').replace(/\s*$/,'')
    },

    /* Methods for placing the Ajax calls */
    doCall: function(params, target, action){
        this.inCall=true

        // add token to call if it's neccessary
        if (action == "createPage")
            params+='&token='+this.URLEncode(this.editToken)

        // check if we are accessing the local wiki. If this is not the case
        // we must send the request to localhost, which then forwards it to the
        // original server via curl
        var url=this.url
        if (this.url.indexOf(wgServer+wgScriptPath) != 0) {
            var newparams='action=ajax&rs=wfUprForwardApiCall&rsargs[]='+this.URLEncode(url)+'&rsargs[]='+this.URLEncode(params)
            params=newparams
            url=wgServer+wgScript
        }
        try {
            this.httpRequest.open('POST', url, true)
        }
        catch (e) {
            if (window.location.hostname == "localhost") {
                alert("Your browser blocks XMLHttpRequest to 'localhost', try using a real hostname for development/testing.")
            }
            throw e
        }
        this.httpRequest.setRequestHeader("Method", "POST " + url + " HTTP/1.1");
        this.httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        this.httpRequest.setRequestHeader("Pragma", "cache=yes");
        this.httpRequest.setRequestHeader("Cache-Control", "no-transform");
        this.target=target
        this.httpRequest.onreadystatechange = this.parseResponse.bind(this)

        this.httpRequest.send(params)
    },

    parseResponse: function() {
        if (this.httpRequest.readyState != 4) return
        try {
            var state = this.httpRequest.status
        } catch(e) {
            return; // probably an aborted call
        }
        if (typeof(this.target) == 'function') {
            this.target(this.httpRequest.responseText)
        }
        this.httpRequest.onreadystatechange=NULL
        this.inCall=false
    },

    callApi: function(params, target, action){
        // add the next call to the queue
        this.callParamsArray.push(params)
        this.callTargetArray.push(target)
        this.callActionArray.push(action)
    },

    callQueue: function(){
        if (this.callParamsArray.length > 0) {
            // check the queue and send next call in line
            if(!this.inCall) {
                params = this.callParamsArray.shift()
                returnTo = this.callTargetArray.shift()
                action = this.callActionArray.shift()
				this.doCall(params, returnTo, action)
            }
        }
    },

    // URL encoding and decoding functions
    URLEncode: function(str) {
        // version: 904.1412
        // discuss at: http://phpjs.org/functions/urlencode

        var tmp_arr = [];
        var ret = (str+'').toString();

        var replacer = function(search, replace, str) {
            var tmp_arr = [];
            tmp_arr = str.split(search);
            return tmp_arr.join(replace);
        };

        // The histogram is identical to the one in urldecode.
        var histogram = this.URL_Histogram()

        // Begin with encodeURIComponent, which most resembles PHP's encoding functions
        ret = encodeURIComponent(ret)

        for (search in histogram) {
            replace = histogram[search]
            ret = replacer(search, replace, ret) // Custom replace. No regexing
        }

        // Uppercase for full PHP compatibility
        return ret.replace(/(\%([a-z0-9]{2}))/g, function(full, m1, m2) {
            return "%"+m2.toUpperCase();
        })

        return ret
    },

    URLDecode: function(str) {
        // version: 904.1412
        // discuss at: http://phpjs.org/functions/urldecode

        var ret = str.toString()
        var replacer = function(search, replace, str) {
            var tmp_arr = []
            tmp_arr = str.split(search)
            return tmp_arr.join(replace)
        }

        // The histogram is identical to the one in urlencode.
        var histogram = this.URL_Histogram();

        for (replace in histogram) {
            search = histogram[replace] // Switch order when decoding
            ret = replacer(search, replace, ret) // Custom replace. No regexing
        }

        // End with decodeURIComponent, which most resembles PHP's encoding functions
        ret = decodeURIComponent(ret)

        return ret
    },

    URL_Histogram: function() {
        var histogram = {}

        histogram["'"]   = '%27'
        histogram['(']   = '%28'
        histogram[')']   = '%29'
        histogram['*']   = '%2A'
        histogram['~']   = '%7E'
        histogram['!']   = '%21'
        histogram['%20'] = '+'
        histogram['\u00DC'] = '%DC'
        histogram['\u00FC'] = '%FC'
        histogram['\u00C4'] = '%D4'
        histogram['\u00E4'] = '%E4'
        histogram['\u00D6'] = '%D6'
        histogram['\u00F6'] = '%F6'
        histogram['\u00DF'] = '%DF'
        histogram['\u20AC'] = '%80'
        histogram['\u0081'] = '%81'
        histogram['\u201A'] = '%82'
        histogram['\u0192'] = '%83'
        histogram['\u201E'] = '%84'
        histogram['\u2026'] = '%85'
        histogram['\u2020'] = '%86'
        histogram['\u2021'] = '%87'
        histogram['\u02C6'] = '%88'
        histogram['\u2030'] = '%89'
        histogram['\u0160'] = '%8A'
        histogram['\u2039'] = '%8B'
        histogram['\u0152'] = '%8C'
        histogram['\u008D'] = '%8D'
        histogram['\u017D'] = '%8E'
        histogram['\u008F'] = '%8F'
        histogram['\u0090'] = '%90'
        histogram['\u2018'] = '%91'
        histogram['\u2019'] = '%92'
        histogram['\u201C'] = '%93'
        histogram['\u201D'] = '%94'
        histogram['\u2022'] = '%95'
        histogram['\u2013'] = '%96'
        histogram['\u2014'] = '%97'
        histogram['\u02DC'] = '%98'
        histogram['\u2122'] = '%99'
        histogram['\u0161'] = '%9A'
        histogram['\u203A'] = '%9B'
        histogram['\u0153'] = '%9C'
        histogram['\u009D'] = '%9D'
        histogram['\u017E'] = '%9E'
        histogram['\u0178'] = '%9F'
        return histogram
    }
}
