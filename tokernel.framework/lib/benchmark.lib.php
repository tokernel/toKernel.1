<?php
/**
 * toKernel - Universal PHP Framework.
 * Benchmark library for application debugginfg.
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
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * benchmark_lib class library 
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class benchmark_lib {

/**
 * Benchmark buffer array.
 * 
 * @access protected
 * @var array
 */	
 protected $buffer;
	
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */
 public function __construct() {
	$this->buffer = array();
 }

/**
 * Start point to benchmark
 * 
 * @access public
 * @param string $owner
 * @return void
 */
 public function start($owner) {
	$this->buffer[$owner]['start'] = round(microtime(true), 3);
 }

/**
 * End benchmarking
 * 
 * @access public
 * @param string $owner
 * @return mixed float | bool
 */ 
 public function end($owner) {

	if(!isset($this->buffer[$owner]['start'])) {
		return false;
	}
		
	$this->buffer[$owner]['end'] = round(microtime(true), 3);
	$this->buffer[$owner]['duration'] = round(
		$this->buffer[$owner]['end'] - $this->buffer[$owner]['start'] , 3);
		
	return $this->buffer[$owner]['duration'];

 } // end func end
	
/**
 * Return benchmark duration between two owners
 * 
 * @access public
 * @param string $owner_start
 * @param string $owner_end
 * @return mixed float | bool
 */
 public function between($owner_start, $owner_end) {
	
 	/* Return false, if owners not defined */
 	if(!isset($this->buffer[$owner_start]['start']) or 
 		!isset($this->buffer[$owner_end])) {
		
 		return false;
	}
	
	/* Define and return duration, if second owner's end not defined */
	if(!isset($this->buffer[$owner_end]['end'])) {
		return round(microtime(true), 3) - $this->buffer[$owner_start]['start'];
	}
		
	return $this->buffer[$owner_end]['end'] - 
		$this->buffer[$owner_start]['start'];
 
 } // end func between
	
/**
 * Return elapsed time by owner name
 * 
 * @access public
 * @param string $owner
 * @return mixed float | bool
 */ 
 public function elapsed($owner) {

 	if(!isset($this->buffer[$owner]['start'])) {
		return false;
	}

	/* Define and return duration if not defined */
	if(!isset($this->buffer[$owner]['duration'])) {
		return round(
				round(microtime(true), 3) - $this->buffer[$owner]['start'], 3);
	}
		
	return $this->buffer[$owner]['duration'];

 } // end func elapsed
	
/**
 * Set some string value for owner
 * This function may help us to debug some data.
 * For example, we can set mysql query string 
 * and get/show it in debug box.
 * 
 * @access public
 * @param string $owner
 * @param string $item
 * @param mixed $value
 * @return bool
 */
 public function set($owner, $item, $value) {

 	if(trim($owner) == '' or trim($item) == '') {
		return false;
	}
	
	$this->buffer[$owner][$item] = $value;
	return true;

 } // end func set
	
/**
 * Get benchmar value
 * Return total benchmark buffer if owner is not set.
 * 
 * @access public
 * @param string $owner = NULL
 * @return mixed array | bool
 */
 public function get($owner = NULL) {
 	
	if(is_null($owner)) {
		return $this->buffer;
	}
		
	if(!isset($this->buffer[$owner])) {
		return false;
	}

	return $this->buffer[$owner];
 } // end func get
	
/* End of class benchmark_lib */
}

/* End of file */
?>