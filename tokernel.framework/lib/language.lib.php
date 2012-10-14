<?php
/**
 * toKernel - Universal PHP Framework.
 * Language processing library for application, addons, etc...
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
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @todo	   Create functions - sync, compare.
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * language_lib class
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class language_lib {
	
/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;

/**
 * Language prefix.
 * Ex: en | ru 
 * 
 * @access protected
 * @var string
 */ 
 protected $prefix;
 
/**
 * Language owner id
 * 
 * @access protected
 * @var string
 */ 
 protected $owner_id;
 
/**
 * Array of include paths for language files.
 * 
 * @access protected
 * @var array
 */ 
 protected $include_paths = array();

/**
 * Array of loaded languages
 * Each item of this array is instance of ini class lib
 * 
 * @access protected
 * @var array
 */ 
 protected $languages;

/**
 * allow searching for english, if specified language not found. 
 * 
 * @access protected
 * @var bool
 */ 
 protected $en;

/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
		
	$this->prefix = '';
	$this->owner_id = '';
	$this->include_paths = array();

	$this->lib = lib::instance();

	$this->languages = array();
	$this->en = false;

 } // end constructor

/**
 * This magic function will call get.
 * Return language value by expression. 
 * 
 * @access public
 * @param string $item
 * @return mixed
 */ 
 public function __get($item) {
	return $this->get($item);
 }
 
/**
 * Magic function will return language prefix.
 * 
 * @access public
 * @return string
 */
 public function __toString() {
	return $this->prefix;
 }
 
/**
 * Return instance of this object
 *
 * @access public
 * @param string $prefix
 * @param string $owner_id
 * @param bool $search_for_en_
 * @return mixed object | bool
 */
 public function instance($prefix, $language_paths, $owner_id, $search_for_en_ = false) {

	if(trim($prefix) == '') {
		trigger_error('Loading language for "'.$owner_id.'" in ' . 
						__CLASS__ . '::' . __FUNCTION__ . 
						'(). Language prefix is empty!', E_USER_WARNING);
		$prefix = 'en';
	}
	
	if(trim($owner_id) == '') {
		trigger_error('Loading language in ' . __CLASS__ . '::' . __FUNCTION__ . 
						'(). Language owner id is empty!', E_USER_WARNING);
		$owner_id = 'application';
	}

	$this->__construct();
	
	$this->include_paths = $language_paths;
	
	$this->prefix = $prefix;
	$this->owner_id = $owner_id;
	$this->en = $search_for_en_;
	
	return clone $this;

 } // end func instance

/**
 * Get language value by item
 * return {item}, if value not found.
 * This function supports optional arguments for other 
 * values in language expression.
 * 
 * Example 1. call with some count of arguments.
 * $this->language->get('say', 'hello', 'word'); 
 * // where 'say' = 'Said - %s %s!' and will return "Said - Hello world!".
 * 
 * Example 2. call with array of values.
 * $this->language->get('say', array('hello', 'word')); 
 * // where 'say' = 'Said - %s %s!' and will return "Said - Hello world!". 
 * 
 * @access public
 * @param string $item
 * @return string
 */ 
 public function get($item) {

 	if(trim($item) == '') {
 		trigger_error('Translation expression is empty for owner id "' . 
 						$this->owner_id.'". language prefix=' . 
 						$this->prefix , E_USER_NOTICE);
 						
 		return '{'.$item.'}';
 	}

 	$return_val = '';
 	
 	// 1. Load custom required language file if not loaded
 	foreach($this->include_paths as $path) {
 		
 		// load file if not loaded
 		if(!isset($this->languages[$this->prefix . '_' . $path])) {
 			$this->file_load($this->prefix, $path);
 		}
 		
 		// Return item if exists from custom file
		if($this->languages[$this->prefix . '_' . $path]->item_exists($item)) {
			$return_val = $this->languages[$this->prefix . '_' . 
													$path]->item_get($item);
			break;
 		}
 		
 	} // end foreach
 	
 	// 2. Search for English if enabled
 	if($this->prefix != 'en' and $this->en == true and $return_val == '') {
 	
 		// Load English
		foreach($this->include_paths as $path) {
 		
 			// load file if not loaded
 			if(!isset($this->languages['en_' . $path])) {
				$this->file_load('en', $path);
 			}
 		
			// Return item if exists from english file
			if($this->languages['en_' . $path]->item_exists($item)) {
				$return_val = $this->languages['en_' . $path]->item_get($item);
				break; 
 			}

		} // end foreach
 	} // end if search for English

 	if($return_val != '') {
 		
 		// manage arguments
 		if(func_num_args() > 1) {
 		
 			$l_args = func_get_args();
 			unset($l_args[0]);
 		
 			if(is_array($l_args[1])) {
 				$l_args = $l_args[1];
 			}
 			
 			$freturn_val = vsprintf($return_val, $l_args);
 			
 			if($freturn_val != '') {
 				return $freturn_val;
 			} else {
 				
 				trigger_error('Too few arguments in function vsprintf() for ' . 
 							  'translation expression `' . $item . '` in ' . 
 							  'language ('.$this->prefix.'). Owner id "' . 
 							  $this->owner_id.'" ', E_USER_NOTICE);
 				
 				return '{'.$return_val.'}';
 			}

 		} else {
 			return $return_val;
 		}
 		
 	} else {
 	
 		trigger_error('Translation expression `' . $item . '` not found in ' . 
 		 			  'language file. Owner id "' . $this->owner_id . '". ' . 
 		 			  'language prefix='.$this->prefix, E_USER_NOTICE);
 		
 		return '{'.$item.'}';
 	}
 	 	
 } // end func get

/**
 * Load language file.
 * 
 * @access protected
 * @param string $prefix
 * @param string $path
 * @return void
 */ 
 protected function file_load($prefix, $path) {

 	$this->languages[$prefix . '_' . $path] = 
			$this->lib->ini->instance($path .  
										$prefix . '.ini', NULL, true);
 
 } // end func file_load

/**
 * Return language prefix
 * Ex: en | ru
 *
 * @access public
 * @return string
 */	
 public function prefix() {
	return $this->prefix;
 }
	
/* End of class language_lib */
}

/* End of file */
?>