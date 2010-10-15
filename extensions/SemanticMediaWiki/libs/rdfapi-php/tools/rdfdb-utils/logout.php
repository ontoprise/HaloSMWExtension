<?php
// ----------------------------------------------------------------------------------
// RDFDBUtils : Logout
// ----------------------------------------------------------------------------------

/** 
 * Destroys all session data
 * <br/></br>
 * History:
 * <ul>
 * <li>06 September 2004 - First version</li>
 * </ul>
 * 
 * @version  V0.1
 * @author   Gunnar AAstrand Grimnes <ggrimnes@csd.abdn.ac.uk>
 *
 **/

session_start();
session_unset();
session_destroy();
//session_write_close();
header("Location: index.php");
?>