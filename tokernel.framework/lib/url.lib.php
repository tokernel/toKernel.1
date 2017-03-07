<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for parsing and working with URL
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
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.5.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * url_lib class library 
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class url_lib {
	
/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;

/**
 * Parameters and other options array
 * 
 * @access protected
 * @var array
 */  
 protected $url_arr;

/**
 * Status of initialization
 * 
 * @access protected
 * @staticvar bool
 */ 
 protected static $initialized;

/**
 * Class constructor
 *
 * @access public
 * @return void
 */ 
 public function  __construct() {

	$this->lib = lib::instance();

	$this->url_arr = array(
			'url' => '',
			'base_url' => '',
			'query_string' => '',
			'language_prefix' => '',
			'language_parsing' => false,
			'alias' => '',
			'id_addon' => '',
			'action' => '',
			'params' => array(),
			'template' => '',
			'mode' => TK_FRONTEND,
			'backend_dir' => '',
			'parse_mode' => '',
			'parts' => array()
	);

	self::$initialized = false;
 
 } // end constructr
 
/**
 * Magic function get.
 * Return values from $url_arr
 * 
 * @access public
 * @param string $item
 * @return mixed string | array | bool
 */  
 public function __get($item) {
 	switch($item) {
 		case 'all':
 			return $this->url_arr;
 			break;
 		case 'url': 
 			return $this->url_arr['url']; 
 			break;
 		case 'base_url':
 			return $this->url_arr['base_url']; 
 			break;
 		case 'query_string':
 			return $this->url_arr['query_string']; 
 			break;
 		case 'lng': 
 		case 'language': 
 		case 'language_prefix':
 			return $this->url_arr['language_prefix']; 
 			break;
 		case 'language_parsing':
 			return $this->url_arr['language_parsing']; 
 			break;
 		case 'alias':
 			return $this->url_arr['alias']; 
 			break;
 		case 'id_addon':
 		case 'addon':
 		case 'controller':
 			return $this->url_arr['id_addon']; 
 			break;
 		case 'action':
 			return $this->url_arr['action']; 
 			break;
 		case 'params':
 			return $this->url_arr['params'];
 			break; 
 		case 'params_count':
 			return count($this->url_arr['params']); 
 			break;
		case 'parts':
			return $this->url_arr['parts'];
			break;
 		case 'template':
 			return $this->url_arr['template']; 
 			break;	
 		case 'mode':
 			return $this->url_arr['mode']; 
 			break;
 		case 'backend_dir':
 			return $this->url_arr['backend_dir']; 
 			break;
 		case 'http_get_var':
 			return $this->url_arr['http_get_var']; 
 			break;
		case 'parse_mode':
			return $this->url_arr['parse_mode'];
			break;
		
 	}  // end switch
 	
 	if(isset($this->url_arr['params'][$item])) {
 		return $this->url_arr['params'][$item];
 	}
 	
 	return false;

 } // end func __get
 
/**
 * Initialize URL Parsing
 * This function will called from application instance at once.
 * 
 * @access public
 * @param array $config_arr
 * @param string $debug_log
 * @return bool
 */ 
 public function init($config_arr) {
 	
 	/* Return true if already initialized */
	if(self::$initialized == true) {
 		trigger_error('URL initialization - ' . __CLASS__ . '->' . 
 				__FUNCTION__ . '() is already initialized!', E_USER_WARNING);
 				
		return true;
 	}
 	
 	tk_e::log_debug('Start with query string - "' . $_SERVER['QUERY_STRING'].'"' 
 								, __CLASS__ . '->' . __FUNCTION__);
 	
	$this->url_arr['parse_mode'] = $config_arr['http_params_mode'];
	
 	$this->url_arr['http_get_var'] = $config_arr['http_get_var'];
 								
 	/* Get base url by default if not specified */
 	$this->url_arr['base_url'] = $config_arr['base_url'];
 	
 	if(trim($this->url_arr['base_url']) == '') {
 		$this->url_arr['base_url'] = $this->dynamic_url();
 	}

 	if(substr($this->url_arr['base_url'], -1) != '/') {
 		$this->url_arr['base_url'] .= '/';
 	}
 	
 	$this->url_arr['query_string'] = $_SERVER['QUERY_STRING'];
 	
 	/* remove 'tokernel_params=' that is defined in .htacess file */
 	$this->url_arr['query_string'] = substr($this->url_arr['query_string'], 
 									strlen($config_arr['http_get_var']) + 1);
 									
	$this->url_arr['url'] = $this->url_arr['base_url'] . 
									$this->url_arr['query_string'];
 	 
	$query_string_arr = explode('/', $this->url_arr['query_string']);

    // Clean array
    $query_string_arr = $this->clean_query_string_arr($query_string_arr);

 	/* 
	 * Check, if backend_dir item defined in configuration file and 
	 * $query_string_arr[0] == backend_dir, then set this item as backend 
	 * (administrator control panel) directory and set application mode 
	 * as 'backend'.
	 */
	if(isset($query_string_arr[0]) and $query_string_arr[0] != '' and 
		$config_arr['backend_dir'] != '' and 
		$config_arr['backend_dir'] == $query_string_arr[0]) {
		
		$this->url_arr['backend_dir'] = $config_arr['backend_dir'];
		$this->url_arr['mode'] = TK_BACKEND;
		array_shift($query_string_arr);
		
		tk_e::log_debug('Detected backend dir - "' . 
						$this->url_arr['backend_dir'] 
						. '" Running in backend mode' 
 						, __CLASS__ . '->' . __FUNCTION__);
	}
	
	/* Define allowed languages as array from configuration */
	$allowed_languages = explode('|', $config_arr['http_allowed_languages']);
		
	/* Define language */
	if(isset($query_string_arr[0]) and $query_string_arr[0] != '' and 
		$config_arr['http_parse_language'] == 1) {
		
		if(!in_array($query_string_arr[0], $allowed_languages)) {
			
			/* Trying to get language from browser */
			$language_prefix = $this->matches_browser_language($allowed_languages);
		
			if(!$language_prefix) {
				$this->url_arr['language_prefix'] = $config_arr['http_default_language'];
			
				tk_e::log_debug('Set default language prefix - "' . 
							$this->url_arr['language_prefix'] 
							. '" by default', __CLASS__ . '->' . __FUNCTION__);
			
			} else {
				$this->url_arr['language_prefix'] = $language_prefix;
			
				tk_e::log_debug('Set language prefix from client browser - "' . 
							$this->url_arr['language_prefix'] . '"', 
							__CLASS__ . '->' . __FUNCTION__);
			}
		
		} else { 
		
			$this->url_arr['language_prefix'] = $query_string_arr[0];
			array_shift($query_string_arr);
		
			tk_e::log_debug('Detected language prefix - "' . 
						$this->url_arr['language_prefix'] 
						. '" ', __CLASS__ . '->' . __FUNCTION__);
		}				
 						
	} else {
		
		/* Trying to get language from browser */
		$language_prefix = $this->matches_browser_language($allowed_languages);
		
		if(!$language_prefix) {
			$this->url_arr['language_prefix'] = $config_arr['http_default_language'];
			
			tk_e::log_debug('Set default language prefix - "' . 
						$this->url_arr['language_prefix'] 
						. '" by default', __CLASS__ . '->' . __FUNCTION__);
			
		} else {
			$this->url_arr['language_prefix'] = $language_prefix;
			
			tk_e::log_debug('Set language prefix from client browser - "' . 
						$this->url_arr['language_prefix'] . '"', 
						__CLASS__ . '->' . __FUNCTION__);
		}
		
	}

	if($this->url_arr['language_prefix'] == '') {
		$this->url_arr['language_prefix'] = 'en';
	}

	if($config_arr['http_parse_language'] == 1) {
		$this->url_arr['language_parsing'] = true;
	} else {
		$this->url_arr['language_parsing'] = false;
	}

    /* Set URL Parts */
    $this->url_arr['parts'] = $query_string_arr;

 	/* Check and set aliasing if exists */
	if(isset($query_string_arr[0]) and $query_string_arr[0] != '') { 
		
		if ($this->set_aliasing($query_string_arr[0], 
											$config_arr['http_params_mode'])) {
			array_shift($query_string_arr);
		}
		
	} // end if alias exist
	
	/* Set id_addon if defined in query string, else set from config */
	if(isset($query_string_arr[0]) and $query_string_arr[0] != '' and 
		$this->url_arr['id_addon'] == '') {
		
		//$this->url_arr['parts'][] = $query_string_arr[0];
		
		$this->url_arr['id_addon'] = $query_string_arr[0];
		array_shift($query_string_arr);
	
		tk_e::log_debug('Detected id_addon - "' . 
						$this->url_arr['id_addon'] . '" '
						, __CLASS__ . '->' . __FUNCTION__);
						
	} elseif($this->url_arr['id_addon'] == '') {
		
		$this->url_arr['id_addon'] = 
			$config_arr[$this->url_arr['mode'].'_default_callable_addon'];
			
		tk_e::log_debug('Set id_addon - "' . 
						$this->url_arr['id_addon'] . '" by default '
						, __CLASS__ . '->' . __FUNCTION__);	
	}
	
	/* Set action if defined in query string, else, set from config */
	if(isset($query_string_arr[0]) and $query_string_arr[0] != '' and 
		$this->url_arr['action'] == '') {
		
		//$this->url_arr['parts'][] = $query_string_arr[0];
		
		$this->url_arr['action'] = $query_string_arr[0];
		$this->url_arr['template'] = $this->url_arr['id_addon'] . 
									 '.' . $this->url_arr['action'];
		
		array_shift($query_string_arr);
		
		tk_e::log_debug('Detected action - "' . 
						$this->url_arr['action'] . '" '
						, __CLASS__ . '->' . __FUNCTION__);	
						
		tk_e::log_debug('Set template - "' . 
						$this->url_arr['template'] . '" '
						, __CLASS__ . '->' . __FUNCTION__);
										
	} elseif($this->url_arr['action'] == '') {
	
		$this->url_arr['action'] = 
		$config_arr[$this->url_arr['mode'].'_default_callable_action'];
		$this->url_arr['template'] = $this->url_arr['id_addon'] . '.' . 
									 $this->url_arr['action'];
									 
		tk_e::log_debug('Set action - "' . 
						$this->url_arr['action'] . '" by default '
						, __CLASS__ . '->' . __FUNCTION__);
						
		tk_e::log_debug('Set template - "' . 
						$this->url_arr['template'] . '" '
						, __CLASS__ . '->' . __FUNCTION__);				
	}
	
	/* make params  array if not empty */
	if(!empty($query_string_arr)) {

		tk_e::log_debug('Detected params - "' . 
						implode('/', $query_string_arr) . '" '
						, __CLASS__ . '->' . __FUNCTION__);
		
		$url_params_arr = array();
		
		// make url params array
		if($config_arr['http_params_mode'] == 'assoc') {
			$url_params_arr = $this->parse_params_assoc($query_string_arr); 	
		} else {
			$url_params_arr = $query_string_arr;
		}
		
		/* Set optional parameters from url */ 
		if(!empty($this->url_arr['params'])) {
	    	/* 
	    	 * Merge parameters from aliasing and url params.
	     	 * NOTE: The identical parameters will be replaced by 
	     	 * aliasing on url params, because aliasing is so main.  
	     	 */
			$this->url_arr['params'] = array_merge($url_params_arr, $this->url_arr['params']);
		} else {
			$this->url_arr['params'] = $url_params_arr;
		}
						
	} else { 
		tk_e::log_debug('Params not detected.', __CLASS__ . '->' . __FUNCTION__);
						
	} // end params count

	unset($query_string_arr);
	self::$initialized = true;
	
	tk_e::log_debug('End with base url - "'.$this->url_arr['base_url'].'" ', __CLASS__ . '->' . __FUNCTION__);
	
	return true;
 
 } // end func init

/**
 * Check, if the browser languages allowed for application.
 * 
 * @access protected
 * @param array $allowed_languages
 * @return string | bool
 * @since v.2.1.0
 */ 
 protected function matches_browser_language($allowed_languages) {

	 $b_languages = $this->lib->client->languages();
	 
	 if(count($b_languages) == 0) {
		 return false;
	 }

	 foreach($b_languages as $language) {
		
		$tmp = explode('-', $language);
		$prefix = $tmp[0];
		 
		if(in_array($prefix, $allowed_languages)) {
			return $prefix;
		}

	 }
	 
	 return false;
			
 } // End func matches_browser_language
 
/**
 * Set aliasing if exists
 * 
 * @access protected
 * @param string $alias_word
 * @param string $http_params_mode each | assoc
 * @return bool
 */ 
 protected function set_aliasing($alias_word, $http_params_mode) {
 	
 	if(trim($alias_word) == '') {
 		return false;
 	}
 	
	$aliasing_arr = $this->lib->alias->get($alias_word);
	
	if(!is_array($aliasing_arr)) {
		
		tk_e::log_debug('Aliasing by word "'.$alias_word.'" not detected' 
									, __CLASS__ . '->' . __FUNCTION__);
						
		return false;
	}	
		
	/* 
	 * Aliasing array exist, so setting 
	 * application parameters.
	 */
	$this->url_arr['alias'] = $alias_word;
	
	tk_e::log_debug('Detected aliasing word "'.$alias_word.'"' 
									, __CLASS__ . '->' . __FUNCTION__);
									
	if(!isset($aliasing_arr['id_addon']) or $aliasing_arr['id_addon'] == '') {
		trigger_error('Item "id_addon" is empty for alias "'.$alias_word.'"!', 
						E_USER_WARNING);
		return false;
	} else {
		
		$this->url_arr['id_addon'] = $aliasing_arr['id_addon'];
		
		tk_e::log_debug('Set id_addon - "' . $this->url_arr['id_addon'] . 
						'" from aliasing', __CLASS__ . '->' . __FUNCTION__);
	}
	
	if(!isset($aliasing_arr['action']) or $aliasing_arr['action'] == '') {
		trigger_error('Item "action" is empty for alias "'.$alias_word.'"!', 
						E_USER_WARNING);
		return false;
	} else {
		$this->url_arr['action'] = $aliasing_arr['action'];
		
		tk_e::log_debug('Set action - "' . $this->url_arr['action'] . 
						'" from aliasing', __CLASS__ . '->' . __FUNCTION__);
	}
	
	if(isset($aliasing_arr['template'])) {
		$this->url_arr['template'] = $aliasing_arr['template'];
		
		tk_e::log_debug('Set template - "' . $this->url_arr['template'] . 
						'" from aliasing', __CLASS__ . '->' . __FUNCTION__);
		
	} else {
		
		tk_e::log_debug('Template not defined in aliasing' 
						, __CLASS__ . '->' . __FUNCTION__);
		
		$this->url_arr['template'] = '';
	}
	
	if(isset($aliasing_arr['params'])) {

		tk_e::log_debug('Set params - "' . $aliasing_arr['params'] . 
						'" from aliasing', __CLASS__ . '->' . __FUNCTION__);
		
		$this->url_arr['params'] = explode('/', $aliasing_arr['params']);

		if($http_params_mode == 'assoc') {
			$this->url_arr['params'] = $this->parse_params_assoc($this->url_arr['params']);
		}
		
	} else {
		
		$this->url_arr['params'] = array();
		
		tk_e::log_debug('Params not defined in aliasing' 
						, __CLASS__ . '->' . __FUNCTION__);
						
	}

	tk_e::log_debug('End with alias - "'.$alias_word.'" ' 
						, __CLASS__ . '->' . __FUNCTION__);
	
	return true;
	
 } // end func has_aliasing
 
/**
 * Parse paraeters array to assoc
 * 
 * @access protected
 * @param array $arr
 * @return array
 */ 
 protected function parse_params_assoc($arr) {
	
 	$ret_arr = array();

 	for($i = 0; $i < count($arr); $i += 2) {

		$param_val = '';
		
		if(isset($arr[$i+1])) {
			$param_val = $arr[$i+1];
		}
		
		if($arr[$i] != '') {
			
			$this->url_arr['parts'][] = $arr[$i];
			$this->url_arr['parts'][] = $param_val;
			
			$ret_arr[$arr[$i]] = $param_val;
		}
		
	} // end for

	return $ret_arr;
	
 } // end func parse_params_assoc

/**
 * Return url
 * 
 * @access public
 * @param mixed $id_addon
 * @param mixed $action
 * @param mixed $params_set
 * @param mixed $params_remove
 * @return string
 */ 
 public function url($id_addon = NULL, $action = NULL, $params_set = NULL, 
 					 $params_remove = NULL) {
 	
 	$url = '';
 	
 	/* Set base url */
 	$url = $this->url_arr['base_url'];
 	
 	/* Append backend dir (administrator control panel name) 
 	 * if in backend mode, and backend_dir is not empty */
 	if($this->mode() == TK_BACKEND and $this->backend_dir() != '') {
 		$url .= $this->backend_dir() . '/';
 	}
 	
 	/* Append language prefix if enabled */
 	if($this->language_parsing() == true) {
 		$url .= $this->language_prefix() . '/';
 	}
 	
 	/* Append id_addon to url */
 	if($id_addon === true) {
 		$url .= $this->id_addon() . '/';
 	} elseif(trim($id_addon) != '') {
 		$url .= $id_addon . '/';
 	}
 	
 	/* Append action to url */
 	if($action === true) {
 		$url .= $this->action() . '/';
 	} elseif(trim($action) != '') {
 		$url .= $action . '/';
 	}
 	
 	$params_arr = array();
 	
 	/* Append parameters */
 	if($params_set === true) {
 		$params_arr = $this->params();
 	} elseif(is_array($params_set) and $params_remove !== true) {
 		$params_arr = array_merge($this->params(), $params_set);
 	} elseif(is_array($params_set) and $params_remove === true) {
 		$params_arr = $params_set;
 	}
 	
 	/* remove parameters that will be removed */
 	if(is_array($params_remove)) {
 		foreach($params_remove as $param) {
 			if(isset($params_arr[$param]) and !isset($pass_params[$param])) {
 				unset($params_arr[$param]);
 			}
 		}
 	}
 	
 	if(count($params_arr) > 0) {
 		foreach($params_arr as $param => $value) {
 			if($value != '') {
				if($this->url_arr['parse_mode'] == 'assoc') {
					$url .= $param . '/' . $value . '/';
				} else {
					$url .= $value . '/';
				}	
			}	
 		}
 	}
 	
 	return $url;
 	
 } // end func url

/**
 * Return base url
 * 
 * @access public
 * @return string
 */ 
 public function base_url() {
 	return $this->url_arr['base_url'];
 }

/**
 * Generate and return server url
 * 
 * @access public
 * @return string
 */ 
 public function dynamic_url() {
 
 	$base_url = '';
 	
 	if(isset($_SERVER['HTTPS'])) {
 		$base_url .= 'https://';
 	} else {
 		$base_url .= 'http://';
 	}

 	$base_url .= $_SERVER['HTTP_HOST'] . '/';
 	 
 	return $base_url;

 } // end func dynamic_url

/**
 * Return query string
 * 
 * @access public
 * @return string
 */ 
 public function query_string() {
 	return $this->url_arr['query_string'];
 }
 
/**
 * Return language prefix
 * 
 * @access public
 * @return string
 */ 
 public function language_prefix() {
 	return $this->url_arr['language_prefix'];
 }

/**
 * Return true, if the item 'http_parse_language' defined 
 * as 1 in application configuration file.
 * 
 * @access public
 * @return bool
 */ 
 public function language_parsing() {
 	return $this->url_arr['language_parsing'];
 }
 
/**
 * Set language prefix
 * 
 * @access public
 * @param string $lp
 * @return void
 */
 public function set_language_prefix($lp) {
 	$this->url_arr['language_prefix'] = $lp;
 }
 
/**
 * Return alias
 * 
 * @access public
 * @return string
 */ 
 public function alias() {
 	return $this->url_arr['alias'];
 }

/**
 * Return id of called addon
 * 
 * @access public
 * @return string
 */ 
 public function id_addon() {
 	return $this->url_arr['id_addon'];
 }

/**
 * Return action for called addon
 * 
 * @access public
 * @return string
 */ 
 public function action() {
 	return $this->url_arr['action'];
 }

/**
 * Check, is param exists. 
 * 
 * @access public
 * @param string $item
 * @return bool
 */
 public function param_exists($item) {
	 
	if(isset($this->url_arr['params'][$item])) {
 		return true;
 	} else {
		return false;
	}
	
 } // End func param_exists

/**
 * Return exploded parts from url
 * 
 * @access public
 * @param int $index
 * @return mixed
 * @since version 2.3.0
 */ 
 public function parts($index = NULL) {
	 
	if(is_null($index)) {
		return $this->url_arr['parts'];
	}
	 
	if(isset($this->url_arr['parts'][$index])) {
		return $this->url_arr['parts'][$index];
	}
 	
 	return false;
	 
 } // End func parts
 
 /**
 * Return count of url parts
 * 
 * @access public
 * @return integer
 * @since version 2.3.0
 */ 
 public function parts_count() {
 	return count($this->url_arr['parts']);
 } 
 
/**
 * Return parameter value by item or parameters array
 * 
 * @access public
 * @param string $item
 * @return mixed
 */ 
 public function params($item = NULL) {
 	
 	if(is_null($item)) {
 		return $this->url_arr['params'];
 	}
 	
 	if($this->param_exists($item) == true) {
		return $this->url_arr['params'][$item];
	}
 	
 	return false;
 
 } // end func params
 
/**
 * Return count of parameters
 * 
 * @access public
 * @return integer
 */ 
 public function params_count() {
 	return count($this->url_arr['params']);
 }

/**
 * Return template name
 * 
 * @access public
 * @return string
 */ 
 public function template() {
 	return $this->url_arr['template'];
 }
 
/**
 * Return application mode frontend | backend
 * 
 * @access public
 * @return string
 */ 
 public function mode() {
 	return $this->url_arr['mode'];
 } 
/**
 * Return application backend (admin control panel directory) 
 * 
 * @access public
 * @return string
 */ 
 public function backend_dir() {
 	return $this->url_arr['backend_dir'];
 } 
 
 
/**
 * Set template name
 * 
 * @access public
 * @param string $template
 * @return void
 */ 
 public function set_template($template) {
 	$this->url_arr['template'] = $template;
 }
 
/**
 * Set addon
 * 
 * @access public
 * @param string $id_addon
 * @return bool
 */
 public function set_id_addon($id_addon) {
 	
 	if(trim($id_addon) != '') {
 		$this->url_arr['id_addon'] = $id_addon;
 		return true;
 	}
 	
 	return false;
	
 } // end func set_id_addon
 
/**
 * Set action
 * 
 * @access public
 * @param string $action
 * @return bool
 */
 public function set_action($action) {
 	
 	if(trim($action) != '') {
 		$this->url_arr['action'] = $action;
 		return true;
 	}
 	
 	return false;
	
 } // end func set_action
 
/**
 * Set application mode
 * 
 * @access public
 * @param string frontend | backend
 * @return void
 */ 
 public function set_mode($mode) {
 	if($mode != TK_BACKEND and $mode != TK_FRONTEND) {
 		trigger_error('Can set application mode only as `' . TK_BACKEND . 
 						'` or `'.TK_FRONTEND.'` !');
 		return false;
 	}
 		
 	$this->url_arr['mode'] = $mode;
 }

/**
 * Clean Query string array
 *
 * @access private
 * @param array $query_string_arr
 * @return array
 * @since 2.4.0
 */
 private function clean_query_string_arr($query_string_arr) {

     if(empty($query_string_arr)) {
         return array();
     }

     $new = array();

     foreach($query_string_arr as $item) {

         if(trim($item) != '') {

             $item = $this->lib->filter->strip_chars(
                 $item,
                 array('-', '_', '.', ' ')
             );

             $new[] = $item;
         }

     }

     return $new;

 } // End func query_string_arr

/**
 * Return true if the request protocol is https
 *
 * @access public
 * @return bool
 * @since version 2.5.0
 */
 public function is_https() {

	if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') {
		return true;
	} else {
		return false;
	}

 } // End func is_https
 
/* End of class cli_lib */
}

/* End of file */
?>