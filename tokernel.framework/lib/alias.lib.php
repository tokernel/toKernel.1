<?php
/**
 * toKernel - Universal PHP Framework.
 * Library for working with application aliases.
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
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.2.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @todo       Create functions - set([ also this function will update existing])
 * 			   But please note! will check alias name if not equal with backend_dir 
 * 			   Create functions - remove(), 
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * alias_lib class library 
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class alias_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;

/**
 * Aliases object 
 * 
 * @var object
 * @access protected
 */  
 protected $aliases;
 
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */
 public function __construct() {
	$this->lib = lib::instance();
	
	$alias_file = TK_CUSTOM_PATH . 'config' . TK_DS . 'aliases.ini';
	
	/* Load alias */
	$this->aliases = $this->lib->ini->instance($alias_file);
	
 } // end constructor
	
/**
 * Return alias array by name.
 * This function available for http and cli run modes. 
 * NOTE: Aliasing configuration file aliases.ini, located 
 * only in application/custom_dir/config/aliases.ini.
 * 
 * @access public
 * @param string $alias_name
 * @return mixed array | bool
 */ 
 public function get($alias_name) {

 	if($this->aliases->section_exists($alias_name)) {
 		return $this->aliases->section_get($alias_name);
 	} else {
 		return false;
 	}
	
} // end func alias

/**
 * Check is alias exists
 * Alias is the section name in the aliases.ini
 * loaded in $this->aliases
 * 
 * @access public
 * @param string
 * @return bool
 * @since 1.1.0
 */
 public function exists($alias_name) {

 	if($this->aliases->section_exists($alias_name)) {
 		return true;
 	} else {
 		return false;
 	} 
 		
 } // end func exists

/**
  * Get all aliases array
  *
  * @access public
  * @return array
  * @since 1.2.0
  */
 public function get_all() {
    return $this->aliases->sections();
 } // end func get_all

/* End of class alias_lib */
}

/* End of file */
?>