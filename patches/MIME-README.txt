Patch - AdditionalMIMETypes for MW 1.12_stable_

Patch for using (four) new namespace (Document, Pdf, Audio and Video) as image namespaces.
The choice of the namespace is made according to the extension of the uploaded file. 

http://dmwiki.ontoprise.de:6080/mediawiki/index.php/Darkmatter:AdditionalMIMETypes

Please extract files to /HaloSMWExtension and add the following row to your LocalSettings.php:

include_once('extensions/SMWHalo/includes/SMW_MIME_settings.php');

---
@ToDo: clearify if the physical storage location for particular mime-types can be determined
such that videos are saved on server x and images on server y:

Well, it´s quite simple to save ALL files on another server than the wiki-server.
If you want to do that just:
Replace the variables in LocalSettings $IP in $wgUploadDirectory with "\\\Server\upload_directory" 
and $wgScriptPath in $wgUploadPath with "http://www.Server.xx/upload_directory". 
Both variables have to point to the same physical directory!

---

summary of changed files:
1. LocalSettings.php
2. Namespace.php
3. Wiki.php
4. ImagePage.php
5. ImageGallery.php
6. ImageQueryPage.php
7. Parser.php
8. Linker.php
9. SpecialUpload.php
10. SpecialImageList.php
11. SpecialNewImages.php
12. SpecialUncategorizedimages.php
13. SpecialMIMEsearch.php
14. SpecialUndelete.php
---
15. /filerepo/File.php

the two basic ideas:
- checking the wanted by using the file extension and the new array defined in LocalSettings.php.
- checking if the actual namespaces fits one of the wanted (new) image-namespaces. 

actual changes:
--------------@ "LocalSettings.php" (bottom)--------------

- added the new fileextensions to $wgFileExtensions.
- created 4 new extraNamespaces incl. aliases
- defined an array for assignment extension -> namespace

--------------@ "SpecialUpload.php" (371 & 40ff)--------------
- checking the wanted by using the file extension and the new array defined in LocalSettings.php

--------------@ "Wiki.php" (221ff)--------------
 - adjusted: switch( $title->getNamespace() )

--------------@ "ImagePage.php" (46 & 307)--------------
- @ function view():
  ...if ( !( Namespace::isImage( $this->mTitle->getNamespace() ) )...
- showing icons for all files	
	
--------------@ "SpecialImageList.php" (111 & 117ff)--------------
- in function formatValue( $field, $value )...
		switch ( $field ) {
			case 'img_name':

--------------@ SpecialNewImages (11 & 136ff)--------------

--------------@ ImageGallery.php (250)--------------
- if( !( Namespace::isImage( $nt->getNamespace() ) ) || !$img ) ...

--------------@ SpecialUncategorizedimages.php--------------
- added the 4 new namespaces to the SQL-query

--------------@ ImageQueryPage.php (49ff)--------------
- changes in private function prepareImage( $row ) ...

--------------@ SpecialMIMEsearch.php (62 ff)--------------

--------------@ Parser (1717 & 1787)--------------
use of Namespace::isImage()

--------------@ Linker.php (521 & 598)--------------
- return $this->makeKnownLinkObj( $title, $frameParams['alt'] );
- makeBrokenImageLinkObj( $title, $fp['alt'], '', '', '', $time==true );

--------------@ Namespace.php (57ff & 179ff)--------------
- added the new namespaces in isMovable()
- created function isImage()

--------------@ SpecialUndelete.php (121 & 272)--------------

