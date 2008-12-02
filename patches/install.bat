@ECHO OFF
IF %1 == mime CALL:mime
IF %1 == wysiwyg CALL:wysiwyg
REM Add additional patches here

IF %1 == all goto all
goto:eof

:all
CALL:mime
CALL:wysiwyg
REM Add additional patches here

goto:eof

:mime
echo Installing patches for MIME-Type extension
xcopy includes\Article.php ..\includes /Y
xcopy includes\CategoryPage.php ..\includes /Y
xcopy includes\EditPage.php ..\includes /Y
xcopy includes\Export.php ..\includes /Y
xcopy includes\Image.php ..\includes /Y
xcopy includes\ImageGallery.php ..\includes /Y
xcopy includes\ImagePage.php ..\includes /Y
xcopy includes\ImageQueryPage.php ..\includes /Y
xcopy includes\Linker.php ..\includes /Y
xcopy includes\Namespace.php ..\includes /Y
xcopy includes\Title.php ..\includes /Y
xcopy includes\Wiki.php ..\includes /Y
xcopy includes\mime.types ..\includes /Y
xcopy includes\specials\SpecialFileDuplicateSearch.php ..\includes\specials /Y
xcopy includes\specials\SpecialFilePath.php ..\includes\specials /Y
xcopy includes\specials\SpecialImagelist.php ..\includes\specials /Y
xcopy includes\specials\SpecialMIMEsearch.php ..\includes\specials /Y
xcopy includes\specials\SpecialNewimages.php ..\includes\specials /Y
xcopy includes\specials\SpecialRecentchangeslinked.php ..\includes\specials /Y
xcopy includes\specials\SpecialSearch.php ..\includes\specials /Y
xcopy includes\specials\SpecialUncategorizedimages.php ..\includes\specials /Y
xcopy includes\specials\SpecialUndelete.php ..\includes\specials /Y
xcopy includes\specials\SpecialUpload.php ..\includes\specials /Y
xcopy includes\specials\SpecialWhatlinkshere.php ..\includes\specials /Y
xcopy includes\filerepo\ArchivedFile.php ..\includes\filerepo /Y
xcopy includes\filerepo\FileRepo.php ..\includes\filerepo /Y
xcopy includes\parser\Parser_OldPP.php ..\includes\parser /Y
xcopy includes\parser\Parser.php ..\includes\parser /Y
xcopy extensions\* ..\extensions /S /Y /EXCLUDE:exclude.dat
xcopy skins\* ..\skins /S /Y
echo Installing patch for Delete/Move extension
xcopy includes\specials\SpecialMovepage.php ..\includes\specials /Y
xcopy includes\FileDeleteForm.php ..\includes\FileDeleteForm.php /Y
echo ----------------------------------------------------------------------
echo Add "include_once('extensions/SMWHalo/includes/SMW_MIME_settings.php')" to your LocalSettings.php;
goto:eof


:wysiwyg
echo Installing patches for WYSIWYG extension
xcopy includes\EditPage.php ..\includes /Y
xcopy includes\Sanitizer.php ..\includes /Y
echo -----------------------------------------------------------------------
echo Add "include_once('extensions/SMWHalo/includes/SMW_WYSIWYG.php')" to your LocalSettings.php;
goto:eof