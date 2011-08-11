<?php

// ----------------------------------------------------------------------------------
// Class: RDFUtil
// ----------------------------------------------------------------------------------

/**
* Useful utility methods.
* Static class.
*
* <BR><BR>History:<UL>
* <LI>12-06-2004                 : improved namespace handling in function
*                                  writeAsHTMLTable() added (tobias.gauss@web.de)</LI>
* <LI>09-10-2004				 : added support for OWL and infered statements</LI>
* <LI>11-18-2003				 : Function writeAsHtmlTable() htmlspecialchars & nl2br
*								   for displaying literals added.</LI>
* <LI>04-23-2003				 : Chunk_split() removed from writeHTMLTable</LI>
* <LI>12-04-2002				 : Added support for "rdf:datatype" in writeHTMLTable</LI>
* <LI>10-03-2002                : Green coloring for RDF_SCHEMA nodes added to writeHTMLTable</LI>
* <LI>09-10-2002                : First version of this class.</LI>
*
* </UL>
* @version  V0.9.1
* @author Chris Bizer <chris@bizer.de>, Daniel Westphal <dawe@gmx.de>
*
* @package utility
* @access	public
*/
class RDFUtil extends Object {

	/**
	* Extracts the namespace prefix out of a URI.
	*
	* @param	String	$uri
	* @return	string
	* @access	public
	*/
	function guessNamespace($uri) {
		$l = RDFUtil::getNamespaceEnd($uri);
		return $l > 1 ? substr($uri ,0, $l) : "";
	}

	/**
	* Delivers the name out of the URI (without the namespace prefix).
	*
	* @param	String	$uri
	* @return	string
	* @access	public
	*/
	function guessName($uri) {
		return substr($uri,RDFUtil::getNamespaceEnd($uri));
	}

	/**
	* Extracts the namespace prefix out of the URI of a Resource.
	*
	* @param	Object Resource	$resource
	* @return	string
	* @access	public
	*/
	function getNamespace($resource) {
		return RDFUtil::guessNamespace($resource->getURI());
	}

	/**
	* Delivers the Localname (without the namespace prefix) out of the URI of a Resource.
	*
	* @param	Object Resource	$resource
	* @return	string
	* @access	public
	*/
	function getLocalName($resource) {
		return RDFUtil::guessName($resource->getURI());
	}

	/**
	* Position of the namespace end
	* Method looks for # : and /
	* @param	String	$uri
	* @access	private
	*/
	function getNamespaceEnd($uri) {
		$l = strlen($uri)-1;
		do {
			$c = substr($uri, $l, 1);
			if($c == '#' || $c == ':' || $c == '/')
			break;
			$l--;
		} while ($l >= 0);
		$l++;
		return $l;
	}

	/**
	* Tests if the URI of a resource belongs to the RDF syntax/model namespace.
	*
	* @param	Object Resource	$resource
	* @return	boolean
	* @access	public
	*/
	function isRDF($resource) {
		return ($resource != NULL && RDFUtil::getNamespace($resource) == RDF_NAMESPACE_URI);
	}

	/**
	* Escapes < > and &
	*
	* @param	String	$textValue
	* @return	String
	* @access	public
	*/
	function escapeValue($textValue) {

		$textValue = str_replace('<', '&lt;', $textValue);
		$textValue = str_replace('>', '&gt;', $textValue);
		$textValue = str_replace('&', '&amp;', $textValue);

		return $textValue;
	}

	/**
	* Converts an ordinal RDF resource to an integer.
	* e.g. Resource(RDF:_1) => 1
	*
	* @param	object Resource	$resource
	* @return	Integer
	* @access	public
	*/
	function getOrd($resource)  {
		if($resource == NULL || !is_a($resource, 'Resource') || !RDFUtil::isRDF($resource))
		return -1;
		$name = RDFUtil::getLocalName($resource);
		echo substr($name, 1).' '.RDFUtil::getLocalName($resource);
		$n = substr($name, 1);
		//noch rein : chekcen ob $n Nummer ist !!!!!!!!!!!!!!!!!!!!!!if($n)
		return $n;
		return -1;
	}

	/**
	* Creates ordinal RDF resource out of an integer.
	*
	* @param	Integer	$num
	* @return	object Resource
	* @access	public
	*/
	function createOrd($num)  {
		return new Resource(RDF_NAMESPACE_URI . '_' . $num);
	}

	/**
	* Prints a MemModel as HTML table.
	* You can change the colors in the configuration file.
	*
	* @param	object MemModel 	&$model
	* @access	public
	*/
	function writeHTMLTable(&$model)  {
		$nms = $model->getParsedNamespaces();
		$names = '';
		$pre = '';


		echo '<table border="1" cellpadding="3" cellspacing="0" width="100%">' . LINEFEED;
		echo INDENTATION . '<tr bgcolor="' . HTML_TABLE_HEADER_COLOR . '">' . LINEFEED . INDENTATION . INDENTATION . '<td td width="68%" colspan="3">';
		echo '<p><b>Base URI:</b> ' . $model->getBaseURI() . '</p></td>' . LINEFEED;
		echo INDENTATION . INDENTATION . '<td width="32%"><p><b>Size:</b> ' . $model->size() . '</p></td>' . LINEFEED . INDENTATION . '</tr>';

		echo '<tr><td><b>Prefix:</b>'.'<br/></td><td colspan="3"><b>Namespace:</b>'.'<br/></td></tr>';
		$i=0;
		if($nms != false){
			foreach($nms as $namespace => $prefix){
				if($i==0){
					$col = HTML_TABLE_NS_ROW_COLOR0;
				}else{
					$col = HTML_TABLE_NS_ROW_COLOR1;
				}
				echo '<tr bgcolor="'.$col.'"><td>'.$prefix.'</td><td colspan="3">'.$namespace.'</td></tr>';
				$i++;
				$i%=2;
			}
		}else{
			echo '<tr><td>-</td><td colspan="3">-</td></tr>';
		}




		echo INDENTATION . '<tr bgcolor="' . HTML_TABLE_HEADER_COLOR . '">' . LINEFEED . INDENTATION . INDENTATION . '<td width="4%"><p align=center><b>No.</b></p></td>' . LINEFEED . INDENTATION . INDENTATION . '<td width="32%"><p><b>Subject</b></p></td>' . LINEFEED . INDENTATION . INDENTATION . '<td width="32%"><p><b>Predicate</b></p></td>' . LINEFEED . INDENTATION . INDENTATION . '<td width="32%"><p><b>Object</b></p></td>' . LINEFEED . INDENTATION . '</tr>' . LINEFEED;

		$i = 1;
		foreach($model->triples as $key => $statement) {
			$infered='';
			if (is_a($statement,'InfStatement')) $infered='<small>(infered)</small>';
			echo INDENTATION . '<tr valign="top">' . LINEFEED . INDENTATION . INDENTATION . '<td><p align=center>' . $i . '.<BR>'.$infered.'</p></td>' . LINEFEED;
			// subject
			echo INDENTATION . INDENTATION . '<td bgcolor="';
			echo RDFUtil::chooseColor($statement->getSubject());
			echo '">';
			echo '<p>' .  RDFUtil::getNodeTypeName($statement->getSubject());
			if(is_a($statement->subj,'Resource')){
				$ns = $statement->subj->getNamespace();
				if(isset($nms[$ns])){
					echo $nms[$ns].':'.RDFUtil::getLocalName($statement->subj);
				}else{
					echo $statement->subj->getLabel();
				}
			}
			echo '</p></td>' .  LINEFEED;
			// predicate
			echo INDENTATION . INDENTATION . '<td bgcolor="';
			echo RDFUtil::chooseColor($statement->getPredicate());
			echo '">';
			echo '<p>' . RDFUtil::getNodeTypeName($statement->getPredicate());
			if(is_a($statement->pred,'Resource')){
				$ns = $statement->pred->getNamespace();
				if(isset($nms[$ns])){
					echo $nms[$ns].':'.RDFUtil::getLocalName($statement->pred);
				}else{
					echo $statement->pred->getLabel();
				}
			}
			echo '</p></td>' .  LINEFEED;
			// object
			echo INDENTATION . INDENTATION . '<td bgcolor="';
			echo RDFUtil::chooseColor($statement->getObject());
			echo '">';
			echo '<p>';
			if (is_a($statement->getObject(), 'Literal')) {
				if ($statement->obj->getLanguage() != null) {
					$lang = ' <b>(xml:lang="' . $statement->obj->getLanguage() . '") </b> ';
				} ELSE $lang = '';
				if ($statement->obj->getDatatype() != null) {
					$dtype = ' <b>(rdf:datatype="' . $statement->obj->getDatatype() . '") </b> ';
				} ELSE $dtype = '';
			} else {
				$lang = '';
				$dtype = '';
			}
			$label = $statement->obj->getLabel();
			if(is_a($statement->obj,'Resource')){
				$ns = $statement->obj->getNamespace();
				if(isset($nms[$ns])){
					$label = $nms[$ns].':'.RDFUtil::getLocalName($statement->obj);
				}else{
					$label = $statement->obj->getLabel();
				}
			}

			echo  RDFUtil::getNodeTypeName($statement->getObject())
			.nl2br(htmlspecialchars($label)) . $lang . $dtype;

			echo '</p></td>' . LINEFEED;
			echo INDENTATION . '</tr>' . LINEFEED;
			$i++;
		}
		echo '</table>' . LINEFEED;
	}

	/**
	* Chooses a node color.
	* Used by RDFUtil::writeHTMLTable()
	*
	* @param	object Node	$node
	* @return	object Resource
	* @access	private
	*/
	function chooseColor($node)  {
		if (is_a($node, 'BlankNode'))
		return HTML_TABLE_BNODE_COLOR;
		elseif (is_a($node, 'Literal'))
		return HTML_TABLE_LITERAL_COLOR;
		else {
			if (RDFUtil::getNamespace($node) == RDF_NAMESPACE_URI ||
			RDFUtil::getNamespace($node) == RDF_SCHEMA_URI ||
			RDFUtil::getNamespace($node) == OWL_URI
			)

			return HTML_TABLE_RDF_NS_COLOR;
		}
		return HTML_TABLE_RESOURCE_COLOR;

	}

	/**
	* Get Node Type.
	* Used by RDFUtil::writeHTMLTable()
	*
	* @param	object Node	$node
	* @return	object Resource
	* @access	private
	*/
	function getNodeTypeName($node)  {
		if (is_a($node, "BlankNode"))
		return 'Blank Node: ';
		elseif (is_a($node, 'Literal'))
		return 'Literal: ';
		else {
			if (RDFUtil::getNamespace($node) == RDF_NAMESPACE_URI ||
			RDFUtil::getNamespace($node) == RDF_SCHEMA_URI ||
			RDFUtil::getNamespace($node) == OWL_URI)
			return 'RDF Node: ';
		}
		return 'Resource: ';

	}

} // end: RDfUtil

?>