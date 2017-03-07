<?php
/**
 * toKernel - Universal PHP Framework.
 * System class library 
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
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @todo	   add more functions to this class
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * system_lib class
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class system_lib {

/**
 * Loaded apache modules
 * 
 * @access protected
 * @var array
 */	
 protected $apache_modules_loaded;
 
/**
 * Loaded PHP extensions
 * 
 * @access protected
 * @var array
 */	
 protected $php_extensions_loaded; 
 
/**
 * Return Apache module status if $mod_name defined, 
 * else return all loaded modules.
 * Please note: in some servers this function will return false.
 * 
 * @access public
 * @param string $mod_name
 * @return mixed
 */ 
 public function apache_modules_loaded($mod_name = NULL) {

	if(!function_exists('apache_get_modules')) {
		trigger_error('Function `apache_get_modules()` not exists!', 
							E_USER_WARNING);
		return false;
	}
		
	if(!isset($this->apache_modules_loaded)) {
		$this->apache_modules_loaded = apache_get_modules();
	}
	
	if(is_null($mod_name)) {
		return $this->apache_modules_loaded;
	}
	
    return in_array($mod_name, $this->apache_modules_loaded);
    
 } // end func apache_modules_loaded 

/**
 * Return PHP extension status if $ext_name defined, 
 * else return all loaded extensions.
 * Please note: in some servers this function will return false.
 * 
 * @access public
 * @param string $ext_name
 * @return mixed
 */ 
 public function php_extensions_loaded($ext_name = NULL) {

	if(!function_exists('get_loaded_extensions')) {
		trigger_error('Function `get_loaded_extensions()` not exists!', 
							E_USER_WARNING);
		return false;
	}

	if(!isset($this->php_extensions_loaded)) {
		$this->php_extensions_loaded = get_loaded_extensions();
	}
	
	if(is_null($ext_name)) {
		return $this->php_extensions_loaded;
	}
	
    return in_array($ext_name, $this->php_extensions_loaded);
    
 } // end func php_extensions_loaded  
 
/**
 * Return OS bits
 * 
 * @access public
 * @return integer
 */
 public function os_bits() {
 	
 	switch (true) {
 		case (0x7FFF > (int)(0x7FFF+1)): // 2^15-1
 			return 16;
 			break;
 		case (0x7FFFFFFF > (int)(0x7FFFFFFF+1)): // 2^31-1
 			return 32;
 			break;
 		case (0x7FFFFFFFFFFFFFFF > (int)(0x7FFFFFFFFFFFFFFF+1)): // 2^63-1
 			return 64;
 			break;
 		case (0x7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF > (int)(0x7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF+1)): // 2^127-1
 			return 128;
 			break;
 		default:
 			return 8;
 			break;
 	}
 	
 } // end func os_bits
 
/* End of class system_lib */
}

/* End of file */
?>