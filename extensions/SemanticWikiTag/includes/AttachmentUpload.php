<?php
	if (!defined('MEDIAWIKI'))                     die('Not an entry point.');
	
	global $wgFileBlacklist, $IP;
	
    $fileName = $_FILES['file']['name'];
    $tmpDir = "$IP/images/tmp/";
    if ( !is_dir( $tmpDir ) ) {
    	wfMkdirParents( $tmpDir );
    }
    $fileFullPath = $tmpDir.$fileName;
    
    // get file ext
    $fileNameArray = split("\.", $fileName);
    $ext = $fileNameArray[count($fileNameArray)-1];

    // check file extension name
    if (count ($fileNameArray) > 1) {
        if (in_array($ext, $wgFileBlacklist)) {
            echo 'false';
            return;
        }
    }

    move_uploaded_file($_FILES['file']['tmp_name'], $fileFullPath);
    $mFileProps = File::getPropsFromPath($fileFullPath, $ext );

    $local = wfLocalFile($fileName);
    $status = $local->upload($fileFullPath, 'Mail Attachment','Mail Attachment', File::DELETE_SOURCE, $mFileProps );
    echo 'success';
?>