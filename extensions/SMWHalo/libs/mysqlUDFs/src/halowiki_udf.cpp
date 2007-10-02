/*  Copyright 2007, ontoprise GmbH
*   Author: Kai Kühn
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
* 	
*	How to use Levensthein edit distance function as MySQL UDF (user defined function):
* 
* 	I. Copy halowiki.dll where the system can find it (e.g. /mysql/bin)
* 	and enter in the MySQL admin console:
* 
* 		CREATE FUNCTION editdistance RETURNS INTEGER SONAME 'halowiki.dll';
*
* 	This makes EDITDISTANCE(term1, term2) function permanently available.
*
* 	II. To drop the function, type:
*
* 		DROP FUNCTION editdistance;
*/


#ifdef STANDARD
#include <stdio.h>
#include <string.h>
#ifdef __WIN__
typedef unsigned __int64 ulonglong;	/* Microsofts 64 bit types */
typedef __int64 longlong;
#else
typedef unsigned long long ulonglong;
typedef long long longlong;
#endif /*__WIN__*/
#else
#include <my_global.h>
#include <my_sys.h>
#endif
#include <mysql.h>
#include <m_ctype.h>
#include <m_string.h>		// To get strmov()

static pthread_mutex_t LOCK_hostname;

#ifdef HAVE_DLOPEN

/* These must be right or mysqld will not find the symbol! */

extern "C" {

my_bool editdistance_init(UDF_INIT *initid, UDF_ARGS *args, char *message);
longlong editdistance(UDF_INIT *initid, UDF_ARGS *args,
  		 char *is_null, char *error);

my_bool editdistance_dl_init(UDF_INIT *initid, UDF_ARGS *args, char *message);
longlong editdistance_dl(UDF_INIT *initid, UDF_ARGS *args,
  		 char *is_null, char *error);

}


/***************************************************************************
** Edit distance function
** Arguments:
** initid	Structure filled by xxx_init
** args		The same structure as to xxx_init. This structure
**		contains values for all parameters.
**		Note that the functions MUST check and convert all
**		to the type it wants!  Null values are represented by
**		a NULL pointer
** is_null	If the result is null, one should store 1 here.
** error	If something goes fatally wrong one should store 1 here.
**
** This function should return the result.
***************************************************************************/

/*
* init function: Checks arguments and correct if necessary
*/
my_bool editdistance_init(UDF_INIT *initid, UDF_ARGS *args, char *message)
{
  uint i;

  if (!args->arg_count)
  {
    strcpy(message,"editdistance must have exactly two arguments");
    return 1;
  }
  /*
  ** As this function wants to have everything as strings, force all arguments
  ** to strings.
  */
  for (i=0 ; i < args->arg_count; i++)
    args->arg_type[i]=STRING_RESULT;
  
  return 0;
}

my_bool editdistance_dl_init(UDF_INIT *initid, UDF_ARGS *args, char *message)
{
  uint i;

  if (!args->arg_count)
  {
    strcpy(message,"editdistance must have exactly two arguments");
    return 1;
  }
  /*
  ** As this function wants to have everything as strings, force all arguments
  ** to strings.
  */
  for (i=0 ; i < args->arg_count; i++)
    args->arg_type[i]=STRING_RESULT;
  
  return 0;
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

/*
* main function called from MySQL
*/
longlong editdistance(UDF_INIT *initid __attribute__((unused)), UDF_ARGS *args,
                     char *is_null, char *error __attribute__((unused)))
{
 
  uint i;

  for (i = 0; i < args->arg_count; i++) {
	  if (args->args[i] == NULL) {
		// NULL values are not allowed here, if so indicate via is_null
		*is_null = 1;
		return 0; 
	  }
  }
  return levenshteinDistance(args->args[0], args->args[1], args->lengths[0], args->lengths[1]);
}

longlong editdistance_dl(UDF_INIT *initid __attribute__((unused)), UDF_ARGS *args,
                     char *is_null, char *error __attribute__((unused)))
{
 
  uint i;

  for (i = 0; i < args->arg_count; i++) {
	  if (args->args[i] == NULL) {
		// NULL values are not allowed here, if so indicate via is_null
		*is_null = 1;
		return 0; 
	  }
  }
  return damerauLevenshteinDistance(args->args[0], args->args[1], args->lengths[0], args->lengths[1]);
}





#endif /* HAVE_DLOPEN */
