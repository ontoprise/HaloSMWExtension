#!/bin/bash

#
# Exports a bundle with images
#
# Usage: ./exportBundle.sh <bundle-id> 
#
# You'll find the created bundle in /tmp/mybundle
# 
# Author: Kai Kühn / ontoprise / 2011
#

# check if bundle-id is specified
if [ -z $1 ]
then
echo "Usage: ./exportBundle.sh <bundle-id> [ output file ]"
exit 0
fi

# convert bundle ID to valid directory name in case of URI
BUNDLENAME=`php exportOntologyBundleDeployDescriptor.php --stripname $1` 

# Remove old bundles
rm -rf /tmp/$BUNDLENAME

# Create output dir
OUTPUTDIR=/tmp/$BUNDLENAME/extensions/$BUNDLENAME
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
if [ -z $2 ]
then
OUTPUTFILE=/tmp/$BUNDLENAME/$BUNDLENAME.zip
else
OUTPUTFILE=$2
fi

PWD=pwd
cd /tmp/$BUNDLENAME/
zip -r $OUTPUTFILE *
cd $PWD

# Remove temp files
rm -rf /tmp/$BUNDLENAME/extensions/
echo
echo The bundle is located at $OUTPUTFILE
echo 
