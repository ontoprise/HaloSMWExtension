#!/bin/bash

if [ $# == 0 ]
then
  echo "Usage: import <dump file> [ <mode> == 0 (dryrun), 1 (warn, default), 2 (force) ]"
  exit 
fi

if [ ! -e $1 ];
then
 echo "File '$1' does not exist"
 exit
fi

php import.php -f $1 -m $2