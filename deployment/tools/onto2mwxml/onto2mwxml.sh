#!/bin/sh

# ---- set this variable before using ---------

TSCDIR=

# ---------------------------------------------

# check if TSCDIR is set
if [ -n "$TSCDIR" ]
then
echo "Using TSCDIR=$TSCDIR"
else
echo "TSCDIR is empty. Please set."
exit 1
fi

#
# Starts onto2mwxml and returns the error code
#
CURRENTDIR=$PWD
cd $TSCDIR
sh ./onto2mwxml.sh $*
if [ $? -ne 0 ]
then
exit 1
fi
cd $CURRENTDIR
