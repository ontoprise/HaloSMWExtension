/*
* Halowiki.nsi (c) Ontoprise 2008-2009
*
*    This script builds an installer for SMW+
*
* Author: Kai K�hn
*
* Needs NSIS 2.35 or higher
* additional extensions: (see extension folder) 
*    - UtilProcWMI.dll
*    - ip.dll
*    - ShellLink.dll
*/

;Without files (compiles much faster, for debugging)
;!define NOFILES
 
;--------------------------------
!include "MUI2.nsh"
!include "LogicLib.nsh"
!include "FileFunc.nsh"
!include "TextFunc.nsh"
!include "EnvVarUpdate.nsh"
!include "WinMessages.nsh"
!insertmacro ConfigWrite
!insertmacro GetFileName

; --- The following definitions are meant to be changed ---

!define PRODUCTPATH "SMWPLUS"
!define PRODUCT_YEAR "2012"
#!define VERSION "1.4.3" set by hudson
#!define BUILD_ID "431" set by hudson
!define REQUIRED_JAVA_VERSION16 16
!define REQUIRED_JAVA_VERSION17 17

!ifdef EXTENSIONS_EDITION
  !define PRODUCT "SMW+"
  !define PRODUCT_SHORT "SMW+"
  !define LOCALSETTINGS "LocalSettings.php.template"
  !define WIKIDB "smwplus_database.sql"
!endif
!ifdef COMMUNITY_EDITION
  !define PRODUCT "SMW+ Community Edition"
  !define PRODUCT_SHORT "SMW+"
  !define LOCALSETTINGS "LocalSettings.php.community.tmpl"
  !define WIKIDB "htdocs\mediawiki\tests\tests_halo\mw17_db.sql"
!endif

; ----------------------------------------------------------

!define MUI_ABORTWARNING

!define MUI_HEADERIMAGE
!define MUI_HEADERIMAGE_BITMAP "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\header-install.bmp"

!define MUI_WELCOMEFINISHPAGE 
!define MUI_WELCOMEFINISHPAGE_BITMAP "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\wizard-install.bmp"
!define MUI_WELCOMEFINISHPAGE_BITMAP_NOSTRETCH
!define MUI_COMPONENTSPAGE_SMALLDESC

!define MUI_WELCOMEPAGE_TITLE "Welcome to the ${PRODUCT} ${VERSION} Setup Wizard"
!define MUI_WELCOMEPAGE_TEXT "This wizard will guide you through the installation of ${PRODUCT} ${VERSION}."

!define MUI_FINISHPAGE_RUN
!define MUI_FINISHPAGE_RUN_CHECKED
!define MUI_FINISHPAGE_RUN_TEXT "Run wiki on startup (installs Windows services)"
!define MUI_FINISHPAGE_RUN_FUNCTION "installAsWindowsService"

!define MUI_FINISHPAGE_SHOWREADME 
!define MUI_FINISHPAGE_SHOWREADME_FUNCTION showReadme
!define MUI_FINISHPAGE_SHOWREADME_TEXT "Show README"
!define MUI_FINISHPAGE_SHOWREADME_CHECKED
!define MUI_FINISHPAGE_TEXT "Installation of ${PRODUCT} ${VERSION} is completed. You are advised to study the README-SMWPLUS.txt file (readme file) \ 
located in the zip-file containing this installer. The readme file contains important information about: $\n \
* how to access the Wiki after the installation $\n \
* default access credentials for administrators $\n \
* where to find documentation for administrators and end users $\n \
* trouble shooting hints and $\n \
* know issues."
!define MUI_FINISHPAGE_LINK "Visit the ontoprise website for the latest news"
!define MUI_FINISHPAGE_LINK_LOCATION "http://wiki.ontoprise.com/"

;Start Menu Folder Page Configuration
!define MUI_STARTMENUPAGE_REGISTRY_ROOT "HKCU" 
!define MUI_STARTMENUPAGE_REGISTRY_KEY "Software\Ontoprise\${PRODUCT} ${VERSION}" 
!define MUI_STARTMENUPAGE_REGISTRY_VALUENAME "Start Menu Folder"

!macro WriteToFile String File
 Push "${String}"
 Push "${File}"
  Call WriteToFile
!macroend
!define WriteToFile "!insertmacro WriteToFile"

!ifdef NOCOMPRESS
SetCompress off
!endif

!macro GetWindowsVersion OUTPUT_VALUE
    Call GetWindowsVersion
    Pop `${OUTPUT_VALUE}`
!macroend
 
!define GetWindowsVersion '!insertmacro "GetWindowsVersion"'
 
!macro StrContains ResultVar String SubString
  Push `${String}`
  Push `${SubString}`
  Call StrContains
  Pop `${ResultVar}`
!macroend

!define StrContains "!insertmacro StrContains"

;--------------------------------

Name "${PRODUCT} v${VERSION}"
Caption "${PRODUCT} ${VERSION}"
Icon "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\nsis1-install.ico"
OutFile "${PRODUCT}-${VERSION}.exe"

AllowSkipFiles off
SetOverwrite on
SetDateSave on
SetDatablockOptimize on
CRCCheck on
SilentInstall normal
# Removed next line to fix: Issue 14112 - Installer should not block complete screen
# BGGradient 000000 95F5E2 FFFFFF
InstallColors FF8080 000030
;XPStyle on
ComponentText "" "" " "
InstallDir "C:\${PRODUCTPATH}\"
DirText $CHOOSEDIRTEXT "" "" "" 
CheckBitmap "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\classic-cross.bmp"
BrandingText "ontoprise GmbH ${PRODUCT_YEAR} - wiki.ontoprise.de - Build: ${BUILD_ID}"
LicenseText "GPL-License"
LicenseData "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\gpl.txt"
ComponentText "Choose type of installation"
RequestExecutionLevel admin

Var FILE_LIST
Var STARTMENU_FOLDER
Var MUI_TEMP

Var JAVA_HOME
Var JAVA_HOME_SHORT
Var JAVA_VER
Var JAVA_INSTALLATION_MSG

; Pages --------------------------------

!define MUI_ICON "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\smwplus_32.ico"
  
!insertmacro MUI_PAGE_WELCOME
!define MUI_LICENSEPAGE_CHECKBOX
!insertmacro MUI_DEFAULT MUI_LICENSEPAGE_TEXT_TOP "License agreement of SMW+ Community Edition and SMW+ Professional"
!insertmacro MUI_PAGE_LICENSE "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\op_license.txt"
!insertmacro MUI_DEFAULT MUI_LICENSEPAGE_TEXT_TOP "License agreement of third party components"
!insertmacro MUI_PAGE_LICENSE "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\thirdparty.txt"
!define MUI_PAGE_CUSTOMFUNCTION_SHOW initComponentsPage
!define MUI_PAGE_CUSTOMFUNCTION_LEAVE checkEnvironment
!insertmacro MUI_PAGE_COMPONENTS

!define MUI_PAGE_CUSTOMFUNCTION_PRE preDirectory
!define MUI_PAGE_CUSTOMFUNCTION_LEAVE checkDirectoryValidity
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_STARTMENU Application $STARTMENU_FOLDER

; Issue 15644: still needed!
Page custom showLuceneParamters checkLuceneParameters
Page custom showWikiCustomize checkWikiCustomize 

!insertmacro MUI_PAGE_INSTFILES
;!define MUI_PAGE_CUSTOMFUNCTION_LEAVE checkForSkype
;!insertmacro MUI_PAGE_FINISH

;!undef MUI_PAGE_CUSTOMFUNCTION_SHOW
!define MUI_PAGE_CUSTOMFUNCTION_SHOW FinishPageShow
!insertmacro MUI_PAGE_FINISH




; Language ------------------------------

!insertmacro MUI_LANGUAGE "English"

; Installation types ---------------------------

!ifndef NOINSTTYPES ; only if not defined
  InstType "New"
  ;InstType "Update"
  InstType /COMPONENTSONLYONCUSTOM 
!endif

AutoCloseWindow false
ShowInstDetails show

;--------------------------------

; Basic variables for environment
Var PHP
Var MEDIAWIKIDIR
Var FART

;Wiki customizations
Var WIKINAME 
Var WIKILOGO 
Var WIKILANG 
Var WIKISKIN 
Var DEFAULTLOGO


; Helper
Var IP
Var CHOOSEDIRTEXT
Var INSTALLTYPE

Function ".onInit"
  InitPluginsDir
  File /oname=$PLUGINSDIR\wikicustomize.ini "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\gui\wikicustomize.ini"
  ; Issue 15644: still needed!
  File /oname=$PLUGINSDIR\lucene.ini "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\gui\lucene.ini"
  
FunctionEnd


Function .onSelChange
 GetCurInstType $R0
 ${Switch} $R0
    ${Case} 0
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Select 'new' if you are setting up a new installation of SMW+"
        ${Break}
    ${Case} 1
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Select 'update' if you want to upgrade your existing installation of SMW+ to a new version."
        ${Break}
    ${Default} 
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Select 'custom' if you want to choose the compontents to install"
        ${Break}
  ${EndSwitch}

FunctionEnd

Function initComponentsPage
    SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Select 'new' if you are setting up a new installation of SMW+"
FunctionEnd


; ---- Install sections ---------------


Section "XAMPP" xampp
  SectionIn 1
  SetOutPath "$INSTDIR"
  CreateDirectory "$INSTDIR"
  !ifndef NOFILES
    File /r /x .svn /x CVS "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\XAMPP\*"
  !endif
  ;Store installation folder
    WriteRegStr HKCU "Software\Ontoprise\${PRODUCT} ${VERSION}" "" $INSTDIR
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "DisplayName" "${PRODUCT} ${VERSION}"
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "UninstallString" "$INSTDIR\Uninstall.exe"
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "DisplayIcon" $INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico
   
    
    
    CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
   
    CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Start ${PRODUCT_SHORT}.lnk" "$INSTDIR\xampp_start.bat"
    CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Stop ${PRODUCT_SHORT}.lnk" "$INSTDIR\xampp_stop.exe"
    ShellLink::SetRunAsAdministrator "$SMPROGRAMS\$STARTMENU_FOLDER\Stop ${PRODUCT_SHORT}.lnk"
    
SectionEnd

Section "-CopyInstaller"
  SectionIn 1 2 3
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki\installer"
  CreateDirectory "$INSTDIR\htdocs\mediawiki\installer"
  !ifndef NOFILES
    File /r /x CVS /x .svn /x *.exe /x *.nsi "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\*"
    ${If} $0 == 1
    
    #CreateShortCut "$DESKTOP\${PRODUCT_SHORT} Main Page.lnk" "http://localhost/mediawiki/index.php" \
    #"" "$INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico" 0
    CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT_SHORT} Main Page.lnk" "http://localhost/mediawiki/index.php" \
    "" "$INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico" 0
    ${EndIf}
  !endif
SectionEnd

/*Section "Online Help" ohelp
    SectionIn 1 2 3
    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    SetOutPath "$INSTDIR\help"
    CreateDirectory "$INSTDIR\help"
    !ifndef NOFILES
        File /r /x CVS ..\com.ontoprise.smwplus.help\compiled\*
        ${If} $0 == 1
            CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
            CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT_SHORT} Help.lnk" "$INSTDIR\help\Help.exe"
        ${EndIf}
    !endif
SectionEnd
*/


SectionGroup "${PRODUCT} ${VERSION}" 
Section "${PRODUCT} ${VERSION} core" smwplus
  SectionIn 1 2 RO
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
 
  ; Set output path 
  
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  CreateDirectory "$INSTDIR\htdocs\mediawiki"
  
  ; Copy files and config 
  ${If} $0 == 1 
     IntOp $INSTALLTYPE 0 + 1
  ${Else}
    IfFileExists $INSTDIR\htdocs\mediawiki\extensions\SMWHalo\*.* 0 notexistsSMWPlus
        
    IntOp $INSTALLTYPE 0 + 0
    goto copyfiles
    
    notexistsSMWPlus:
       MessageBox MB_OK|MB_ICONSTOP  "Could not find wiki installation. Abort here." 
       Abort
            
                
  ${EndIf}
  
  copyfiles:
      !ifndef NOFILES
            
            File /r /x .svn /x CVS /x *.zip /x *.exe /x *.cache /x .buildpath /x .project /x *.settings /x LocalSettings.php /x ACLs.php /x *.nsi /x SKOSExpander.php *
            
            File /oname=deployment\tools\Smwplus.zip Smwplus.zip
            File /oname=deployment\tools\Smwplussandbox.zip Smwplussandbox.zip
            File /oname=deployment\tools\patch.exe deployment\tools\patch.exe
            File /oname=deployment\tools\unzip.exe deployment\tools\unzip.exe
            File /oname=deployment\tools\maintenance\export\7za.exe deployment\tools\maintenance\export\7za.exe
            CopyFiles $INSTDIR\htdocs\mediawiki\patches\patch.php $INSTDIR\htdocs\mediawiki\deployment\tools
            CopyFiles $INSTDIR\htdocs\mediawiki\deployment\config\settings.php $INSTDIR\htdocs\mediawiki\deployment
            !ifndef COMMUNITY_EDITION
                File /oname=extensions\RichMedia\bin\xpdf\pdftotext.exe extensions\RichMedia\bin\xpdf\pdftotext.exe
                File /oname=extensions\RichMedia\bin\AbiWord\bin\AbiWord.exe extensions\RichMedia\bin\AbiWord\bin\AbiWord.exe
            !endif
      !endif  
  ;Create uninstaller (only when newly installed)
  ${If} $INSTALLTYPE == 1 
      WriteUninstaller "$INSTDIR\Uninstall.exe"
      CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
      CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Uninstall.lnk" "$INSTDIR\Uninstall.exe"
  ${EndIf}
  
   ; Register scheduled task for services
    SetOutPath "$INSTDIR"
    ${ConfigWrite} "$INSTDIR\apache_start.bat" "SET SMWPLUSDIR=" '$INSTDIR' $R0
    ${ConfigWrite} "$INSTDIR\apache_stop.bat" "SET SMWPLUSDIR=" '$INSTDIR' $R0
    ${ConfigWrite} "$INSTDIR\mysql_start.bat" "SET SMWPLUSDIR=" '$INSTDIR' $R0
    ${ConfigWrite} "$INSTDIR\mysql_stop.bat" "SET SMWPLUSDIR=" '$INSTDIR' $R0
     
    StrCpy $FART "$INSTDIR\tools\fart.exe"
       
         ${GetWindowsVersion} $R0
         ${If} $R0 == "7"
         ${OrIf} $R0 == "Vista"
         ${OrIf} $R0 == "2008"
            DetailPrint "Add starts scripts as planned task for Windows 7/2008 Server"
            
            # Apache
            nsExec::ExecToLog 'schtasks /delete /tn "start_apache" /F'
            nsExec::ExecToLog 'schtasks /delete /tn "stop_apache" /F'
            nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_apache.txt" {{command}} "\"$INSTDIR\apache_start.bat\""'
            nsExec::ExecToLog 'schtasks /create /tn "start_apache" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_apache.txt"'
            nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_apache.txt" {{command}} "\"$INSTDIR\apache_stop.bat\""'
            nsExec::ExecToLog 'schtasks /create /tn "stop_apache" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_apache.txt"'
           
            
            # Mysql
            nsExec::ExecToLog 'schtasks /delete /tn "start_mysql" /F'
            nsExec::ExecToLog 'schtasks /delete /tn "stop_mysql" /F'
            nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_mysql.txt" {{command}} "\"$INSTDIR\mysql_start.bat\""'
            nsExec::ExecToLog 'schtasks /create /tn "start_mysql" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_mysql.txt"'
            nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_mysql.txt" {{command}} "\"$INSTDIR\mysql_stop.bat\""'
            nsExec::ExecToLog 'schtasks /create /tn "stop_mysql" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_mysql.txt"'
            
            # SOLR
            nsExec::ExecToLog 'schtasks /delete /tn "start_solr" /F'
            nsExec::ExecToLog 'schtasks /delete /tn "stop_solr" /F'
            nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_solr.txt" {{command}} "\"$INSTDIR\solr\wiki\StartSolr.bat\""'
            nsExec::ExecToLog 'schtasks /create /tn "start_solr" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_solr.txt"'
            nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_solr.txt" {{command}} "\"$INSTDIR\solr\wiki\StopSolr.bat\""'
            nsExec::ExecToLog 'schtasks /create /tn "stop_solr" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_solr.txt"'
            
            # Memcached
            nsExec::ExecToLog 'schtasks /delete /tn "start_memcached" /F'
            nsExec::ExecToLog 'schtasks /delete /tn "stop_memcached" /F'
            nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_memcachedservice.txt" {{command}} "\"$INSTDIR\memcached\memcached -d start\""'
            nsExec::ExecToLog 'schtasks /create /tn "start_memcached" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_memcachedservice.txt"'
            nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_memcachedservice.txt" {{command}} "\"$INSTDIR\memcached\memcached -d stop\""'
            nsExec::ExecToLog 'schtasks /create /tn "stop_memcached" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_memcachedservice.txt"'
         ${EndIf}
         
         ${If} $R0 == "XP"
         ${OrIf} $R0 == "2003"
         
            DetailPrint "Add starts scripts as planned task for Windows XP/2003 Server"
            
            #apache
            nsExec::ExecToLog 'schtasks /create /tn "start_apache" /ru "SYSTEM" /tr "\"$INSTDIR\apache_start.bat\"" /sc once /st 00:00'
            nsExec::ExecToLog 'schtasks /create /tn "stop_apache" /ru "SYSTEM" /tr "\"$INSTDIR\apache_stop.bat\"" /sc once /st 00:00'
            
            #mysql
            nsExec::ExecToLog 'schtasks /create /tn "start_mysql" /ru "SYSTEM" /tr "\"$INSTDIR\mysql_start.bat\"" /sc once /st 00:00'
            nsExec::ExecToLog 'schtasks /create /tn "stop_mysql" /ru "SYSTEM" /tr "\"$INSTDIR\mysql_stop.bat\"" /sc once /st 00:00'
            
            #solr
            nsExec::ExecToLog 'schtasks /create /tn "start_solr" /ru "SYSTEM" /tr "\"$INSTDIR\solr\wiki\StartSolr.bat\"" /sc once /st 00:00'
            nsExec::ExecToLog 'schtasks /create /tn "stop_solr" /ru "SYSTEM" /tr "\"$INSTDIR\solr\wiki\StopSolr.bat\"" /sc once /st 00:00'
            
            #memcached
            nsExec::ExecToLog 'schtasks /create /tn "start_memcached" /ru "SYSTEM" /tr "\"$INSTDIR\memcached\memcached -d start\"" /sc once /st 00:00'
            nsExec::ExecToLog 'schtasks /create /tn "stop_memcached" /ru "SYSTEM" /tr "\"$INSTDIR\memcached\memcached -d stop\"" /sc once /st 00:00'
            
            
         ${EndIf}
        
    
  ;configure:
      ${If} $INSTALLTYPE == 0 
        CALL changeConfigForSMWPlusUpdate
      ${EndIf}
      ${If} $INSTALLTYPE == 1
        CALL changeConfigForFullXAMPP
      ${EndIf}
  
  
SectionEnd

Section "SMW+ Setup" smwplussetup
  SectionIn 1 RO
  DetailPrint "Configure SMW+"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}

  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"

  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\maintenance\update.php" --quick'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\extensions\SemanticMediaWiki\maintenance\SMW_setup.php"'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\extensions\SMWHalo\maintenance\SMW_setup.php"'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\extensions\EnhancedRetrieval\maintenance\setup.php"'

  SetOutPath "$MEDIAWIKIDIR\deployment\tools"

  ; Set PHP path for deployment framework
  DetailPrint "Set PHP path for deployment framework"
  ${ConfigWrite} "$MEDIAWIKIDIR\deployment\tools\smwadmin.bat" "SET PHP=" '"$INSTDIR\php\php.exe"' $R0
  ${ConfigWrite} "$MEDIAWIKIDIR\deployment\settings.php" "'df_php_executable' =>" "'$INSTDIR\php\php.exe'," $R0
  ${ConfigWrite} "$MEDIAWIKIDIR\deployment\settings.php" "'df_mysql_dir' =>" "'$INSTDIR\mysql'" $R0
  
  DetailPrint "Create bundle properties"
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\deployment\tools\smwadmin\smwadmin.php" --nocheck --noask --createproperties'
  
  DetailPrint "Install bundles into wiki"
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\deployment\tools\smwadmin\smwadmin.php" --nocheck --noask -f -i Smwplus.zip'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\deployment\tools\smwadmin\smwadmin.php" --nocheck --noask -f --finalize'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\deployment\tools\smwadmin\smwadmin.php" --nocheck --noask -f -i Smwplussandbox.zip'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\deployment\tools\smwadmin\smwadmin.php" --nocheck --noask -f --finalize'

SectionEnd


Section "Semantic Forms" semforms
  SectionIn 1 RO
  DetailPrint "Configure Semantic Forms extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
  
  ; change config file
  ;nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importSemForms=1 ls=LocalSettings.php'
SectionEnd

Section "Treeview" treeview
  SectionIn 1 RO
  DetailPrint "Configure Semantic Calendar extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
  
  ; change config file
  ;nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importTreeview=1 ls=LocalSettings.php'
SectionEnd

Section "WYSIWYG" wysiwyg
  SectionIn 1 RO
  DetailPrint "Configure WYSIWYG extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
  
  ; change config file
  ;nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importWYSIWYG=1 ls=LocalSettings.php'
SectionEnd

Section "Solr" solr
    SectionIn 1 RO
    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}

    SetOutPath "$INSTDIR\solr\wiki"
    StrCpy $PHP "$INSTDIR\php\php.exe"

    ${If} $0 == 1
        SetOutPath "$INSTDIR\solr\wiki"
        CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
        CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Start Solr.lnk" "$INSTDIR\solr\wiki\startSolr.bat"
        CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Stop Solr.lnk" "$INSTDIR\solr\wiki\stopSolr.bat"
        ShellLink::SetRunAsAdministrator "$SMPROGRAMS\$STARTMENU_FOLDER\Stop Solr.lnk"
    ${EndIf}

    ;DetailPrint "set solr_ip to $IP"
    ;${WriteToFile} "<?php$\r$\n$$solrIP='$IP';" "$MEDIAWIKIDIR\extensions\EnhancedRetrieval\SOLR\solr_ip.php"
    ;${ConfigWrite} "$MEDIAWIKIDIR\extensions\EnhancedRetrieval\SOLR\solr_ip.php" "<?php$\n\$$solrIP=" '"$IP";' $R0
    
    CopyFiles "$INSTDIR\htdocs\mediawiki\extensions\EnhancedRetrieval\SOLR\smwdb-data-config.xml" "$INSTDIR\solr\wiki\solr\conf\smwdb-data-config.xml"
    CopyFiles "$INSTDIR\htdocs\mediawiki\extensions\EnhancedRetrieval\SOLR\schema.xml" "$INSTDIR\solr\wiki\solr\conf\schema.xml"
    CopyFiles "$INSTDIR\htdocs\mediawiki\extensions\EnhancedRetrieval\SOLR\solrconfig.xml" "$INSTDIR\solr\wiki\solr\conf\solrconfig.xml"
    nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="\"$INSTDIR\solr\wiki\solr\conf\smwdb-data-config.xml\"" out="\"$INSTDIR\solr\wiki\solr\conf\smwdb-data-config.xml\"" wgDBname=semwiki_en wgDBserver=localhost wgDBport=3306 wgDBuser=root wgDBpassword=m8nix'

    CreateDirectory "$INSTDIR\solr\wiki\logs"
    
    ; activating incremental updates
    DetailPrint "Activating incremental updates on SOLR"
    StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
    ${ConfigWrite} "$MEDIAWIKIDIR\LocalSettings.php" "$$fsgEnableIncrementalIndexer=" "true;" $R0
    
    DetailPrint "Run Solr. Please be patient this may take a while..."
    ; run the solr server
    Exec '"$INSTDIR\solr\wiki\startSolr.bat"'

SectionEnd

SectionGroupEnd


;Languages (english)
LangString DESC_xampp ${LANG_ENGLISH} "Select XAMPP contains the server infrastructure."
LangString DESC_smwplus ${LANG_ENGLISH} "${PRODUCT} ${VERSION}"
LangString DESC_ohelp ${LANG_ENGLISH} "Eclipse-based online help."

LangString DESC_semforms ${LANG_ENGLISH} "Semantic Forms ease the annotation process by providing a simple interface."
LangString DESC_treeview ${LANG_ENGLISH} "The Treeview extension allows a hierarchical displaying of content or links."
LangString DESC_wysiwyg ${LANG_ENGLISH} "The WYSIWYG extension allows editing with a Word-like comfortable editor."

LangString CUSTOMIZE_PAGE_TITLE ${LANG_ENGLISH} "Customize your wiki"
LangString CUSTOMIZE_PAGE_SUBTITLE ${LANG_ENGLISH} "Set wiki name or logo"
LangString LUCENE_PAGE_TITLE ${LANG_ENGLISH} "Server settings"
LangString LUCENE_PAGE_SUBTITLE ${LANG_ENGLISH} "Specify the IP address of the Wiki server"

LangString SELECT_XAMPP_DIR ${LANG_ENGLISH} "Select an empty directory where to install XAMPP and the wiki."
LangString SELECT_NEWUPDATE_DIR ${LANG_ENGLISH} "Select an existing installation to update."
LangString STARTED_SERVERS ${LANG_ENGLISH} "There are already running instances of Apache, MySQL, SOLR and/or memcached. You MUST stop them before continuing."
LangString NOJAVAINSTALLED ${LANG_ENGLISH} "No Java found! Please quit the installer and install the latest release of Java 6."
LangString COULD_NOT_START_SERVERS ${LANG_ENGLISH} "Apache and MySQL could not be started for some reason. Installation may not be complete!"
LangString FIREWALL_COMPLAIN_INFO ${LANG_ENGLISH} "Windows firewall may block the apache and mySQL processes. $\n If this is the case with your installation, then unblock both processes in the pop-up windows $\n and click on 'OK' to finish the installation process."
LangString DIRECTORY_HINT ${LANG_ENGLISH} "Please note that SMW+ must not be installed in directories containing parantheses or blanks, like 'Programs (x86)'"

;Assign language strings to sections
!insertmacro MUI_FUNCTION_DESCRIPTION_BEGIN
    !insertmacro MUI_DESCRIPTION_TEXT ${xampp} $(DESC_xampp)
    !insertmacro MUI_DESCRIPTION_TEXT ${smwplus} $(DESC_smwplus)
    #!insertmacro MUI_DESCRIPTION_TEXT ${ohelp} $(DESC_ohelp)
    !insertmacro MUI_DESCRIPTION_TEXT ${semforms} $(DESC_semforms)
    !insertmacro MUI_DESCRIPTION_TEXT ${treeview} $(DESC_treeview)
!insertmacro MUI_FUNCTION_DESCRIPTION_END
;--------------------------------


Function preDirectory
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 1
    StrCpy $CHOOSEDIRTEXT $(SELECT_XAMPP_DIR)
  ${Else}
    StrCpy $CHOOSEDIRTEXT $(SELECT_NEWUPDATE_DIR)
  ${EndIf}
  
FunctionEnd

Function checkDirectoryValidity
  ${StrContains} $0 $INSTDIR "("
  ${StrContains} $1 $INSTDIR ")"
  ${If} $0 != ""
  ${OrIf} $1 != ""
     MessageBox MB_OK $(DIRECTORY_HINT) IDOK 0 IDCANCEL 0
     Abort
  ${EndIf}
FunctionEnd

Function checkEnvironment
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 1
    CALL checkForApacheAndMySQLAndMemcached
  ${EndIf}
  
  ; Check for Java
  Call LocateJVM
  StrCmp "" $JAVA_INSTALLATION_MSG Success InstallJava

  Success:
     Goto JavaInstalled
        
  InstallJava:
    MessageBox MB_ICONEXCLAMATION|MB_OK $JAVA_INSTALLATION_MSG IDOK 0
    Abort
     
  JavaInstalled: 
FunctionEnd

Function checkForApacheAndMySQLAndMemcached
 checkagain:
   UtilProcWMI::FindProc "httpd.exe"
   IntOp $0 0 + $R0
   UtilProcWMI::FindProc "mysqld.exe"
   IntOp $1 0 + $R0
   UtilProcWMI::FindProc "mysqld-nt.exe"
   IntOp $1 $1 + $R0
   UtilProcWMI::FindProc "memcached.exe"
   IntOp $2 0 + $R0
   UtilProcWMI::FindProc "solr.exe"
   IntOp $3 0 + $R0
   ${If} $0 == 1
   ${OrIf} $1 == 1
   ${OrIf} $2 == 1
   ${OrIf} $3 == 1
    MessageBox MB_ICONEXCLAMATION|MB_OKCANCEL $(STARTED_SERVERS) IDOK 0 IDCANCEL skipCheck
    goto checkagain
   ${EndIf}
   goto out
 skipcheck:
    Abort
 out:
FunctionEnd

Function waitForApacheAndMySQL
   IntOp $2 0 + 10
 checkagain:
   Sleep 1000
   UtilProcWMI::FindProc "httpd.exe"
   IntOp $0 0 + $R0
   UtilProcWMI::FindProc "mysqld.exe"
   IntOp $1 0 + $R0
   UtilProcWMI::FindProc "mysqld-nt.exe"
   IntOp $1 $1 + $R0
   ${If} $0 == 0
   ${OrIf} $1 == 0
    IntOp $2 $2 - 1
    IntCmp $2 0 notfound
    goto checkagain
   ${EndIf}
   goto out
 notfound:
    MessageBox MB_OK|MB_ICONEXCLAMATION $(COULD_NOT_START_SERVERS)
 out:
FunctionEnd



Function showWikiCustomize

    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    ${If} $0 == 1 
        !insertmacro MUI_HEADER_TEXT $(CUSTOMIZE_PAGE_TITLE) $(CUSTOMIZE_PAGE_SUBTITLE)
          Push $R0
          InstallOptions::dialog $PLUGINSDIR\wikicustomize.ini
          Pop $R0

    ${Else}
        Abort
    ${EndIf}
         
FunctionEnd

Function checkWikiCustomize
  CALL checkOS
  CALL checkPorts
FunctionEnd

; Issue 15644: still needed!
Function showLuceneParamters
    SectionGetFlags ${lucene} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    ${If} $0 == 1 
        !insertmacro MUI_HEADER_TEXT $(LUCENE_PAGE_TITLE) $(LUCENE_PAGE_SUBTITLE)
          
          ; get IPs
          ip::get_ip
          Pop $0
         
          Loop:
          Push $0
          Call GetNextIp
          Call CheckIp
          Pop $2 ; Type of current IP-address
          Pop $1 ; Current IP-address
          Pop $0 ; Remaining addresses
          StrCmp $2 '1' '' NoLoopBackIp
            Goto Continue
          NoLoopBackIp:
          StrCmp $2 '2' '' NoAPA
            Goto Continue
          NoAPA:
          StrCmp $2 '3' '' NoLanIp
            Goto ExitLoop
          NoLanIp:
            Goto ExitLoop
          Continue:
          StrCmp $0 '' ExitLoop Loop
          ExitLoop:  
          WriteINIStr "$PLUGINSDIR\lucene.ini" "Field 2" "state" $1
        
          Push $R0
          InstallOptions::dialog $PLUGINSDIR\lucene.ini
          Pop $R0

    ${Else}
        Abort
    ${EndIf}
FunctionEnd

; Issue 15644: still needed!
Function checkLuceneParameters
    ReadINIStr $IP "$PLUGINSDIR\lucene.ini" "Field 2" "state"
    ${If} $IP == "localhost"
    ${OrIf} $IP == "127.0.0.1"
; Issue 15644
;        MessageBox MB_OK|MB_ICONEXCLAMATION "Not allowed. Please enter a real IP or leave it blank."
;        Abort    
    ${EndIf}
    ${If} $IP == ""
        StrCpy $IP "true"
    ${EndIf}
FunctionEnd




Function changeConfigForFullXAMPP
    ; setup XAMPP (setup_xampp.bat and install script slightly modified)
    DetailPrint "Update XAMPP"
    SetOutPath "$INSTDIR"
    nsExec::ExecToLog '"$INSTDIR\setup_xampp.bat"'
    SetOutPath "$INSTDIR\htdocs\mediawiki"
    ; Apache logs directory doesn't exist, so create it
    CreateDirectory "$INSTDIR\apache\logs"
    
    ; setup halowiki (change LocalSettings.php)
    ; Use LocalSettings.php from input and change the following variables:
    ;   phpInterpreter
    ;   smwgIQEnabled
    ;   smwgAllowNewHelpQuestions
    ;   wgUseAjax
    ;   smwgKeepGardeningConsole
    ;   smwgEnableLogging
    ;   smwgDeployVersion
    ;   smwgSemanticAC
    ;   smwgGardeningBotDelay
    ;   wgScriptPath
    
    DetailPrint "Update LocalSettings.php"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\changeLS.php" phpInterpreter="$INSTDIR\php\php.exe" \
        smwgIQEnabled=true smwgAllowNewHelpQuestions=true wgUseAjax=true wgJobRunRate=0 wgEnableUploads=true \
        smwgKeepGardeningConsole=false smwgEnableLogging=false smwgDeployVersion=true \
        smwgSemanticAC=false smwgGardeningBotDelay=100 wgScriptPath="/mediawiki" ls=${LOCALSETTINGS}'
    
    ; Activate php_gd2.dll for thumbnails and php_openssl.dll for SSL 
    DetailPrint "Update php.ini"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\activateExtension.php" ini="$INSTDIR\php\php.ini" on=php_gd2,php_openssl,php_curl'
    
    
    ; Make halowiki directory accessible by Apache  
    DetailPrint "Update httpd.conf"  
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\changeHttpd.php" httpd="$INSTDIR\apache\conf\httpd.conf" wiki-path=mediawiki fs-path="$INSTDIR\htdocs\mediawiki" memcache=true'
        
    DetailPrint "Config customizations"
    CALL configCustomizationsForNew
    
    DetailPrint "Config external apps"
    CALL changeExternalApps
FunctionEnd

; deprecated
Function changeConfigForSMWPlusUpdate
    
    CALL checkForApacheAndMySQLAndMemcached
    ; update MediaWiki
    DetailPrint "Update MediaWiki database"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\maintenance\update.php --quick"'
    
    ; update SMW tables
    DetailPrint "Update SMW tables"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SemanticMediaWiki\maintenance\SMW_setup.php"'
    
    ; update SMW+ data
    DetailPrint "Refresh semantic data"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SemanticMediaWiki\maintenance\SMW_refreshData.php"'
    
    ; run job queue
    DetailPrint "Run job queue"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\maintenance\runJobs.php"'
    
    DetailPrint "Config customizations"
    CALL configCustomizationsForUpdate
FunctionEnd

Function configCustomizationsForNew
    
    ; Set customization
    ;   Wikiname
    ;   Wikilogo
    ;   Wiki content language
    ;   Wiki skin
    ;   Use Context-sensitive help (true/false)
    ;   Install help pages (true/false)
    ReadINIStr $WIKINAME "$PLUGINSDIR\wikicustomize.ini" "Field 2" "state"
    ReadINIStr $WIKILOGO "$PLUGINSDIR\wikicustomize.ini" "Field 4" "state"
    ReadINIStr $WIKILANG "$PLUGINSDIR\wikicustomize.ini" "Field 6" "state"
    ReadINIStr $WIKISKIN "$PLUGINSDIR\wikicustomize.ini" "Field 8" "state"
    
    
    ${If} $WIKINAME == ""
        StrCpy $WIKINAME "MyWiki"
    ${EndIf}
    ${Switch} $WIKISKIN
        ${Case} 'ontoskin3'
            StrCpy $WIKISKIN "ontoskin3"
            StrCpy $DEFAULTLOGO "skins/ontoskin3/img/wiki.png"
            ${Break}   
        ${Default}
            StrCpy $WIKISKIN "ontoskin3"
            StrCpy $DEFAULTLOGO "skins/ontoskin3/img/wiki.png"
        ${Break}
    ${EndSwitch}
    ${Switch} $WIKILANG
      ${Case} 'English'
        StrCpy $WIKILANG "en"
        ${Break}
      ${Case} 'German'
        StrCpy $WIKILANG "de"
        ${Break}
      ${Default}
        StrCpy $WIKILANG "en"
        ${Break}
    ${EndSwitch}
    
    
    IfFileExists $WIKILOGO 0 logo_not_exists
        CopyFiles $WIKILOGO $INSTDIR\htdocs\mediawiki
        ${GetFileName} $WIKILOGO $R0
        StrCpy $WIKILOGO "$R0"
        goto updateLocalSettings
    logo_not_exists:
        StrCpy $WIKILOGO $DEFAULTLOGO
    updateLocalSettings:    
        ${GetFileName} $WIKILOGO $R0
        DetailPrint "Configuring LocalSettings.php"
        DetailPrint "Instdir: $INSTDIR\php\php.exe"
        DetailPrint "Wikiname: $WIKINAME"
        DetailPrint "Wikilang: $WIKILANG"
        DetailPrint "Wikilogo: $WIKILOGO"
        DetailPrint "Wikiskin: $WIKISKIN"
   
        SetOutPath "$INSTDIR\htdocs\mediawiki"
        nsExec::ExecToLog ' "$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\changeLS.php" \
        wgSitename="$WIKINAME" wgDBname="semwiki_$WIKILANG" wgLogo=$$wgScriptPath/url:("$WIKILOGO") wgLanguageCode=$WIKILANG wgDefaultSkin="$WIKISKIN" \
        ls=LocalSettings.php'
    
        DetailPrint "Starting XAMPP"
        CALL installMemcached
        SetOutPath "$INSTDIR"
        Exec "$INSTDIR\xampp_start.bat"
        CALL waitForApacheAndMySQL
        MessageBox MB_OK|MB_ICONINFORMATION $(FIREWALL_COMPLAIN_INFO)
    
        DetailPrint "Import wiki database"
        nsExec::ExecToLog ' "$INSTDIR\import_smwplus_db.bat" "$INSTDIR" "root" "m8nix" "semwiki_en" "$INSTDIR\${WIKIDB}" '
        
        DetailPrint "Set php.exe in PATH Variable"
        ${EnvVarUpdate} $0 "PATH" "A" "HKLM" "$INSTDIR\php"
        ${EnvVarUpdate} $0 "PATH" "A" "HKLM" "$INSTDIR\mysql\bin" 
FunctionEnd



Function configCustomizationsForUpdate
    ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
    ReadINIStr $WIKINAME "$PLUGINSDIR\wikicustomize.ini" "Field 2" "state"
    ReadINIStr $WIKILOGO "$PLUGINSDIR\wikicustomize.ini" "Field 4" "state"
    ReadINIStr $WIKILANG "$PLUGINSDIR\wikicustomize.ini" "Field 6" "state"
    ReadINIStr $WIKISKIN "$PLUGINSDIR\wikicustomize.ini" "Field 8" "state"
    
    
    ${If} $WIKINAME == ""
        StrCpy $WIKINAME "__notset__"
    ${EndIf}
    ${If} $WIKISKIN == ""
        StrCpy $WIKISKIN "__notset__"
    ${EndIf}
    ${Switch} $WIKILANG
      ${Case} 'English'
        StrCpy $WIKILANG "en"
        ${Break}
      ${Case} 'German'
        StrCpy $WIKILANG "de"
        ${Break}
      ${Default}
        StrCpy $WIKILANG "__notset__"
        ${Break}
    ${EndSwitch}
    
    
    IfFileExists $WIKILOGO 0 logo_not_exists
        CopyFiles $WIKILOGO $INSTDIR
        ${GetFileName} $WIKILOGO $R0
        StrCpy $WIKILOGO "$R0"
        goto updateLocalSettings
    logo_not_exists:
        StrCpy $WIKILOGO "__notset__"
    updateLocalSettings:
        nsExec::ExecToLog '"$PHP" "$INSTDIR\installer\changeLS.php" \
        wgSitename="$WIKINAME" wgDBname="semwiki_$WIKILANG" wgLogo=$$wgScriptPath/url:("$WIKILOGO") wgLanguageCode=$WIKILANG wgDefaultSkin="$WIKISKIN" \
        smwgAllowNewHelpQuestions="true" ls=LocalSettings.php'
    
    /*DetailPrint "Updating helppages"
        DetailPrint "Starting XAMPP"
        SetOutPath "$INSTDIR"
        #Exec "$INSTDIR\xampp_start.bat"
        #CALL waitForApacheAndMySQL
        MessageBox MB_OK $(FIREWALL_COMPLAIN_INFO)
        SetOutPath "$INSTDIR\htdocs\mediawiki"
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHaloHelp\maintenance\setup.php" --deinstall'
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHaloHelp\maintenance\setup.php" --install'
     */
FunctionEnd

Function checkForSkype
    UtilProcWMI::FindProc "Skype.exe"
    ${If} $R0 == 1
        MessageBox MB_OKCANCEL  "Seems that Skype is running. Please close it or change its config, so that it does not block TCP port 80." IDOK ok IDABORT abortinstaller 
        abortInstaller:
            Abort
        ok:
    ${EndIf}
FunctionEnd

Function changeExternalApps
        # Set SOLR installation path in externalapps
        IfFileExists "$INSTDIR\htdocs\mediawiki\deployment\config\externalapps" 0 create_externalapps
        Goto inst_externalapps
        
        create_externalapps:
        ; Check if DF (WAT) is installed
        IfFileExists "$INSTDIR\htdocs\mediawiki\deployment\config\*.*" 0 nodf
        ; create file externalapps
        FileOpen $0 "$INSTDIR\htdocs\mediawiki\deployment\config\externalapps" w
        FileClose $0
        
        inst_externalapps:
        DetailPrint "Set SOLR path in externalapps"
        SetOutPath "$INSTDIR\htdocs\mediawiki\deployment\config"
        StrCpy $R1 "$INSTDIR\htdocs\mediawiki\deployment\config\externalapps"
       
        ${ConfigWrite} $R1 "solr=" '$INSTDIR\solr' $R0
        
        nodf:
FunctionEnd


Function FinishPageShow
  ;SectionGetFlags ${lucene} $0
  ;IntOp $0 $0 & ${SF_SELECTED}
  
  ;${If} $0 == 0
 ;   GetDlgItem $R0 $mui.FinishPage 1203
 ;   ShowWindow $R0 ${SW_HIDE}
 ; ${EndIf}
  
  ;write log
   StrCpy $0 "$INSTDIR\install.log"
   Push $0
   Call DumpLog
   
  /*SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 0
    GetDlgItem $R0 $mui.FinishPage 1204
    ShowWindow $R0 ${SW_HIDE}
  ${Endif}*/
FunctionEnd


Function LocateJVM
    Push $0
    Push $1
    
    ReadRegStr $JAVA_VER HKLM "SOFTWARE\JavaSoft\Java Runtime Environment" "CurrentVersion"
#    MessageBox MB_OK "Detected Java version: $JAVA_VER"
    DetailPrint "Detected Java version: [$JAVA_VER]."
    StrCmp "" $JAVA_VER JavaNotPresent CheckJavaVer

    JavaNotPresent:
        DetailPrint "No Java detected."
        StrCpy $JAVA_INSTALLATION_MSG "Java Runtime Environment is not \
             installed on your computer. You need version 1.6 or newer to \
             run this program."

        Goto Done

    CheckJavaVer:

        DetailPrint "Checking Java version ..."
        ReadRegStr $0 HKLM "SOFTWARE\JavaSoft\Java Runtime Environment\$JAVA_VER" JavaHome
        GetFullPathName $JAVA_HOME "$0"
        GetFullPathName /SHORT $JAVA_HOME_SHORT "$0"
        StrCpy $0 $JAVA_VER 1 0
        StrCpy $1 $JAVA_VER 1 2
        StrCpy $JAVA_VER "$0$1"
        IntCmp ${REQUIRED_JAVA_VERSION16} $JAVA_VER FoundCorrectJavaVer FoundCorrectJavaVer 0
    	IntCmp ${REQUIRED_JAVA_VERSION17} $JAVA_VER FoundCorrectJavaVer FoundCorrectJavaVer JavaVerNotCorrect
        
    FoundCorrectJavaVer:

        DetailPrint "Found valid Java version."
        IfFileExists "$JAVA_HOME_SHORT\bin\javaw.exe" 0 JavaNotPresent
        Goto Done
        
    JavaVerNotCorrect:
    
        DetailPrint "Found invalid Java version."
        StrCpy $JAVA_INSTALLATION_MSG "The version of Java Runtime Environment \
            installed on your computer is $JAVA_VER. Version ${REQUIRED_JAVA_VERSION16} or newer is required to \
            run this program."
        
    Done:
        Pop $1
        Pop $0
FunctionEnd

Function installMemcached
    DetailPrint "Install and start memcached"
    SetOutPath "$INSTDIR"
    Exec "$INSTDIR\installMemcachedAsService.bat"
FunctionEnd

Function installAsWindowsService
	SetOutPath "$INSTDIR"
    DetailPrint "Install Apache and MySQL as service."
    Exec "$INSTDIR\installApacheMySQLAsService.bat"
    
    SetOutPath "$INSTDIR\solr"
    DetailPrint "Install SOLR as service."
    Exec "$INSTDIR\solr\installAsService.bat"
    
    ; Register scheduled task for services to run them in higher runlevel
    ; remove the above registering by ones which uses apache & mysql as services.
    SetOutPath "$INSTDIR"
    StrCpy $FART "$INSTDIR\tools\fart.exe"
    ${GetWindowsVersion} $R0
    ${If} $R0 == "7"
    ${OrIf} $R0 == "Vista"
    ${OrIf} $R0 == "2008"
           DetailPrint "Add starts scripts as planned task for Windows 7/2008 Server"
            
           # Apache (remove others before)
           nsExec::ExecToLog 'schtasks /delete /tn "start_apache" /F'
           nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_apacheservice.txt" {{command}} "\"$INSTDIR\restartapacheservice.bat\""'
           nsExec::ExecToLog 'schtasks /create /tn "start_apache" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_apacheservice.txt"'
           # We do not need a stop command for apache
            
           # Mysql (remove others before)
           nsExec::ExecToLog 'schtasks /delete /tn "start_mysql" /F'
           nsExec::ExecToLog 'schtasks /delete /tn "stop_mysql" /F'
           nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_mysqlservice.txt" {{command}} "\"net start mysql\""'
           nsExec::ExecToLog 'schtasks /create /tn "start_mysql" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_mysqlservice.txt"'
           nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_mysqlservice.txt" {{command}} "\"net stop mysql\""'
           nsExec::ExecToLog 'schtasks /create /tn "stop_mysql" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_mysqlservice.txt"'
           
            # SOLR (remove others before)
           nsExec::ExecToLog 'schtasks /delete /tn "start_solr" /F'
           nsExec::ExecToLog 'schtasks /delete /tn "stop_solr" /F'
           nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_solrservice.txt" {{command}} "\"net start SOLR\""'
           nsExec::ExecToLog 'schtasks /create /tn "start_solr" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_start_solrservice.txt"'
           nsExec::ExecToLog '"$FART" -- "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_solrservice.txt" {{command}} "\"net stop SOLR\""'
           nsExec::ExecToLog 'schtasks /create /tn "stop_solr" /XML "$INSTDIR\htdocs\mediawiki\deployment\tools\internal\scheduled_tasks\runas_template_stop_solrservice.txt"'
            
        
     ${EndIf}
         
     ${If} $R0 == "XP"
     ${OrIf} $R0 == "2003"
         
         DetailPrint "Add starts scripts as planned task for Windows XP/2003 Server"
         nsExec::ExecToLog 'schtasks /delete /tn "start_apache" /F'
         nsExec::ExecToLog 'schtasks /delete /tn "stop_apache" /F'
         nsExec::ExecToLog 'schtasks /create /tn "start_apache" /ru "SYSTEM" /tr "\"net start apache\"" /sc once /st 00:00'
         nsExec::ExecToLog 'schtasks /create /tn "stop_apache" /ru "SYSTEM" /tr "\"net stop apache\"" /sc once /st 00:00'
              
         nsExec::ExecToLog 'schtasks /delete /tn "start_mysql" /F'
         nsExec::ExecToLog 'schtasks /delete /tn "stop_mysql" /F'
         nsExec::ExecToLog 'schtasks /create /tn "start_mysql" /ru "SYSTEM" /tr "\"net start mysql\"" /sc once /st 00:00'
         nsExec::ExecToLog 'schtasks /create /tn "stop_mysql" /ru "SYSTEM" /tr "\"net stop mysql\"" /sc once /st 00:00'
         
         nsExec::ExecToLog 'schtasks /delete /tn "start_solr" /F'
         nsExec::ExecToLog 'schtasks /delete /tn "stop_mysql" /F'
         nsExec::ExecToLog 'schtasks /create /tn "start_solr" /ru "SYSTEM" /tr "\"net start SOLR\"" /sc once /st 00:00'
         nsExec::ExecToLog 'schtasks /create /tn "stop_mysql" /ru "SYSTEM" /tr "\"net stop SOLR\"" /sc once /st 00:00'
            
     ${EndIf}

FunctionEnd


; Uninstaller
Function un.uninstallApacheAndMySQLAsWindowsService
    SetOutPath "$INSTDIR"
    DetailPrint "Stop and uninstall Apache and MySQL as service."
    Exec "$INSTDIR\uninstallApacheMySQLAsService.bat"
    
    SetOutPath "$INSTDIR\solr"
    DetailPrint "Stop and uninstall SOLR as service."
    Exec "$INSTDIR\solr\uninstallAsService.bat"
    
    SetOutPath "c:\temp\halo" #dummy to make installation dir removable
FunctionEnd

Function un.stopApacheMySQL
   # can kill 64-bit processes
   DetailPrint "Stop Apache and MySQL."
   UtilProcWMI::KillProc "httpd.exe"
   IntOp $0 0 + $R0
   UtilProcWMI::KillProc "mysqld.exe"
   IntOp $1 0 + $R0
   UtilProcWMI::KillProc "mysqld-nt.exe"
FunctionEnd

Function un.stopSolr
    # can kill 64-bit processes
    UtilProcWMI::KillProc "solr.exe"
FunctionEnd

Function un.uninstallMemcached
    DetailPrint "Stop and uninstall memcached"
    SetOutPath "$INSTDIR"
    Exec "$INSTDIR\uninstallMemcachedAsService.bat"
    SetOutPath "c:\temp\halo" #dummy to make installation dir removable
FunctionEnd

Function un.checkForApacheAndMySQLAndMemcachedAndSolr
 checkagain:
   UtilProcWMI::FindProc "httpd.exe"
   IntOp $0 0 + $R0
   UtilProcWMI::FindProc "mysqld.exe"
   IntOp $1 0 + $R0
   UtilProcWMI::FindProc "mysqld-nt.exe"
   IntOp $1 $1 + $R0
   UtilProcWMI::FindProc "memcached.exe"
   IntOp $2 0 + $R0
   UtilProcWMI::FindProc "solr.exe"
   IntOp $3 0 + $R0
   ${If} $0 == 1
   ${OrIf} $1 == 1
   ${OrIf} $2 == 1
   ${OrIf} $3 == 1
    MessageBox MB_ICONEXCLAMATION|MB_OKCANCEL $(STARTED_SERVERS) IDOK 0 IDCANCEL skipCheck
    goto checkagain
   ${EndIf}
   goto out
 skipcheck:
    
 out:
FunctionEnd

Section "Uninstall"

    !insertmacro MUI_STARTMENU_GETFOLDER Application $MUI_TEMP
    
    MessageBox MB_OKCANCEL|MB_ICONEXCLAMATION \
        "Please note that all running ${PRODUCT} instances must be closed before uninstall. $\n$\n \
        Are you sure you want to deinstall the ${PRODUCT}? All files (including \
        changed configuration files and log files) will be removed." \
    IDOK Deinstall
    # MessageBox MB_OK "User aborted!"
    goto FinalExit

    Deinstall:
	# MessageBox MB_OK "User said OK!"
    
    ; Un-install services (if installed at all)
    Call un.uninstallApacheAndMySQLAsWindowsService
    Call un.uninstallMemcached
    Call un.stopApacheMySQL
    Call un.stopSolr
    
    Call un.checkForApacheAndMySQLAndMemcachedAndSolr
    
    # Delete from PATH variable
    ${un.EnvVarUpdate} $0 "PATH" "R" "HKLM" "$INSTDIR\php"
    ${un.EnvVarUpdate} $0 "PATH" "R" "HKLM" "$INSTDIR\mysql\bin"      
    
    # Delete all start menu entries
    Delete "$SMPROGRAMS\$MUI_TEMP\Uninstall.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\Start ${PRODUCT_SHORT}.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\Stop ${PRODUCT_SHORT}.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\Start Solr.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\Stop Solr.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\${PRODUCT_SHORT} Main Page.lnk"
    
    ;Delete start menu IF THE MENU IS EMPTY.
    StrCpy $MUI_TEMP "$SMPROGRAMS\$MUI_TEMP"

    startMenuDeleteLoop:
        RMDir $MUI_TEMP
        GetFullPathName $MUI_TEMP "$MUI_TEMP\.."
        
        IfErrors startMenuDeleteLoopDone

        StrCmp $MUI_TEMP $SMPROGRAMS startMenuDeleteLoopDone startMenuDeleteLoop
    startMenuDeleteLoopDone:

    DeleteRegKey HKCU "Software\Ontoprise\${PRODUCT} ${VERSION}"
    DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}"

    

    Delete "$INSTDIR\*"
   
    RMDir /r "$INSTDIR\webalizer"
    RMDir /r "$INSTDIR\anonymous"
    RMDir /r "$INSTDIR\apache"
    RMDir /r "$INSTDIR\tmp"
    RMDir /r "$INSTDIR\sendmail"
    RMDir /r "$INSTDIR\cgi-bin"
    RMDir /r "$INSTDIR\security"
    RMDir /r "$INSTDIR\FileZillaFTP"
    RMDir /r "$INSTDIR\phpMyAdmin"
    RMDir /r "$INSTDIR\php"
    RMDir /r "$INSTDIR\perl"
    RMDir /r "$INSTDIR\help"
    RMDir /r "$INSTDIR\htdocs"
    RMDir /r "$INSTDIR\install"
    RMDir /r "$INSTDIR\licenses"
    RMDir /r "$INSTDIR\MercuryMail"
    RMDir /r "$INSTDIR\mysql"
    RMDir /r "$INSTDIR\webdav"
    RMDir /r "$INSTDIR\contrib"
    RMDir /r "$INSTDIR\tomcat"
    RMDir /r "$INSTDIR\memcached"
    RMDir /r "$INSTDIR\solr"
    RMDir /r "$INSTDIR\tools"
        
    ; only remove if empty
    RMDir "$INSTDIR"

    ; If OntoStudio is installed in:
    ; c:\Program Files\Ontoprise\OntoStudio
    ; we want the uninstaller to remove
    ; c:\Program Files\Ontoprise\OntoStudio
    ; AND
    ; c:\Program Files\Ontoprise
    ; but the latter only if there's no other file or directory
    ; The following code accomplishes this.
    Push 5 #maximum amount of directories to remove
    Push "$INSTDIR" #input string

    Exch $R0 ;input string
    Exch
    Exch $R1 ;maximum number of dirs to check for
    Push $R2
    Push $R3
    Push $R4
    Push $R5
       IfFileExists "$R0\*.*" 0 +2
       RMDir "$R0"
     StrCpy $R5 0
    top:
     StrCpy $R2 0
     StrLen $R4 $R0
    loop:
     IntOp $R2 $R2 + 1
      StrCpy $R3 $R0 1 -$R2
     StrCmp $R2 $R4 exit
     StrCmp $R3 "\" 0 loop
      StrCpy $R0 $R0 -$R2
       IfFileExists "$R0\*.*" 0 +2
       RMDir "$R0"
     IntOp $R5 $R5 + 1
     StrCmp $R5 $R1 exit
    Goto top
    exit:
    Pop $R5
    Pop $R4
    Pop $R3
    Pop $R2
    Pop $R1
    Pop $R0

    
    
    Push "$INSTDIR\output.txt" # output file (dummy)
    Push "*" # filter (dummy)
    Push "C:\tmp2\bbb\test" # folder to search in (dummy)
    Call un.MakeFileList
    StrCmp "" "$FILE_LIST" DeleteOk DeleteNotOk
    DeleteOk:
        Goto FinalExit
        
    DeleteNotOk:
        MessageBox MB_OK "Some files could not be deleted."

    FinalExit:

SectionEnd

###########################################################################
# Function for generating a list of files
# http://nsis.sourceforge.net/archive/nsisweb.php?page=922&instances=0
###########################################################################
Function un.MakeFileList
    StrCpy $FILE_LIST ""
    Exch $R0 #path
    Exch
    Exch $R1 #filter
    Exch
    Exch 2
    Exch $R2 #output file
    Exch 2
    Push $R3
    Push $R4
    Push $R5
     ClearErrors
     #MessageBox MB_OK "MakeFileList: FindFirst with [$R3] [$R4] [$R0\$R1]"
     FindFirst $R3 $R4 "$R0\$R1"
      #FileOpen $R5 $R2 w
    
     Loop:
     IfErrors Done
      #FileWrite $R5 "$R0\$R4$\r$\n"
      #MessageBox MB_OK "--- 1: [$FILE_LIST]"
      StrCpy $FILE_LIST "$R0\$R4$\r$\n$FILE_LIST"
      #MessageBox MB_OK "--- 2: [$FILE_LIST]"
      FindNext $R3 $R4
      Goto Loop
    
     Done:
      FileClose $R5
     FindClose $R3
    Pop $R5
    Pop $R4
    Pop $R3
    Pop $R2
    Pop $R1
    Pop $R0
    #MessageBox MB_OK "The following files could not be deleted: $FILE_LIST"
    DetailPrint "The following files could not be deleted: [$FILE_LIST]"
FunctionEnd

; dumps the install log
!define LVM_GETITEMCOUNT 0x1004
!define LVM_GETITEMTEXT 0x102D
Function DumpLog
  Exch $5
  Push $0
  Push $1
  Push $2
  Push $3
  Push $4
  Push $6
 
  FindWindow $0 "#32770" "" $HWNDPARENT
  GetDlgItem $0 $0 1016
  StrCmp $0 0 exit
  FileOpen $5 $5 "w"
  StrCmp $5 "" exit
    SendMessage $0 ${LVM_GETITEMCOUNT} 0 0 $6
    System::Alloc ${NSIS_MAX_STRLEN}
    Pop $3
    StrCpy $2 0
    System::Call "*(i, i, i, i, i, i, i, i, i) i \
      (0, 0, 0, 0, 0, r3, ${NSIS_MAX_STRLEN}) .r1"
    loop: StrCmp $2 $6 done
      System::Call "User32::SendMessageA(i, i, i, i) i \
        ($0, ${LVM_GETITEMTEXT}, $2, r1)"
      System::Call "*$3(&t${NSIS_MAX_STRLEN} .r4)"
      FileWrite $5 "$4$\r$\n"
      IntOp $2 $2 + 1
      Goto loop
    done:
      FileClose $5
      System::Free $1
      System::Free $3
  exit:
    Pop $6
    Pop $4
    Pop $3
    Pop $2
    Pop $1
    Pop $0
    Exch $5
FunctionEnd

Function showReadme
    Exec '"notepad.exe" "$INSTDIR\README-SMWPLUS.txt"'
FunctionEnd

; Function GetNextIp
; input: head of stack
; format: 'ip1;ip2;ip3;ip4;'
; output: 'ip1' head of stack
;         'ip2;ip3;ip4;' second entry of stack
 
Function GetNextIp
  Exch $0
  Push $1
  Push $2
  Strcpy $2 0             ; Counter
  Loop:
    IntOp $2 $2 + 1
    StrCpy $1 $0 1 $2
    StrCmp $1 '' ExitLoop
    StrCmp $1 ';' '' Loop
    StrCpy $1 $0 $2       ; IP-address
    IntOp $2 $2 + 1
    StrCpy $0 $0 '' $2    ; Remaining string
  ExitLoop:
  Pop $2
  Push $0
  Exch 2
  Pop $0
  Exch $1
FunctionEnd
 
; Function CheckIP
; input: IP-address on stack
; output: additional entry on stack
;         1 - LoopBack IP (localhost, indicates no connection to a LAN or to the internet).
;         2 - Automatic Private IP Address (no DHCP server).
;         3 - Network IP.
;         4 - Internet IP.
; Eg:
; Push '192.168.0.100'
; Call CheckIP
; Pop $0 ; Contains '3'
; Pop $1 ; Contains '192.168.0.100'
 
Function CheckIP
  Exch $0
  Push $1
 
  ; Check 127.x.x.x
  Push '127.0.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' Range1     ; IP cannot be in range of LoopBack addresses
  Push '127.255.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 LoopBack      ; We found a LoopBack IP
 
  ; Check 10.x.x.x
  Range1:
  Push '10.0.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' Range2     ; IP cannot be in range 1
  Push '10.255.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 LanIp         ; We found a LanIp
 
  ; Check 172.16.x.x to 172.31.x.x
  Range2:
  Push '172.16.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' Range3     ; IP cannot be in range 2
  Push '172.31.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 LanIp         ; We found a LanIp
 
  ; Check 192.168.x.x
  Range3:
  Push '192.168.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' Range4     ; IP cannot be in range 3
  Push '192.168.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 LanIp         ; We found a LanIp
 
  ; Check 169.254.x.x
  Range4:
  Push '169.254.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' InternetIp ; It should be an internet IP
  Push '169.254.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 APA           ; We found an Automatic Private IP Address
 
  Goto InternetIp           ; Remaining addresses are internet IPs
 
  LoopBack:
  StrCpy $1 1
  Goto Exit
 
  APA:
  StrCpy $1 2
  Goto Exit
 
  LanIp:
  StrCpy $1 3
  Goto Exit
 
  InternetIp:
  StrCpy $1 4
 
  Exit:
  Exch $1
  Exch 1
  Exch $0
  Exch 1
FunctionEnd
 
; Function VersionCheck
; input: 'v1', 'v2' on stack
; output 1 - if number 1 is newer
;        2 - if number 2 is newer
;        0 - if it is the same verion
; Eg:
; Push '3.5.1.4'
; Push '3.5'
; Call VersionCheck
; Pop $0 ; now contains 1
 
Function VersionCheck
  Exch $0 ;second versionnumber
  Exch
  Exch $1 ;first versionnumber
  Push $R0 ;counter for $0
  Push $R1 ;counter for $1
  Push $3 ;temp char
  Push $4 ;temp string for $0
  Push $5 ;temp string for $1
  StrCpy $R0 "-1"
  StrCpy $R1 "-1"
  Start:
  StrCpy $4 ""
  DotLoop0:
  IntOp $R0 $R0 + 1
  StrCpy $3 $0 1 $R0
  StrCmp $3 "" DotFound0
  StrCmp $3 "." DotFound0
  StrCpy $4 $4$3
  Goto DotLoop0
  DotFound0:
  StrCpy $5 ""
  DotLoop1:
  IntOp $R1 $R1 + 1
  StrCpy $3 $1 1 $R1
  StrCmp $3 "" DotFound1
  StrCmp $3 "." DotFound1
  StrCpy $5 $5$3
  Goto DotLoop1
  DotFound1:
  Strcmp $4 "" 0 Not4
    StrCmp $5 "" Equal
    Goto Ver2Less
  Not4:
  StrCmp $5 "" Ver2More
  IntCmp $4 $5 Start Ver2Less Ver2More
  Equal:
  StrCpy $0 "0"
  Goto Finish
  Ver2Less:
  StrCpy $0 "1"
  Goto Finish
  Ver2More:
  StrCpy $0 "2"
  Finish:
  Pop $5
  Pop $4
  Pop $3
  Pop $R1
  Pop $R0
  Pop $1
  Exch $0
FunctionEnd

; Returns windows version
; Usage: ${GetWindowsVersion} $R0
;
; $R0 contains: 95, 98, ME, NT x.x, 2000, XP, 2003, Vista, 7 or '' (for unknown)
Function GetWindowsVersion
 
  Push $R0
  Push $R1
 
  ClearErrors
 
  ReadRegStr $R0 HKLM \
  "SOFTWARE\Microsoft\Windows NT\CurrentVersion" CurrentVersion
 
  IfErrors 0 lbl_winnt
 
  ; we are not NT
  ReadRegStr $R0 HKLM \
  "SOFTWARE\Microsoft\Windows\CurrentVersion" VersionNumber
 
  StrCpy $R1 $R0 1
  StrCmp $R1 '4' 0 lbl_error
 
  StrCpy $R1 $R0 3
 
  StrCmp $R1 '4.0' lbl_win32_95
  StrCmp $R1 '4.9' lbl_win32_ME lbl_win32_98
 
  lbl_win32_95:
    StrCpy $R0 '95'
  Goto lbl_done
 
  lbl_win32_98:
    StrCpy $R0 '98'
  Goto lbl_done
 
  lbl_win32_ME:
    StrCpy $R0 'ME'
  Goto lbl_done
 
  lbl_winnt:
 
  StrCpy $R1 $R0 1
 
  StrCmp $R1 '3' lbl_winnt_x
  StrCmp $R1 '4' lbl_winnt_x
 
  StrCpy $R1 $R0 3
 
  StrCmp $R1 '5.0' lbl_winnt_2000
  StrCmp $R1 '5.1' lbl_winnt_XP
  StrCmp $R1 '5.2' lbl_winnt_2003
  StrCmp $R1 '6.0' lbl_winnt_vista
  StrCmp $R1 '6.1' lbl_winnt_7 lbl_error
 
  lbl_winnt_x:
    StrCpy $R0 "NT $R0" 6
  Goto lbl_done
 
  lbl_winnt_2000:
    Strcpy $R0 '2000'
  Goto lbl_done
 
  lbl_winnt_XP:
    Strcpy $R0 'XP'
  Goto lbl_done
 
  lbl_winnt_2003:
    Strcpy $R0 '2003'
  Goto lbl_done
 
  lbl_winnt_vista:
    Strcpy $R0 'Vista'
  Goto lbl_done
 
  lbl_winnt_7:
    Strcpy $R0 '7'
  Goto lbl_done
 
  lbl_error:
    Strcpy $R0 ''
  lbl_done:
 
  Pop $R1
  Exch $R0
 
FunctionEnd

Function WriteToFile
 Exch $0 ;file to write to
 Exch
 Exch $1 ;text to write

  FileOpen $0 $0 a #open file
   FileSeek $0 0 END #go to end
   FileWrite $0 $1 #write to file
  FileClose $0

 Pop $1
 Pop $0
FunctionEnd


Function checkOS
    ${GetWindowsVersion} $R0
    ${If} $R0 == "7"
    ${OrIf} $R0 == "2008"
        Goto os_ok
    ${EndIf}
    MessageBox MB_OK|MB_ICONEXCLAMATION  "Non-supported OS detected! Please use Windows 7 or Windows Server 2008 (R2)." 
    Abort 
os_ok:
FunctionEnd
;
; Checks if TCP ports 80 (HTTP) and 3306 (MySQL) are available.
; Aborts installtion with an according message, if not.
;
Function checkPorts
  TCP::CheckPort 80
  Pop $0
  StrCmp $0 "free" http_port_ok
  StrCmp $0 "socket_error" http_socket_error
  StrCmp $0 "inuse" http_socket_inuse
  Goto http_port_ok
http_socket_inuse:
    UtilProcWMI::FindProc "Skype.exe"
    ${If} $R0 == 1
        MessageBox MB_OK|MB_ICONEXCLAMATION   "Seems that Skype is blocking TCP port 80. Please close it or change its config."  
        Abort
    ${EndIf}
      MessageBox MB_OK|MB_ICONEXCLAMATION "HTTP Port 80 is in use by another application."
      Abort
        
http_socket_error:
  MessageBox MB_OK|MB_ICONEXCLAMATION "Invalid TCP Port number. It should be an integer between 1 and 65535."
  Abort
http_port_ok:

  TCP::CheckPort 3306
  Pop $0
  StrCmp $0 "free" mysql_port_ok
  StrCmp $0 "socket_error" mysql_socket_error
  StrCmp $0 "inuse" mysql_socket_inuse
  Goto mysql_port_ok
mysql_socket_inuse:
  MessageBox MB_OK|MB_ICONEXCLAMATION "MySQL port 3306 is in use by another application."
  Abort
mysql_socket_error:
  MessageBox MB_OK|MB_ICONEXCLAMATION "Invalid TCP Port number. It should be an integer between 1 and 65535."
  Abort
mysql_port_ok:
FunctionEnd
 
Function StrContains
/*After this point:
  ------------------------------------------
  $R0 = SubString (input)
  $R1 = String (input)
  $R2 = SubStringLen (temp)
  $R3 = StrLen (temp)
  $R4 = StartCharPos (temp)
  $R5 = TempStr (temp)*/
 
  ;Get input from user
  Exch $R0
  Exch
  Exch $R1
  Push $R2
  Push $R3
  Push $R4
  Push $R5
 
  ;Get "String" and "SubString" length
  StrLen $R2 $R0
  StrLen $R3 $R1
  ;Start "StartCharPos" counter
  StrCpy $R4 0
 
  ;Loop until "SubString" is found or "String" reaches its end
  ${Do}
    ;Remove everything before and after the searched part ("TempStr")
    StrCpy $R5 $R1 $R2 $R4
 
    ;Compare "TempStr" with "SubString"
    ${IfThen} $R5 == $R0 ${|} ${ExitDo} ${|}
    ;If not "SubString", this could be "String"'s end
    ${IfThen} $R4 >= $R3 ${|} ${ExitDo} ${|}
    ;If not, continue the loop
    IntOp $R4 $R4 + 1
  ${Loop}
 
/*After this point:
  ------------------------------------------
  $R0 = ResultVar (output)*/
 
  ;Remove part before "SubString" on "String" (if there has one)
  StrCpy $R0 $R1 `` $R4
 
  ;Return output to user
  Pop $R5
  Pop $R4
  Pop $R3
  Pop $R2
  Pop $R1
  Exch $R0
FunctionEnd

