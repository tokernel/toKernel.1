<?php
/**
 * toKernel- Universal PHP Framework.
 * Class library for data filtering
 * 
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   framework
 * @package    toKernel
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2012 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.1.4
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * filter_lib class
 * 
 * Filter global input data.  
 * Validates for many data types.
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */ 
class filter_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access private
 */ 
 private $lib;
 
/**
 * This globals will be removed, because they 
 * are deprecated and we not use them.
 * 
 * @access private
 * @var array
 */	
 private $globals_to_remove = array(
	'HTTP_ENV_VARS',
	'HTTP_POST_VARS',
	'HTTP_GET_VARS',
	'HTTP_COOKIE_VARS',
	'HTTP_SERVER_VARS',
	'HTTP_POST_FILES',
 );

/**
 * Expressions which will be replaced 
 * from any data in clean_xss function.
 * 
 * @access private
 * @var array
 */ 
 private $expressions_to_replaced = array(
	'document.cookie'	=> '',
	'document.write'	=> '',
	'.parentNode'		=> '',
	'.innerHTML'		=> '',
	'window.location'	=> '',
    'self.location'     => '',
	'-moz-binding'		=> '',
	'<!--'				=> '&lt;!--',
	'-->'				=> '--&gt;',
	'<![CDATA['			=> '&lt;![CDATA['
 );

/**
 * Patterns which will be removed 
 * from any data in clean_xss function.
 * 
 * @access private
 * @var array
 */ 
 private $patterns_to_remove = array(
	"#javascript\s*:#i"				=> '',
	"#expression\s*(\(|&\#40;)#i"	=> '',
	"#vbscript\s*:#i"				=> '',
	"#Redirect\s+302#i"				=> ''
 );

/**
 * Invisible chars which will be removed
 * from any data in clean_xss function.
 * 
 * @access private
 * @var array
 */ 
 private $chars_to_remove = array(
	'/%0[0-8bcef]/',	// url encoded 00-08, 11, 12, 14, 15
	'/%1[0-9a-f]/',		// url encoded 16-31
	'/[\x00-\x08]/',	// 00-08
	'/\x0b/', '/\x0c/',	// 11, 12
	'/[\x0e-\x1f]/'		// 14-31
 ); 

/**
 * Static variable to check, is globals already cleaned.
 * 
 * @access private
 * @staticvar bool 
 */ 
 private static $globals_cleaned = false;
 
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
 	$this->lib = lib::instance();
 } // end constructor
 
/**
 * Clean globals.
 * 
 * Unset deprecated globals
 * Clean globals such as 	$_GET, $_POST, 
 * 							$_COOKIE, $_FILES, $_REQUEST
 * 
 * @access public
 * @param bool not unset $_GET array.
 * @param bool clean all globals by clean_xss function
 * @return void 
 */
 public function clean_globals($xss_clean = false) {

 	/* Check, is globals already cleaned */
 	if(self::$globals_cleaned === true) {
 		return; 
 	}
 
 	/* Prevent malicious GLOBALS overload attack */
	if(isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
		trigger_error('Global variable overload attack detected! ' . 
						'Request aborted.', E_USER_ERROR);
		exit(1);
	}

 	/* 
 	 * Remove globals which exist in removable_globals, 
 	 * otherwise do clean by received arguments.
 	 */
	foreach($GLOBALS as $g_key => $g_value) {
		if(in_array($g_key, $this->globals_to_remove)) {
			unset($GLOBALS[$g_key]);
		}
	} // end foreach

	/* 
	 * Unset $_GET.   
	 */
	$_GET = array();
	
	/* Clean some globals */ 
	$_SERVER	= $this->clean_data($_SERVER);
	$_POST 		= $this->clean_data($_POST);
	$_REQUEST	= $this->clean_data($_REQUEST);
	$_COOKIE 	= $this->clean_data($_COOKIE);
	$_FILES 	= $this->clean_data($_FILES);
	if(isset($_SESSION)) {
		$_SESSION 	= $this->clean_data($_SESSION);
	}
	
	/* Clean xss also */
	if($xss_clean == true) {
		$_SERVER	= $this->clean_xss($_SERVER, false);
		$_POST 		= $this->clean_xss($_POST, false);
		$_REQUEST	= $this->clean_xss($_REQUEST, false);
		$_COOKIE 	= $this->clean_xss($_COOKIE, false);
		$_FILES 	= $this->clean_xss($_FILES, false);
		if(isset($_SESSION)) {
			$_SESSION 	= $this->clean_xss($_SESSION, false);
		}
	}
	
	self::$globals_cleaned = true;

 } // end func clean_globals

/**
 * Clean url for specialy this framework.
 * 
 * @access public
 * @param string $http_get_var
 * @return void
 */ 
 public function clean_url($http_get_var) {
 	
 	if(isset($_SERVER['HTTP_HOST'])) {
		
		/* Ensure hostname only contains characters allowed in hostnames */
		$_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
		
		if(!$this->lib->valid->http_host($_SERVER['HTTP_HOST'])) {
			trigger_error('Incorrect HTTP_HOST `'.$_SERVER['HTTP_HOST'].'`!', 
							E_USER_ERROR);
		}
		
	} else {
		$_SERVER['HTTP_HOST'] = '';
	}
	
 	/* Return false, if htaccess file content is invalid */
 	/*
	if(substr($_SERVER['QUERY_STRING'], 0, strlen($http_get_var)) 
 															!= $http_get_var) {
 																
	   trigger_error('The variable ' . $http_get_var . ' in .htaccess ' .
	              	 'file not initialized (' . __CLASS__ . '::' . 
	   				 __FUNCTION__ . ')', E_USER_ERROR);
	}
	*/
	
 	/* Clean some globals before using */
	
	$_SERVER['REQUEST_URI'] = $this->clean_data($_SERVER['REQUEST_URI']);
	$_SERVER['REQUEST_URI'] = $this->clean_xss($_SERVER['REQUEST_URI'], true);
	$_SERVER['QUERY_STRING']= $this->clean_data($_SERVER['QUERY_STRING']);
	$_SERVER['QUERY_STRING']= $this->clean_xss($_SERVER['QUERY_STRING'], true);
	
	if(isset($_SERVER['REDIRECT_URL'])) {
		$_SERVER['REDIRECT_URL']=$this->clean_data($_SERVER['REDIRECT_URL']);
		$_SERVER['REDIRECT_URL']=$this->clean_xss($_SERVER['REDIRECT_URL'], true);
	}

	if(isset($_SERVER['REDIRECT_QUERY_STRING'])) {
		$_SERVER['REDIRECT_QUERY_STRING'] = $this->clean_data(
									$_SERVER['REDIRECT_QUERY_STRING']);
									
		$_SERVER['REDIRECT_QUERY_STRING'] = $this->clean_xss(
									$_SERVER['REDIRECT_QUERY_STRING'], true);
	}
	
	if(isset($_SERVER['argv'][0])) {
		$_SERVER['argv'][0] = $this->clean_data($_SERVER['argv'][0]);
		$_SERVER['argv'][0] = $this->clean_xss($_SERVER['argv'][0], true);
	}
	
	if(isset($_REQUEST[$http_get_var])) {
		$_REQUEST[$http_get_var]=$this->clean_data($_REQUEST[$http_get_var]);
		$_REQUEST[$http_get_var]=$this->clean_xss($_REQUEST[$http_get_var], true);
	}
	
	/* Get query string source for parsing */
	$_SERVER['QUERY_STRING'] = trim($_SERVER['QUERY_STRING']);

 } // end cunt clean_url
 
/**
 * Clean data with some options.
 * if xss_clean defined as true this function
 * will call clean_xss function.
 * 
 * @access public
 * @param mixed data to clean
 * @return mixed
 */
 public function clean_data($data) {
 	/* 
 	 * Call this function recursively
 	 * if $data argument is array.
 	 */
 	if(is_array($data)) {
		$tmp_arr = array();
		
		/* Clean keys also, if array is associative */
		if($this->lib->data->is_assoc($data)) {
			foreach($data as $key => $value) {
				/* Clean key. pass only a-z, A-Z, 0-9, -, _, . chars */
				$key = $this->strip_chars($key, array('-', '_', '.'));
				/* Clean value */
				$tmp_arr[$key] = $this->clean_data($value);
			} // end foreach
		} else {
			foreach($data as $value) {
				$tmp_arr[] = $this->clean_data($value);
			} // end foreach
		} // end if array is assoc	
		
		return $tmp_arr;
		
	} elseif (is_string($data)) {

		/* Decode html entities by application encoding */
		/* This part is now commented because some issues detected */
		// $data = $this->decode_html_entities($data);
    
 		if(function_exists('get_magic_quotes_gpc')) {
			if(get_magic_quotes_gpc()) {
				$data = stripslashes($data);
			}	
		}
		
		$data = str_replace(array("\r\n", "\r"), "\n", $data);
		$data = trim($data);
		
		return $data;
	} // end if is_string
} // end func clean_data

/**
 * Clean data for Cross Site Scripting Hacks.
 * If second argument $clean tags is false, 
 * then will not remove any html tags.
 *  
 * @access public
 * @param mixed data to clean
 * @param bool remove html tags
 * @return string
 */
 function clean_xss($data, $clean_tags = false) {
 	
 	/* 
 	 * Call this function recursively
 	 * if $data argument is array.
 	 */
 	if(is_array($data)) {
		$tmp_arr = array();
		
		/* Clean keys also, if array is associative */
		if($this->lib->data->is_assoc($data)) {
			foreach($data as $key => $value) {
				/* Clean key. */
				$key = $this->clean_xss($key);
				/* Clean value */
				$tmp_arr[$key] = $this->clean_xss($value);
			} // end foreach
		} else {
			foreach($data as $value) {
				$tmp_arr[] = $this->clean_xss($value);
			} // end foreach
		} // end if array assoc
		return $tmp_arr;
		
 	} elseif (is_string($data)) {
 	
		/* Remove invisible chars */
		foreach($this->chars_to_remove as $char) {
			$data = preg_replace($this->chars_to_remove, '', $data);
		}
	
		/* Replace dangerous expressions */
 		foreach($this->expressions_to_replaced as $etr_k => $etr_v) {
			$data = str_replace($etr_k, $etr_v, $data);
		}
	
		/* Remove dangerous script, etc */
    	foreach($this->patterns_to_remove as $ptr_k => $ptr_v) {
			$data = preg_replace($ptr_k, $ptr_v, $data);
		}

		if($clean_tags == true) {
 		
			/* Strip any html tag */
			$data = $this->strip_tags($data);
		
			/* Covert < and > to and */
			//$data = str_replace(array('<', '>'), array('&lt;', '&gt;'), $data);
		
		} else { 
	
			/* Remove any attribute starting with "on*" or "xmlns" */
			$data = $this->strip_attributes($data);
	
			/* 
	 	 	 * Remove some elements which we definitively don't 
	 	 	 * want in something like user comments.
	 	  	 */
			do {
    			$old_data = $data;
    			$data = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i',"", $data);
 			} while ($old_data != $data);

			/*
	 	 	 * Clean source code, replace javascript and php 
 	 	     * vulnerable functions to special chars.
 	  	     */ 
			if($clean_tags == true) {
				$data = $this->clean_source($data);
			}

			/* Strip any script definition */
			$data = $this->strip_scripts($data);
		
		} // end if clean tags
	
		return $data;
 	} // end if is_tring(data) 	
 } // end func clean_xss

/**
 * Fix source code, replace javascript and php
 * vulnerable functions to special chars.
 *  
 * example: eval ("echo 333;"); -> eval &#40;"echo 333;"&#41;;
 * 
 * @access public
 * @param string data to clean
 * @return string 
 */
 public function clean_source($data) {
	return preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $data);
 } // end func clean_source

/**
 * Return cleaned text with new lines
 * for each application run mode
 * 
 * @access public
 * @param string
 * @return string
 * @since 1.1.0
 */ 
 public function clean_nl($data) {
 	
 	if(TK_RUN_MODE == 'cli') {
 		return str_replace(
 				array("\r\n", "\n\r", "\r", "<br>", "<br />", "<BR>", "<BR />"), 
 				"\n", 
 				$data); 
 	} else {
 		return nl2br($data);
 		/*
 		return str_replace(
 				array("\n", "\n\r", "\r\n", "\r"), 
 				"<br />", 
 				$data);
 		*/		 
 	}
 	
 } // end func clean_nl
  
/**
 * Strip slashes if magic_quotes is on.
 * 
 * @access public
 * @param string
 * @return string
 */
 function strip_slashes($data) {
	if(function_exists('get_magic_quotes_gpc')) {
		if(get_magic_quotes_gpc()) {
			$data = stripslashes($data);
		}
	}
	return $data;
 } // end func strip_slashes

/**
 * Clean string as a-z, A-Z, 0-9.
 * pass chars defined in $allowed_chars array.
 * 
 * @access public
 * @param mixed data to clean
 * @param array chars to pass
 * @return string
 */
 public function strip_chars($data, $allowed_chars = NULL) {
	
 	/* Make allowed chars pattern */
 	
 	$chars_str = '';
 	
	if(is_array($allowed_chars)) {
		foreach($allowed_chars as $char) {
			$chars_str .= $char;
		}
	}
	
	return preg_replace("#[^a-z0-9A-Z".$chars_str."]#", '', $data);
} // end func strip_chars

/**
 * Remove any attribute starting with "on*" or "xmlns"
 * 
 * @access public
 * @param string data to clean
 * @return string
 */
 public function strip_attributes($data) {
	return preg_replace('#(?:on[a-z]+|xmlns)\s*=\s*["\x00-\x20]?[^>"]*[\'"\x00-\x20]?\s?#iu', '', $data);
 } // end func strip_attributes

/**
 * Convert tabs to char specified.
 * By default will convert to empty string.
 * 
 * @access public
 * @param string string to convert
 * @return string
 */
 public function strip_tabs($data, $char = '') {
 	return str_replace("\t", $char, $data);
 } // end func strip_tabs
 
/**
 * Strip image tags.
 * If $keep_src is true, then keep 
 * image source string.
 * 
 * @access public
 * @param string data to strip
 * @return string
 */
 public function strip_image_tags($data, $keep_src = false) {
	if($keep_src) {
		$src_str = '$1';
	} else {
		$src_str = '';
	}
	
	return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', $src_str, $data);
 } // end func strip_image_tags

/**
 * Strip hyperlinks.
 * 
 * @access public
 * @param string data to strip
 * @return string
 */ 
 public function strip_hyperlinks($data) {
	return preg_replace('@<a[^>]*?>.*?</a>@si', '', $data);
 } // end func strip_hyperlinks

/**
 * Strip meta tags.
 * 
 * @access public
 * @param string data to strip
 * @return string
 */ 
 public function strip_meta($data) {
	return preg_replace('#<meta\s.*?(?:content\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '', $data);
 } // end func strip_meta

/**
 * Strip style definitions.
 * 
 * <style>...</style>
 * <link href=".." rel="stylesheet" type="text/css">
 * 
 * @access public
 * @param string data to strip
 * @return string
 */
 public function strip_styles($data) {
	return preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>)|<style[^>]*>.*?<\/style>/is', '', $data);
 } // end func strip_styles
 
/**
 * Strip any script definition.
 * 
 * We detected the issue in libraries of some frameworks, 
 * where the single script definition <script> not stripped 
 * by function similar to this. So in this case, users can 
 * not corrupt html content writing <script>. It also will 
 * be stripped in this function.
 * 
 * @access public
 * @param string data to strip
 * @return string
 */
 public function strip_scripts($data) {
	
    $data = preg_replace('@<script[^>]*?>.*?</script>@si', '', $data);
	$data = preg_replace('@<script>@si', '', $data);
	
	$data = preg_replace('@<\?php[^>]*.*?\?>@si', '', $data);
	$data = preg_replace('@<\?[^>]*.*?\?>@si', '', $data);
	
	//$data = str_replace(array('<?php', '<?'), '', $data);
	
	return $data;
 } // end func strip_scripts

/**
 * Strip comments.
 * If second argument is true, then 
 * will delete non html comments
 * 
 * <!-- ... -->
 * /*  * /
 * 
 * @access public
 * @param string data to strip
 * @return string
 */
 public function strip_comments($data, $non_html = true) {
	/* remove html comments */
	$data = preg_replace('@<![\s\S]*?--[ \t\n\r]*>@', '', $data);
	
	/* remove /* * / comments */
	if($non_html) {
	  $data = preg_replace('%/\*[\s\S]+?\*/|(?://).*(?:\r\n|\n)%m', '', $data);
	}
	
	return $data;
 } // end func strip_comments

/**
 * Strip any html tag.
 * 
 * @access public
 * @param string data to strip
 * @return string 
 */
 public function strip_tags($data) {
	return preg_replace('@<[\/\!]*?[^<>]*?>@si', '', $data);
 } // end func strip_tags
 
/**
 * Strips extra whitespaces.
 *
 * @access public
 * @param string data to strip
 * @return string
 */
 public function strip_whitespaces($data) {
	$data = preg_replace('/[\n\r\t]+/', '', $data);
	return preg_replace('/\s{2,}/', ' ', $data);
 } // end func strip_whitespaces
 
/**
 * Encode html entity by application encoding.
 * 
 * @access public
 * @param mixed array | string data to encode
 * @return string
 */ 
 public function encode_html_entities($data, $encoding = NULL) {
	
	if(is_null($encoding)) {
		$encoding = 'UTF-8';
    }
	
	if(is_array($data)) {
		foreach($data as $key => $value) {
			$data[$key] = $this->encode_html_entities($value, $encoding);
		}
		
		return $data;
		
	} else {
		return htmlentities((string)$data, ENT_QUOTES, $encoding);
	}
	
} // end func encode_html_entities

/**
 * Decode html entity by application encoding.
 * 
 * @access public
 * @param mixed array | string data to decode
 * @return string
 */
 public function decode_html_entities($data, $encoding = NULL) {
	
	if(is_null($encoding)) {
		$encoding = 'UTF-8';
    }
    
	if(is_array($data)) {
		foreach($data as $key => $value) {
			$data[$key] = $this->decode_html_entities($value, $encoding);
		}
		
		return $data;
		
	} else {
		return html_entity_decode(urldecode($data), ENT_QUOTES, $encoding);
	}
	
 } // end func decode_html_entities

/**
 * Return data from $_POST global array
 * 
 * @access public
 * @param string item name
 * @param bool clean xss = false
 * @param bool strip html tags in xss func.
 * @return string
 */ 
 public function post($item, $encode_html_entities = true, $clean_xss = false, $strip_tags = false) {
 	
 	if(!isset($_POST[$item])) {
 		return false;
 	}
 	
 	$data = $_POST[$item];
 	
 	if($clean_xss == true) {
 	   $data = $this->clean_xss($data, $strip_tags); 
 	} elseif ($strip_tags == true) {
		$data = strip_tags($data); 
 	}
 	
 	if($encode_html_entities == true) {
 		$data = $this->encode_html_entities($data); 
 	}
 	
 	return $data;
 	
 } // end func post 

/**
 * Return data from $_REQUEST global array
 * 
 * @access public
 * @param string item name
 * @param bool clean xss = false
 * @param bool strip html tags in xss func.
 * @return string
 */ 
 public function request($item, $clean_xss = false, $strip_tags = false) {
 	
	if(!isset($_REQUEST[$item])) {
 		return false;
 	}
 	
 	if($clean_xss == true) {
 	   return $this->clean_xss($_REQUEST[$item], $strip_tags); 
 	} else {
 	   return $_REQUEST[$item];
 	}
 } // end func request
 
/**
 * Return data from $_COOKIE global array
 * 
 * @access public
 * @param string item name
 * @param bool clean xss = false
 * @param bool strip html tags in xss func.
 * @return string
 */ 
 public function cookie($item, $clean_xss = false, $strip_tags = false) {
 	
	if(!isset($_COOKIE[$item])) {
 		return false;
 	}
 	
 	if($clean_xss == true) {
 	   return $this->clean_xss($_COOKIE[$item], $strip_tags); 
 	} else {
 	   return $_COOKIE[$item];
 	}
 } // end func cookie

 /**
 * Return data from $_FILES global array
 * 
 * @access public
 * @param string item name
 * @param bool clean xss = false
 * @param bool strip html tags in xss func.
 * @return string
 */ 
 public function files($item, $clean_xss = false, $strip_tags = false) {
 	
	if(!isset($_FILES[$item])) {
 		return false;
 	}
 	
 	if($clean_xss == true) {
 	   return $this->clean_xss($_FILES[$item], $strip_tags); 
 	} else {
 	   return $_FILES[$item];
 	}
 } // end func files

/* End of class filter_lib */
}

/* End of file filter.lib.php */
?>