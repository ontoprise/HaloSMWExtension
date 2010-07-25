<?php

global $cacheEntryAgeWeight, $accessFrequencyWeight, $invalidationFrequencyWeight, $invalidWeight;

/*
 * Weight the cache entry's age, when computing a query's update priority
 * 
 * The cache entry's age in seconds * $cacheEntryAgeWeight will be added to the query's update priority 
 */
$cacheEntryAgeWeight = 1;

/*
 * Weight the access frequency of a cache entry, when computing a query's update priority
 * 
 * The cache entry's accress frequency * $accessFrequencyWeight will be added to the query's update priority
 */
$accessFrequencyWeight = 60*60; 

/*
 * Weight the unvalidation frequency of a cache entry, when computing a query's update priority
 * 
 * The cache entry's invalidation frequency * $invalidationFrequencyWeight will be added to the query's update priority
 */
$invalidationFrequencyWeight = 60*60;

/*
 * Weight invalid cache entry's, when computing a query's update priority
 * 
 * The cache entry's invalidation status (1 or 0) * $invalidWeight will be added to the query's update priority
 */
$invalidWeight = 60*60*20;



global $accessFrequencyAgingFactor, $invalidationFrequencyAgingFactor;

/*
 * Decrease a cache entry's access frequency when the cache entry is updated by the Cache Updator
 * 
 * The cache entry's new access frequency will be set to old access frequency * $accessFrequencyAgingFactor
 */
$accessFrequencyAgingFactor = 0.5;

/*
 * Decrease a cache entry's invalidation frequency when the cache entry is updated by the Cache Updator
 * 
 * The cache entry's new invalidation frequency will be set to old invalidation frequency * $invalidationFrequencyAgingFactor 
 */
$invalidationFrequencyAgingFactor = 0.5;



global $showInvalidatedCacheEntries, $invalidateParserCache;

/*
 * Denotes whether cache entries will be displayed although they are invalidated. 
 * 
 * Choose between performance (false) and accuracy (true). 
 */
$showInvalidatedCacheEntries = true;

/*
 * Denotes whether the Parser Cache of an article will be invalidated, if a cache entry for a query in this
 * article has been updated or invalidated.  
 * 
 * Choose between performance (false) and accuracy (true). 
 */
$invalidateParserCache = true;