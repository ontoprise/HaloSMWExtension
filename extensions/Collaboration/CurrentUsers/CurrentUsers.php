<?php
# Extension:CurrentUsers
# - Licenced under LGPL (http://www.gnu.org/copyleft/lesser.html)
# - Author: [http://www.organicdesign.co.nz/nad User:Nad]{{Category:Extensions created with Template:Extension}}
# - Started: 2007-07-25

if ( !defined( 'MEDIAWIKI' ) ) die( 'Not an entry point.' );
 
define( 'CURRENTUSERS_VERSION', '1.0.4, 2008-06-07' );

$egCurrentUsersMagic           = 'currentusers';
$egCurrentUsersTemplate        = 'CurrentUsers';
$egCurrentUsersTimeout         = 60;
 
$wgExtensionFunctions[]        = 'efSetupCurrentUsers';
$wgHooks['LanguageGetMagic'][] = 'efCurrentUsersLanguageGetMagic';
 
$wgExtensionCredits['parserhook'][] = array(
        'name'        => 'CurrentUsers',
        'author'      => '[http://www.organicdesign.co.nz/nad User:Nad]',
        'description' => 'An example extension made with [http://www.organicdesign.co.nz/Template:Extension Template:Extension]',
        'url'         => 'http://www.mediawiki.org/wiki/Extension:CurrentUsers',
        'version'     => CURRENTUSERS_VERSION
);
 
/**
 * Called from $wgExtensionFunctions array when initialising extensions
 */
function efSetupCurrentUsers() {
        global $wgUser, $wgParser, $egCurrentUsers, $egCurrentUsersTimeout, $egCurrentUsersMagic;
        $wgParser->setFunctionHook( $egCurrentUsersMagic, 'efCurrentUsersMagic' );
        if ( strtolower( $_REQUEST['title'] ) == 'robots.txt' ) $bot = 'bot';
        else $bot = '';
        $file = dirname( __FILE__ ) . '/CurrentUsers.txt';
        $data = file( $file );
        $h = strftime( '%H' );
        $m = strftime( '%M' );
        $now = $h*60+$m;
        $user = $wgUser->getUserPage()->getText();
        $egCurrentUsers = array( "$h:$m:$user" );
        $bot = '';
        foreach ( $data as $item ) {
                list( $h, $m, $u, $b ) = split( ':', trim( $item ) );
                $age = $now-$h*60-$m;
                if ( $age < 0 ) $age += 1440;
                if ( $u == $user && $b == 'bot' ) $bot = $b;
                if ( $u != '' && $u != $user && $age < $egCurrentUsersTimeout ) $egCurrentUsers[] = "$h:$m:$u:$b";
        }
        $egCurrentUsers[0] .= ":$bot";
        file_put_contents( $file, join( "\n", $egCurrentUsers ) );
}
 
/**
 * Needed in MediaWiki >1.8.0 for magic word hooks to work properly
 */
function efCurrentUsersLanguageGetMagic( &$magicWords, $langCode = 0 ) {
        global $egCurrentUsersMagic;
        $magicWords[$egCurrentUsersMagic] = array( 0, $egCurrentUsersMagic );
        return true;
}

function efCurrentUsersMagic( &$parser ) {
        global $egCurrentUsers, $egCurrentUsersTemplate, $wgTitle;
        $parser->disableCache();
        $users = '';
        $guests = 0;
        $bots = 0;
        foreach ( $egCurrentUsers as $item ) {
                list( $h, $m, $u, $b ) = split( ':', $item );
                if ( User::isIP( $u ) ) $b ? $bots++ : $guests++;
                else $users .= "{" . "{" . "$egCurrentUsersTemplate|$h:$m|$u|}" . "}\n";
        }
        if ( $guests ) $users .= "{" . "{" . "$egCurrentUsersTemplate|Guests||$guests}" . "}\n";
        if ( $bots )   $users .= "{" . "{" . "$egCurrentUsersTemplate|Robots||$bots}" . "}\n";
        $users =  $parser->preprocess( $users, $wgTitle, $parser->mOptions );
        return array(
                $users,
                'found'   => true,
                'nowiki'  => false,
                'noparse' => false,
                'noargs'  => false,
                'isHTML'  => false
        );
}