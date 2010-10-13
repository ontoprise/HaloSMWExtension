<?php
/**
 * @author Ning Hu
 */

if (!defined('MEDIAWIKI')) die();

class SCRest {
	static $restApiPatterns = array();
	static function registerRestApis() {
		global $scgRestApiPatterns;
		if(!is_array($scgRestApiPatterns)) return;
		
		SCRest::$restApiPatterns = array();
		
		foreach($scgRestApiPatterns as $pattern => $func) {
			$pattern = trim($pattern, '/');
			$p_array = explode('/', $pattern);
			$groups = array();
			foreach($p_array as $key => $p) {
				$start = strpos($p, '{');
				$end = strrpos($p, '}');
				if($start !== FALSE && $end !== FALSE && $start == 0 && $end == (strlen($p) - 1)) {
					$groups[] = substr($p, 1, strlen($p) - 2);
					$p_array[$key] = '([^\/]+)';
				}
			}
			$pattern = '/' . implode('\/', $p_array) . '/i';
			SCRest::$restApiPatterns[$pattern] = array('groups' => $groups, 'func' => $func);
		}
	}

	static function callRestApi( $query ) {
		while(strpos($query,'/')===0)
			$query = substr($query, 1);
		while(strrpos($query,'/')===strlen($query)-1)
			$query = substr($query, 0, strlen($query) - 1);

		foreach(SCRest::$restApiPatterns as $pattern => $value) {
			if(preg_match($pattern, $query, $matches)>0) {
				if($matches[0] == $query) {
					$match = array();
					$func = $value['func'];
					foreach($value['groups'] as $key => $name) {
						$match[$name] = $matches[$key + 1];
					}
					$class = NULL;
					$method = NULL;
					if (is_array($func)) {
						if (count($func) == 2) {
							$class = $func[0];
							$method = $func[1];
						} else if (count($func) == 1) {
							$method = $func[0];
						} else {
							return '{
								success : false, 
								msg : "Invalid REST api function."
							}';
						}
					} else if (is_string($func)) {
						$method = $func;
					} else {
						return '{
							success : false, 
							msg : "Invalid REST api function."
						}';
					}
						
					if ( isset( $class ) ) {
						$callback = array( $class, $method );
					} elseif ( false !== ( $pos = strpos( $func, '::' ) ) ) {
						$callback = array( substr( $func, 0, $pos ), substr( $func, $pos + 2 ) );
					} else {
						$callback = $func;
					}

					// Run autoloader (workaround for call_user_func_array bug)
					is_callable( $callback );

					/* Call the hook. */
					wfProfileIn( $func );
					$retval = call_user_func( $callback, $match );
					wfProfileOut( $func );

					return '{
						success : true, 
						msg : "' . str_replace("\r", '', str_replace("\n", '\n', str_replace('"', '\"', $retval))) . '"
					}';
				}
			}
		}
		return '{
			success : false, 
			msg : "Invalid REST api pattern."
		}';
	}
}
