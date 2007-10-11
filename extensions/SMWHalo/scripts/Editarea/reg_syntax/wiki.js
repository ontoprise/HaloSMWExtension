/*
* Language file for wiki syntax highlighting in EditArea,
* especially designed for use with the Semantic MediaWiki
* extension.
* @author: Markus Nitsche, 2007
*/

editAreaLoader.load_syntax["wiki"] = {
	'COMMENT_SINGLE' : {}
	,'COMMENT_MULTI' : {}
	,'QUOTEMARKS' : {}
	,'KEYWORD_CASE_SENSITIVE' : false
	,'KEYWORDS' : {
	}
	,'OPERATORS' :[
	]
	,'DELIMITERS' :[
	]
	,'REGEXPS' : {
		'category' : {
			'search' : '()(\\[\\[Category:.*?\\]\\])()'
			,'class' : 'category'
			,'modifiers' : 'g'
			,'execute' : 'before'

		}
		,'attribute' : {
			'search' : '()(\\[\\[[^\\]]*?:=[^\\]]*?\\]\\])()'
			,'class' : 'attribute'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'relation' : {
			'search' : '()(\\[\\[[^\\]]*?::[^\\]]*?\\]\\])()'
			,'class' : 'relation'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'template' : {
			'search' : '()(\\{\\{.*?\\}\\})()'
			,'class' : 'template'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'wikilink' : {
			'search' : '()(\\[\\[[^=]*?\\]\\])()'
			,'class' : 'link'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'link' : {
			'search' : '()(\\[http:.*?\\])()'
			,'class' : 'link'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}


	}
	,'STYLES' : {
		'COMMENTS': 'color: #AAAAAA;'
		,'QUOTESMARKS': 'color: #AAAAAA;'
		,'KEYWORDS' : {
			}
		,'OPERATORS' : 'color: #AAAAAA;'
		,'DELIMITERS' : 'color: #AAAAAA;'
		,'REGEXPS' : {
			'relation': 'color: #ff4600; '
			,'category': 'color: #064df3;  text-decoration: none;'
			,'attribute': 'color: #ff4600;'
			,'template': 'color: #990000; '
			,'link': 'color: #0000ff; text-decoration: underline;'
		}
	}
};