Patch - AdditionalMIMETypes for MW 1.13_stable_

Patch for using (four) new namespaces (Document, Pdf, Audio and Video) as image namespaces.
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

includes/filerepo/ArchivedFile.php
includes/filerepo/FileRepo.php
includes/parser/Parser_OldPP.php
includes/parser/Parser.php
specials/SpecialFileDuplicateSearch.php
specials/SpecialFilepath.php
specials/SpecialImagelist.php
specials/SpecialMIMEsearch.php
specials/SpecialNewimages.php
specials/SpecialRecentchangeslinked.php
specials/SpecialSearch.php
specials/SpecialUncategorizedimages.php
specials/SpecialUndelete.php
specials/SpecialUpload.php
specials/SpecialWhatlinkshere.php
includes/Article.php 
includes/CategoryPage.php
includes/EditPage.php
includes/Export.php
includes/Image.php
includes/ImageGallery.php
includes/ImagePage.php
includes/ImageQueryPage.php
includes/Linker.php
includes/Namespace.php
includes/Title.php
includes/Wiki.php



the two basic ideas:
- checking the wanted Namespace by using the file extension and the new array defined in LocalSettings.php.
- checking if the actual namespace fits one of the wanted (new) image-namespaces. 
