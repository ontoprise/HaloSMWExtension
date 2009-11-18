cls
d:
cd \devel\workspace-wtp\TestDBWiki\maintenance
php dumpBackup.php --current > pages.xml
xcopy pages.xml d:\devel\workspace-wtp\HaloSMWExtensionSVN\extensions\DataAPI\tests\pages
cd \devel\workspace-wtp\HaloSMWExtensionSVN\tests\tests_halo
php init.php -t d:\devel\workspace-wtp\HaloSMWExtensionSVN\extensions\DataAPI\tests -x d:\devel\workspace-wtp\XAMPP
cd \devel\workspace-wtp\HaloSMWExtensionSVN\extensions\DataAPI\tests