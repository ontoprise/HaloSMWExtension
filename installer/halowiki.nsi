; bigtest.nsi
;
; This script attempts to test most of the functionality of the NSIS exehead.

;--------------------------------
!include "${NSISDIR}\include\LogicLib.nsh"


!ifdef NOCOMPRESS
SetCompress off
!endif

;--------------------------------

Name "Halowiki"
Caption "SMW+ 1.0"
Icon "images\nsis1-install.ico"
OutFile "halowiki.exe"

SetDateSave on
SetDatablockOptimize on
CRCCheck on
SilentInstall normal
BGGradient 000000 800000 FFFFFF
InstallColors FF8080 000030
XPStyle on

InstallDir "$PROGRAMFILES\ontoprise\xampp"

CheckBitmap "images\classic-cross.bmp"

LicenseText "GPL-License"
LicenseData "gpl.txt"

RequestExecutionLevel admin

;--------------------------------

Page license
Page components
Page custom showFullInstWithoutXAMPP
Page custom showMWAndSMWUpdate
Page directory
Page instfiles

UninstPage uninstConfirm
UninstPage instfiles

;--------------------------------

!ifndef NOINSTTYPES ; only if not defined
  InstType "New (with XAMPP)"
  InstType "New (without XAMPP)"
  InstType "Update from MediaWiki"
  InstType "Update from SMW"
  InstType "Update from SMW+"
  InstType /NOCUSTOM
!endif

AutoCloseWindow false
ShowInstDetails show

;--------------------------------

Var PHP
Var MYSQLDUMP
Var DBSERVER
Var DBUSER
Var DBPASS
Var CURINSTTYPE

Function ".onInit"
  InitPluginsDir
  File /oname=$PLUGINSDIR\wikiinst.ini "gui\wikiinst.ini"
  File /oname=$PLUGINSDIR\smwinst.ini "gui\smwinst.ini"
FunctionEnd

Function .onSelChange
	GetCurInstType $CURINSTTYPE
	
FunctionEnd

Function showFullInstWithoutXAMPP
  GetCurInstType $CURINSTTYPE
   MessageBox MB_OK $CURINSTTYPE
  ${If} $CURINSTTYPE == 1
  	 Push $R0
	  InstallOptions::dialog $PLUGINSDIR\wikiinst.ini
	  Pop $R0
	  ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
	  ReadINIStr $MYSQLDUMP "$PLUGINSDIR\wikiinst.ini" "Field 4" "state"
	  ReadINIStr $DBSERVER "$PLUGINSDIR\wikiinst.ini" "Field 6" "state"
	  ReadINIStr $DBUSER "$PLUGINSDIR\wikiinst.ini" "Field 8" "state"
	  ReadINIStr $DBPASS "$PLUGINSDIR\wikiinst.ini" "Field 10" "state"
	  Pop $R0
  ${Else}
  	  Abort
  ${EndIf}
  
FunctionEnd

Function showMWAndSMWUpdate
  GetCurInstType $CURINSTTYPE
  ${If} $CURINSTTYPE == 4
  	Abort
  ${Else}
  	 Push $R0
	  InstallOptions::dialog $PLUGINSDIR\smwinst.ini
	  Pop $R0
	  ReadINIStr $PHP "$PLUGINSDIR\smwinst.ini" "Field 2" "state"
	  Pop $R0	
  ${EndIf}
 
FunctionEnd

Section "SMW+"
 SectionIn 1 2 3 4 RO 
SectionEnd
; ---- Install sections ---------------
Section "-XAMPP"
  SectionIn 1 
  SetOutPath $INSTDIR
  CreateDirectory "$INSTDIR"
  ;File /r c:Programme\xampp\xampp\*
  ;TODO: start setup_xampp
SectionEnd

Section "-MediaWiki 1.12"
	SectionIn 2
SectionEnd

Section "-SMW 1.0"
	SectionIn 3
SectionEnd

Section "-SMW+ 1.0"
  SectionIn 4
  SetOutPath $INSTDIR\htdocs\wiki
  CreateDirectory "INSTDIR\htdocs\wiki"
  IfFileExists $INSTDIR\htdocs\wiki\extensions\SMWHalo\*.* 0 new
   MessageBox MB_YESNO "Do you want to update SMW+?" IDNO cancel
  update:
     ;File /r /x CVS /x *.zip /x *.exe ..\*
     ;TODO start update scripts and setup DB
  new:
 	 ;File /r /x CVS /x *.zip /x *.exe ..\*
  cancel:
SectionEnd


;--------------------------------

; Uninstaller

UninstallText "This will uninstall SMW+. Hit next to continue."
UninstallIcon "${NSISDIR}\Contrib\Graphics\Icons\nsis1-uninstall.ico"

Section "Uninstall"


SectionEnd
