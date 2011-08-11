/**
*   Implementation of editdistance algorithms:
*  
*	Copyright 2009, ontoprise GmbH
*   Author: Kai Kühn
*
*   This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.	
*  
*	jaro distance
*	jaro-winkler distance
*	levenshtein distance
*	levenshtein-damerau distance
*/


#include <my_global.h>
#include <my_sys.h>
#include <mysql.h>
#include <m_ctype.h>
#include <m_string.h>

int maxval(int v1, int v2) {
	return v1 > v2 ? v1 : v2;
}

int minval(int v1, int v2) {
	return v1 < v2 ? v1 : v2;
}   

char * get_common_characters(char * string1, char * string2, unsigned long ying_lengthp, unsigned long yang_lengthp, unsigned long distanceSep) {
        char * returnCommons = new char[ying_lengthp*yang_lengthp+1];
        char * copy = new char[yang_lengthp];
		strncpy(copy, string2, yang_lengthp);
		unsigned int appendTo = 0;
        for (size_t i = 0; i < ying_lengthp; i++) {
            char ch = string1[i];
            bool foundIt = false;
            for (size_t j = maxval(0, i - distanceSep); !foundIt && j < minval(i + distanceSep, yang_lengthp); j++) {
                if (copy[j] == ch) {
                    foundIt = true;
                    returnCommons[appendTo] = ch;
					appendTo++;
                    copy[j] = '#';
                }
            }
        }
		delete [] copy;
		returnCommons[appendTo] = 0;
        return returnCommons;
    }

int get_prefix_length(char * string1, char * string2, unsigned long ying_lengthp, unsigned long yang_lengthp) {
    int n = minval(6, minval(ying_lengthp, yang_lengthp));
    for (int i = 0; i < n; i++) {
        if (string1[i] != string2[i]) {
            return i;
        }
    }
    return n;
}

double  jaro_distance(char *string1, char *string2, unsigned long ying_lengthp, unsigned long yang_lengthp)

{
		int halflen = minval(ying_lengthp, yang_lengthp) / 2 + 1;
        char * common1 = get_common_characters(string1, string2, ying_lengthp, yang_lengthp, halflen);
        char * common2 = get_common_characters(string2, string1, yang_lengthp, ying_lengthp, halflen);
        if (strlen(common1) == 0 || strlen(common2) == 0) {
            return 0.0f;
        }
        if (strlen(common1) != strlen(common2)) {
            return 0.0f;
        }
        int transpositions = 0;
        for (size_t i = 0; i < strlen(common1); i++) {
            if (common1[i] != common2[i]) {
                transpositions++;
            }
        }
        transpositions /= 2;
        double dist = (
				(double) strlen(common1) / (double) ying_lengthp 
			  + (double) strlen(common2) / (double) yang_lengthp 
			  + (double) (strlen(common1) - transpositions) / (double) strlen(common1)) / 3.0f;
		delete [] common1;
		delete [] common2;
		return dist;

}

double  jarowinkler_distance(char *string1, char *string2, unsigned long ying_lengthp, unsigned long yang_lengthp) {
	double jarodist = jaro_distance(string1, string2, ying_lengthp, yang_lengthp);
	int prefixLength = get_prefix_length(string1, string2, ying_lengthp, yang_lengthp);
    return jarodist + (double) prefixLength * 0.1f * (1.0f - jarodist);
}

/*
* Helper function: Calc minimum of two values.
*/
unsigned long minimum(unsigned long m1, unsigned long m2) {
	return m1 < m2 ? m1 : m2;
}

/*
* Calculates Levenshtein distance
* s: term1
* t: term2
* ls: length of term1
* lt: length of term2
*/
longlong levenshteinDistance(char *s, char *t, unsigned long ls, unsigned long lt) {
   unsigned long **d = new unsigned long*[ls+1];
   unsigned long i, j, cost;

   // initialize
   for (i=0; i < ls+1;i++) {
	   d[i] = new unsigned long[lt+1];
       d[i][0] = i;
   }
   for (j=0; j < lt+1;j++) {
       d[0][j] = j;
   }

   // calc LS-Distance
   for (i=1; i < ls+1;i++) {
       for (j=1; j < lt+1;j++) {
          if (s[i-1] == t[j-1]) { 
          	cost = 0; 
          }
          else { 
          	cost = 1; 
          }
          d[i][j] = minimum(minimum(d[i-1][j] + 1, 
                             d[i][j-1] + 1),
                             d[i-1][j-1] + cost);
	   }
   }
   unsigned long lst_distance = d[ls][lt];
   
   // de-allocate memory
   for (i=0; i < ls+1;i++) {
	   delete[] d[i];
   }
   delete[] d;

   return lst_distance;
}

/*
* Calculates Damerau-Levenshtein distance
* s: term1
* t: term2
* ls: length of term1
* lt: length of term2
*/
longlong damerauLevenshteinDistance(char *s, char *t, unsigned long ls, unsigned long lt) {
   unsigned long **d = new unsigned long*[ls+1];
   unsigned long i, j, cost;

   // initialize
   for (i=0; i < ls+1;i++) {
	   d[i] = new unsigned long[lt+1];
       d[i][0] = i;
   }
   for (j=0; j < lt+1;j++) {
       d[0][j] = j;
   }

   // calc LS-Distance
   for (i=1; i < ls+1;i++) {
       for (j=1; j < lt+1;j++) {
          if (s[i-1] == t[j-1]) { 
          	cost = 0; 
          }
          else { 
          	cost = 1; 
          }
          d[i][j] = minimum(minimum(d[i-1][j] + 1, 
                             d[i][j-1] + 1),
                             d[i-1][j-1] + cost);
		  if ((i > 1) && (j > 1) && (s[i - 1] == t[j - 2]) && (s[i - 2] == t[j - 1])) {
			d[i][j] = minimum(d[i][j], d[i - 2][j - 2] + cost);
          }
	   }
   }
   unsigned long lst_distance = d[ls][lt];
   
   // de-allocate memory
   for (i=0; i < ls+1;i++) {
	   delete[] d[i];
   }
   delete[] d;

   return lst_distance;
}