#!/bin/sh

#
# Starts onto2mwxml and returns the error code
#
TSCDIR=
CURRENTDIR=$PWD
cd $TSCDIR
sh ./onto2mwxml.sh $*
if [ $? -ne 0 ]
then
exit 1
fi
cd $CURRENTDIR