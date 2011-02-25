<?php
/*  Copyright 2011, ontoprise GmbH
*  This file is part of the Enhanced Retrieval Extension.
*
*   The Enhanced Retrieval Extension is free software; you can redistribute it 
*   and/or modify it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Enhanced Retrieval Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains articles that are used in test cases.
 * 
 * @author Thomas Schweitzer
 * Date: 22.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval extension. It is not a valid entry point.\n" );
}


/**
 * 
 * This class is just a wrapper for the array of article definitions to avoid
 * a global variable
 * 
 * @author Thomas Schweitzer
 * 
 */
class ERTestArticles {
	
	public static $mArticles = array(
//------------------------------------------------------------------------------	
			'Property:Located_in' =>
<<<ARTICLE
	[[has type::Type:Page| ]]
	
ARTICLE
,
//------------------------------------------------------------------------------	
			'Property:Located_in_state' =>
<<<ARTICLE
	[[has type::Type:Page| ]]
	
ARTICLE
,
//------------------------------------------------------------------------------	
			'Property:Image' =>
<<<ARTICLE
	[[has type::Type:String| ]]	
ARTICLE
,
//------------------------------------------------------------------------------	
			'Property:Height_stories' =>
<<<ARTICLE
	[[has type::Type:Number| ]]
	
ARTICLE
,
//------------------------------------------------------------------------------	
			'Property:Building_name' =>
<<<ARTICLE
	[[has type::Type:String| ]]
	
ARTICLE
,
//------------------------------------------------------------------------------	
			'Property:Year_built' =>
<<<ARTICLE
	[[has type::Type:Date| ]]
	
ARTICLE
,
//------------------------------------------------------------------------------	
			'Property:Description' =>
<<<ARTICLE
	[[has type::Type:Text| ]]
	
ARTICLE
,

//------------------------------------------------------------------------------	
			'_1201_Third_Avenue' =>
<<<ARTICLE
	[[Description::This is the description of _1201_Third_Avenue.]]
	[[Description::This is the description of _1201_Third_Avenue.]]
	[[Category:_1988_architecture]]
	[[Category:Building]]
	[[Category:Kohn_Pedersen_Fox_buildings]]
	[[Category:Office_buildings_in_Seattle,_Washington]]
	[[Category:Postmodern_architecture_in_Washington_(U.S._state)]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Seattle,_Washington]]
	[[Category:Wright_Runstad_%26_Company]]
	[[Located_in::Seattle]]
	[[Image::Seattle_Washington_Mutual_Tower_2004-08-30.jpg]]
	[[Height_stories::55]]
	[[Building_name::1201 Third Avenue Tower]]
	[[Year_built::1/1/1988]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_1801_California_Street' =>
<<<ARTICLE
	[[Description::This is the description of _1801_California_Street.]]
	[[Category:Buildings_and_structures_completed_in_1982]]
	[[Category:Office_buildings_in_Denver,_Colorado]]
	[[Category:Qwest]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Denver,_Colorado]]
	[[Category:Telecommunications_company_headquarters_in_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_191_Peachtree_Tower' =>
<<<ARTICLE
	[[Description::This is the description of _191_Peachtree_Tower.]]
	[[Category:Atlanta,_Georgia_stubs]]
	[[Category:Buildings_and_structures_completed_in_1990]]
	[[Category:Georgia_(U.S._state)_building_and_structure_stubs]]
	[[Category:Headquarters_in_the_United_States]]
	[[Category:John_Burgee_buildings]]
	[[Category:Office_buildings_in_Atlanta,_Georgia]]
	[[Category:Philip_Johnson_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Atlanta,_Georgia]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_20_Exchange_Place' =>
<<<ARTICLE
	[[Description::This is the description of _20_Exchange_Place.]]
	[[Category:Art_Deco_buildings_in_New_York_City]]
	[[Category:Buildings_and_structures_completed_in_1931]]
	[[Category:Buildings_and_structures_in_Manhattan]]
	[[Category:Condominiums_in_New_York_City]]
	[[Category:Landmarks_in_New_York_City]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_300_North_LaSalle' =>
<<<ARTICLE
	[[Description::This is the description of _300_North_LaSalle.]]
	[[Category:Leadership_in_Energy_and_Environmental_Design_gold_certified_buildings]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_311_South_Wacker_Drive' =>
<<<ARTICLE
	[[Description::This is the description of _311_South_Wacker_Drive.]]
	[[Category:Buildings_and_structures_completed_in_1990]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_383_Madison_Avenue' =>
<<<ARTICLE
	[[Description::This is the description of _383_Madison_Avenue.]]
	[[Category:Bear_Stearns]]
	[[Category:Buildings_and_structures_completed_in_2002]]
	[[Category:Financial_services_company_headquarters_in_the_United_States]]
	[[Category:JPMorgan_Chase_buildings]]
	[[Category:Kohn_Pedersen_Fox_buildings]]
	[[Category:Office_buildings_in_Manhattan]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_40_Wall_Street' =>
<<<ARTICLE
	[[Description::This is the description of _40_Wall_Street.]]
	[[Category:_1930_architecture]]
	[[Category:Art_Deco_buildings_in_New_York_City]]
	[[Category:Buildings_and_structures_on_the_National_Register_of_Historic_Places_in_Manhattan]]
	[[Category:Former_world%27s_tallest_buildings]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Wall_Street]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_500_Fifth_Avenue' =>
<<<ARTICLE
	[[Description::This is the description of _500_Fifth_Avenue.]]
	[[Category:_1931_architecture]]
	[[Category:Art_Deco_buildings_in_New_York_City]]
	[[Category:New_York_building_and_structure_stubs]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_555_California_Street' =>
<<<ARTICLE
	[[Description::This is the description of _555_California_Street.]]
	[[Category:_1969_architecture]]
	[[Category:Bank_of_America]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Building]]
	[[Category:Office_buildings_in_San_Francisco,_California]]
	[[Category:Pietro_Belluschi_buildings]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_San_Francisco,_California]]
	[[Image::Bank_of_America_Tower_San_Francisco.jpg]]
	[[Located_in::San_Francisco]]
	[[Building_name::555 California Street]]
	[[Height_stories::52]]
	[[Year_built::1/1/1969]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_60_Wall_Street' =>
<<<ARTICLE
	[[Description::This is the description of _60_Wall_Street.]]
	[[Category:Buildings_and_structures_completed_in_1989]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Wall_Street]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_777_Tower' =>
<<<ARTICLE
	[[Description::This is the description of _777_Tower.]]
	[[Category:Buildings_and_structures_completed_in_1991]]
	[[Category:C%C3%A9sar_Pelli_buildings]]
	[[Category:Office_buildings_in_Los_Angeles,_California]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Los_Angeles,_California]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_7_World_Trade_Center' =>
<<<ARTICLE
	[[Description::This is the description of _7_World_Trade_Center.]]
	[[Category:Buildings_and_structures_completed_in_1987]]
	[[Category:Buildings_and_structures_completed_in_2006]]
	[[Category:Buildings_destroyed_in_the_September_11_attacks]]
	[[Category:Emery_Roth_buildings]]
	[[Category:Leadership_in_Energy_and_Environmental_Design_gold_certified_buildings]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Use_mdy_dates_from_August_2010]]
	[[Category:World_Trade_Center]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_8_Spruce_Street' =>
<<<ARTICLE
	[[Description::This is the description of _8_Spruce_Street.]]
	[[Category:Buildings_and_structures_in_Manhattan]]
	[[Category:Buildings_and_structures_under_construction_in_the_United_States]]
	[[Category:Frank_Gehry_buildings]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'_900_North_Michigan' =>
<<<ARTICLE
	[[Description::This is the description of _900_North_Michigan.]]
	[[Category:Buildings_and_structures_completed_in_1989]]
	[[Category:Condo-hotels_in_the_United_States]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:Residential_skyscrapers_in_Chicago,_Illinois]]
	[[Category:Shopping_centers_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'AXA_Center' =>
<<<ARTICLE
	[[Description::This is the description of AXA_Center.]]
	[[Category:_1986_architecture]]
	[[Category:AXA]]
	[[Category:Seventh_Avenue_(Manhattan)]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Times_Square]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Alabama' =>
<<<ARTICLE
	[[Description::This is the description of Alabama.]]
	[[Category:Alabama]]
	[[Category:Former_British_colonies]]
	[[Category:States_and_territories_established_in_1819]]
	[[Category:States_of_the_Confederate_States_of_America]]
	[[Category:States_of_the_Southern_United_States]]
	[[Category:States_of_the_United_States]]
	[[Category:Alabama_navigational_boxes]]
	[[Category:United_States_State]]
//------------------------------------------------------------------------------	
			'American_International_Building' =>
<<<ARTICLE
	[[Description::This is the description of American_International_Building.]]
	[[Category:_1932_architecture]]
	[[Category:American_International_Group]]
	[[Category:Art_Deco_buildings_in_New_York_City]]
	[[Category:Building]]
	[[Category:Insurance_company_headquarters_in_the_United_States]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Located_in::New_York_City]]
	[[Image::American_International_Building3.JPG]]
	[[Height_stories::66]]
	[[Building_name::American International Building]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Aon_Center' =>
<<<ARTICLE
	[[Description::This is the description of Aon_Center.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Aon_Center_(Chicago)' =>
<<<ARTICLE
	[[Description::This is the description of Aon_Center_(Chicago).]]
	[[Category:Amoco]]
	[[Category:BP_buildings_and_structures]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1973]]
	[[Category:Edward_Durell_Stone_buildings]]
	[[Category:Insurance_company_headquarters_in_the_United_States]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Located_in::Chicago]]
	[[Image::Aon_and_Blue_Cross_Blue_Shield.jpg]]
	[[Height_stories::83]]
	[[Building_name::Aon Center]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Aon_Center_(Los_Angeles)' =>
<<<ARTICLE
	[[Description::This is the description of Aon_Center_(Los_Angeles).]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1973]]
	[[Category:Office_buildings_in_Los_Angeles,_California]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Los_Angeles,_California]]
	[[Image::Aon_Center_LA.jpg]]
	[[Located_in::Los_Angeles]]
	[[Height_stories::62]]
	[[Building_name::Aon Center]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Aqua' =>
<<<ARTICLE
	[[Description::This is the description of Aqua.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Aqua_(skyscraper)' =>
<<<ARTICLE
	[[Description::This is the description of Aqua_(skyscraper).]]
	[[Category:Apartments_in_Chicago,_Illinois]]
	[[Category:Building]]
	[[Category:Condo-hotels_in_the_United_States]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:Hotels_in_Chicago,_Illinois]]
	[[Category:Residential_skyscrapers_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Image::AquaChicago071209.JPG]]
	[[Located_in::Chicago]]
	[[Height_stories::86]]
	[[Building_name::Aqua]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Atlanta' =>
<<<ARTICLE
	[[Description::This is the description of Atlanta.]]
	[[Category:Atlanta,_Georgia]]
	[[Category:Atlanta_metropolitan_area]]
	[[Category:Cities_in_Georgia_(U.S._state)]]
	[[Category:County_seats_in_Georgia_(U.S._state)]]
	[[Category:Host_cities_of_the_Summer_Olympic_Games]]
	[[Category:Populated_places_established_in_1845]]
	[[Category:Populated_places_in_Georgia_(U.S._state)_with_African_American_majority_populations]]
	[[Category:Summer_Paralympic_Games]]
	[[Category:United_States_places_with_Orthodox_Jewish_communities]]
	[[Located_in_state::Georgia]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Atlantic_City,_New_Jersey' =>
<<<ARTICLE
	[[Description::This is the description of Atlantic_City,_New_Jersey.]]
	[[Category:Atlantic_City,_New_Jersey]]
	[[Category:Cities_in_New_Jersey]]
	[[Category:Culture_of_Philadelphia,_Pennsylvania]]
	[[Category:Faulkner_Act_Mayor-Council]]
	[[Category:Gambling_in_the_United_States]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Populated_places_established_in_1854]]
	[[Category:Populated_places_in_Atlantic_County,_New_Jersey]]
	[[Category:Seaside_resorts_in_the_United_States]]
	[[Category:Tourism_in_New_Jersey]]
	[[Category:Unverifiable_lists]]
	[[Located_in_state::New_Jersey]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bank_of_America_Center' =>
<<<ARTICLE
	[[Description::This is the description of Bank_of_America_Center.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bank_of_America_Center_(Houston)' =>
<<<ARTICLE
	[[Description::This is the description of Bank_of_America_Center_(Houston).]]
	[[Category:Bank_of_America]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1983]]
	[[Category:John_Burgee_buildings]]
	[[Category:Office_buildings_in_Houston,_Texas]]
	[[Category:Philip_Johnson_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Houston,_Texas]]
	[[Category:Skyscrapers_in_Texas]]
	[[Located_in::Houston]]
	[[Image::Bank_of_America_Center_Houston_1.jpg]]
	[[Height_stories::56]]
	[[Building_name::Bank of America Center]]
	[[Year_built::1/1/1983]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bank_of_America_Corporate_Center' =>
<<<ARTICLE
	[[Description::This is the description of Bank_of_America_Corporate_Center.]]
	[[Category:Bank_of_America]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Buildings_and_structures_completed_in_1992]]
	[[Category:C%C3%A9sar_Pelli_buildings]]
	[[Category:Office_buildings_in_North_Carolina]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Charlotte,_North_Carolina]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bank_of_America_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of Bank_of_America_Plaza.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bank_of_America_Plaza_(Atlanta)' =>
<<<ARTICLE
	[[Description::This is the description of Bank_of_America_Plaza_(Atlanta).]]
	[[Category:Bank_of_America]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1992]]
	[[Category:Office_buildings_in_Atlanta,_Georgia]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_Atlanta,_Georgia]]
	[[Located_in::Atlanta]]
	[[Image::Bankofamerica-atlanta-feb09.jpg]]
	[[Height_stories::55]]
	[[Building_name::Bank of America Plaza]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bank_of_America_Plaza_(Dallas)' =>
<<<ARTICLE
	[[Description::This is the description of Bank_of_America_Plaza_(Dallas).]]
	[[Category:Bank_of_America]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1985]]
	[[Category:Buildings_and_structures_in_Dallas,_Texas]]
	[[Category:Office_buildings_in_Dallas,_Texas]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Dallas,_Texas]]
	[[Image::Bank_of_America_Plaza_-_Dallas.jpg]]
	[[Located_in::Dallas]]
	[[Height_stories::72]]
	[[Building_name::Bank of America Plaza]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bank_of_America_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Bank_of_America_Tower.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bank_of_America_Tower_(New_York_City)' =>
<<<ARTICLE
	[[Description::This is the description of Bank_of_America_Tower_(New_York_City).]]
	[[Category:Bank_of_America]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_2009]]
	[[Category:Leadership_in_Energy_and_Environmental_Design_certified_buildings]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Skyscrapers_over_350_meters]]
	[[Located_in::New_York_City]]
	[[Image::OBP_-_Ext_-_42nd_East.jpg]]
	[[Height_stories::58]]
	[[Building_name::Bank of America Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bell_Atlantic_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Bell_Atlantic_Tower.]]
	[[Category:_1991_architecture]]
	[[Category:Pennsylvania_building_and_structure_stubs]]
	[[Category:Philadelphia_stubs]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Philadelphia,_Pennsylvania]]
	[[Category:Verizon_Communications]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bertelsmann_Building' =>
<<<ARTICLE
	[[Description::This is the description of Bertelsmann_Building.]]
	[[Category:Bertelsmann_AG]]
	[[Category:Buildings_and_structures_completed_in_1990]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Times_Square]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Bloomberg_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Bloomberg_Tower.]]
	[[Category:Bloomberg_L.P.]]
	[[Category:Buildings_and_structures_completed_in_2005]]
	[[Category:Condominiums_in_New_York_City]]
	[[Category:Media_company_headquarters_in_the_United_States]]
	[[Category:New_York_City_building_and_structure_stubs]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Residential_skyscrapers_in_New_York_City]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Blue_Cross_Blue_Shield_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Blue_Cross_Blue_Shield_Tower.]]
	[[Category:Chicago,_Illinois_stubs]]
	[[Category:Insurance_company_headquarters_in_the_United_States]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Boston' =>
<<<ARTICLE
	[[Description::This is the description of Boston.]]
	[[Category:Boston,_Massachusetts]]
	[[Category:Cities_in_Massachusetts]]
	[[Category:County_seats_in_Massachusetts]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Irish-American_culture]]
	[[Category:Irish_American_history]]
	[[Category:Populated_coastal_places_in_Massachusetts]]
	[[Category:Populated_places_established_in_1630]]
	[[Category:Populated_places_in_Suffolk_County,_Massachusetts]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:University_towns_in_the_United_States]]
	[[Located_in_state::Massachusetts]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'California' =>
<<<ARTICLE
	[[Description::This is the description of California.]]
	[[Category:California_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:All_pages_needing_cleanup]]
	[[Category:All_pages_needing_factual_verification]]
	[[Category:California]]
	[[Category:Former_Spanish_colonies]]
	[[Category:States_and_territories_established_in_1850]]
	[[Category:States_of_the_United_States]]
	[[Category:West_Coast_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Capella_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Capella_Tower.]]
	[[Category:Buildings_and_structures_completed_in_1992]]
	[[Category:James_Ingo_Freed_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Minneapolis,_Minnesota]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Carnegie_Hall_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Carnegie_Hall_Tower.]]
	[[Category:_1991_architecture]]
	[[Category:C%C3%A9sar_Pelli_buildings]]
	[[Category:New_York_City_building_and_structure_stubs]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Charlotte,_North_Carolina' =>
<<<ARTICLE
	[[Description::This is the description of Charlotte,_North_Carolina.]]
	[[Category:Charlotte,_North_Carolina]]
	[[Category:Charlotte_metropolitan_area]]
	[[Category:Cities_in_North_Carolina]]
	[[Category:County_seats_in_North_Carolina]]
	[[Category:Populated_places_established_in_1755]]
	[[Category:North_Carolina_navigational_boxes]]
	[[Located_in_state::North_Carolina]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Chase_Tower_(Chicago)' =>
<<<ARTICLE
	[[Description::This is the description of Chase_Tower_(Chicago).]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1969]]
	[[Category:JPMorgan_Chase_buildings]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Located_in::Chicago]]
	[[Image::Chase_Tower,_Chicago.jpg]]
	[[Height_stories::60]]
	[[Building_name::Chase Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Chase_Tower_(Indianapolis)' =>
<<<ARTICLE
	[[Description::This is the description of Chase_Tower_(Indianapolis).]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1990]]
	[[Category:JPMorgan_Chase_buildings]]
	[[Category:Office_buildings_in_Indianapolis,_Indiana]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Indianapolis,_Indiana]]
	[[Image::ChaseTowerIndianapolis.jpg]]
	[[Located_in::Indianapolis]]
	[[Height_stories::48]]
	[[Building_name::Chase Tower]]
	[[Year_built::1/1/1990]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Chicago' =>
<<<ARTICLE
	[[Description::This is the description of Chicago.]]
	[[Category:Chicago,_Illinois]]
	[[Category:Chicago_metropolitan_area]]
	[[Category:Cities_in_Illinois]]
	[[Category:Communities_on_U.S._Route_66]]
	[[Category:County_seats_in_Illinois]]
	[[Category:Irish-American_culture]]
	[[Category:Polish_American_history]]
	[[Category:Populated_places_established_in_1833]]
	[[Category:Populated_places_in_Cook_County,_Illinois]]
	[[Category:Populated_places_in_DuPage_County,_Illinois]]
	[[Category:Populated_places_on_the_Great_Lakes]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:United_States_places_with_Orthodox_Jewish_communities]]
	[[Category:Chicago_templates]]
	[[Category:Illinois_navigational_boxes]]
	[[Category:United_States_City]]
	[[Located_in_state::Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Chicago_Title_and_Trust_Center' =>
<<<ARTICLE
	[[Description::This is the description of Chicago_Title_and_Trust_Center.]]
	[[Category:Buildings_and_structures_completed_in_1992]]
	[[Category:Chicago,_Illinois_stubs]]
	[[Category:Kohn_Pedersen_Fox_buildings]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Chrysler_Building' =>
<<<ARTICLE
	[[Description::This is the description of Chrysler_Building.]]
	[[Category:_1930_architecture]]
	[[Category:All_pages_needing_cleanup]]
	[[Category:Art_Deco_buildings_in_New_York_City]]
	[[Category:Art_Deco_skyscrapers]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_on_the_National_Register_of_Historic_Places_in_Manhattan]]
	[[Category:Chrysler]]
	[[Category:Former_world%27s_tallest_buildings]]
	[[Category:Modernist_architecture_in_New_York]]
	[[Category:National_Historic_Landmarks_in_New_York_City]]
	[[Category:Office_buildings_in_Manhattan]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Vague_or_ambiguous_time_from_January_2011]]
	[[Located_in::New_York_City]]
	[[Image::Chrysler_Building_by_David_Shankbone_Retouched.jpg]]
	[[Building_name::Chrysler Building]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Citigroup_Center' =>
<<<ARTICLE
	[[Description::This is the description of Citigroup_Center.]]
	[[Category:_1977_architecture]]
	[[Category:Bank_buildings_in_the_United_States]]
	[[Category:Citigroup_buildings]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'CitySpire_Center' =>
<<<ARTICLE
	[[Description::This is the description of CitySpire_Center.]]
	[[Category:_1987_architecture]]
	[[Category:Apartments_in_New_York_City]]
	[[Category:Buildings_and_structures_in_Manhattan]]
	[[Category:Residential_skyscrapers_in_New_York_City]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Cleveland' =>
<<<ARTICLE
	[[Description::This is the description of Cleveland.]]
	[[Category:Cities_in_Ohio]]
	[[Category:Cleveland,_Ohio]]
	[[Category:County_seats_in_Ohio]]
	[[Category:Cuyahoga_County,_Ohio]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Populated_places_established_in_1796]]
	[[Category:Populated_places_in_Ohio_with_African_American_majority_populations]]
	[[Category:Populated_places_on_the_Great_Lakes]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:Cleveland_templates]]
	[[Located_in_state::Ohio]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Colorado' =>
<<<ARTICLE
	[[Description::This is the description of Colorado.]]
	[[Category:Colorado_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:Colorado]]
	[[Category:States_and_territories_established_in_1876]]
	[[Category:States_of_the_United_States]]
	[[Category:Use_mdy_dates_from_August_2010]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Columbia_Center' =>
<<<ARTICLE
	[[Description::This is the description of Columbia_Center.]]
	[[Category:Bank_of_America_buildings]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1985]]
	[[Category:Office_buildings_in_Seattle,_Washington]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Seattle,_Washington]]
	[[Image::Columbia_center_from_smith_tower.jpg]]
	[[Located_in::Seattle]]
	[[Building_name::Columbia Center]]
	[[Height_stories::76]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Comcast_Center' =>
<<<ARTICLE
	[[Description::This is the description of Comcast_Center.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Comcast_Center_(office_building)' =>
<<<ARTICLE
	[[Description::This is the description of Comcast_Center_(office_building).]]
	[[Category:_2008_architecture]]
	[[Category:_2008_establishments]]
	[[Category:Building]]
	[[Category:Comcast_Corporation]]
	[[Category:Leadership_in_Energy_and_Environmental_Design_gold_certified_buildings]]
	[[Category:Modernist_architecture_in_Pennsylvania]]
	[[Category:Postmodern_architecture_in_the_United_States]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Philadelphia,_Pennsylvania]]
	[[Category:Telecommunications_company_headquarters_in_the_United_States]]
	[[Located_in::Philadelphia]]
	[[Image::Comcast_Philly.JPG]]
	[[Building_name::Comcast Center]]
	[[Height_stories::58]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Comerica_Bank_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Comerica_Bank_Tower.]]
	[[Category:Bank_buildings_in_the_United_States]]
	[[Category:Buildings_and_structures_completed_in_1987]]
	[[Category:Buildings_and_structures_in_Dallas,_Texas]]
	[[Category:John_Burgee_buildings]]
	[[Category:Office_buildings_in_Dallas,_Texas]]
	[[Category:Philip_Johnson_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Dallas,_Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Dallas' =>
<<<ARTICLE
	[[Description::This is the description of Dallas.]]
	[[Category:All_pages_needing_cleanup]]
	[[Category:Cities_in_Texas]]
	[[Category:Collin_County,_Texas]]
	[[Category:County_seats_in_Texas]]
	[[Category:Dallas,_Texas]]
	[[Category:Dallas_County,_Texas]]
	[[Category:Dallas_%E2%80%93_Fort_Worth_Metroplex]]
	[[Category:Denton_County,_Texas]]
	[[Category:Kaufman_County,_Texas]]
	[[Category:Populated_places_established_in_1841]]
	[[Category:Rockwall_County,_Texas]]
	[[Category:Vague_or_ambiguous_time_from_January_2010]]
	[[Category:Texas_city_templates]]
	[[Located_in_state::Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Denver' =>
<<<ARTICLE
	[[Description::This is the description of Denver.]]
	[[Category:All_pages_needing_cleanup]]
	[[Category:Cities_in_Colorado]]
	[[Category:Colorado_counties]]
	[[Category:County_seats_in_Colorado]]
	[[Category:Denver,_Colorado]]
	[[Category:Denver_metropolitan_area]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Populated_places_established_in_1858]]
	[[Category:Vague_or_ambiguous_time]]
	[[Category:United_States_City]]
	[[Located_in_state::Colorado]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Detroit' =>
<<<ARTICLE
	[[Description::This is the description of Detroit.]]
	[[Category:Cities_in_Michigan]]
	[[Category:County_seats_in_Michigan]]
	[[Category:Detroit,_Michigan]]
	[[Category:Former_United_States_state_capitals]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Metro_Detroit]]
	[[Category:Michigan_Neighborhood_Enterprise_Zone]]
	[[Category:Populated_places_established_in_1701]]
	[[Category:Populated_places_in_Michigan_with_African_American_majority_populations]]
	[[Category:Populated_places_on_the_Great_Lakes]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:Underground_Railroad_locations]]
	[[Category:Wayne_County,_Michigan]]
	[[Category:Michigan_navigational_boxes]]
	[[Category:United_States_City]]
	[[Located_in_state::Michigan]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Duke_Energy_Center' =>
<<<ARTICLE
	[[Description::This is the description of Duke_Energy_Center.]]
	[[Category:_2006_architecture]]
	[[Category:Buildings_and_structures_under_construction_in_the_United_States]]
	[[Category:Office_buildings_in_North_Carolina]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Charlotte,_North_Carolina]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Empire_State_Building' =>
<<<ARTICLE
	[[Description::This is the description of Empire_State_Building.]]
	[[Category:_1931_architecture]]
	[[Category:Accidents_involving_fog]]
	[[Category:Art_Deco_buildings_in_New_York_City]]
	[[Category:Art_Deco_skyscrapers]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_on_the_National_Register_of_Historic_Places_in_Manhattan]]
	[[Category:Family_businesses]]
	[[Category:Fifth_Avenue_(Manhattan)]]
	[[Category:Former_world%27s_tallest_buildings]]
	[[Category:National_Historic_Landmarks_in_New_York_City]]
	[[Category:Office_buildings_in_Manhattan]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Skyscrapers_over_350_meters]]
	[[Category:Visitor_attractions_in_Manhattan]]
	[[Category:Visitor_attractions_in_New_York_City]]
	[[Located_in::New_York_City]]
	[[Image::Empire_State_Building_all.jpg]]
	[[Height_stories::102]]
	[[Building_name::Empire State Building]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Enterprise_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of Enterprise_Plaza.]]
	[[Category:_1980_architecture_in_the_United_States]]
	[[Category:Buildings_and_structures_completed_in_1980]]
	[[Category:Hines_Interests_Limited_Partnership]]
	[[Category:Office_buildings_in_Houston,_Texas]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Houston,_Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Figueroa_at_Wilshire' =>
<<<ARTICLE
	[[Description::This is the description of Figueroa_at_Wilshire.]]
	[[Category:_1990_architecture]]
	[[Category:Office_buildings_in_Los_Angeles,_California]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Los_Angeles,_California]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Florida' =>
<<<ARTICLE
	[[Description::This is the description of Florida.]]
	[[Category:Florida_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:All_pages_needing_cleanup]]
	[[Category:Florida]]
	[[Category:Former_British_colonies]]
	[[Category:Former_Spanish_colonies]]
	[[Category:Peninsulas_of_the_United_States]]
	[[Category:States_and_territories_established_in_1845]]
	[[Category:States_of_the_Confederate_States_of_America]]
	[[Category:States_of_the_Southern_United_States]]
	[[Category:States_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Fontainebleau_Resort_Las_Vegas' =>
<<<ARTICLE
	[[Description::This is the description of Fontainebleau_Resort_Las_Vegas.]]
	[[Category:Buildings_and_structures_in_Paradise,_Nevada]]
	[[Category:Casinos_in_Las_Vegas]]
	[[Category:Companies_that_have_filed_for_Chapter_11_bankruptcy]]
	[[Category:Las_Vegas_Strip]]
	[[Category:Resorts_in_Las_Vegas]]
	[[Category:Skyscraper_hotels_in_the_Las_Vegas_metropolitan_area]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Unfinished_buildings_and_structures]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Fountain_Place' =>
<<<ARTICLE
	[[Description::This is the description of Fountain_Place.]]
	[[Category:_1986_architecture]]
	[[Category:Buildings_and_structures_in_Dallas,_Texas]]
	[[Category:I._M._Pei_buildings]]
	[[Category:Office_buildings_in_Dallas,_Texas]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Dallas,_Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Franklin_Center_(Chicago)' =>
<<<ARTICLE
	[[Description::This is the description of Franklin_Center_(Chicago).]]
	[[Category:AT%26T_buildings]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1989]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Category:Telecommunications_company_headquarters_in_the_United_States]]
	[[Located_in::Chicago]]
	[[Image::_2010-07-12_1240x1860_chicago_at%26t_corporate_center.jpg]]
	[[Height_stories::60]]
	[[Building_name::Franklin Center]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'GE_Building' =>
<<<ARTICLE
	[[Description::This is the description of GE_Building.]]
	[[Category:_1933_architecture]]
	[[Category:Art_Deco_buildings_in_New_York_City]]
	[[Category:Buildings_and_structures_on_the_National_Register_of_Historic_Places_in_Manhattan]]
	[[Category:Landmarks_in_Manhattan]]
	[[Category:Media_company_headquarters_in_the_United_States]]
	[[Category:NBC_buildings]]
	[[Category:Office_buildings_in_Manhattan]]
	[[Category:Rockefeller_Center]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Gas_Company_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Gas_Company_Tower.]]
	[[Category:Headquarters_in_the_United_States]]
	[[Category:Office_buildings_in_Los_Angeles,_California]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Los_Angeles,_California]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Georgia' =>
<<<ARTICLE
	[[Description::This is the description of Georgia.]]
	[[Category:Georgia_(U.S._state)_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
	[[Category:Place_name_disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Goldman_Sachs_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Goldman_Sachs_Tower.]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_2004]]
	[[Category:C%C3%A9sar_Pelli_buildings]]
	[[Category:Leadership_in_Energy_and_Environmental_Design_certified_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Jersey_City,_New_Jersey]]
	[[Located_in::Jersey_City,_New_Jersey]]
	[[Image::_30hudson.jpg]]
	[[Height_stories::42]]
	[[Building_name::Goldman Sachs Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Heritage_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of Heritage_Plaza.]]
	[[Category:_1987_architecture]]
	[[Category:Chevron_Corporation]]
	[[Category:Office_buildings_in_Houston,_Texas]]
	[[Category:Oil_company_headquarters_in_the_United_States]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Houston,_Texas]]
	[[Category:Skyscrapers_in_Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Houston' =>
<<<ARTICLE
	[[Description::This is the description of Houston.]]
	[[Category:Cities_in_Texas]]
	[[Category:Houston,_Texas]]
	[[Category:Populated_coastal_places_in_Texas]]
	[[Category:Populated_places_established_in_1836]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Located_in_state::Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'IDS_Center' =>
<<<ARTICLE
	[[Description::This is the description of IDS_Center.]]
	[[Category:Buildings_and_structures_completed_in_1974]]
	[[Category:John_Burgee_buildings]]
	[[Category:Philip_Johnson_buildings]]
	[[Category:Postmodern_architecture]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Minneapolis,_Minnesota]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Illinois' =>
<<<ARTICLE
	[[Description::This is the description of Illinois.]]
	[[Category:Illinois_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:Illinois]]
	[[Category:States_and_territories_established_in_1818]]
	[[Category:States_of_the_United_States]]
	[[Category:Use_mdy_dates_from_January_2011]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Indiana' =>
<<<ARTICLE
	[[Description::This is the description of Indiana.]]
	[[Category:Indiana_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:Indiana]]
	[[Category:States_and_territories_established_in_1816]]
	[[Category:States_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Indianapolis' =>
<<<ARTICLE
	[[Description::This is the description of Indianapolis.]]
	[[Category:Cities_in_Indiana]]
	[[Category:Consolidated_city%E2%80%93counties_in_the_United_States]]
	[[Category:County_seats_in_Indiana]]
	[[Category:Indianapolis,_Indiana]]
	[[Category:Indianapolis_metropolitan_area]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Marion_County,_Indiana]]
	[[Category:National_Road]]
	[[Category:Planned_cities_in_the_United_States]]
	[[Category:Populated_places_established_in_1821]]
	[[Category:Indiana_city_templates]]
	[[Category:Indianapolis,_Indiana_navigational_boxes]]
	[[Category:Indianapolis,_Indiana_templates]]
	[[Located_in_state::Indiana]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'JPMorgan_Chase_Tower_(Houston)' =>
<<<ARTICLE
	[[Description::This is the description of JPMorgan_Chase_Tower_(Houston).]]
	[[Category:_1981_architecture]]
	[[Category:Building]]
	[[Category:I._M._Pei_buildings]]
	[[Category:JPMorgan_Chase_buildings]]
	[[Category:Office_buildings_in_Houston,_Texas]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_Houston,_Texas]]
	[[Category:Skyscrapers_in_Texas]]
	[[Image::Chase_Tower,_a_block_away.jpg]]
	[[Located_in::Houston]]
	[[Height_stories::75]]
	[[Building_name::JPMorgan Chase Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Jersey_City,_New_Jersey' =>
<<<ARTICLE
	[[Description::This is the description of Jersey_City,_New_Jersey.]]
	[[Category:Cities_in_New_Jersey]]
	[[Category:County_seats_in_New_Jersey]]
	[[Category:Faulkner_Act_Mayor-Council]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Jersey_City,_New_Jersey]]
	[[Category:New_Jersey_Meadowlands_District]]
	[[Category:New_Jersey_Urban_Enterprise_Zones]]
	[[Category:Populated_places_established_in_1633]]
	[[Category:Populated_places_in_Hudson_County,_New_Jersey]]
	[[Category:Populated_places_on_the_Hudson_River]]
	[[Located_in_state::New_Jersey]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'John_Hancock_Center' =>
<<<ARTICLE
	[[Description::This is the description of John_Hancock_Center.]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1970]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Residential_skyscrapers_in_Chicago,_Illinois]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Located_in::Chicago]]
	[[Image::Hancock_tower_2006.jpg]]
	[[Building_name::John Hancock Center]]
	[[Height_stories::100]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Key_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Key_Tower.]]
	[[Category:_1991_architecture]]
	[[Category:Building]]
	[[Category:C%C3%A9sar_Pelli_buildings]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Cleveland,_Ohio]]
	[[Located_in::Cleveland]]
	[[Image::Key_tower.jpg]]
	[[Height_stories::57]]
	[[Building_name::Key Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Las_Vegas' =>
<<<ARTICLE
	[[Description::This is the description of Las_Vegas.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Las_Vegas,_Nevada' =>
<<<ARTICLE
	[[Description::This is the description of Las_Vegas,_Nevada.]]
	[[Category:Cities_in_Nevada]]
	[[Category:Cities_in_the_Mojave_Desert]]
	[[Category:Clark_County,_Nevada]]
	[[Category:County_seats_in_Nevada]]
	[[Category:Gambling_in_the_United_States]]
	[[Category:Las_Vegas,_Nevada]]
	[[Category:Populated_places_established_in_1905]]
	[[Category:Semi-protected_portals]]
	[[Category:Use_mdy_dates_from_February_2011]]
	[[Located_in_state::Nevada]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Legacy_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Legacy_Tower.]]
	[[Category:Building]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Category:University_and_college_academic_buildings_in_the_United_States]]
	[[Image::Legacytowerchicago.jpg]]
	[[Located_in::Chicago]]
	[[Height_stories::72]]
	[[Building_name::The Legacy at Millennium Park]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Los_Angeles' =>
<<<ARTICLE
	[[Description::This is the description of Los_Angeles.]]
	[[Category:All_accuracy_disputes]]
	[[Category:Cities_in_California]]
	[[Category:Cities_in_Los_Angeles_County,_California]]
	[[Category:Communities_on_U.S._Route_66]]
	[[Category:County_seats_in_California]]
	[[Category:Host_cities_of_the_Summer_Olympic_Games]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Los_Angeles,_California]]
	[[Category:Populated_places_established_in_1781]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:Semi-protected_portals]]
	[[Category:Use_mdy_dates_from_December_2010]]
	[[Category:California_city_templates]]
	[[Category:Los_Angeles_navbox_templates]]
	[[Located_in_state::California]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Massachusetts' =>
<<<ARTICLE
	[[Description::This is the description of Massachusetts.]]
	[[Category:Massachusetts_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:Former_British_colonies]]
	[[Category:Massachusetts]]
	[[Category:New_England]]
	[[Category:Northeastern_United_States]]
	[[Category:States_and_territories_established_in_1788]]
	[[Category:States_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'MetLife_Building' =>
<<<ARTICLE
	[[Description::This is the description of MetLife_Building.]]
	[[Category:_1963_architecture]]
	[[Category:Aviation_in_New_York_City]]
	[[Category:Heliports_in_New_York]]
	[[Category:Insurance_company_headquarters_in_the_United_States]]
	[[Category:Metropolitan_Life_Insurance_Company]]
	[[Category:Modernist_architecture_in_New_York]]
	[[Category:Office_buildings_in_Manhattan]]
	[[Category:Pan_Am]]
	[[Category:Pietro_Belluschi_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Walter_Gropius_buildings]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Metropolitan_Life_Insurance_Company_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Metropolitan_Life_Insurance_Company_Tower.]]
	[[Category:_1909_architecture]]
	[[Category:Buildings_and_structures_on_the_National_Register_of_Historic_Places_in_Manhattan]]
	[[Category:Clock_towers_in_the_United_States]]
	[[Category:Former_world%27s_tallest_buildings]]
	[[Category:Metropolitan_Life_Insurance_Company]]
	[[Category:National_Historic_Landmarks_in_New_York_City]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Proposed_buildings_and_structures_in_the_United_States]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Towers_in_New_York]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Metropolitan_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Metropolitan_Tower.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Miami' =>
<<<ARTICLE
	[[Description::This is the description of Miami.]]
	[[Category:Bermuda_Triangle]]
	[[Category:Cities_in_Miami-Dade_County,_Florida]]
	[[Category:County_seats_in_Florida]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Miami,_Florida]]
	[[Category:Populated_places_established_in_1896]]
	[[Category:Populated_places_in_the_United_States_with_Hispanic_majority_populations]]
	[[Category:Port_cities_in_Florida]]
	[[Category:Seaside_resorts_in_Florida]]
	[[Category:Tropics]]
	[[Category:Use_mdy_dates_from_August_2010]]
	[[Category:Florida_city_navigational_boxes]]
	[[Category:WikiProject_Miami]]
	[[Located_in_state::Florida]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Michigan' =>
<<<ARTICLE
	[[Description::This is the description of Michigan.]]
	[[Category:Michigan_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:Michigan]]
	[[Category:States_and_territories_established_in_1837]]
	[[Category:States_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Minneapolis' =>
<<<ARTICLE
	[[Description::This is the description of Minneapolis.]]
	[[Category:Cities_in_Minnesota]]
	[[Category:County_seats_in_Minnesota]]
	[[Category:Hennepin_County,_Minnesota]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Minneapolis,_Minnesota]]
	[[Category:Minneapolis_%E2%80%93_Saint_Paul]]
	[[Category:Populated_places_established_in_1856]]
	[[Category:Populated_places_on_the_Mississippi_River]]
	[[Located_in_state::Minnesota]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Minnesota' =>
<<<ARTICLE
	[[Description::This is the description of Minnesota.]]
	[[Category:Minnesota_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:Minnesota]]
	[[Category:States_and_territories_established_in_1858]]
	[[Category:States_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Mobile,_Alabama' =>
<<<ARTICLE
	[[Description::This is the description of Mobile,_Alabama.]]
	[[Category:Cities_in_Alabama]]
	[[Category:County_seats_in_Alabama]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Mobile,_Alabama]]
	[[Category:Populated_places_established_in_1702]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:United_States_colonial_and_territorial_capitals]]
	[[Located_in_state::Alabama]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Nevada' =>
<<<ARTICLE
	[[Description::This is the description of Nevada.]]
	[[Category:Former_Spanish_colonies]]
	[[Category:Nevada]]
	[[Category:States_and_territories_established_in_1864]]
	[[Category:States_of_the_United_States]]
	[[Category:Western_United_States]]
	[[Category:Nevada_navigational_boxes]]
	[[Category:United_States_State]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'New_Jersey' =>
<<<ARTICLE
	[[Description::This is the description of New_Jersey.]]
	[[Category:Former_British_colonies]]
	[[Category:New_Jersey]]
	[[Category:Peninsulas_of_the_United_States]]
	[[Category:Semi-protected_portals]]
	[[Category:States_and_territories_established_in_1787]]
	[[Category:States_of_the_United_States]]
	[[Category:New_Jersey_navigational_boxes]]
	[[Category:United_States_State]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'New_York' =>
<<<ARTICLE
	[[Description::This is the description of New_York.]]
	[[Category:New_York_templates]]
	[[Category:United_States_State]]
	[[Category:Former_British_colonies]]
	[[Category:New_York]]
	[[Category:Semi-protected_portals]]
	[[Category:States_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'New_York_City' =>
<<<ARTICLE
	[[Description::This is the description of New_York_City.]]
	[[Category:_1624_establishments_in_the_Thirteen_Colonies]]
	[[Category:Cities_in_New_York]]
	[[Category:Former_United_States_state_capitals]]
	[[Category:Former_capitals_of_the_United_States]]
	[[Category:Former_national_capitals]]
	[[Category:Government_of_New_York_City]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Metropolitan_areas_of_the_United_States]]
	[[Category:New_York_City]]
	[[Category:Populated_places_established_in_1624]]
	[[Category:Populated_places_on_the_Hudson_River]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:Summer_Paralympic_Games]]
	[[Category:New_York_City_templates]]
	[[Category:New_York_government_navigational_boxes]]
	[[Located_in_state::New_York]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'North_Carolina' =>
<<<ARTICLE
	[[Description::This is the description of North_Carolina.]]
	[[Category:Former_British_colonies]]
	[[Category:North_Carolina]]
	[[Category:Spanish_colonization_of_the_Americas]]
	[[Category:States_and_territories_established_in_1789]]
	[[Category:States_of_the_Confederate_States_of_America]]
	[[Category:States_of_the_Southern_United_States]]
	[[Category:States_of_the_United_States]]
	[[Category:North_Carolina_navigational_boxes]]
	[[Category:United_States_State]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Ohio' =>
<<<ARTICLE
	[[Description::This is the description of Ohio.]]
	[[Category:Ohio_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:Midwestern_United_States]]
	[[Category:Ohio]]
	[[Category:States_and_territories_established_in_1803]]
	[[Category:States_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Olympia_Centre' =>
<<<ARTICLE
	[[Description::This is the description of Olympia_Centre.]]
	[[Category:_1986_architecture]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Residential_skyscrapers_in_Chicago,_Illinois]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Astor_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of One_Astor_Plaza.]]
	[[Category:Buildings_and_structures_completed_in_1972]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Times_Square]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Atlantic_Center' =>
<<<ARTICLE
	[[Description::This is the description of One_Atlantic_Center.]]
	[[Category:Buildings_and_structures_completed_in_1987]]
	[[Category:Hines_Interests_Limited_Partnership]]
	[[Category:IBM_facilities]]
	[[Category:John_Burgee_buildings]]
	[[Category:Office_buildings_in_Atlanta,_Georgia]]
	[[Category:Philip_Johnson_buildings]]
	[[Category:Postmodern_architecture_in_the_United_States]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Chase_Manhattan_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of One_Chase_Manhattan_Plaza.]]
	[[Category:_1961_architecture]]
	[[Category:Buildings_associated_with_the_Rockefeller_family]]
	[[Category:JPMorgan_Chase_buildings]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Liberty_Place' =>
<<<ARTICLE
	[[Description::This is the description of One_Liberty_Place.]]
	[[Category:_1987_architecture]]
	[[Category:Building]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Philadelphia,_Pennsylvania]]
	[[Located_in::Philadelphia]]
	[[Image::One_liberty_place.JPG]]
	[[Height_stories::63]]
	[[Building_name::One Liberty Place]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Liberty_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of One_Liberty_Plaza.]]
	[[Category:Buildings_and_structures_completed_in_1973]]
	[[Category:New_York_City_stubs]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Museum_Park' =>
<<<ARTICLE
	[[Description::This is the description of One_Museum_Park.]]
	[[Category:_2007_architecture]]
	[[Category:_2010_establishments]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:Museum_places]]
	[[Category:Residential_skyscrapers_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Penn_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of One_Penn_Plaza.]]
	[[Category:Buildings_and_structures_completed_in_1972]]
	[[Category:Pennsylvania_Plaza]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Prudential_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of One_Prudential_Plaza.]]
	[[Category:_1955_architecture]]
	[[Category:Insurance_company_headquarters_in_the_United_States]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Prudential_Financial_buildings]]
	[[Category:Skyscrapers_between_150_and_199_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Shell_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of One_Shell_Plaza.]]
	[[Category:_1971_architecture]]
	[[Category:Office_buildings_in_Houston,_Texas]]
	[[Category:Oil_company_headquarters_in_the_United_States]]
	[[Category:Royal_Dutch_Shell_buildings_and_structures]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Houston,_Texas]]
	[[Category:Skyscrapers_in_Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'One_Worldwide_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of One_Worldwide_Plaza.]]
	[[Category:Buildings_and_structures_completed_in_1989]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Park_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Park_Tower.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Park_Tower_(Chicago)' =>
<<<ARTICLE
	[[Description::This is the description of Park_Tower_(Chicago).]]
	[[Category:Condo-hotels_in_the_United_States]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:Residential_skyscrapers_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Pennsylvania' =>
<<<ARTICLE
	[[Description::This is the description of Pennsylvania.]]
	[[Category:Pennsylvania_navigational_boxes]]
	[[Category:United_States_State]]
	[[Category:Former_British_colonies]]
	[[Category:Northeastern_United_States]]
	[[Category:Pennsylvania]]
	[[Category:States_and_territories_established_in_1787]]
	[[Category:States_of_the_United_States]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Philadelphia' =>
<<<ARTICLE
	[[Description::This is the description of Philadelphia.]]
	[[Category:_1682_establishments_in_the_Thirteen_Colonies]]
	[[Category:All_pages_needing_cleanup]]
	[[Category:Cities_in_Pennsylvania]]
	[[Category:Consolidated_city%E2%80%93counties_in_the_United_States]]
	[[Category:County_seats_in_Pennsylvania]]
	[[Category:Former_United_States_state_capitals]]
	[[Category:Former_capitals_of_the_United_States]]
	[[Category:Former_national_capitals]]
	[[Category:Greek_loanwords]]
	[[Category:Philadelphia,_Pennsylvania]]
	[[Category:Planned_cities_in_the_United_States]]
	[[Category:Populated_places_established_in_1682]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:Semi-protected_portals]]
	[[Category:Vague_or_ambiguous_time_from_May_2010]]
	[[Category:Pennsylvania_city_navigational_boxes]]
	[[Category:Pennsylvania_navigational_boxes]]
	[[Located_in_state::Pennsylvania]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Pittsburgh' =>
<<<ARTICLE
	[[Description::This is the description of Pittsburgh.]]
	[[Category:_1794_establishments]]
	[[Category:Cities_in_Pennsylvania]]
	[[Category:County_seats_in_Pennsylvania]]
	[[Category:Early_American_industrial_centers]]
	[[Category:Pittsburgh,_Pennsylvania]]
	[[Category:Pittsburgh_metropolitan_area]]
	[[Category:Populated_places_established_in_1758]]
	[[Category:Populated_places_in_Allegheny_County,_Pennsylvania]]
	[[Category:Populated_places_on_the_Ohio_River]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:Pennsylvania_templates]]
	[[Located_in_state::Pennsylvania]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Prudential_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Prudential_Tower.]]
	[[Category:_1964_architecture]]
	[[Category:Back_Bay,_Boston]]
	[[Category:Landmarks_in_Boston,_Massachusetts]]
	[[Category:Office_buildings_in_Boston,_Massachusetts]]
	[[Category:Prudential_Financial_buildings]]
	[[Category:Revolving_restaurants]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Boston,_Massachusetts]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'RSA_Battle_House_Tower' =>
<<<ARTICLE
	[[Description::This is the description of RSA_Battle_House_Tower.]]
	[[Category:Buildings_and_structures_completed_in_2007]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Mobile,_Alabama]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Renaissance_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Renaissance_Tower.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Renaissance_Tower_(Dallas)' =>
<<<ARTICLE
	[[Description::This is the description of Renaissance_Tower_(Dallas).]]
	[[Category:_1974_architecture]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_in_Dallas,_Texas]]
	[[Category:Office_buildings_in_Dallas,_Texas]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Dallas,_Texas]]
	[[Located_in::Dallas]]
	[[Image::Dallas_Renaissance_Tower_1.jpg]]
	[[Height_stories::56]]
	[[Year_built::1/1/1974]]
	[[Building_name::Renaissance Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Republic_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of Republic_Plaza.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Place_name_disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'San_Francisco' =>
<<<ARTICLE
	[[Description::This is the description of San_Francisco.]]
	[[Category:California_counties]]
	[[Category:Consolidated_city%E2%80%93counties_in_the_United_States]]
	[[Category:County_seats_in_California]]
	[[Category:Hudson%27s_Bay_Company_trading_posts]]
	[[Category:Infobox_Settlement_US_maintenance]]
	[[Category:Populated_coastal_places_in_California]]
	[[Category:Populated_places_established_in_1776]]
	[[Category:Port_settlements_in_the_United_States]]
	[[Category:San_Francisco,_California]]
	[[Category:California_city_templates]]
	[[Category:California_county_navigational_boxes]]
	[[Category:Northern_California_navigational_boxes]]
	[[Located_in_state::California]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Seattle' =>
<<<ARTICLE
	[[Description::This is the description of Seattle.]]
	[[Category:Cities_in_Washington_(U.S._state)]]
	[[Category:Cities_in_the_Seattle_metropolitan_area]]
	[[Category:County_seats_in_Washington_(U.S._state)]]
	[[Category:Geography_of_Seattle,_Washington]]
	[[Category:Isthmuses]]
	[[Category:King_County,_Washington]]
	[[Category:Neighborhoods_in_Seattle,_Washington]]
	[[Category:Populated_places_established_in_1853]]
	[[Category:Port_settlements_in_Washington_(U.S._state)]]
	[[Category:Seattle,_Washington]]
	[[Located_in_state::Washington_(state)]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Seattle_Municipal_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Seattle_Municipal_Tower.]]
	[[Category:_1990_architecture]]
	[[Category:Building]]
	[[Category:Office_buildings_in_Seattle,_Washington]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Seattle,_Washington]]
	[[Image::Seattle_Municipal_Tower.JPG]]
	[[Located_in::Seattle]]
	[[Building_name::Seattle Municipal Tower]]
	[[Height_stories::62]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'SunTrust_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of SunTrust_Plaza.]]
	[[Category:Bank_company_headquarters_in_the_United_States]]
	[[Category:Buildings_and_structures_completed_in_1992]]
	[[Category:John_C._Portman,_Jr._buildings]]
	[[Category:Office_buildings_in_Atlanta,_Georgia]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Terminal_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Terminal_Tower.]]
	[[Category:Forest_City_Enterprises]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Cleveland,_Ohio]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Texas' =>
<<<ARTICLE
	[[Description::This is the description of Texas.]]
	[[Category:United_States_State]]
	[[Category:Former_Spanish_colonies]]
	[[Category:States_and_territories_established_in_1845]]
	[[Category:States_of_the_Confederate_States_of_America]]
	[[Category:States_of_the_Southern_United_States]]
	[[Category:States_of_the_United_States]]
	[[Category:Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'The_New_York_Times_Building' =>
<<<ARTICLE
	[[Description::This is the description of The_New_York_Times_Building.]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_2007]]
	[[Category:Eighth_Avenue_(Manhattan)]]
	[[Category:Forest_City_Enterprises]]
	[[Category:Media_company_headquarters_in_the_United_States]]
	[[Category:Modernist_architecture_in_New_York]]
	[[Category:Office_buildings_in_Manhattan]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Renzo_Piano_buildings]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:The_New_York_Times]]
	[[Category:Times_Square]]
	[[Located_in::New_York_City]]
	[[Image::Ny-times-tower.jpg]]
	[[Height_stories::52]]
	[[Building_name::The New York Times Building]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Three_First_National_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of Three_First_National_Plaza.]]
	[[Category:_1981_architecture]]
	[[Category:Chicago,_Illinois_stubs]]
	[[Category:Illinois_building_and_structure_stubs]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Three_World_Financial_Center' =>
<<<ARTICLE
	[[Description::This is the description of Three_World_Financial_Center.]]
	[[Category:_1985_architecture]]
	[[Category:American_Express]]
	[[Category:Lehman_Brothers]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:World_Financial_Center]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Times_Square_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Times_Square_Tower.]]
	[[Category:Buildings_and_structures_completed_in_2004]]
	[[Category:New_York_City_building_and_structure_stubs]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Transamerica_Pyramid' =>
<<<ARTICLE
	[[Description::This is the description of Transamerica_Pyramid.]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1972]]
	[[Category:Headquarters_in_the_United_States]]
	[[Category:Modernist_architecture_in_California]]
	[[Category:Office_buildings_in_San_Francisco,_California]]
	[[Category:Pyramids]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_San_Francisco,_California]]
	[[Category:William_Pereira_buildings]]
	[[Located_in::San_Francisco]]
	[[Image::SF_Transamerica_full_CA.jpg]]
	[[Height_stories::48]]
	[[Building_name::Transamerica Pyramid]]
	[[Year_built::1/1/1972]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Trump_International_Hotel_and_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Trump_International_Hotel_and_Tower.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Trump_International_Hotel_and_Tower_(Chicago)' =>
<<<ARTICLE
	[[Description::This is the description of Trump_International_Hotel_and_Tower_(Chicago).]]
	[[Category:_2009_architecture]]
	[[Category:Building]]
	[[Category:Condo-hotels_in_the_United_States]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:Hotels_in_Chicago,_Illinois]]
	[[Category:Residential_skyscrapers_in_Chicago,_Illinois]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscraper_hotels_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_over_350_meters]]
	[[Located_in::Chicago]]
	[[Image::_20090518_Trump_International_Hotel_and_Tower,_Chicago.jpg]]
	[[Height_stories::98]]
	[[Building_name::Trump International Hotel and Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Trump_World_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Trump_World_Tower.]]
	[[Category:Buildings_and_structures_in_Manhattan]]
	[[Category:Condominiums_in_New_York_City]]
	[[Category:Residential_skyscrapers_in_New_York_City]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Two_California_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of Two_California_Plaza.]]
	[[Category:_1992_architecture]]
	[[Category:Arthur_Erickson_buildings]]
	[[Category:Office_buildings_in_Los_Angeles,_California]]
	[[Category:Skyscrapers_between_100_and_149_meters]]
	[[Category:Skyscrapers_in_Los_Angeles,_California]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Two_Liberty_Place' =>
<<<ARTICLE
	[[Description::This is the description of Two_Liberty_Place.]]
	[[Category:_1987_architecture]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Philadelphia,_Pennsylvania]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Two_Prudential_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of Two_Prudential_Plaza.]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1990]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Prudential_Financial_buildings]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Image::_2006-08-16_1580x2800_chicago_two_pru.jpg]]
	[[Located_in::Chicago]]
	[[Building_name::Two Prudential Plaza]]
	[[Height_stories::64]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'U.S._Bank_Tower' =>
<<<ARTICLE
	[[Description::This is the description of U.S._Bank_Tower.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'U.S._Bank_Tower_(Los_Angeles)' =>
<<<ARTICLE
	[[Description::This is the description of U.S._Bank_Tower_(Los_Angeles).]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1989]]
	[[Category:Office_buildings_in_Los_Angeles,_California]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_Los_Angeles,_California]]
	[[Category:U.S._Bank_buildings]]
	[[Image::Los_Angeles_Library_Tower_(small)_crop.jpg]]
	[[Located_in::Los_Angeles]]
	[[Height_stories::73]]
	[[Building_name::U.S. Bank Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'U.S._Steel_Tower' =>
<<<ARTICLE
	[[Description::This is the description of U.S._Steel_Tower.]]
	[[Category:_1970_architecture]]
	[[Category:Buildings_and_structures_completed_in_1970]]
	[[Category:Headquarters_in_the_United_States]]
	[[Category:Office_buildings_in_Pittsburgh,_Pennsylvania]]
	[[Category:Pittsburgh,_Pennsylvania]]
	[[Category:Pittsburgh_metropolitan_area]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Pittsburgh,_Pennsylvania]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Union_Square_(Seattle)' =>
<<<ARTICLE
	[[Description::This is the description of Union_Square_(Seattle).]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_completed_in_1981]]
	[[Category:Buildings_and_structures_completed_in_1989]]
	[[Category:NBBJ_buildings]]
	[[Category:Office_buildings_in_Seattle,_Washington]]
	[[Category:Skyscrapers_between_100_and_149_meters]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Seattle,_Washington]]
	[[Category:Twin_towers]]
	[[Image::Two_Union_Square_2.jpg]]
	[[Located_in::Seattle]]
	[[Year_built::1/1/1989]]
	[[Height_stories::56]]
	[[Building_name::Union Square]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Wachovia_Financial_Center' =>
<<<ARTICLE
	[[Description::This is the description of Wachovia_Financial_Center.]]
	[[Category:_1984_architecture]]
	[[Category:Bank_buildings_in_the_United_States]]
	[[Category:Office_buildings_in_Miami,_Florida]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Miami,_Florida]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Washington' =>
<<<ARTICLE
	[[Description::This is the description of Washington.]]
	[[Category:United_States_State]]
	[[Category:Washington_(U.S._state)_navigational_boxes]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
	[[Category:Place_name_disambiguation_pages]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Water_Tower_Place' =>
<<<ARTICLE
	[[Description::This is the description of Water_Tower_Place.]]
	[[Category:_1975_architecture]]
	[[Category:Condo-hotels_in_the_United_States]]
	[[Category:Condominiums_in_Chicago,_Illinois]]
	[[Category:General_Growth_Properties]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Residential_skyscrapers_in_Chicago,_Illinois]]
	[[Category:Shopping_centers_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Category:Visitor_attractions_in_Chicago,_Illinois]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Wells_Fargo_Center' =>
<<<ARTICLE
	[[Description::This is the description of Wells_Fargo_Center.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
	[[Category:Wells_Fargo_buildings]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Wells_Fargo_Center_(Minneapolis)' =>
<<<ARTICLE
	[[Description::This is the description of Wells_Fargo_Center_(Minneapolis).]]
	[[Category:_1988_architecture]]
	[[Category:Art_Deco_architecture_in_Minnesota]]
	[[Category:Building]]
	[[Category:C%C3%A9sar_Pelli_buildings]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_Minneapolis,_Minnesota]]
	[[Category:Wells_Fargo_buildings]]
	[[Located_in::Minneapolis]]
	[[Image::Wells_Fargo_Center_from_Foshay.jpg]]
	[[Height_stories::57]]
	[[Building_name::Wells Fargo Center]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Wells_Fargo_Plaza' =>
<<<ARTICLE
	[[Description::This is the description of Wells_Fargo_Plaza.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
	[[Category:Wells_Fargo_buildings]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Wells_Fargo_Plaza_(Houston)' =>
<<<ARTICLE
	[[Description::This is the description of Wells_Fargo_Plaza_(Houston).]]
	[[Category:_1983_architecture]]
	[[Category:Building]]
	[[Category:Office_buildings_in_Houston,_Texas]]
	[[Category:Skyscrapers_between_300_and_349_meters]]
	[[Category:Skyscrapers_in_Houston,_Texas]]
	[[Category:Skyscrapers_in_Texas]]
	[[Category:Wells_Fargo_buildings]]
	[[Located_in::Houston]]
	[[Image::Wells_Fargo_Bank_Plaza,_Houston,_from_base.jpg]]
	[[Height_stories::71]]
	[[Year_built::1/1/1979]]
	[[Building_name::Wells Fargo Plaza]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Wells_Fargo_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Wells_Fargo_Tower.]]
	[[Category:All_article_disambiguation_pages]]
	[[Category:All_disambiguation_pages]]
	[[Category:Disambiguation_pages]]
	[[Category:Wells_Fargo_buildings]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Westin_Peachtree_Plaza_Hotel' =>
<<<ARTICLE
	[[Description::This is the description of Westin_Peachtree_Plaza_Hotel.]]
	[[Category:Buildings_and_structures_completed_in_1976]]
	[[Category:Hotels_established_in_1976]]
	[[Category:Hotels_in_Atlanta,_Georgia]]
	[[Category:John_C._Portman,_Jr._buildings]]
	[[Category:Revolving_restaurants]]
	[[Category:Skyscraper_hotels_in_the_United_States]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Westin_hotels]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Williams_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Williams_Tower.]]
	[[Category:_1983_architecture]]
	[[Category:John_Burgee_buildings]]
	[[Category:Office_buildings_in_Houston,_Texas]]
	[[Category:Philip_Johnson_buildings]]
	[[Category:Skyscrapers_between_250_and_299_meters]]
	[[Category:Skyscrapers_in_Houston,_Texas]]
	[[Category:Skyscrapers_in_Texas]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Willis_Tower' =>
<<<ARTICLE
	[[Description::This is the description of Willis_Tower.]]
	[[Category:_1973_architecture]]
	[[Category:Building]]
	[[Category:Buildings_and_structures_on_U.S._Route_66]]
	[[Category:Former_world%27s_tallest_buildings]]
	[[Category:Landmarks_in_Chicago,_Illinois]]
	[[Category:Office_buildings_in_Chicago,_Illinois]]
	[[Category:Retail_company_headquarters_in_the_United_States]]
	[[Category:Skidmore,_Owings_and_Merrill_buildings]]
	[[Category:Skyscrapers_in_Chicago,_Illinois]]
	[[Category:Skyscrapers_over_350_meters]]
	[[Category:United_Airlines]]
	[[Category:Visitor_attractions_along_U.S._Route_66]]
	[[Category:Visitor_attractions_in_Chicago,_Illinois]]
	[[Located_in::Chicago]]
	[[Image::Sears_Tower_ss.jpg]]
	[[Height_stories::108]]
	[[Building_name::Willis Tower]]
ARTICLE
,
//------------------------------------------------------------------------------	
			'Woolworth_Building' =>
<<<ARTICLE
	[[Description::This is the description of Woolworth_Building.]]
	[[Category:_1913_architecture]]
	[[Category:Buildings_and_structures_on_the_National_Register_of_Historic_Places_in_Manhattan]]
	[[Category:Cass_Gilbert_buildings]]
	[[Category:F._W._Woolworth_Company_buildings_and_structures]]
	[[Category:Fordham_University]]
	[[Category:Former_world%27s_tallest_buildings]]
	[[Category:Gothic_Revival_architecture_in_New_York]]
	[[Category:National_Historic_Landmarks_in_New_York_City]]
	[[Category:Neo-Gothic_skyscrapers]]
	[[Category:Office_buildings_in_New_York_City]]
	[[Category:Retail_company_headquarters_in_the_United_States]]
	[[Category:Skyscrapers_between_200_and_249_meters]]
	[[Category:Skyscrapers_in_New_York_City]]
	[[Category:Terracotta]]
	[[Category:Visitor_attractions_in_Manhattan]]
ARTICLE
	
	);
    
}
