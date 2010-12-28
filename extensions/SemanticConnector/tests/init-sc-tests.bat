cls
d:
cd {{wiki-dir}}\maintenance
php dumpBackup.php --current > pages.xml
xcopy pages.xml {{wiki-dir}}\extensions\SemanticConnector\tests\pages
cd {{wiki-dir}}\tests\tests_halo
php init.php -t {{wiki-dir}}\extensions\SemanticConnector\tests -x {{xampp-dir}}
cd {{wiki-dir}}\extensions\SemanticConnector\tests