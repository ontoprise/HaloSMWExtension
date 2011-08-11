<?php

/*
 * This class provides a parser function, that can detect
 * whether a certain extension is installed.
 */
class SMWIsExtensionInstalledPF {
	
	static function registerFunctions( &$parser ) {
		$parser->setFunctionHook( 'isExtensionInstalled', 
			array( 'SMWIsExtensionInstalledPF', 'renderIEIPF' ), SFH_OBJECT_ARGS); 
		return true;
	}
	
	static function renderIEIPF( &$parser, $frame, $args) {
		if(defined($args[0])){
			return 'true';
		} else {
			return 'false';
		}
	}
}