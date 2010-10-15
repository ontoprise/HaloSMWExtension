<?php

// ----------------------------------------------------------------------------------
// Class: Node
// ----------------------------------------------------------------------------------

/**
 * An abstract RDF node. 
 * Can either be resource, literal or blank node. 
 * Node is used in some comparisons like is_a($obj, "Node"), 
 * meaning is $obj a resource, blank node or literal.
 * 
 * <BR><BR>History:<UL>
 * <li>09-10-2002                : First version of this class.</li>
 * </UL>
 * 
 * @version  V0.9.1
 * @author Chris Bizer <chris@bizer.de>
 *
 * @todo nothing
 * @package model
 * @abstract
 *
 */
 class Node extends Object {
 } // end:RDFNode


?>