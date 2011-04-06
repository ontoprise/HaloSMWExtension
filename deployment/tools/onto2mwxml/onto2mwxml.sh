#!/bin/sh

#
# Starts onto2mwxml and returns the error code
#

cd tsc
sh ./onto2mwxml.sh $*
if [ $? -ne 0 ]
then
exit 1
fi
cd .. 