<?php
/**
 * toKernel - Universal PHP Framework.
 * Content caching library.
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
 * @version    3.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * cache_lib class library.
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class cache_lib {

/**
 * Main Application object for 
 * accessing app functions from this class
 * 
 * @var object
 * @access protected
 */ 
 protected $app;

/**
 * Cache library object 
 * 
 * @access protected
 * @var object
 */ 
 protected $cache;
 
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct($config = array()) {
	
 	$this->app = app::instance();
    $this->cache = $this->load($config);
	
 } // end func __construct 
 
 /**
  * Return cloned copy of this object
  *
  * @access public
  * @return object
  * @since 2.0.0
  */
 public function instance($config = array()) {
 	
	$obj = $this->load($config);
	return $obj->instance($config);
		
 } // End func instance
	
/**
 * Load cache object
 * 
 * @access protected
 * @param array $config
 * @return void 
 */
 protected function load($config) {
	 
	/* Load cache library */
	if(isset($config['cache_lib'])) {
		$cache_lib = $config['cache_lib'];
	} else {
		$cache_lib = $this->app->config('cache_lib', 'CACHING');
	}
	
	$lib_file = dirname(__FILE__) . TK_DS . 'cache' . TK_DS . $cache_lib . '.lib.php';
	
	if(!file_exists($lib_file)) {
		trigger_error('Cache library `'.$lib_file.'` not exists!', E_USER_ERROR);
	}
	
	require_once($lib_file);
	
	$obj_name = $cache_lib . '_lib';
	$obj = new $obj_name($config);
	
	return $obj;
	 
 } // End func load
 
 
/**
 * Return cache file expiration status.
 * Expiration time defined in 
 * file: application/config/application.ini 
 * section: [CACHING]
 * 
 * @access public
 * @param string $id
 * @param integer $minutes
 * @return bool
 */ 
 public function expired($id, $minutes = NULL) {
	
	return $this->cache->expired($id, $minutes); 
 	
 } // end func expired
 
/**
 * Return cached content if exists or not expired.
 * Else, return false;
 * 
 * @access public
 * @param string $id
 * @param integer $minutes
 * @return mixed string | bool
 */ 
 public function get_content($id, $minutes = NULL) {
	 
	return $this->cache->get_content($id, $minutes);
	
 } // end func get_content
 
/**
 * Write cache content.
 * 
 * @access public 
 * @param string $id
 * @param string $buffer
 * @param integer $minutes
 * @return bool
 */ 
 public function write_content($id, $buffer, $minutes = NULL) {

	return $this->cache->write_content($id, $buffer, $minutes);
 	
} // end func write_content

/**
 * Remove cache item by id
 * 
 * @access public
 * @param string $id
 * @return bool
 */ 
 public function remove($id) {
	
	return $this->cache->remove($id); 
 		
 } // end func remove
 
/**
 * Clean all cache
 * 
 * @access public
 * @return mixed integer | bool
 */ 
 public function clean_all() {
    
	return $this->cache->clean_all(); 
 	 	 	
 } // end func clean_all
 
/**
 * Get cache statistics 
 * 
 * @access public
 * @return array
 * @since 3.1.0
 */ 
 public function stats() {
	 return $this->cache->stats();
 } // End func stats
 
/* End of class cache_lib */ 
}

/* End of file */
?>