#!/bin/bash
if php smwadmin/smwadmin.php $*
then smwadmin/smwadmin.php --finalize
fi