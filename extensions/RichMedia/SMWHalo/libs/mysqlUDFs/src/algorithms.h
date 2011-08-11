/**
* Edit distance algorithms 
*
* Author: Kai Kühn 2009
*
*/

double  jaro_distance(char *string1, char *string2, unsigned long ying_lengthp, unsigned long yang_lengthp);
double  jarowinkler_distance(char *string1, char *string2, unsigned long ying_lengthp, unsigned long yang_lengthp);
longlong levenshteinDistance(char *s, char *t, unsigned long ls, unsigned long lt);
longlong damerauLevenshteinDistance(char *s, char *t, unsigned long ls, unsigned long lt);