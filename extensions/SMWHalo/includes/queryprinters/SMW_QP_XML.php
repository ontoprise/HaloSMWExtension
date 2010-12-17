<?php
/**
 * @file
 * @ingroup SMWHaloQueryPrinters
 *
 * Print query results in tables.
 * @author Kai
 */

/**
 * Implementation of SMW's printer for SPARQL XML results.
 *
 *  see also: http://www.w3.org/TR/rdf-sparql-XMLres/
 *
 * @note AUTOLOADED
 */
class SMWXMLResultPrinter extends SMWResultPrinter {

	public function getMimeType($res) {
		return 'text/xml';
	}

	protected function getResultText($res, $outputmode) {
		$variables = array();
		$result = $this->printHeader();
		$result .= $this->printVariables($res->getPrintRequests(), $variables);
		$result .= $this->printResults($res, $variables);
		$result .= $this->printFooter();
		return $result;
	}

	private function printVariables($printRequests, & $variables) {

		$synthVar = "_var";
		$i = 0;
		$result = "\t<head>\n";
		foreach ($printRequests as $pr) {
			$data = $pr->getData();
			if ($data instanceof Title) {
				$result .= "\t\t<variable name=\"".$data->getText()."\"/>\n";
				$variables[] = $data->getText();
			} else if ($data instanceof SMWPropertyValue) {
				$result .= "\t\t<variable name=\"".$data->getDBkey()."\"/>\n";
				$variables[] = $data->getDBkey();
			} else {
				$result .= "\t\t<variable name=\"".$synthVar.$i."\"/>\n";
				$variables[] = $synthVar.$i;
			}
			$i++;
		}
		$result .= "\t</head>\n";
		return $result;
	}

	private function printResults($res, $variables) {
		$result = "\t<results>\n";
		while ( $row = $res->getNext() ) {
			$result .= "\t\t<result>\n";

			$i = 0;
			foreach ($row as $field) {

				$content = $field->getContent();
				if (count($content) === 0) continue; // do not serialize null bindings

				$result .= "\t\t\t<binding name=\"$variables[$i]\">";

				while ( ($object = $field->getNextObject()) !== false ) {
					if ($object->getTypeID() == '_wpg') {  // print whole title with prefix in this case

						$uri = TSNamespaces::getInstance()->getFullURI($object->getTitle());
						$uri_enc = htmlspecialchars($uri);
						$result .= "<uri>$uri_enc</uri>";
					} else {
						switch($object->getTypeID()) {
							case '_geo':
								// TODO: add all datatypes which have more than
								// one DBkey
								$text = implode(",",$object->getDBkeys());
								break;
							
							default:
								$text = array_shift($object->getDBkeys());
								$unit = $object->getUnit();
								if (!is_null($unit)) $text .= " $unit";
								
								break;
						}
                        $text_enc = htmlspecialchars($text);
						$datatype = WikiTypeToXSD::getXSDType($object->getTypeID());
						$datatype = str_replace("xsd:", "http://www.w3.org/2001/XMLSchema#", $datatype);
						$datatype = str_replace("tsctype:", "http://www.ontoprise.de/smwplus/tsc/unittype#", $datatype);
						$result .= "<literal datatype=\"$datatype\">$text_enc</literal>";
					}
				}

				$result .= "</binding>\n";
					
				$i++;
			}
			$result .= "\t\t</result>\n";
		}
		$result .= "\t</results>\n";
		return $result;
	}

	private function printHeader() {
		return "<?xml version=\"1.0\"?>\n<sparql xmlns=\"http://www.w3.org/2005/sparql-results#\">\n";
	}

	private function printFooter() {
		return '</sparql>';
	}
}
