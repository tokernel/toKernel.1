<?php
/**
 * toKernel - Universal PHP Framework. 
 * Main library loader singleton class.
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
 * @copyright  Copyright (c) 2012 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * lib class
 *
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class lib {

/**
 * Status of this class instance
 * 
 * @staticvar object
 * @access private
 */
 private static $instance;
 
/**
 * Loaded libraries array
 * 
 * @staticvar array
 * @access private
 */
 private static $loaded_objects = array();
	
/**
 * Private constructor to prevent it being created directly
 * 
 * @final
 * @access private
 * @return void
 */
 final private function __construct() {}
        
/**
 * Class destructor
 * 
 * @access public
 * @return void
 */
 public function __destruct() {
	self::$loaded_objects = array();
 }

/**
 * Prevent cloning of the object. 
 * Issues an E_USER_ERROR if this is attempted
 * 
 * @access public
 * @return void
 */
 public function __clone() {
 	trigger_error( 'Cloning the object is not permitted (' . 
	               __CLASS__.')', E_USER_ERROR );
 } 
 
/**
 * Return result from load_lib function
 *
 * @final
 * @access public
 * @param string $object_name
 * @return object
 */ 
 final public function __get($object_name) {
	return $this->load($object_name);
 }
  
/**
 * Singleton method used to access the object
 * 
 * @static
 * @final
 * @access public
 * @return object $instance
 */
 final public static function instance() {
	if(!isset(self::$instance)) {
		 $obj = __CLASS__;
		 self::$instance = new $obj;
	}

	return self::$instance;
 } // end func instance
	
/**
 * Load library and return object.
 * Include library file from application dir if it exists,
 * else include from framework dir.
 * Return false if lib file not found in any of them.
 * 
 * @access public
 * @param string $object_name
 * @param array $params
 * @return mixed object | bool
 */
 public function load($object_name, $params = array()) {
    
 	if(trim($object_name) == '') {
 		trigger_error('Library name is empty!', E_USER_WARNING);
 		return false;
 	}
 	
 	/* Return lib object if already loaded */
	if(array_key_exists($object_name, self::$loaded_objects)) {
       return self::$loaded_objects[$object_name];
	}
	
	/* Set lib file name from application directory. */
	$app_lib_file = TK_CUSTOM_PATH . 'lib' . TK_DS . 
	                $object_name . '.lib.php';

	/* Set lib file name from framework directory */ 
	$tk_lib_file = TK_PATH . 'lib' . TK_DS . $object_name . '.lib.php';

	// ! case 0. both not exists 
	if(!is_file($tk_lib_file) and !is_file($app_lib_file)) {
		// trigger_Error
		return false;
	}

	// ! case 1. lib exists only in tokernel/lib dir.
	if(is_file($tk_lib_file) and !is_file($app_lib_file)) {
		require_once($tk_lib_file);
		$class_name = $object_name . '_lib';
	}
	
	// ! case 2. lib file exists only in application/lib dir.
	if(!is_file($tk_lib_file) and is_file($app_lib_file)) {
		require_once($app_lib_file);
		$class_name = $object_name . '_lib';
	}

	// ! case 3. Both exists, the lib is inherited.
	if(is_file($tk_lib_file) and is_file($app_lib_file)) {
		require_once($tk_lib_file);
		require_once($app_lib_file);
		$class_name = $object_name . '_ext_lib';
	}
	
	if(!class_exists($class_name)) {
		// trigger error
		return false;
	}
	
	self::$loaded_objects[$object_name] = new $class_name($params);

    return self::$loaded_objects[$object_name];

 } // end of func load

/**
 * Return array with names of loaded libs
 * if variable object_name is null. Else, 
 * return status of object as bool. 
 * 
 * @access public
 * @param string $object_name
 * @return mixed array | bool
 */
 public function loaded($object_name = NULL) {
 	
 	if(!is_null($object_name)) {
		
		/* Return status of library */
 		if(array_key_exists($object_name, self::$loaded_objects)) {
	    	return true;
		} else {
			return false;
		}
		
	} else {
		
		/* Return array with names of loaded libs */
		return array_keys(self::$loaded_objects);
		
	} // end if id_addon
 } // end func loaded
 
/* End of class lib */ 
}

/* End of file */
?>