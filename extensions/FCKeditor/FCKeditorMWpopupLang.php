<?php
$messages_special['en'] = array(

'noinclude' => 'Anything between &lt;noinclude&gt; and &lt;/noinclude&gt; will be processed and displayed only when the page is being viewed directly; it will not be included or substituted.',
'includeonly' => ' Text between &lt;includeonly&gt; and &lt;/includeonly&gt; will be processed and displayed only when the page is being included.',
'nowiki' => 'The nowiki tag ignores [[Wiki]] \'\'markup\'\'',
'onlyinclude' => 'With &lt;onlyinclude&gt;wikitext&lt;/onlyinclude&gt; on a page, the display of the wikitext so surrounded and the rest of the page (except includeonly parts) is rendered on the page itself normally (note: this means interwiki translation links will behave as normal external links, unless they are further bracketed by &lt;includeonly&gt;).',
'gallery' => 'Create a gallery of images. Image links (without [[brakets]]) must be inside the &lt;gallery&gt; tags.',
'dpl' => 'DPL stands for Dynamic Page List, and allows to generate a formatted list of pages based on selection criteria. See <a href="#" onclick="javascript:window.open(\'http://semeb.com/dpldemo/index.php?title=Dynamic_Page_List\')">manual</a> for details',
'inputbox' => 'Inputbox allows to create a form for users to create new pages. The new pages edit box can be pre-loaded with any template. See <a href="#" onclick="javascript:window.open(\'http://meta.wikimedia.org/wiki/Help:Inputbox\')">manual</a> for details',

'NOTOC' => 'Hides the table of contents (TOC).',
'FORCETOC' => 'Forces the table of content to appear at its normal position (above the first header).',  
'TOC' => 'Places a table of contents at the word\'s current position (overriding __NOTOC__). If this is used multiple times, the table of contents will appear at the first word\'s position.',   
'NOEDITSECTION' => 'Hides the section edit links beside headings.',   
'NEWSECTIONLINK' => 'Adds a link ("+" by default) beside the "edit" tab for adding a new section on a non-talk page (see Adding a section to the end).',
'NONEWSECTIONLINK' => 'Removes the link beside the "edit" tab on pages in talk namespaces.',
'NOGALLERY' => 'Used on a category page, replaces thumbnails in the category view with normal links.',
'HIDDENCAT' => 'Used on a category page, hides the category from the lists of categories in its members and parent categories (there is an option in the user preferences to show them).',
'NOCC' => 'On wikis with language variants, don\'t perform any content language conversion (character and phase) in article display; for example, only show Chinese (zh) instead of variants like zh_cn, zh_tw, zh_sg, or zh_hk.',    
'NOTC' => 'On wikis with language variants, don\'t perform language conversion on the title (all other content is converted).',   
'INDEX' => 'Tell search engines to index the page (overrides $wgArticleRobotPolicies, but not robots.txt).',
'NOINDEX' => 'Tell search engines not to index the page (ie, do not list in search engines\' results).',
'STATICREDIRECT' => 'On redirect pages, don\'t allow MediaWiki to automatically update the link when someone moves a page and checks "Update any redirects that point to the original title".',

'CURRENTYEAR' => 'Year',
'CURRENTMONTH' => 'Month (zero-padded number)',
'CURRENTMONTHNAME' => 'Month (name)',
'CURRENTMONTHNAMEGEN' => 'Month (genitive form)',
'CURRENTMONTHABBREV' => 'Month (abbreviation)',
'CURRENTDAY' => 'Day of the month (unpadded number)',
'CURRENTDAY2' => 'Day of the month (zero-padded number)',
'CURRENTDOW' => 'Day of the week (unpadded number)',
'CURRENTDAYNAME' => 'Day of the week (name)', 
'CURRENTTIME' => 'Time (24-hour HH:mm format)',    
'CURRENTHOUR' => 'Hour (24-hour zero-padded number)',   
'CURRENTWEEK' => 'Week (number)',
'CURRENTTIMESTAMP' => 'YYYYMMDDHHmmss timestamp',

'SITENAME' => 'The wiki\'s site name ($wgSitename).',
'SERVER' => 'domain URL ($wgServer)',
'SERVERNAME' => 'domain name ($wgServerName)',
'DIRMARK' => 'Outputs a unicode-directional mark that matches the wiki\'s default language\'s direction (&lrm; on left-to-right wikis, &rlm; on right-to-left wikis), useful in text with multi-directional text.',
'SCRIPTPATH' => 'relative script path ($wgScriptPath)',
'CURRENTVERSION' => 'The wiki\'s MediaWiki version.',
'CONTENTLANG' => 'The wiki\'s default interface language ($wgLanguageCode)',
'REVISIONID' => 'Unique revision ID',
'REVISIONDAY' => 'Day edit was made (unpadded number)',
'REVISIONDAY2' => 'Day edit was made (zero-padded number)',
'REVISIONMONTH' => 'Month edit was made (unpadded number)',
'REVISIONYEAR' => 'Year edit was made',
'REVISIONTIMESTAMP' => 'Timestamp as of time of edit',
'REVISIONUSER' => 'The username of the user who made the most recent edit to the page.',
'FULLPAGENAME' => 'Namespace and page title.',
'PAGENAME' => 'Page title',
'BASEPAGENAME' => 'Page title excluding the current subpage and namespace ("Title" on "Title/foo").',
'SUBPAGENAME' => 'The subpage title ("foo" on "Title/foo").',
'SUBJECTPAGENAME' => 'The namespace and title of the associated content page.',
'TALKPAGENAME' => 'The namespace and title of the associated talk page.',
'NAMESPACE' => 'Namespace (name)',
'ARTICLESPACE' => 'Name of the associated content namespace',
'TALKSPACE' => 'The namespace and title of the associated talk page.',

'lc' => 'The lowercase input',
'lcfirst' => 'The input with the <u>very first</u> character lowercase.',
'uc' => 'The uppercase input',
'ucfirst' => 'The input with the <u>very first</u> character uppercase.',
'formatnum' => 'The input with decimal and decimal group separators, and localized digit script, according to the wiki\'s default locale. the <pre>|R</pre> parameter can be used to unformat a number, for use in mathematical situations.',
'#dateformat' => 'Formats an unlinked date based on user "Date format" preference. For logged-out users and those who have not set a date format in their preferences, dates can be given a default: <pre>mdy, dmy, ymd, ISO 8601</pre> (all case sensitive). If a format is not specified or is invalid, the input format is used as a default. If the supplied date is not recognized as a valid date, it is rendered unchanged.',
'padleft' => 'Inserts a string of padding characters (character chosen in third parameter; default \'0\') of a specified length (second parameter) next to a chosen base character or variable (first parameter). The final digits or characters in the base replace the final characters in the padding; i.e. {{padleft:44|3|0}} produces 044',
'padright' => 'Identical to padleft, but adds padding characters to the right side.',
'plural' => 'Outputs the correct given pluralization form (parameters except first) depending on the count (first parameter). Plural transformations are used for languages like Russian based on "count mod 10".',
'grammar' => 'Outputs the correct inflected form of the given word described by the inflection code after the colon (language-dependent). Grammar transformations are used for inflected languages like Polish. See also <a href="#" onclick="javascript:window.open(\'http://www.mediawiki.org/wiki/Manual:$wgGrammarForms\');">Manual:$wgGrammarForms.</a>',
'#language' => 'The native name for the given language code, in accordance with <a href="#" onclick="javascript:window.open(\'http://en.wikipedia.org/wiki/ISO_639\');">ISO 639</a>.',
'int' => 'Internationalizes (translates) the given interface (MediaWiki namespace) message into the user language. Note that this can damage/confuse cache consistency',
'#tag' => 'Alias for XML-style parser or extension tags, but parsing wiki code. Attribute values can be passed as parameter values (\'<pre>&lt;tagname attribute="value"&gt;\' -&gt; \'{{#tag:tagname|attribute=value}}</pre>\'), and inner content as an unnamed parameter (\'<pre>&lt;tagname&gt;content&lt;/tagname&gt;\' -&gt; \'{{#tag:tagname|content}}</pre>\')',

);

// insert your language here


foreach (array_keys($messages_special) as $lang) {
    $messages_special[$lang]['NOCONTENTCONVERT'] = $messages_special[$lang]['NOCC'];
    $messages_special[$lang]['NOTITLECONVERT'] = $messages_special[$lang]['NOTC'];
    $messages_special[$lang]['DIRECTIONMARK'] = $messages_special[$lang]['DIRMARK'];
    $messages_special[$lang]['CONTENTLANGUAGE'] = $messages_special[$lang]['CONTENTLANG'];
    $messages_special[$lang]['SUBJECTSPACE'] =     $messages_special[$lang]['ARTICLESPACE'];
    $messages_special[$lang]['#formatdate'] =     $messages_special[$lang]['#dateformat'];
}

?>