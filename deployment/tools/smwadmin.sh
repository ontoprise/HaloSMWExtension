#!/bin/bash

# Update MW if it was recently updated
if [ -f ../../init$.ext ] ; then
php ../../maintenance/update.php --quick
rm ../../init$.ext
fi

# Run SMWAdmin tool
if php smwadmin/smwadmin.php $*
then php smwadmin/smwadmin.php --finalize $*
fi