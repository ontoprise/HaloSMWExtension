#!/bin/bash

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
