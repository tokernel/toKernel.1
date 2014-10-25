<?php
/**
 * toKernel - Universal PHP Framework.
 * Memcache - library for caching with memcache.
 * System/Memcached and PHP Memcache extension required.
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
 * @version    1.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.3.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * memcache_lib class library.
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class memcache_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;
 
/**
 * Main Application object for 
 * accessing app functions from this class
 * 
 * @var object
 * @access protected
 */ 
 protected $app;

/**
 * Cache expiration by minutes.
 * 
 * xxx mintes
 * 0 disabled
 * -1 never expire
 * 
 * @access protected
 * @var integer
 */ 
 protected $cache_expiration = 0;

/**
 * Memcache host 
 * 
 * @access protected
 * @var string
 */ 
 protected $host;

/**
 * Memcache port
 * 
 * @access protected
 * @var int
 */ 
 protected $port;
 
/**
 * Memcache object
 * 
 * @access protected
 * @var obj 
 */ 
 protected $memcache = NULL;
 
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct($config = array()) {
	
 	$this->lib = lib::instance();
    $this->app = app::instance();
    
    /* Set cache expiration */
	if(isset($config['cache_expiration'])) {
		$ce_ = $config['cache_expiration'];
	} else {
		$ce_ = $this->app->config('cache_expiration', 'CACHING');
	}
    
    if($this->lib->valid->digits($ce_) or $ce_ == '-1') {
    	$this->cache_expiration = $ce_;
    }

	/* Set connection values */
	$this->host = $this->app->config('memcache_host', 'CACHING');
	$this->port = $this->app->config('memcache_port', 'CACHING');
    
	$this->connect();
    
 } // end func __construct 
 
 /**
  * Return cloned copy of this object
  *
  * @access public
  * @return object
  * @since 2.0.0
  */
 public function instance($config = array()) {
 	
	$obj = clone $this;
	$obj->__construct($config);
	
	return $obj;
	
 } // End func instance
 
/**
 * Connect to memcache server
 * 
 * @access protected
 * @return void
 */ 
 protected function connect() {
	 
	 if(!is_null($this->memcache)) {
		 return true;
	 }
	 
	 $this->memcache = new Memcache;
	 
	 if(!$this->memcache->connect($this->host, $this->port)) {
		 trigger_error('Could not connect to memcache server.', E_USER_ERROR);
	 }
	 
 } // End func connect
	
/**
 * Return cache file expiration status.
 * Expiration time defined in application configuration 
 * file application.ini defined in [CACHING] section.
 * 
 * @access public
 * @param string $id
 * @return bool
 */ 
 public function expired($id) {
	
	$content = $this->get_content($id); 
	
	if(!$content) {
		return false;
	} else {
		return true;
	}
 	
 } // end func expired
 
/**
 * Return cached content if exists or not expired.
 * Else, return false;
 * 
 * @access public
 * @param string $id
 * @return mixed string | bool
 */ 
 public function get_content($id) {
	
 	$content = $this->memcache->get($id);
	
	if(!$content) {
		return false;
	}
	 
 	return $content;
	
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

 	if(is_null($minutes)) {
		$minutes = $this->cache_expiration;
	}
	
	if($minutes == '-1') {
		$minutes = 0;
	} else {
		$minutes = $minutes * 60;
	}

 	return $this->memcache->set($id, $buffer, false, $minutes);
	 
} // end func write_content

/**
 * Remove cache by id.
 * 
 * @access public
 * @param string $id
 * @return bool
 */ 
 public function remove($id) {
	
 	return $this->memcache->delete($id);
	
 } // end func remove
 
/**
 * Clean all cache
 * 
 * @access public
 * @return bool
 */ 
 public function clean_all() {
    
 	return $this->memcache->flush();
 	 	
 } // end func clean_all
 
/**
 * Get cache statistics 
 * 
 * @access public
 * @return array
 * @since 1.1.0
 */ 
 public function stats() {
	 return $this->memcache->getStats();
 } // End func stats
 
/* End of class cache_lib */ 
}

/* End of file */
?>