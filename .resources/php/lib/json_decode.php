<?php

/**
 * Implementation of function json_decode on PHP
 *
 * @author Alexander Muzychenko
 * @link https://github.com/alexmuz/php-json
 * @see http://php.net/json_decode
 * @license GNU Lesser General Public License (LGPL) http://www.gnu.org/copyleft/lesser.html
 */
if (!function_exists('json_decode')) {

	function json_decode($json, $assoc = false)
	{
		//mb_internal_encoding("UTF-8"); CRASH
	    $i = 0;
        $n = strlen($json);
        try {
            $result = json_decode_value($json, $i, $assoc);
            while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
            if ($i < $n) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            return null;
        }		
	}
	
	function json_decode_value($json, &$i, $assoc = false)
	{
        $n = strlen($json);
        while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
        switch ($json[$i]) {
        	// object
            case '{':
                $i++;
                $result = $assoc ? array() : new stdClass();
	            while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
	            if ($json[$i] === '}') {
	                $i++;
	                return $result;
	            }
	            while ($i < $n) {
	                $key = json_decode_string($json, $i);
	                while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
	                if ($json[$i++] != ':') {
	                    throw new Exception("Expected ':' on ".($i - 1));
	                }
	                if ($assoc) {
	                    $result[$key] = json_decode_value($json, $i, $assoc);
	                } else {
	                    $result->$key = json_decode_value($json, $i, $assoc);
	                }
	                while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
	                if ($json[$i] === '}') {
	                    $i++;
	                    return $result;
	                }
	                if ($json[$i++] != ',') {
	                    throw new Exception("Expected ',' on ".($i - 1));
	                }
	                while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
	            }
	            throw new Exception("Syntax error");
            // array
            case '[':
                $i++;
                $result = array();
	            while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
	            if ($json[$i] === ']') {
	                $i++;
	                return array();
	            }
	            while ($i < $n) {
	                $result[] = json_decode_value($json, $i, $assoc);
	                while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
	                if ($json[$i] === ']') {
	                    $i++;
	                    return $result;
	                }
	                if ($json[$i++] != ',') {
	                    throw new Exception("Expected ',' on ".($i - 1));
	                }
	                while ($i < $n && $json[$i] && $json[$i] <= ' ') $i++;
	            }            	
	            throw new Exception("Syntax error");
            // string
            case '"':
                return json_decode_string($json, $i);
            // number
            case '-':
                return json_decode_number($json, $i);
            // true
            case 't':
                 if ($i + 3 < $n && substr($json, $i, 4) === 'true') {
                     $i += 4;
                     return true;
                 }
            // false
            case 'f':
                 if ($i + 4 < $n && substr($json, $i, 5) === 'false') {
                     $i += 5;
                     return false;
                 }
            // null
            case 'n':
                 if ($i + 3 < $n && substr($json, $i, 4) === 'null') {
                     $i += 4;
                     return null;
                 }            
            default:
            	// number
                if ($json[$i] >= '0' && $json[$i] <= '9') {
                    return json_decode_number($json, $i);
                } else {
                    throw new Exception("Syntax error");
                };
        }
	}
	
	function json_decode_string($json, &$i)
	{
        $result = '';
        $escape = array('"' => '"', '\\' => '\\', '/' => '/', 'b' => "\b", 'f' => "\f", 'n' => "\n", 'r' => "\r", 't' => "\t");
        $n = strlen($json);
        if ($json[$i] === '"') {
            while (++$i < $n) {
                if ($json[$i] === '"') {
                    $i++;
                    return $result;
                } elseif ($json[$i] === '\\') {
                    $i++;
                    if ($json[$i] === 'u') {
                        $code = "&#".hexdec(substr($json, $i + 1, 4)).";";
                        $convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
                        //$result .= mb_decode_numericentity($code, $convmap, 'UTF-8'); CRASH guess no unicode for me ¯\_(ツ)_/¯
                        $i += 4;
                    } elseif (isset($escape[$json[$i]])) {
                        $result .= $escape[$json[$i]];
                    } else {
                        break;
                    }
                } else {
                    $result .= $json[$i];
                }
            }
        }
     	throw new Exception("Syntax error"); 		
	}
	
	function json_decode_number($json, &$i)
	{
        $result = '';
        if ($json[$i] === '-') {
            $result = '-';
            $i++;
        }
        $n = strlen($json);
        while ($i < $n && $json[$i] >= '0' && $json[$i] <= '9') {
            $result .= $json[$i++];
        }
        
        if ($i < $n && $json[$i] === '.') {
            $result .= '.';
            $i++;
            while ($i < $n && $json[$i] >= '0' && $json[$i] <= '9') {
                $result .= $json[$i++];
            }
        }
        if ($i < $n && ($json[$i] === 'e' || $json[$i] === 'E')) {
            $result .= $json[$i];
            $i++;
            if ($json[$i] === '-' || $json[$i] === '+') {
                $result .= $json[$i++];
            }
            while ($i < $n && $json[$i] >= '0' && $json[$i] <= '9') {
                $result .= $json[$i++];
            }
        }
         
        return (0 + $result);		
	}
}
