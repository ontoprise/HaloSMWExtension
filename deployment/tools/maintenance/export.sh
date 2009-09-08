#!/bin/sh

if [! -e "$2" ]
then
 mkdir $2
fi
:dump
php export.php --current --output=file:$2/dump.xml -b $1 $3 $4 $5
php exportDesc.php -o $2/deploy.xml -b $1 -d dump.xml $3 $4 $5