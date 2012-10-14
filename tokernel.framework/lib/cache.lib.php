<?php
/**
 * toKernel - Universal PHP Framework.
 * Library for working with cache files.
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
 * @version    1.0.0
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
 * Cache file extension.
 * 
 * @access protected
 * @var string
 */
 protected $ext = '';
 
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
	
 	$this->lib = lib::instance();
    $this->app = app::instance();
    
    /* 
     * Get cache expiration.
     */
    $ce_ = $this->app->config('cache_expiration', 'CACHING');
    
    if($this->lib->valid->digits($ce_) or $ce_ == '-1') {
    	$this->cache_expiration = $ce_;
    }

    /* Get cache file extension */
    $this->ext = $this->app->config('cache_file_extension', 'CACHING');
    
 } // end func __construct 
	
/**
 * Return cache file expiration status.
 * Expiration time defined in application configuration 
 * file tokernel.ini defined in [CACHING] section.
 * 
 * @access public
 * @param string $file_id
 * @param integer $minutes
 * @return bool
 */ 
 public function expired($file_id, $minutes = NULL) {
	
 	/* Set cache file path/name with extension */
	$file = $this->filename($file_id);
	
	if(!is_file($file)) {
		return true;
	}
	
	/* -1 assume that the cache never expire */ 
	if($minutes == '-1') {
		return false;
	}
	
	/* 
	 * if minutes is not set, then set 
	 * minutes from app configuration 
	 */
	if(!$this->lib->valid->digits($minutes)) {
		$minutes = $this->cache_expiration;
	}
	
	/* Set seconds */
	$exp_sec = $minutes * 60;
	
	/* Get file time */
    $file_time = filemtime($file);

    /* Return true if cache expired */
	if(time() > ($exp_sec + $file_time)) {
		$this->remove($file_id);
		return true;
	} else { 
		return false;
	}

 } // end func expired
 
/**
 * Read and return cached file content if exist.
 * Return false if cache is expired.
 * 
 * @access public
 * @param string $file_id
 * @param integer $minutes
 * @return mixed string | bool
 */ 
 public function get_content($file_id, $minutes = NULL) {
	
 	/* Return false if expired */
	if($this->expired($file_id, $minutes) === true) {
		return false;
	}
	
	/* Set cache file path/name with extension */
	$file = $this->filename($file_id);

	/* Return false if file is not readable */ 
	if(!is_readable($file)) {
		return false;
	}

	return file_get_contents($file);
	
 } // end func get_content
 
/**
 * Write cache content if expired.
 * 
 * @access public 
 * @param string $file_id
 * @param string $buffer
 * @param integer $minutes
 * @return bool
 */ 
 public function write_content($file_id, $buffer, $minutes = NULL) {

 	/* Try to put content if cache expired */
	if($this->expired($file_id, $minutes)) {
		
		/* Set cache file path/name with extension */
		$file = $this->filename($file_id);

		if(@file_put_contents($file, $buffer)) {
			return true;
		} else {
			trigger_error('Can not write cache content: '. $file . ' (ID: ' . 
						$file_id . ')', E_USER_WARNING);
			return false;
		} 
		
	} // end if expired
		
	return true;
} // end func write_content

/**
 * Remove cache file.
 * 
 * @access public
 * @param string $file_id
 * @return bool
 */ 
 public function remove($file_id) {
	
 	/* Set cache file path/name with extension */
	$file = $this->filename($file_id);
	
	if(is_writable($file)) {
		unlink($file);
		return true;
	} else {
		return false;
	}
	
 } // end func remove
 
/**
 * Clean all cache files
 * 
 * @access public
 * @return integer deleted files count
 */ 
 public function clean_all() {
    
 	$del_files_count = 0;
 	
 	$cache_dir = $this->app->config('cache_dir', 'CACHING');
 	if(!is_writable($cache_dir)) {
 		return false;
 	}
 	
 	/* create a handler for the directory */
    $handler = opendir($cache_dir);

    /* open directory and walk through the filenames */
    while($file = readdir($handler)) {

      /*
       * if file isn't this directory or its parent, 
       * also if it have .cache extension, then remove it 
       */
      if($file != "." && $file != ".." && pathinfo($file, PATHINFO_EXTENSION) 
               == $this->ext) {
         
		if(unlink($cache_dir . $file)) {
			$del_files_count++;
        }
      } // end if cache file

    } // end while

    // tidy up: close the handler
    closedir($handler);

    return $del_files_count;
 	 	
 } // end func clean_all
 
/**
 * Make cache file name with path and extendion.
 * 
 * @access public
 * @param string $string
 * @return mixed string | bool
 */
 public function filename($string) {
	if(trim($string) == '') {
		return false; 
	}
	
 	return $this->app->config('cache_dir', 'CACHING') . 
 							md5($string) . '.' . $this->ext;
 			
 } // end func filename
 
/* End of class cache_lib */ 
}

/* End of file */
?>