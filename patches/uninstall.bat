@ECHO OFF
IF %1 == mime CALL:mime
IF %1 == wysiwyg CALL:wysiwyg
IF %1 == delmove CALL:delmove

IF %1 == all goto all
goto:eof

:all
CALL:mime
CALL:wysiwyg
CALL:delmove

goto:eof


:mime
echo Un-Installing patches for MIME-Type extension
xcopy old\includes\ImageGallery.php ..\includes /Y
xcopy old\includes\ImagePage.php ..\includes /Y
xcopy old\includes\ImageQueryPage.php ..\includes /Y
xcopy old\includes\Namespace.php ..\includes /Y
xcopy old\includes\Wiki.php ..\includes /Y
xcopy old\includes\Parser.php ..\includes /Y
xcopy old\includes\Linker.php ..\includes /Y
xcopy old\includes\SpecialUpload.php ..\includes /Y
xcopy old\includes\SpecialImageList.php ..\includes /Y
xcopy old\includes\SpecialNewImages.php ..\includes /Y
xcopy old\includes\SpecialUncategorizedimages.php ..\includes /Y
xcopy old\includes\SpecialMIMEsearch.php ..\includes /Y
xcopy old\includes\SpecialUndelete.php ..\includes /Y
del extensions\SMWHalo\includes\SMW_MIME_settings.php

goto:eof


:wysiwyg
echo Un-Installing patches for WYSIWYG extension
xcopy old\includes\EditPage.php ..\includes /Y
xcopy old\includes\Sanitizer.php ..\includes /Y

goto:eof

:delmove
echo Un-Installing patches for Delete/Move extension
xcopy old\includes\Article.php ..\includes /Y
xcopy old\includes\SpecialMovepage.php ..\includes /Y

goto:eof

