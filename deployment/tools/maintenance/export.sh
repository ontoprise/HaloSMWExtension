#!/bin/bash

if [ ! -e $2 ];
then
 echo "Create directory $2..."
 mkdir $2
 echo "done!"
fi

php export.php --current --output=file:$2/dump.xml -b $1 $3 $4 $5
php exportDesc.php -o $2/deploy.xml -b $1 -d dump.xml $3 $4 $5