<?php
/*
 * Created on 26.11.2007
 *
 * Author: kai
 */
 
 abstract class SMWSuggestStatistics {
 	
 	/**
 	 * Returns pages edited by $username, which have gardening issues of the given type.
 	 * 
 	 * @param $bot_id 
 	 * @param $gi_class
 	 * @param $gi_type
 	 * @param $username
 	 * @param $requestoptions
 	 */
 	public abstract function getLastEditedPages($botID, $gi_class, $gi_type, $username, $requestoptions);
 	
 	/**
 	 * Returns pages which are member of the same category as articles edited by $username 
 	 * and which have gardening issues of the given type.
 	 * 
 	 * @param $bot_id 
 	 * @param $gi_class
 	 * @param $gi_type
 	 * @param $username
 	 * @param $requestoptions
 	 */
 	public abstract function getLastEditedPagesOfSameCategory($botID, $gi_class, $gi_type, $username, $requestoptions) ;
	
	/**
	 * Returns undefined categories which are used on articles edited by $username
	 * 
	 * @param $username
 	 * @param $requestoptions
	 */
 	public abstract function getLastEditPagesOfUndefinedCategories($username, $requestoptions) ;
 	
 	/**
	 * Returns undefined properties which are used on articles edited by $username
	 * 
	 * @param $username
 	 * @param $requestoptions
	 */
 	public abstract function getLastEditPagesOfUndefinedProperties($username, $requestoptions) ;
 	
 	/**
 	 * Returns an array of $limit annotations (non-nary) which are not yet rated.
 	 * 
 	 * @param $limit integer
 	 * @return array of titles (subject, predicate, objectOrValue)
 	 */
 	public abstract function getAnnotationsForRating($limit);
 }
?>
