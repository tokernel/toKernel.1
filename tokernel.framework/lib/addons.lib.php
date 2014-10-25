<?php
/**
 * toKernel - Universal PHP Framework.
 * Addons class library for accessing/loading addons.
 * This class required for toKernel framework.
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
 * @copyright  Copyright (c) 2013 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.5
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * addons_lib class library 
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class addons_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;
 
/**
 * Array of loaded addons (objects).
 * 
 * @access protected
 * @staticvar array
 */	
 protected static $loaded_addons = array();
 
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
	$this->lib = lib::instance();
 } // end of func __construct

/**
 * This function will call 'load'
 * 
 * @final
 * @access public
 * @param string $id_addon
 * @return mixed object | bool
 */ 
 final public function __get($id_addon) {
 	return self::load($id_addon);
 }

/**
 * Load and return addon object by id.
 * 
 * @access public
 * @param string $id_addon
 * @param array $params
 * @return mixed object | bool
 */ 
 public function load($id_addon, $params = array()) {

	if(trim($id_addon) == '') {
		trigger_error('Called addons->load() with empty id_addon!', 
								E_USER_ERROR);
		return false;
    }

	/* Return addon object, if it is already laoaded */
	if(array_key_exists($id_addon, self::$loaded_addons)) {
		return self::$loaded_addons[$id_addon];
	}
	
	/*
	 * Note: Addon id will be equal with addon directory.
	 */
	
	/* Define addon lib file in application custom dir */
	$app_addon_lib = TK_CUSTOM_PATH . 'addons' . TK_DS .  
	                                  $id_addon . TK_DS . 'lib' . TK_DS .  
	                                  $id_addon . '.addon.php';
	
	/* Define addon lib file in framework dir */
	$tk_addon_lib = TK_PATH . 'addons' . TK_DS .  
							   $id_addon . TK_DS . 'lib' . TK_DS .  
	                           $id_addon . '.addon.php';
	                           
 	/* 
 	 * Case 0. 
 	 * both files not exists
 	 */ 
	if(!is_file($tk_addon_lib) and !is_file($app_addon_lib)) {
		
		trigger_error('Library file "' . $tk_addon_lib . '" for addon "' . 
						$id_addon.'" not exists!', E_USER_WARNING);
		return false;
	}
	
	/* Addon configuration file */
	$addon_custom_config_file = TK_CUSTOM_PATH . 'addons' . TK_DS . $id_addon . 
						  		TK_DS . 'config' . TK_DS . 'config.ini';  
	
	$addon_tk_config_file = TK_PATH . 'addons' . TK_DS . $id_addon . 
						  	TK_DS . 'config' . TK_DS . 'config.ini';
	
	$loaded_from_custom = NULL;
	
	/*
	 * Case 1. 
	 * addon lib exists only in tokernel
	 */ 
	if(is_file($tk_addon_lib) and !is_file($app_addon_lib)) {
		
		require($tk_addon_lib);
		$addon_path = TK_PATH . 'addons' .  TK_DS . $id_addon . TK_DS;
		$loaded_from_custom = false;
		$addon_class = $id_addon . '_addon';
		$addon_config_file = $addon_tk_config_file;
	}

 	/*
 	 * Case 2.
 	 * addon lib exists only in application custom directory
 	 */ 
	if(!is_file($tk_addon_lib) and is_file($app_addon_lib)) {
		
		require($app_addon_lib);
		$addon_path = TK_CUSTOM_PATH . 'addons' . TK_DS . $id_addon . TK_DS;
		$loaded_from_custom = true;
		$addon_class = $id_addon . '_addon';
		$addon_config_file = $addon_custom_config_file;
	}
	
	/* 
	 * Case 3. 
	 * both files exists. inheriting.
	 */ 
	if(is_file($tk_addon_lib) and is_file($app_addon_lib)) {
		
		require($tk_addon_lib);
		require($app_addon_lib);
		$loaded_from_custom = true;
		$addon_class = $id_addon . '_ext_addon';
		$addon_path = TK_CUSTOM_PATH . 'addons' . TK_DS . $id_addon . TK_DS;
		$addon_config_file = $addon_custom_config_file;
	}
	
	/* Check addon class which must be equal with addon id */
	if(!class_exists($addon_class)) {
		trigger_error('Class ' . $addon_class . ' not exists in addon ' . 
						$id_addon . ' library!', E_USER_WARNING);
		return false;
	}
	
	/* Check configuration file {id_addon}.addon.ini */
	if(!is_readable($addon_config_file)) {
		trigger_error('Configuration file "' . $addon_config_file . 
					  '" not exists or not readable for loading addon "' . 
					  $id_addon . '"!', E_USER_ERROR);
	}
	
	/* Load addon configuration object */
	$addon_config = $this->lib->ini->instance($addon_config_file, NULL, false);
	
	/* Load new addon object into loaded addons array */
	self::$loaded_addons[$id_addon] = new $addon_class($params, $addon_config);
	
	if($loaded_from_custom == true) {
		tk_e::log_debug('"'.$id_addon.'" from app path ' . 
						' with params - "' . implode(', ', $params) . '"', 
						__CLASS__.'->'.__FUNCTION__);
	} else {
		tk_e::log_debug('"'.$id_addon.'" from framework path ' . 
						' with params - "' . implode(', ', $params) . '"', 
						__CLASS__.'->'.__FUNCTION__);
	}
					
	/* Return addon object */
	return self::$loaded_addons[$id_addon];
	
} // end func load 

/**
 * Return array of loaded addons (names only), 
 * if variable id_addon is null. Else, 
 * return status of addon as bool. 
 * 
 * @access public
 * @param string $id_addon
 * @return mixed array | bool
 */
 public function loaded($id_addon = NULL) {
	
 	/* Return array with names of loaded addons */
 	if(is_null($id_addon)) {
 		return array_keys(self::$loaded_addons);
 	}
 	
 	/* Return status of addon */
 	if(array_key_exists($id_addon, self::$loaded_addons)) {
		return true;
	} else {
		return false;
	}

 } // end of func loaded

/**
 * Return is addon exists.
 * This function will check addon main library and directory name.
 * Call this function with defined $custom_only as true to check 
 * only in app custom directory.
 * 
 * @access public
 * @param string $id_addon
 * @param bool $custom_only
 * @return bool
 */
 public function exist($id_addon, $custom_only = false) {
 	if(trim($id_addon) == '') {
 		return false;
 	}
 	
	$addon_dir = TK_CUSTOM_PATH . 'addons' . TK_DS . $id_addon;
	$addon_lib = TK_CUSTOM_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 
								  'lib' . TK_DS . $id_addon . '.addon.php';
 	
 	if(is_dir($addon_dir) and is_file($addon_lib)) {
 		/* Addon exist in application custom path */
 		return true;
 	}
 	
 	if($custom_only == true) {
 		/* check only for custom */
 		return false;
 	}
 	
	$addon_dir = TK_PATH . 'addons' . TK_DS . $id_addon;
	$addon_lib = TK_PATH . 'addons' . TK_DS . $id_addon . TK_DS . 
						   'lib' . TK_DS . $id_addon . '.addon.php';
 	
 	if(is_dir($addon_dir) and is_file($addon_lib)) {
 		/* Addon exist in framework path */
 		return true;
 	}
 	
 	/* Addon not exists */
 	return false;

 } // end func exist

 public function all($tk_only = false) {
 	$tk_addons = $this->lib->file->ls(TK_PATH . 'addons', 'd');
 	
 	if($tk_only == true) {
 		return $tk_addons; 
 	}
 	
 	$app_addons = $this->lib->file->ls(TK_CUSTOM_PATH . 'addons', 'd');

 	$addons = array_merge($tk_addons, $app_addons);
 	
	sort($addons);
	
 	return $addons;
 } 
 
/* End of class addons_lib */ 
}

/* End of file */
?>