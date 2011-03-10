#!/bin/bash

#
# Exports a bundle with images
#
# Usage: ./exportBundle.sh <bundle-id> <temporary name>
#
# Note: <temporary name> is a unique directory name just used for bundle generation.
#       It is arbitrary, e.g. 'mybundle'. Then you'll find the created bundle in /tmp/mybundle
# 
# Author: Kai Kühn / ontoprise / 2011
#

# Create output dir
OUTPUTDIR=/tmp/$2/extensions/$1
if [ ! -e $OUTPUTDIR ];
then
 echo "Create directory $OUTPUTDIR..."
 mkdir -p $OUTPUTDIR
 echo "done!"
fi

# Export bundle
php export.php --current --output=file:$OUTPUTDIR/dump.xml -b $1 $3 $4 $5
php exportOntologyBundleDeployDescriptor.php -o $OUTPUTDIR/deploy.xml -b $1 -d dump.xml $3 $4 $5

# Zip bundle
PWD=pwd
cd /tmp/$2/
zip -r /tmp/$2/$1.zip *
cd $PWD

# Remove temp files
rm -rf /tmp/$2/extensions/
echo
echo The bundle is located at /tmp/$2
echo 
