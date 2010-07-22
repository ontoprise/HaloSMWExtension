<?php

class SMWQRCPriorityCalculator {
	
	private static $instance;
	
	/*
	 * singleton
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/*
	 * Computes a new access frequency value based on the afing factor
	 */
	public function computeNewAccessFrequency($af){
		global $accessFrequencyAgingFactor;
		return round($accessFrequencyAgingFactor * $af);
	}
	
	/*
	 * Computes a new access frequency value based on the afing factor
	 */
	public function computeNewInvalidationFrequency($if){
		global $invalidationFrequencyAgingFactor;
		$iF = round($invalidationFrequencyAgingFactor * $if);
		return $iF;
	}
	
	/*
	 * Computes the priority of a query regarding the next query update run
	 */
	public function computeQueryUpdatePriority($lastUpdate, $af, $if){
		global $lastUpdateTimeStampWeight, $accessFrequencyWeight, $invalidationFrequencyWeight;
		return $lastUpdate*$lastUpdateTimeStampWeight - $af*$accessFrequencyWeight - $if*$invalidationFrequencyWeight;
	}
}