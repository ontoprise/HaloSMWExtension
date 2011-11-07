#!/bin/bash

#
# Exports a bundle with images
#
# Usage: ./exportBundle.sh <bundle-id> 
#
# Note: You'll find the created bundle in /tmp/mybundle
# 
# Author: Kai K�hn / ontoprise / 2011
#

# check if bundle-id is specified
if [ -z $1 ]
then
echo "Usage: ./exportBundle.sh <bundle-id>"
exit 0
fi

# FIXME: convert bundle id in case of URI
# php exportOntologyBundleDeployDescriptor.php --stripname $1 

# Create output dir
OUTPUTDIR=/tmp/$1/extensions/$1
if [ ! -e $OUTPUTDIR ];
then
 echo "Create directory $OUTPUTDIR..."
 mkdir -p $OUTPUTDIR
 echo "done!"
fi

# Export bundle
php export.php --current --output=file:$OUTPUTDIR/dump.xml -b $1 $2 $3 $4 $5

if [ $? -ne 0 ]
then
exit $?
fi

php exportOntologyBundleDeployDescriptor.php -o $OUTPUTDIR/deploy.xml -b $1 -d dump.xml $2 $3 $4 $5

if [ $? -ne 0 ]
then
exit $?
fi

# Zip bundle
PWD=pwd
cd /tmp/$1/
zip -r /tmp/$1/$1.zip *
cd $PWD

# Remove temp files
rm -rf /tmp/$1/extensions/
echo
echo The bundle is located at /tmp/$1.zip
echo 
