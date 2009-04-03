<?php
/**
 * @author: Kai K�hn
 * 
 * Created on: 27.01.2009
 *
 */
class US_LanguageDe {

	public $us_contentMessages = array('us_skos_preferedLabel' => 'Bezeichnung',
                                        'us_skos_altLabel' => 'Auch bekannt als',
                                        'us_skos_hiddenLabel' => 'Selten benannt als',
                                        'us_skos_broader' => 'Oberbegriff',
                                        'us_skos_narrower' => 'Unterbegriff',
                                        'us_skos_description' => 'Definition',
                                        'us_skos_example' => 'Beispiel',
                                        'us_skos_term' => 'Term');

	public $us_userMessages = array (
        'us_search' => 'Erweiterte Suche',
	    'us_tolerance'=> 'Toleranz',
        'us_page_does_not_exist' => 'Diese Seite exisitiert nicht. $1',
        'us_clicktocreate' => 'Klicken Sie hier, um die Seite zu erstellen.',
        'us_refinesearch' => 'Suche einschr&auml;nken',
        'us_browse_next' => 'n&auml;chste',
        'us_browse_prev' => 'vorherige',
        'us_results' => '<b>Ergebnisse</b>',
        'us_noresults' => 'Keine Suchtreffer',
	    'us_search_tooltip_refine' => 'Filtern nach $1',
	    'us_noresults_text' => 'Es wurden <b>keine</b> mit ihrer Suchanfrage - <b>$1</b> - <b>&uuml;bereinstimmenden Treffer</b> gefunden. 
	               <br><br>Vorschl&auml;ge: <ul>
	               <li>Probieren Sie einen andere Suchbegriffe.</li>
	               <li>Probieren Sie allgemeinere Suchbegriffe.</li>
	               <li>Vergewissen Sie sich dass Sie alle W&ouml;rter richtig geschrieben haben.</li></ul>',
	   'us_resultinfo' => '<b>$1</b> - <b>$2</b> von <b>$3</b> f&uuml;r <b>$4</b>',
	   'us_page' => 'Seite',
        'us_searchfield' => 'Suche',
	    'us_lastchanged'=> 'Zuletzt ge&auml;ndert',
	    'us_isincat' => 'liegt in Kategorie',
	    'us_searchbutton' => 'Suche',
	'us_entries_per_page' => 'Eintr&auml;ge pro Seite',
	
	    'us_article' => 'Artikel',
	    'us_all' => 'Kein Filter',


        'us_totalresults' => 'Gesamtzahl Ergebnisse',
       
        'us_didyoumean' => 'Meinten Sie',
        'us_showdescription' => 'Zeige Beschreibung',

        'us_tolerantsearch' => 'tolerant',
        'us_semitolerantsearch' => 'mittel',
        'us_exactsearch' => 'exakt',
        'unifiedsearchstatistics' => 'Statistik Allgemeine Suche',
        'us_statistics_docu' => 'Statistische Informationen &uuml;ber Suchtreffer. 
						        <br>Damit k&ouml;nnen sie z.B. Suchterme identifizieren, die h&auml;ufig benutzt, 
						        aber wenig oder keine Treffer erzielen.',
        'us_search_asc'=> 'Aufsteigend',
        'us_search_desc' => 'Absteigend',
	    'us_search_term'=>'Suchbegriff',
        'us_search_hits'=> 'Treffer',
        'us_search_tries' => 'Versuche',
	    'us_go_button' => 'Ok',
	    'us_sort_for'=>'Sortieren nach',
        'us_order_for'=>'Reihenfolge',
		'us_termsappear' => 'Diese Suchbegriffe sind markiert'        
        );

	public $us_pathsearchMessages = array(
		'us_pathsearch_tab_fulltext' => 'Volltext',
		'us_pathsearch_tab_path' => 'Pfadsuche',
		'us_pathsearch_no_results' => 'Es wurden keine Pfade zu den Suchbegriffen gefunden',
		'us_pathsearch_show_all_results' => 'Zeige alle Ergebnisse für diesen Pfad (%s) ...',
		'us_pathsearch_no_instances' => 'Es wurden keine Ergebnisse für den aktuellen Pfad gefunden',
		'us_pathsearch_error_in_path' => 'Im Pfad ist ein Fehler aufgetreten',
		'us_pathsearch_result_popup_header' => 'Alle Ergebnisse für diese Pfad',
	);        
}
?>