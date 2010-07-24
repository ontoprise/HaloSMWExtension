<?php

global $lastUpdateTimeStampWeight, $accessFrequencyWeight, $invalidationFrequencyWeight, $invalidWeight;

$lastUpdateTimeStampWeight = 1;

$accessFrequencyWeight = 60*60; //query access makes query 1h older

$invalidationFrequencyWeight = 60*60;

$invalidWeight = 60*60*5;



global $accessFrequencyAgingFactor, $invalidationFrequencyAgingFactor;

$accessFrequencyAgingFactor = 0.5;

$invalidationFrequencyAgingFactor = 0.5;



global $showInvalidatedCacheEntries, $invalidateParserCache;

$showInvalidatedCacheEntries = true;

$invalidateParserCache = true;