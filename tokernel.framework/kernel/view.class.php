<?php
/**
 * toKernel - Universal PHP Framework.
 * Main View class for addons.
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
 * @subpackage kernel
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2013 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.3.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * addon view
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class view {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $__lib_;
 
/**
 * Main Application object for 
 * accessing app functions from this class
 * 
 * @var object
 * @access protected
 */ 
 protected $app;

/**
 * Addon configuration object
 * 
 * @access protected
 * @var object
 */ 
 protected $config;
  
/**
 * Addon log instance
 * 
 * @var object
 * @access protected
 */ 
 protected $log; 

/**
 * Addon language
 * 
 * @access protected
 * @var object
 */ 
 protected $language;
  
/**
 * Buffer (html content)
 * 
 * @access private
 * @var string
 */ 
 protected $_buffer = NULL;
  
/**
 * Mixed values
 * 
 * @access protected
 * @var array
 */
 protected $values = array();
 
/**
 * Mixed variables
 * 
 * @access protected
 * @var array
 */ 
 protected $vars = array();
 
/**
 * View file with full path
 * 
 * @access protected
 * @var string
 */
 protected $file = ''; 
 
/**
 * View's owner addon id
 * 
 * @access protected
 * @var string
 */ 
 protected $id = '';
 
/**
 * Class construcor
 * 
 * @access public
 * @param string $file
 * @return bool
 */
 public function __construct($file, $id_addon, ini_lib $config, 
 							log_lib $log, language_lib $language, $params) {
 	
 	$this->lib = lib::instance();
	$this->app = app::instance();
	
	$this->config = $config;
	$this->log = $log;
    $this->language = $language;
	
	$this->file = $file;
	$this->id = $id_addon;
	
	$this->values = $params;
 } // end constructor

/**
 * Class destructor
 * 
 * @access public
 * @return void
 */
 public function __destruct() {
 	unset($this->_buffer);
 	unset($this->values);
 	unset($this->vars);
 	unset($this->file);
 	unset($this->id);
 } // end destructor
 
/**
 * Return variable by name 
 * 
 * @access public
 * @param string $var
 * @return mixed
 */
 public function __get($var) {
	if(isset($this->vars[$var])) {
 		return $this->vars[$var];
	} else {
		return false;
	}
 } // end func __get

/**
 * Set variable by name 
 * 
 * @access public
 * @param string $var_name
 * @param mixed $var_value
 * @return void
 */
 public function __set($var_name, $var_value) {
 	$this->vars[$var_name] = $var_value;
 } 

/**
 * Unset a variable by name
 * 
 * @access public
 * @param string $var
 * @return mixed
 * @since 1.3.0 
 */
 public function __unset($var) {
	
	if(isset($this->vars[$var])) {
		unset($this->vars[$var]);
	}
	
 } // end func __unset 
 
/**
 * Check whether a variable has been defined 
 * 
 * @access public
 * @param string $var
 * @return mixed
 * @since 1.3.0
 */
 public function __isset($var) {
	
	if(isset($this->vars[$var])) {
		return true;
	} else {
		return false;
	}
	
 } // end func __isset 
 
/**
 * Reset all values
 *
 * @access public
 * @return void
 * @since 1.2.0 
 */
 public function reset() { 
	$this->vars = array();
	$this->values = array();
	$this->_buffer = NULL;
 }	
 
/**
 * Call for accessing addon functions in this view 
 * 
 * @access public
 * @param string $func
 * @param array $arguments
 * @return void
 */ 
 //public function __call($func, $args) {
 	//return call_user_func_array(array($this->lib->addons->$this->id_addon, $func), $args);
 //}
 
/**
 * Return id_addon of this view
 * 
 * @access public
 * @return string
 */
 public function id_addon() {
	return $this->id_addon;
 }

/**
 * Set value to replace
 * 
 * @access public
 * @param string | array $val_name
 * @param $string $value = NULL
 * @return void
 */ 
 public function set_value($val_name, $value = NULL) {
 	
 	if(is_array($val_name)) {
 		foreach($val_name as $n => $v) {
 			$this->values[$n] = $v;
 		}
 	} else {
 		$this->values[$val_name] = $value;
 	}
 } // end func add_value 

/**
 * Get value by name
 * 
 * @access public
 * @param string $val_name
 * @return string
 */ 
 public function get_value($val_name) {
 	
 	if(is_set($this->values[$val_name])) {
 		return $this->values[$val_name];
 	} else {
 		return false;
 	}	
 	
 } // end func get_value 

/**
 * Call Interpreter and echo the content.
 * 
 * @access public
 * @return void
 */   
 public function show() {
	echo $this->run();
 }
 
/**
 * Interprete view file and return buffer
 * 
 * @access public
 * @param array $values
 * @return string
 */  
 public function run($values = array()) {
 	
 	ob_start();
	
	require($this->file);
   	$this->_buffer .= ob_get_contents();
	ob_end_clean();
   	
	/* Merge this values with new values */
	$str_vals = array_merge($this->values, $values);
	
	/* Replace all values */
	if(is_array($str_vals)) {
		foreach($str_vals as $vk => $value) {
   			$this->_buffer = str_replace('{.'.$vk.'}', $value, $this->_buffer);
   		}
	}
   	
    tk_e::log_debug('End for addon/module - "' . $this->id . '". File - "' . 
					basename($this->file) . '".', 
					get_class($this) . '->' . __FUNCTION__);
	
   	return $this->_buffer;
   	
 }
 
/**
 * Get language value by expression
 * Return language prefix if item is null.
 * 
 * @final
 * @access public
 * @param string $item
 * @return string
 */ 
 final public function language($item = NULL) {
 	
 	if(is_null($item)) {
 		return $this->lib->url->prefix();
 	}
 	
 	if(func_num_args() > 1) {
 		$l_args = func_get_args();
 	
 		unset($l_args[0]);
 		
 		if(is_array($l_args[1])) {
 			$l_args = $l_args[1];
 		}
 		
 		return $this->language->get($item, $l_args);
 	}
 	
 	return $this->language->get($item);
 	
 } // end func language  

/**
 * Return addon configuration values
 * 
 * @final
 * @access public
 * @param string $item
 * @param string $section
 * @return mixed
 */
 final public function config($item = NULL, $section = NULL) {
	return $this->config->item_get($item, $section);
 } // end func config 
 
/* End of class view */
}
 
/* End of file */
?>