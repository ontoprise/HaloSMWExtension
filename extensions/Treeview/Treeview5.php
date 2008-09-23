<?php
/**
* MediaWiki Treeview Extension
* - See http://www.mediawiki.org/wiki/Extension:Tree_view for installation and usage details
* - Licenced under LGPL (http://www.gnu.org/copyleft/lesser.html)
* - Author:  http://www.organicdesign.co.nz/nad
* - Started: (Version5) 2007-10-24
*/
 
if (!defined('MEDIAWIKI')) die('Not an entry point.');
 
define('TREEVIEW5_VERSION','5.1.10, 2008-04-15');

require_once('TreeGenerator.php');

# Set any unset images to default titles
if (!isset($wgTreeViewImages) || !is_array($wgTreeViewImages)) $wgTreeViewImages = array();
 
$wgTreeView5Magic              = "tree"; # the parser-function name for trees
$wgTreeViewShowLines           = false;  # whether to render the dotted lines joining nodes
$wgExtensionFunctions[]        = 'wfSetupTreeView5';
$wgHooks['LanguageGetMagic'][] = 'wfTreeView5LanguageGetMagic';
 
$wgExtensionCredits['parserhook'][] = array(
    'name'        => 'Treeview5',
    'author'      => '[http://www.organicdesign.co.nz/nad Nad], [http://www.organicdesign.co.nz/User:Sven Sven]',
    'url'         => 'http://www.mediawiki.org/wiki/Extension:Treeview',
    'description' => 'Extends the wiki parser to allow bullet and numbered lists to work with recursion and optionally
                        allows these to be rendered as collapsible trees using the free
                        [http://www.destroydrop.com/javascripts/tree dTree] JavaScript tree menu.',
    'version'     => TREEVIEW5_VERSION
    );
 
class TreeView5 {
 
    var $version  = TREEVIEW5_VERSION;
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
            $wgTreeView5Magic,$wgTreeViewImages,$wgTreeViewShowLines;
 
        # Add hooks
        $wgParser->setFunctionHook($wgTreeView5Magic,array($this,'expandTree'));
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
 
        # Add link to output to load dtree.js script
        $wgOut->addScript("<script type=\"$wgJsMimeType\" src=\"{$this->baseUrl}/dtree.js\"><!-- Treeview5 --></script>\n");
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
 
        # Store args for this tree for later use
        $args = array();
        foreach (func_get_args() as $arg) if (!is_object($arg)) {
            if (preg_match('/^(\\w+?)\\s*=\\s*(.+)$/s',$arg,$m)) $args[$m[1]] = $m[2]; else $text = $arg;
            }
 
        # Create a unique id for this tree or use id supplied in args and store args wrt id
        $this->id = isset($args['id']) ? $args['id'] : uniqid('');
        $this->args[$this->id] = $args;
        
        $this->class = isset($args['class']) ? $args['class'] : "dtree";
 
        # Reformat tree rows for matching in ParserAfterStrip
        $text = preg_replace('/(?<=\\*)\\s*\\[\\[Image:(.+?)\\]\\]/',"{$this->uniq}3$1{$this->uniq}4",$text);
        //FIX KK: parse each row separately to prevent memory overflows in PHP regexp lib
        $rows = explode("\n", $text);
        $newtext = "";
        foreach($rows as $row) {
            preg_match('/^(\\*+)(.*?)$/m', $row, $m);
            $newtext .= "\x7f1{$this->uniq}\x7f{$this->id}\x7f".(strlen($m[1])-1)."\x7f$m[2]\x7f2{$this->uniq}\n";
        }
        return $newtext;
   }
 
 
    /**
     * Reformat tree bullet structure recording row, depth and id in a format which is not altered by wiki-parsing
     * - format is: 1{uniq}-{id}-{depth}-{item}-2{uniq}
     * - sequences of this format will be matched in ParserAfterTidy and converted into dTree JavaScript
     * - NOTE: we can't encode a unique row-id because if the same tree instranscluded twice a cached version
     *         may be used (even if parser-cache disabled) this also means that tree id's may be repeated
     */
    private function formatRow($m) {
        return "\x7f1{$this->uniq}\x7f{$this->id}\x7f".(strlen($m[1])-1)."\x7f$m[2]\x7f2{$this->uniq}";
        }
 
 
    /**
     * Called after parser has finished (ParserAfterTidy) so all transcluded parts can be assembled into final trees
     */
    public function renderTree(&$parser, &$text) {
        global $wgJsMimeType;
        $u = $this->uniq;
 
        # Determine which trees are sub trees
        # - there should be a more robust way to do this,
        #   it's just based on the fact that all sub-tree's have a minus preceding their row data
        if (!preg_match_all("/\x7f\x7f1$u\x7f(.+?)\x7f/",$text,$subs)) $subs = array(1 => array());
 
        # Extract all the formatted tree rows in the page and if any, replace with dTree JavaScript
        if (preg_match_all("/\x7f1$u\x7f(.+?)\x7f([0-9]+)\x7f({$u}3(.+?){$u}4)?(.*?)(?=\x7f[12]$u)/",$text,$matches,PREG_SET_ORDER)) {
            # PASS-1: build $rows array containing depth, and tree start/end information
            $rows   = array();
            $depths = array('' => 0); # depth of each tree root
            $rootId   = '';            # the id of the current root-tree (used as tree id in PASS2)
            $lastId = '';
            $lastDepth = 0;
            foreach ($matches as $match) {
                list(,$id,$depth,,$icon,$item) = $match;
                $start = false;
                if ($id != $lastId) {
                    if (!isset($depths[$id])) $depths[$id] = $depths[$lastId]+$lastDepth;
                    if ($start = $rootId != $id && !in_array($id,$subs[1])) $depths[$rootId = $id] = 0;
                }
                if ($item) $rows[] = array($rootId,$depth+$depths[$id],$icon,addslashes($item),$start);
                $lastId    = $id;
                $lastDepth = $depth;
            }
 
            # PASS-2: build the JavaScript and replace into $text
            $parents = array(); # parent node for each depth
            $last    = -1;
            $nodes   = '';
            foreach ($rows as $node => $info) {
                $node++;
                list($id,$depth,$icon,$item,$start) = $info;
                $args = $this->args[$id];
                if (!isset($args['root'])) $args['root'] = ''; # tmp - need to handle rootless trees
                $end  = $node == count($rows) || $rows[$node][4];
                $add  = isset($args['root']) ? "tree.add(0,-1,'".$args['root']."');" : '';
 
                # Append the dTree JS to add a node for this row
                if ($depth > $last) $parents[$depth] = $node-1;
                $parent = $parents[$depth];
                $last   = $depth;
                $nodes .= "{$this->uniqname}$id.add($node,$parent,'$item');\n";
 
                # Last row of current root-tree, surround nodes dtree JS and div etc
                if ($end) {
 
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
                        <div class='Treeview5' id='$id'>
                            <script type=\"$wgJsMimeType\">
                                // TreeView{$this->version}
                                tree = new dTree('{$this->uniqname}$id', '{$this->class}');
                                for (i in tree.icon) tree.icon[i] = '{$this->baseUrl}/'+tree.icon[i];{$this->images}
                                tree.config.useLines = {$this->useLines};
                                $add
                                {$this->uniqname}$id = tree;
                                $nodes
                                document.getElementById('$id').innerHTML = {$this->uniqname}$id.toString();
                            </script>
                        </div>
                        $bottom
                        ";
                    $text  = preg_replace("/\x7f1$u\x7f$id\x7f.+?$/m",$tree,$text,1); # replace first occurence of this trees root-id
                    $nodes = '';
                }
            }
        }
 
        $text = preg_replace("/\x7f1$u\x7f.+?[\\r\\n]+/m",'',$text); # Remove all unreplaced row information
        return true;
    }
 
}
 
 
/**
 * Called from $wgExtensionFunctions array when initialising extensions
 */
function wfSetupTreeView5() {
    global $wgTreeView5;
     // register tree generator
    new TreeGenerator();
    $wgTreeView5 = new TreeView5();
    }
 
 
/**
 * Needed in MediaWiki >1.8.0 for magic word hooks to work properly
 */
function wfTreeView5LanguageGetMagic(&$magicWords,$langCode = 0) {
    global $wgTreeView5Magic;
    $magicWords[$wgTreeView5Magic] = array($langCode,$wgTreeView5Magic);
    $magicWords['generateTree']  = array( 0, 'generateTree' );
    return true;
    }
  