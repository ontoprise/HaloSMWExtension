/*  Copyright 2011, ontoprise GmbH
*  This file is part of the FacetedSearch-Extension.
*
*   The FacetedSearch-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The FacetedSearch-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
 * @ingroup FacetedSearchScripts
 * @author: Thomas Schweitzer
 */

if (typeof FacetedSearch == "undefined") {
// Define the FacetedSearch module	
	var FacetedSearch = { 
		classes : {}
	};
}

/**
 * @class DateFacetClusterer
 * This class clusters the values of a facet with type "date".
 * 
 */
FacetedSearch.classes.DateFacetClusterer = function (facetName, plainName) {
	// Call the constructor of the super class
	var that = FacetedSearch.classes.FacetClusterer(facetName, plainName);

	//--- Constants ---
	
	//--- Private members ---

	
	// string - The statistics for a date are not retrieved from the original
	//          date field.
	var mStatisticsFacet;
	
	// int - ID of the date/time field that is incremented
	var mIncrementField;
	

	/**
	 * Constructor for the DateFacetClusterer class.
	 * 
	 * @param string facetName
	 * 		The full name of the facet whose values are clustered. 
	 */
	function construct(facetName, plainName) {
		mStatisticsFacet = 'smwh_' + plainName + '_datevalue_l';
	};
	that.construct = construct;
	
	/**
	 * For clustering the statistics of a facet have to be retrieved to find its
	 * min and max values. Normally this is the field stored in mFacetName. Sub 
	 * classes can overwrite this method it a different field is to be used.
	 */
	that.getStatisticsField = function () {
		return mStatisticsFacet;
	}
	
	/**
	 * Formats a boundary value of a cluster for display in the UI.
	 * @param string value
	 * 		The value to format. It is a long value for a date/time like
	 * 		20110302123442
	 * @return string
	 * 		The formated value e.g. 2011-03-02 12:34:42
	 */
	that.formatBoundary = function (value) {
		var dto = FacetedSearch.classes.DateTime(value);
		
		switch (mIncrementField) {
			case dto.YEAR:
				return dto.getYear();
			case dto.MONTH:
				return dto.getYear() + '-' + dto.getMonthStr();
			case dto.DAY:
				return dto.getYear() + '-' + dto.getMonthStr() + '-' + dto.getDayStr();
			case dto.HOUR:
				return dto.getDayStr() + '-' + dto.getHourStr()+ ':' +dto.getMinStr();
			case dto.MINUTE:
				return dto.getHourStr()+ ':' +dto.getMinStr();
			case dto.SECOND:
				return dto.getHourStr()+ ':' +dto.getMinStr();
		}
	}

	
	/**
	 * This function generates clusters for date values between min and max.
	 * Both min and max date are encoded as long values i.e. 2011-03-16 12:23:42
	 * becomes 20110316122342.
	 * 
	 * @param {string} min
	 * 		The minimal date value of the value range.
	 * @param {string} max
	 * 		The maximal date value of the value range.
	 */
	that.makeClusters = function makeClusters(min, max) {
		var incr = findIncrement(min, max);
		mIncrementField = incr.getIncrementField();
		
		var values = [];
		var lowerVal, upperVal, tempVal;
		
		lowerVal = incr.next();
		while ((upperVal = incr.next()) !== null) {
			tempVal = upperVal.copy();
			tempVal.aSecondBefore();
			values.push([lowerVal.toEncodedLong(), tempVal.toEncodedLong()]);
			lowerVal = upperVal;
		}
		return values;
	}
	
		
	/**
	 * Calculates a the best suited increment for ranges between the data/time
	 * values min and max that are given as strings that represent long values.
	 * @param {long} min
	 * 		Lower bound of date/time
	 * @param {long} max
	 * 		Upper bound of date/time
	 * 
	 * @return {Object} 
	 * 		This object contains the increments for year, month, day, hour,
	 * 		minute and mSecond
	 */
	function findIncrement(min, max) {
		var incr = FacetedSearch.classes.DateTimeIncrement(min, max, that.NUM_CLUSTERS);
		return incr;		
	}
	
	construct(facetName, plainName);
	return that;
	
}

/**
 * @class DateTime
 * This class represents dates and time and operations on them.
 * 
 */
FacetedSearch.classes.DateTime = function (dateTime) {
	var that = {}
	//--- Constants ---
	
	// Fields of a DateTime object
	that.YEAR   = 0;
	that.MONTH  = 1;
	that.DAY    = 2;
	that.HOUR   = 3;
	that.MINUTE = 4;
	that.SECOND = 5;
	
	//--- Private members ---

	var mSec = 0;
	var mMin = 0;
	var mHour = 0;
	var mDay = 1;
	var mMonth = 1;
	var mYear = 0;

	//--- Getters/Setters ---
	that.getYear = function()	{ return mYear; }
	that.getMonth = function()	{ return mMonth; }
	that.getDay = function()	{ return mDay; }
	that.getHour = function()	{ return mHour; }
	that.getMin = function()	{ return mMin; }
	that.getSec = function()	{ return mSec; }
	that.getMonthStr = function()	{ return mMonth < 10 ? '0'+mMonth : mMonth.toString(); }
	that.getDayStr = function()		{ return mDay < 10   ? '0'+mDay   : mDay.toString(); }
	that.getHourStr = function()	{ return mHour < 10  ? '0'+mHour  : mHour.toString(); }
	that.getMinStr = function()		{ return mMin < 10   ? '0'+mMin   : mMin.toString(); }
	that.getSecStr = function()		{ return mSec < 10   ? '0'+mSec   : mSec.toString(); }
	
	//--- Public methods ---
	
	/**
	 * Constructor for the DateTime class.
	 * 
	 * @param string dateTime
	 * 		The string representation of date/time as long value e.g. 20110302123442. 
	 */
	function construct(dateTime) {
		if (dateTime) {
			initWithEncodedLong(dateTime);
		}
	};
	that.construct = construct;

	/**
	 * Converts a date/time given as long value e.g. 20110316122342 and stores
	 * year, month, day, hour, min, sec in this object as numbers.
	 * 
	 * @param {string} dateTime
	 * 		The encoded date/time
	 * @return {Object}
	 * 		A date/time object
	 */
	function initWithEncodedLong(dateTime) {
		if (typeof dateTime != "string") {
			dateTime = dateTime.toString();
		}
		var len = dateTime.length;
		
		mSec   = parseInt(dateTime.substring(len-2, len), 10);
		mMin   = parseInt(dateTime.substring(len-4, len-2), 10);
		mHour  = parseInt(dateTime.substring(len-6, len-4), 10);
		mDay   = parseInt(dateTime.substring(len-8, len-6), 10);
		mMonth = parseInt(dateTime.substring(len-10, len-8), 10);
		mYear  = parseInt(dateTime.substring(0, len-10), 10);
		
	}
	that.initWithEncodedLong = initWithEncodedLong;

	/**
	 * Initializes this object with an object that may contain one or more of
	 * the fields year, month, day, hour, min, sec in this object as numbers.
	 * Fields that are not present are initialized with 0 or 1 (month, day).
	 * 
	 * @param {Object} dateTime
	 * 		Object with the optional fields  year, month, day, hour, min, sec
	 * @return {Object}
	 * 		A date/time object
	 */
	function initWithObject(dateTime) {
		
		mSec   = dateTime.sec || 0;
		mMin   = dateTime.min || 0;
		mHour  = dateTime.hour || 0;
		mDay   = dateTime.day || 1;
		mMonth = dateTime.month || 1;
		mYear  = dateTime.year || 0;
		
	}
	that.initWithObject = initWithObject;
	
	/**
	 * Returns a copy of this object.
	 * 
	 * @return DateTime
	 * 		The copy of this object.
	 */
	function copy() {
		var c = FacetedSearch.classes.DateTime();
		c.initWithObject({
			year:  mYear,
			month: mMonth,
			day:   mDay,
			hour:  mHour,
			min:   mMin,
			sec:   mSec
		});
		return c;
	}
	that.copy = copy;
	
	/**
	 * Adds the given value to the given field.
	 * 
	 * @param {double} value
	 * 		A positive value to add.
	 * @param {int} field
	 * 		One of the constants for a field
	 */
	function add(value, field) {
		switch (field) {
			case that.SECOND:
				mSec += value;
				break;
			case that.MINUTE:
				mMin += value;
				break;
			case that.HOUR:
				mHour += value;
				break;
			case that.DAY:
				mDay += value;
				break;
			case that.MONTH:
				mMonth += value;
				break;
			case that.YEAR:
				mYear += value;
				break;
		}
		correctOverflow();
	}
	that.add = add;
	
	/**
	 * Subtracts one second from this date.
	 */
	function aSecondBefore() {
		--mSec;
		if (mSec < 0) {
			mSec = 59;
			--mMin;
		}
		if (mMin < 0) {
			mMin = 59;
			--mHour;
		}
		if (mHour < 0) {
			mHour = 23;
			--mDay;
		}
		if (mDay == 0) {
			--mMonth;
			if (mMonth == 0) {
				mMonth = 12
				--mYear;
			}
			var daysPerMonth = [0,31,28,31,30,31,30,31,31,30,31,30,31];
			var maxDays = daysPerMonth[mMonth];
			if (maxDays == 28) {
				//Leap year?
				if (mYear % 4 == 0 && (mYear % 100 != 0 || mYear %400 == 0)) {
					maxDays = 29;
				}
			}
			mDay = maxDays;
		}
		
	}
	that.aSecondBefore = aSecondBefore;
	
	/**
	 * Calculates the difference of this date and the other date otherDT. 
	 * This date must be larger than otherDT
	 * @param {DateTime} otherDT
	 * 		The other date/time
	 * @param {int} field
	 * 		Calculates the difference in the unit of the field i.e. in years,
	 * 		month, days etc.
	 * @return {DateTime} 
	 * 		The difference of both date/times
	 * 		
	 */
	function diff(otherDT, field) {
		var otherYear = otherDT.getYear();
		var otherMonth = otherDT.getMonth();
		var otherDay = otherDT.getDay();
		var otherHour = otherDT.getHour();
		var otherMin = otherDT.getMin();
		var otherSec = otherDT.getSec();
		
		var thisDate = new Date(mYear, mMonth-1, mDay, mMin, mHour, mMin, mSec);
		var otherDate = new Date(otherYear, otherMonth-1, otherDay, otherHour, otherMin, otherSec);
		var diff = thisDate.getTime() - otherDate.getTime();
		
		switch (field) {
			case that.YEAR:
				return diff / 31557600000;
			case that.MONTH:
				return diff / 2629800000;
			case that.DAY:
				return diff / 86400000;
			case that.HOUR:
				return diff / 3600000;
			case that.MINUTE:
				return diff / 60000;
			case that.SECOND:
				return diff / 1000;
		}
	}
	that.diff = diff;
	
	/**
	 * A date/time may have fields with fractional values e.g. 42.5734 seconds.
	 * This function rounds all fields and handles resulting overflows.
	 * 
	 */
	function round() {
		mYear = Math.round(mYear);
		mMonth = Math.round(mMonth);
		mDay = Math.round(mDay);
		mHour = Math.round(mHour);
		mMin = Math.round(mMin);
		mSec = Math.round(mSec);
		correctOverflow();
	}
	that.round = round;
	
	/**
	 * Encodes this date as a long value e.g. 20110302123442
	 * @return {long}
	 * 		Encoded date/time
	 */
	function toEncodedLong() {
		return mSec +
			   mMin   * 100 +
			   mHour  * 10000 +
			   mDay   * 1000000 +
			   mMonth * 100000000 +
			   mYear  * 10000000000;
	}
	that.toEncodedLong = toEncodedLong;
	
	/**
	 * If a field of this date/time is too large, this overflow is corrected by
	 * incrementing other fields.
	 */
	function correctOverflow() {
		if (mSec > 59) {
			var sec = Math.floor(mSec);
			var frac = mSec - sec;
			mSec = sec % 60 + frac;
			var min = Math.floor(sec/60);
			mMin += min;
		}
		if (mMin > 59) {
			var min = Math.floor(mMin);
			var frac = mMin - min;
			mMin = min % 60 + frac;
			var h = Math.floor(min/60);
			mHour += h;
		}
		if (mHour > 23) {
			var h = Math.floor(mHour);
			var frac = mHour - h;
			mHour = h % 24 + frac;
			var d = Math.floor(h/24);
			mDay += d;
		}
		while (mDay > 28) {
			var daysPerMonth = [0,31,28,31,30,31,30,31,31,30,31,30,31];
			var maxDays = daysPerMonth[mMonth];
			if (maxDays == 28) {
				//Leap year?
				if (mYear % 4 == 0 && (mYear % 100 != 0 || mYear %400 == 0)) {
					maxDays = 29;
				}
			}
			if (mDay > maxDays) {
				mDay -= maxDays;
				++mMonth;
				if (mMonth > 12) {
					mMonth = 1;
					++mYear;
				}
			} else {
				break;
			}
		}
		if (mMonth > 12) {
			var mon = Math.floor(mMonth);
			var frac = mMonth - mon;
			mMonth = (mon-1) % 12 + frac + 1;
			var y = Math.floor((mon-1)/12);
			mYear += y;
		}
		
	}
	
	construct(dateTime);
	return that;
}

/**
 * @class DateTimeIncrement
 * This class represents increments on date/times. It is derived from
 * FacetedSearch.classes.DateTime.
 * 
 */
FacetedSearch.classes.DateTimeIncrement = function(minDT, maxDT, numSteps){
	//--- Constants ---
	
	//--- Private members ---
	var that = FacetedSearch.classes.DateTime();
	
	// DateTime - Rounded start of the range that is incremented
	var mStart;
	
	// DateTime - Rounded end of the range that is incremented
	var mEnd;

	// double - The increment
	var mIncrement;
	
	// int - Only one field of a date/time is the basis for incrementing e.g.
	//       month.
	var mIncrementField;
	
	// DateTime - The current date while incrementing with the method next()
	// from the start to the end of the range. This value may contain fractions
	// in the fields of a date e.g. month = 2.374
	var mCurrent;
	
	// int - The current step while iterating over the range
	var mCurrentStep;
	
	// int - The number of steps the range is divided into.
	var mNumSteps;
	
	//--- Getters / Setters ---
	that.getIncrementField = function () { return mIncrementField; }
	
	//--- Functions ---
	
	/**
	 * Constructor
	 * @param {string} minDT
	 * 		A string with an encoded lower bound of date/time
	 * @param {string} maxDT
	 * 		A string with an encoded upper bound of date/time
	 * @param {int} numSteps
	 * 		The number of steps between lower and upper bound determine the
	 * 		increment.
	 */
	function construct(minDT, maxDT, numSteps) {
		findIncrement(minDT, maxDT, numSteps);
		mCurrent = mStart.copy();
		mCurrentStep = 0;
		mNumSteps = numSteps;
	}

	/**
	 * Initializes this object by adjusting the range between a start and end 
	 * date and calculating the increment value.
	 * 
	 * @param {string} minDT
	 * 		A string with an encoded lower bound of date/time
	 * @param {string} maxDT
	 * 		A string with an encoded upper bound of date/time
	 * @param {int} numSteps
	 * 		The number of steps between lower and upper bound determine the
	 * 		increment.
	 */
	function findIncrement(minDT, maxDT, numSteps){
		var minDT = FacetedSearch.classes.DateTime(minDT);
		var maxDT = FacetedSearch.classes.DateTime(maxDT);
		var diff;
		
		mStart = FacetedSearch.classes.DateTime();
		mEnd = FacetedSearch.classes.DateTime();
		
		var start = { year : minDT.getYear() };
		var end = { 
				year:maxDT.getYear(),
				month:12,
				day:31,
				hour:23,
				min:59,
				sec:59
		};
		mIncrementField = false; 
		
		// Year
		var diff = maxDT.diff(minDT, maxDT.YEAR);
		if (diff >= numSteps) {
			mIncrementField = that.YEAR;
		} 
		if (mIncrementField === false) {
			var diff = maxDT.diff(minDT, maxDT.MONTH);
			start.month = minDT.getMonth();
			end.month = maxDT.getMonth();
			if (diff >= numSteps) {
				mIncrementField = that.MONTH;
			} 
		} 
		if (mIncrementField === false) {
			var diff = maxDT.diff(minDT, maxDT.DAY);
			start.day = minDT.getDay();
			end.day = maxDT.getDay();
			if (diff >= numSteps) {
				mIncrementField = that.DAY;
			} 
		} 
		if (mIncrementField === false) {
			var diff = maxDT.diff(minDT, maxDT.HOUR);
			start.hour = minDT.getHour();
			end.hour = maxDT.getHour();
			if (diff >= numSteps) {
				mIncrementField = that.HOUR;
			} 
		} 
		if (mIncrementField === false) {
			var diff = maxDT.diff(minDT, maxDT.MINUTE);
			start.minute = minDT.getMin();
			end.minute = maxDT.getMin();
			if (diff >= numSteps) {
				mIncrementField = that.MINUTE;
			} 
		} 
		if (mIncrementField === false) {
			var diff = maxDT.diff(minDT, maxDT.SECOND);
			start.second = minDT.getSec();
			end.second = maxDT.getSec();
			mIncrementField = that.SECOND;
		} 

		mStart.initWithObject(start);
		mEnd.initWithObject(end);
		diff = mEnd.diff(mStart, mIncrementField);				
		mIncrement = diff / numSteps;
		
	}
	
	/**
	 * Resets the current date for the iteration with method next()
	 */
	function reset() {
		mCurrent = mStart.copy();
		mCurrentStep = 0;
	}
	that.reset = reset;
	
	/**
	 * Iterates over the range from start to end date and returns a date for
	 * each call. null is returned after the end date was reached.
	 * 
	 * @return DateTime
	 * 		The next date in the range
	 */
	function next() {
		if (mCurrentStep > mNumSteps) {
			return null;
		}
		if (mCurrentStep == mNumSteps) {
			mCurrentStep++;
			return mEnd;
		}
		var result = mCurrent.copy();
		result.round();
		
		mCurrentStep++;
		mCurrent.add(mIncrement, mIncrementField);
		return result;
	}
	that.next = next;
	
	construct(minDT, maxDT, numSteps);
	return that;
}