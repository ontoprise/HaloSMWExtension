<?php

/**
 * Exports uploaded files contained in one bundle.
 *
 */
class DeployUploadExporter {
    function __construct( $args, $filehandle = NULL ) {
        global $IP, $wgUseSharedUploads;
        $this->mAction = 'fetchLocal';
        $this->mBasePath = $IP;
        $this->mShared = false;
        $this->mSharedSupplement = false;
        $this->filehandle = $filehandle;
        
        if( isset( $args['help'] ) ) {
            $this->mAction = 'help';
        }
        
        if( isset( $args['base'] ) ) {
            $this->mBasePath = $args['base'];
        }
        
        if( isset( $args['local'] ) ) {
            $this->mAction = 'fetchLocal';
        }
        
        if( isset( $args['used'] ) ) {
            $this->mAction = 'fetchUsed';
        }
        
        if( isset( $args['shared'] ) ) {
            if( isset( $args['used'] ) ) {
                // Include shared-repo files in the used check
                $this->mShared = true;
            } else {
                // Grab all local *plus* used shared
                $this->mSharedSupplement = true;
            }
        }
    }
    
    function run() {
        $this->{$this->mAction}( $this->mShared );
        if( $this->mSharedSupplement ) {
            $this->fetchUsed( true );
        }
    }
    
   
    
    /**
     * Fetch a list of all or used images from a particular image source.
     * @param string $table
     * @param string $directory Base directory where files are located
     * @param bool $shared true to pass shared-dir settings to hash func
     */
function fetchUsed( $shared ) {
        $dbr = wfGetDB( DB_SLAVE );

        $image = $dbr->tableName( 'image' );
        $imagelinks = $dbr->tableName( 'imagelinks' );

        $partOfBundlePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeProperty($dfgLang->getLanguageString("df_partofbundle")));
        $partOfBundleID = smwfGetStore()->getSMWPageID($bundeID, NS_MAIN, "");

        $sql = "SELECT DISTINCT il_to, img_name
        FROM $imagelinks
        LEFT OUTER JOIN $image
        ON il_to=img_name";
        $result = $dbr->query( $sql );

        foreach( $result as $row ) {
            if ($this->isInBundle($row->il_to, $partOfBundlePropertyID, $partOfBundleID)) {
                $this->outputItem( $row->il_to, $shared );
            }
        }
        $dbr->freeResult( $result );
    }

    function isInBundle($il_to, $partOfBundlePropertyID, $partOfBundleID) {
        $smwids     = $this->db->tableName( 'smw_ids' );
        $smwrels     = $this->db->tableName( 'smw_rels2' );
        $page = $dbr->tableName( 'page' );
        $categorylinks = $dbr->tableName( 'categorylinks' );
        $dbr = wfGetDB( DB_SLAVE );
        $sql = "SELECT smw_id FROM $page JOIN $smwids ON smw_title = page_title AND smw_namespace = page_namespace JOIN $smwrels ON smw_id = s_id  WHERE  page_title = $il_to AND p_id = $partOfBundlePropertyID AND o_id = $partOfBundleID";
        $result = $dbr->query( $sql );
        if (count($result) > 0) {
            $dbr->freeResult( $result );
            return true;
        }
        $dbr->freeResult( $result );
        $sql = "SELECT smw_id FROM $page JOIN $categorylinks ON page_id = cl_from JOIN $smwids ON smw_title = cl_from AND smw_namespace = NS_CATEGORY JOIN $smwrels ON smw_id = s_id  WHERE  page_title = $il_to AND p_id = $partOfBundlePropertyID AND o_id = $partOfBundleID";
        $result = $dbr->query( $sql );
        if (count($result) > 0) {
            $dbr->freeResult( $result );
            return true;
        }
        $dbr->freeResult( $result );
        return false;

    }
    
    function fetchLocal( $shared ) {
        $dbr = wfGetDB( DB_SLAVE );
        $result = $dbr->select( 'image',
            array( 'img_name' ),
            '',
            __METHOD__ );
        
        foreach( $result as $row ) {
            $this->outputItem( $row->img_name, $shared );
        }
        $dbr->freeResult( $result );
    }
    
    function outputItem( $name, $shared ) {
        $file = wfFindFile( $name );
        if( $file && $this->filterItem( $file, $shared ) ) {
            $filename = $file->getFullPath();
            $rel = wfRelativePath( $filename, $this->mBasePath );
            if (!is_null($this->filehandle)) {
            	fwrite($this->filehandle, "\t\t<file loc=\"$rel\"/>\n"); 
            } else {
                echo "$rel\n";
            }
        } else {
            wfDebug( __METHOD__ . ": base file? $name\n" );
        }
    }
    
    function filterItem( $file, $shared ) {
        return $shared || $file->isLocal();
    }
}

