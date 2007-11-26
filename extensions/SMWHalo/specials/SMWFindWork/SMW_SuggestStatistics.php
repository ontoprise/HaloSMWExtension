<?php
/*
 * Created on 26.11.2007
 *
 * Author: kai
 */
 
 abstract class SMWSuggestStatistics {
 	
 	public abstract function getLastEditedPages($username, $intersectWithGIs, $requestoptions);
 	
 	public abstract function getLastEditedCategories($username, $intersectWithGIs, $requestoptions);
 }
?>
