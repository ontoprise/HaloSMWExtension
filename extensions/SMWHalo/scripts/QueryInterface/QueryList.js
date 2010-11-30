/**
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloQueryInterface
 *
 *  Query Interface for Semantic MediaWiki
 *
 *  QueryList.js
 *  Manages the query list when loading queries in the Query Interface
 *  @author Stephan Robotta
 */

var QIList = function() {
    this.list = [];
};
QIList.prototype = {
    search : function() {
        var term = $('qiLoadConditionTerm').value,
            type;
        if (!term) return;
        for (var i = 0; i < $('qiLoadCondition').options.length; i++) {
            if ($('qiLoadCondition').options[i].selected) {
                type = $('qiLoadCondition').options[i].value;
                break;
            }
        }
        this.reset(true);
		sajax_do_call('smwf_qi_QIAccess',
                      [ "searchQueries", term, type ],
                      this.fetchResults.bind(this));
    },
    
    fetchResults : function(result) {
        if (result.readyState == 4 && result.status == 200) {
        	data = result.responseText;
        }
        else return;

        var resObj = !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(data.replace(/"(\\.|[^"\\])*"/g, '')))
             && eval('(' + data + ')');
        this.list = [];
        if (resObj.length == 0)
            this.addTableRow(null, true)
        else {
            for (var i = 0; i < resObj.length; i++) {
                var item = resObj[i];
                this.list.push(item);
                this.addTableRow(resObj[i]);
            }
        }
        // make search result table visible
        $('qiLoadTabResult').style.display='inline';
    },

    addTableRow : function(item, noresults) {
        var cont = '<span style="cursor:pointer" onclick="qihelper.queryList.selectRow(this)">%s</span>';
        var row = $('qiLoadTabResultTable').insertRow(-1);
        var cell = row.insertCell(0);
        if (noresults) {
            cell.setAttribute('colspan', '3');
            cell.innerHTML = gLanguage.getMessage('QI_NO_QUERIES_FOUND');
            return;
        }
        cell.innerHTML = cont.replace('%s', (item.name ? item.name : '<i>' + gLanguage.getMessage('QI_NOT_SPECIFIED') + '</i>'));
        cell = row.insertCell(1);
        cell.innerHTML = cont.replace('%s', (item.format ? item.format : 'table'));
        cell = row.insertCell(2);
        cell.innerHTML = cont.replace('%s', '<a href="'+wgScript+'/'+ item.page.replace(/ /g, '_')+'">'+item.page+'</a>');
    },
    selectRow : function(span) {
        var num;
        for ( var i = 1, n = $('qiLoadTabResultTable').rows.length; i < n; i++) {
            if ($('qiLoadTabResultTable').rows[i] == span.parentNode.parentNode ) {
                num = i;
            }
			$('qiLoadTabResultTable').rows[i].className = '';
        }
        $('qiLoadTabResultTable').rows[num].className = 'qiLoadTabResultTableSelected';
        //var row = span.parentNode.parentNode.className = 'qiLoadTabResultTableSelected';
        qihelper.initFromQueryString(this.list[num -1].query);
        $('qiDefTabInLoad').style.display= 'inline'; // show the tree
        $('qiLoadQueryButton').style.display= 'inline'; // and load button

    },
    reset : function(inSearch) {
        if (!inSearch) $('qiLoadConditionTerm').value = '';
        for ( var i = 1, n = $('qiLoadTabResultTable').rows.length; i < n; i++)
            // empty table of query list
			$('qiLoadTabResultTable').deleteRow(1);
        $('qiDefTabInLoad').style.display = 'none';
        $('qiLoadTabResult').style.display='none';
        $('qiLoadQueryButton').style.display='none';
    }

}