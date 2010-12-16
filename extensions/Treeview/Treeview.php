<?php
/**
* MediaWiki Semantic Treeview Extension
* - See http://smwforum.ontoprise.com/smwforum/index.php/Semantic_Treeview for installation and usage details
* - Licenced under LGPL (http://www.gnu.org/copyleft/lesser.html)
* - Author:  based on Treeview5 by http://www.organicdesign.co.nz/nad
*            improved by Ontoprise
* - Started: $Id$
*
* @file
* @ingroup Treeview
* @defgroup Treeview Semantic Treeview extension
*/

if (!defined('MEDIAWIKI')) die('Not an entry point.');

define('SEMANTIC_TREEVIEW_VERSION','{{$VERSION}} [B{{$BUILDNUMBER}}]');

require_once('TreeGenerator.php');

// file for responding to Ajax calls
require_once('getTree.php');

require_once('TreeviewResize.php');
# Set any unset images to default titles
if (!isset($wgTreeViewImages) || !is_array($wgTreeViewImages)) $wgTreeViewImages = array();

$wgTreeViewMagic               = "tree"; # the parser-function name for trees
$wgTreeViewShowLines           = false;  # whether to render the dotted lines joining nodes
$wgExtensionFunctions[]        = 'wfSetupTreeView';
$wgHooks['LanguageGetMagic'][] = 'wfTreeViewLanguageGetMagic';

$wgExtensionCredits['parserhook'][] = array(
    'name'        => 'Semantic Treeview',
    'author'      => 'based on the work of [http://www.organicdesign.co.nz/nad Nad], improved by [http://www.ontoprise.de Ontoprise]',
    'url'         => 'http://smwforum.ontoprise.com/smwforum/index.php/Help:TreeView_Extension',
    'description' => 'Improved version of the Mediawiki extension [http://www.mediawiki.org/wiki/Extension:Treeview Treeview].'.
    				 ' Extends the wiki parser to allow bullet and numbered lists to work with recursion and optionally'.
                     ' allows these to be rendered as collapsible trees using the free'.
                     ' [http://www.destroydrop.com/javascripts/tree dTree] JavaScript tree menu.',
    'version'     => SEMANTIC_TREEVIEW_VERSION
    );

//Insert show trigger into toolbox
function smwhg_AddTreeToToolbox(& $template) {

    //Don't insert tree into ontoskin
    global $wgUser;
    $skin = $wgUser !== NULL ? $wgUser->getSkin()->getSkinName() : $wgDefaultSkin;
    if(($skin == 'ontoskin') || ($skin == 'ontoskin2') || ($skin == 'ontoskin3')) return true;

    echo '<li><a href="javascript:smwhf_showTree()">'.'Show TreeView'.'</a></li>';
    return true;
}

$wgHooks['SkinTemplateToolboxEnd'][] = 'smwhg_AddTreeToToolbox';
 


class SemanticTreeview {

    var $version  = SEMANTIC_TREEVIEW_VERSION;
    var $uniq     = '';      # uniq part of all tree id's
    var $uniqname = 'tv';    # input name for uniqid
    var $id       = '';      # id for specific tree
    var $baseDir  = '';      # internal absolute path to treeview directory
    var $baseUrl  = '';      # external URL to treeview directory (relative to domain)
    var $images   = '';      # internal JS to update dTree images
    var $useLines = true;    # internal variable determining whether to render connector lines
    var $args     = array(); # args for each tree

    /**
     * Constructor
     */
    function __construct() {
        global $wgOut,$wgHooks,$wgParser,$wgScriptPath,$wgJsMimeType,
            $wgTreeViewMagic,$wgTreeViewImages,$wgTreeViewShowLines;

        # Add hooks
        $wgParser->setFunctionHook($wgTreeViewMagic,array($this,'expandTree'));
        $wgHooks['ParserAfterTidy'][] = array($this,'renderTree');

        # Update general tree paths and properties
        $this->baseDir  = dirname(__FILE__);
        //XXX: (KK) replace backslash with slash
        $this->baseDir  = str_replace("\\", "/", $this->baseDir);
        $this->baseUrl  = preg_replace('|^.+(?=[/\\\\]extensions)|',$wgScriptPath,$this->baseDir);

        $this->useLines = $wgTreeViewShowLines ? 'true' : 'false';
        $this->uniq     = uniqid($this->uniqname);

        # Convert image titles to file paths and store as JS to update dTree
        foreach ($wgTreeViewImages as $k => $v) {
            $title = Title::newFromText($v,NS_IMAGE);
            $image = Image::newFromTitle($title);
            $v = $image && $image->exists() ? $image->getURL() : $wgTreeViewImages[$k];
            $this->images .= "tree.icon['$k'] = '$v';";
        }

        // Tell the script manager, that we need prototype
        if (defined('SCM_VERSION')) {
            global $smgJSLibs;
            $smgJSLibs[] = 'prototype';
        }
        else // no script manager is in use
            $wgOut->addScript("<script type=\"$wgJsMimeType\" src=\"{$this->baseUrl}/prototype.js\"></script>\n");

        # Add link to output to load dtree.js script
        $wgOut->addScript("<script type=\"$wgJsMimeType\" src=\"{$this->baseUrl}/dtree.js\"><!-- Semantic Treeview ".SEMANTIC_TREEVIEW_VERSION." --></script>\n");
        $wgOut->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => "{$this->baseUrl}/dtree.css"
                ));
        }


    /**
     * Expand #tree parser-functions (reformats tree rows for matching later) and store args
     */
    public function expandTree(&$parser) {
        global $wgServer, $wgScriptPath;

        # Store args for this tree for later use
        $args = array();
        $text = "";
        foreach (func_get_args() as $arg) {
            if (!is_object($arg)) {
                if (preg_match('/^(\\w+?)\\s*=\\s*(.+)$/s',$arg,$m)) $args[$m[1]] = $m[2]; else $text = $arg;
            }
        }

        # Create a unique id for this tree or use id supplied in args and store args wrt id
        $this->id = isset($args['id']) ? $args['id'] : uniqid('');
        $this->args[$this->id] = $args;

        $this->class = isset($args['class']) ? $args['class'] : "dtree";
        $this->args[$this->id."class"] = $this->class;

        # Check if the treeGenerator passed some values encapsulated in the tree
        $lines = explode("\n",$text);
        $text= "";
        foreach ($lines as $line) {
            if ((strpos($line, "\x7f") !== false) && // do string check first before doing a regex eval (faster)
	            (preg_match('/\\x7f(.*?)\\x7f(\*+)(.*)$/', $line, $matches))) {

	            // check if initOnload is set, then skip the rest here but just replace the initOnload function
	            if (substr($matches[1], 0, 10) == "initOnload") {
	            	$this->args[$this->id."SmwUrl"] = "setupSmwUrl('".$wgServer.$wgScriptPath."');";
	            	$text .= $matches[2]."*".$matches[1]."\n";
	            	$matches[1] = substr($matches[1], 12); // remove initOnload('
	            	$matches[1] = substr($matches[1], 0, -2); // and ')'
	            }
	            parse_str($matches[1], $params);
	    	    if (isset($params['opento']))
	    	    	$this->args[$this->id."opento"] = urlencode($params['opento']);
	    	    if (isset($params['urlparams'])) $this->args[$this->id."urlparams"] = urldecode($params['urlparams']);
                if (isset($params['dynamic']) && $params['dynamic'] == 1) {
            	    $this->args[$this->id."SmwUrl"] = "setupSmwUrl('".$wgServer.$wgScriptPath."');";
            	    $addSmwData = "addSmwData(, '".((isset($params['property'])) ? $params['property'] : '')."',";
            	    $addSmwData .= (isset($params['category'])) ? "'".$params['category']."', " : "null, ";
            	    $addSmwData .= (isset($params['display'])) ? "'".$params['display']."', " : "null, ";
                    $addSmwData .= (isset($params['linkto'])) ? "'".$params['linkto']."', " : "null, ";
            	    $addSmwData .= (isset($params['start'])) ? "'".$params['start']."', " : "null, ";
					$addSmwData .= (isset($params['maxDepth'])) ? $params['maxDepth']."," : "null, ";
					$addSmwData .= (isset($params['condition'])) ? "'".urlencode($params['condition'])."', " : "null, ";
					$addSmwData .= (isset($params['urlparams'])) ? "'".$params['urlparams']."', " : "null, ";
					$addSmwData .= (isset($params['orderbyProperty'])) ? "'".$params['orderbyProperty']."', " : "null, ";
                    $addSmwData .= (isset($params['checkNode'])) ? "1, " : "null, ";
					$addSmwData .= (isset($params['useTsc'])) ? "1);" : "null);";
            	    $text.= $matches[2]."*".
                	    	$addSmwData."\n";
                    if (isset($params['refresh']) && $params['refresh'] == 1)
                        $this->args[$this->id."refresh"] = true;
                }
                $text.= $matches[2].$matches[3]."\n";
    	    }
	        else $text.= $line."\n";
	        array_shift($lines);
        }
        # Reformat tree rows for matching in ParserAfterStrip
        $text = preg_replace('/(?<=\\*)\\s*\\[\\[Image:(.+?)\\]\\]/',"{$this->uniq}3$1{$this->uniq}4",$text);
        //FIX KK: parse each row separately to prevent memory overflows in PHP regexp lib
        $rows = explode("\n", $text);
        $newtext = "";
        foreach($rows as $row) {
            preg_match('/^(\\*+)(.*?)$/m', $row, $m);
           	$newtext .= $this->formatRow($m, $row)."\n";
        }
        return $newtext;
   }


    /**
     * Reformat tree bullet structure recording row, depth and id in a format which is not altered by wiki-parsing
     * - format is: 1{uniq}-{id}-{depth}-{item}-{hc}-2{uniq}
     * - sequences of this format will be matched in ParserAfterTidy and converted into dTree JavaScript
     * - NOTE: we can't encode a unique row-id because if the same tree instranscluded twice a cached version
     *         may be used (even if parser-cache disabled) this also means that tree id's may be repeated
     */
    private function formatRow($m, $row) {
    	if (count($m) > 0) {
    		$m1 = strlen($m[1]) -1;
    		$m2 = $m[2];
    		$m3 = (substr($row, -1) == "\x7f") ? "1" : "\x7f0";
    	} else {
    		$m1 = 0;
    		$m2 = '';
    		$m3 = "\x7f";
    	}

        return "\x7f1{$this->uniq}\x7f{$this->id}\x7f{$m1}\x7f{$m2}{$m3}\x7f2{$this->uniq}";
    }

    /**
     * Called after parser has finished (ParserAfterTidy) so all transcluded parts can be assembled into final trees
     */
    public function renderTree(&$parser, &$text) {
        global $wgJsMimeType;
        $u = $this->uniq;

        # first, split text into single lines to have a smaller amount to do a regex matching with
        $subs = array();
        $matches = array();
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
           # Extract all the formatted tree rows in the page
           if (preg_match_all("/\x7f1$u\x7f(.+?)\x7f([0-9]+)\x7f({$u}3(.+?){$u}4)?(.*?)(?=\x7f[12]$u)/",$line,$lineMatch,PREG_SET_ORDER)) {
         	   foreach ($lineMatch as &$item)
                   $matches[]= $item;
           }
           # Determine which trees are sub trees
           # it's based on the fact that all sub-tree's have a minus preceding their row data
           if (preg_match_all("/\x7f\x7f1$u\x7f(.+?)\x7f/",$text,$lineMatch)) {
               foreach ($lineMatch as &$item)
                   $subs[]= $item;
           }
        }

        // if there are no subtrees found, initialize the array as below
        if (count($subs) == 0) $subs = array(1 => array());
        # Use extracted tree rows in the page and if any, replace with dTree JavaScript
        if (count($matches) > 0) {
            # PASS-1: build $rows array containing depth, and tree start/end information
            $rows   = array();
            $depths = array('' => 0); # depth of each tree root
            $rootId   = '';            # the id of the current root-tree (used as tree id in PASS2)
            $lastId = '';
            $lastDepth = 0;
            while ($match = array_shift($matches)) {
                list(,$id,$depth,,$icon,$item) = $match;
                $isLeaf = substr($item, -1);
                $item = substr($item, 0, -2);
                $start = false;
                if ($id != $lastId) {
                    if (!isset($depths[$id])) $depths[$id] = $depths[$lastId]+$lastDepth;
                    if ($start = $rootId != $id && !in_array($id,$subs[1])) $depths[$rootId = $id] = 0;
                }
                if ($item) $rows[] = array($rootId,$depth+$depths[$id],$icon,addslashes($item),$start, $isLeaf);
                $lastId    = $id;
                $lastDepth = $depth;
            }

            # PASS-2: build the JavaScript and replace into $text
            $parents = array(); # parent node for each depth
            $last    = -1;
            $nodes   = '';
            $node = 0;
            $openTo = '';
            while ($info = array_shift($rows)) {
                $node++;
                list($id,$depth,$icon,$item,$start, $isLeaf) = $info;
                $args = $this->args[$id];
                $class = $this->args[$id."class"];
                if (!isset($args['root'])) $args['root'] = ''; # tmp - need to handle rootless trees
                $smwUrl  = isset($this->args[$id."SmwUrl"]) ? $this->uniqname.$id.".".$this->args[$id."SmwUrl"] : NULL;
                $end     = (count($rows) == 0) || $rows[0][4]; // start flag of next node
                $refresh = isset($this->args[$id."refresh"]) ? "tree.config.refresh = true;" : '';
                $add     = isset($args['root']) ? "tree.add(0,-1,'".$args['root']."');" : '';

                # Append the dTree JS to add a node for this row
                if (strpos($item, "addSmwData(") !== false ) {
                    $node--;
                    $nodes .= "{$this->uniqname}$id.".trim(str_replace('addSmwData(,', 'addSmwData('.$node.',', stripcslashes($item)))."\n";
                }
                else if (strpos($item, "initOnload(") !== false ) {
                    $node--;
                    $nodes .= "{$this->uniqname}$id.".trim(str_replace('initOnload(', "initOnload($node,", stripcslashes($item)))."\n";
                } else {
                    if ($depth > $last) $parents[$depth] = $node - 1;
                    $parent = $parents[$depth];
                    $last   = $depth;
                    if (isset($this->args[$id."urlparams"]) &&
                    	preg_match('@(href=\\\"[^\\\]*)\\\@', $item, $paths)) {
                        $urlparams = (strpos($paths[1], '?') === false) ? '?' : '&';
                        $urlparams.= $this->args[$id."urlparams"];
                        $item = str_replace($paths[1], $paths[1].$urlparams, $item);
                    }
                    $nodes .= "{$this->uniqname}$id.add($node,$parent,'$item');\n";
                    if ($isLeaf) $nodes .= "{$this->uniqname}$id.setLeaf($node);\n";
                }

                # Last row of current root-tree, surround nodes dtree JS and div etc

                if ($end) {
                	// add opento command to node list if neccessarry
                	$openTo = (isset($this->args[$id."opento"]))
                   		      ? "{$this->uniqname}$id.openToName('".$this->args[$id."opento"]."');\n"
                   		      : "";

                    # Open all and close all links
                    $top = $bottom = $root = '';

                    foreach ($args as $arg => $pos)
                        if (($pos == 'top' || $pos == 'bottom' || $pos == 'root') && ($arg == 'open' || $arg == 'close'))
                            $$pos .= "<a href=\"javascript: {$this->uniqname}$id.{$arg}All();\">&nbsp;{$arg} all</a>&nbsp;";

                    if ($top) $top = "<p>&nbsp;$top</p>";
                    if ($bottom) $bottom = "<p>&nbsp;$bottom</p>";
                    if ($root) $add = "tree.add(0,-1,'$root');";

                    # Build tree JS
                    $tree = "
                        $top
                        <div class='Treeview' id='$id'>
                            <script id=\"navtreejs\" type=\"$wgJsMimeType\">
                                // TreeView{$this->version}
                                tree = new dTree('{$this->uniqname}$id', '$class');
                                for (i in tree.icon) tree.icon[i] = '{$this->baseUrl}/'+tree.icon[i];{$this->images}
                                tree.config.useLines = {$this->useLines};
                                $refresh
                                $add
                                {$this->uniqname}$id = tree;
                                $smwUrl
                                $nodes
                                document.getElementById('$id').innerHTML = {$this->uniqname}$id.toString();
                                ".((strlen($openTo) > 0) ? $openTo : "")."
                            </script>
                        </div>
                        $bottom
                        ";
                    foreach (array_keys($lines) as $i) {
                    	$newLine = preg_replace("/\x7f1$u\x7f$id\x7f.+?$/",$tree,$lines[$i],1); # replace first occurence of this trees root-id
                    	if (($newLine !== false) && ($lines[$i] != $newLine)) {
                    		$lines[$i]= $newLine;
                    		break;
                    	}
                    }
                    $nodes = '';
                    $node= 0;
                    $openTo = '';
                }
            }
        }
        foreach (array_keys($lines) as $i) {
        	if (preg_match("/\x7f1$u\x7f.+?$/",$lines[$i])) # Remove all unreplaced row information
        		unset($lines[$i]);
        }
 		$text = implode("\n", $lines);
        return true;
    }
}


/**
 * Called from $wgExtensionFunctions array when initialising extensions
 */
function wfSetupTreeView() {
    global $wgTreeView;
     // register tree generator
    new TreeGenerator();
    $wgTreeView = new SemanticTreeview();
    }


/**
 * Needed in MediaWiki >1.8.0 for magic word hooks to work properly
 */
function wfTreeViewLanguageGetMagic(&$magicWords,$langCode = 0) {
    global $wgTreeViewMagic;
    $magicWords[$wgTreeViewMagic] = array($langCode,$wgTreeViewMagic);
    $magicWords['generateTree']  = array( 0, 'generateTree' );
    return true;
    }