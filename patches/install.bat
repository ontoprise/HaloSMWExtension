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
echo Installing patches for MIME-Type extension
xcopy includes\ImageGallery.php ..\includes /Y
xcopy includes\ImagePage.php ..\includes /Y
xcopy includes\ImageQueryPage.php ..\includes /Y
xcopy includes\Namespace.php ..\includes /Y
xcopy includes\Wiki.php ..\includes /Y
xcopy includes\Parser.php ..\includes /Y
xcopy includes\Linker.php ..\includes /Y
xcopy includes\SpecialUpload.php ..\includes /Y
xcopy includes\SpecialImageList.php ..\includes /Y
xcopy includes\SpecialNewImages.php ..\includes /Y
xcopy includes\SpecialUncategorizedimages.php ..\includes /Y
xcopy includes\SpecialMIMEsearch.php ..\includes /Y
xcopy includes\SpecialUndelete.php ..\includes /Y
xcopy extensions\* ..\extensions /S /Y /EXCLUDE:exclude.dat

goto:eof


:wysiwyg
echo Installing patches for WYSIWYG extension
xcopy includes\EditPage.php ..\includes /Y
xcopy includes\Sanitizer.php ..\includes /Y

goto:eof

:delmove
echo Installing patches for Delete/Move extension
xcopy includes\Article.php ..\includes /Y
xcopy includes\SpecialMovepage.php ..\includes /Y

goto:eof

