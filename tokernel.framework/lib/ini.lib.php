<?php
/**
 * toKernel - Universal PHP Framework.
 * ini file processing library
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
 * @version    1.2.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @todo	   Create functions - sync, compare.
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * ini_lib class
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class ini_lib {
	
/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;

/**
 * Main ini array, content of ini file.
 * 
 * @access protected
 * @var array
 */ 
 protected $ini_arr;

/**
 * Loaded file name
 * 
 * @access protected
 * @var string
 */ 
 protected $file;
 
/**
 * String -end of file.
 * Ex: "; End of file. Last update 05-03-2010" 
 * 
 * @access protected
 * @var string
 */ 
 protected $end_of_file;
 
/**
 * Is file opened as new (empty).
 * 
 * @access protected
 * @var bool
 */ 
 protected $is_new_file;
 
/**
 * Comment start char.
 * 
 * @access protected
 * @var string
 */ 
 protected $comment_sign;
 
/**
 * Is file opened as readonly
 * This value will be true, if loading 
 * ini file with specified section.
 * 
 * @access protected
 * @var bool
 */ 
 protected $is_file_read_only;

/**
 * Is loaded file have sections
 * 
 * @access protected
 * @var bool
 */
 protected $have_sections;

/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
	
	$this->lib = lib::instance();
	
	$this->ini_arr = array();
	$this->file = '';
	$this->is_new_file = false;
	$this->end_of_file = '';
	$this->comment_sign = ';';
	$this->is_file_read_only = false;
	$this->have_sections = false;
	$this->end_of_file = $this->comment_sign . 
								' End of file. Last update: ' . date('d-m-Y');
 } // end constructor

/**
 * Class destructor
 * 
 * @access public
 * @return void
 */ 
 public function __destruct() {
	unset($this->ini_arr);
	unset($this->file);
	unset($this->is_new_file);
	unset($this->end_of_file);
	unset($this->comment_sign);
	unset($this->is_file_read_only);
	unset($this->have_sections);	
 } // end destructor

/**
 * Magic function. Return item from ini.
 * 
 * @access public
 * @param string $item
 * @return mixed
 */ 
 public function __get($item) {
	return $this->item_get($item);
 }

/**
 * Magic function set. set ini item value.
 * 
 * @access public
 * @param string $item
 * @param mixed $value
 * @return bool
 */ 
 public function __set($item, $value) {
	return $this->item_set($item, $value);
 }

 /**
  * Magic function isset. return, is item exists.
  * 
  * @access public
  * @param string $item
  * @return mixed
  */
 public function __isset($item) {
	return $this->item_exists($item);
 }

/**
 * Magic function unset. delete ini item.
 * 
 * @access public
 * @param string $item
 * @return mixed
 */ 
 public function __unset($item) {
	$this->item_delete($item);
 }

/**
 * Load ini file and return cloned instance of this object. 
 * 
 * @access public
 * @param string $file
 * @param string $section
 * @param bool $allow_cnif
 * @return mixed object | bool
 */
 public function instance($file, $section = NULL, $allow_cnif = false) {
	
	if(!is_readable($file) and $allow_cnif == false) {
		return false;
	}
	
	$this->__construct();
	
	if(!$this->file_load($file, $section, $allow_cnif)) {
		return false;
	}
	
	return clone $this;
 } // end func instance

/**
 * Load ini file
 * 
 * @access public
 * @param string $file
 * @param string $section
 * @param bool $allow_cnif
 * @return bool
 */ 
 public function file_load($file, $section = NULL, $allow_cnif = false) {
	
	if(!is_readable($file) and $allow_cnif == false) {
		return false;
	}
	
	if(!is_readable($file)) {
		$this->file = $file;
		$this->is_new_file = true;
		return true;
	}
	
	$lines = file($file, FILE_IGNORE_NEW_LINES);
	
	$this->file = $file;
	
	$current_section = NULL;
	
	foreach($lines as $line) {
	
	    $line = trim($line);
		
	    // Define section
	    if(substr($line, 0, 1) == '[' and substr($line, -1) == ']') {
	    	
	        // Get section name
	    	$tmp_section = trim(substr($line, 1, -1));
			
	    	if($tmp_section != '') {
	    	
	    		$current_section = $tmp_section; 
	    	
				$this->section_set($current_section);
				$this->have_sections = true;
			} else {
				$this->comment_set('; INVALID LINE: ['.$tmp_section.']');
			}
			
		// define comment	
	    } elseif($this->is_comment($line) == true) {
			
			if($this->is_end_of_file($line) == false) {
				$this->comment_set($line, $current_section);
			} 
			
		// define params
	    } else {
		  	if($line != '') {
		  		$equal_sign_pos = strpos($line, '=');
		  	
		  		if($equal_sign_pos === false) {
		  			$line = '; INVALID LINE: ' . $line;
		  			
		  			$this->comment_set($line, $current_section);
		  		} else {
		    		$key = trim(substr($line, 0, $equal_sign_pos));
		    		$value = trim(substr($line, $equal_sign_pos+1));
		    		
		    		$this->item_set($key, $value, $current_section);
		  		}
		  	} else {
		  		$this->comment_set($line, $current_section);
		  	}	
	    } 
		
	} // End foreach lines
	
	unset($lines);
	
	if(is_null($section)) {
		return true; 	
	}
		
	if(!$this->section_exists($section)) {
		return false;
	}	

	$tmp_arr = $this->ini_arr[$section];
	$this->ini_arr = array();
	$this->ini_arr[$section] = $tmp_arr;
	$this->is_file_read_only = true;
	
	return true;
	
 } // end func file_load

/**
 * Save ini file
 * 
 * @access public 
 * @param string $file
 * @param bool $overwrite
 * @return bool
 */
 public function file_save($file = NULL, $overwrite = false) {
	
	if($this->is_file_read_only == true and $overwrite == false 
													and is_null($file)) {
		return false;
	}

	if(is_file($file) and $overwrite == false) {
		return false;
	}

	if(is_null($file)) {
		$file = $this->file;
	}
	
	if($file == '') {
		return false;
	}
	
	$ini_buf = '';
	
	foreach($this->ini_arr as $key => $value) {
		
		if(!is_array($value)) {
			
			if(is_numeric($key)) {
				$ini_buf .= $value . "\n";
			} else {
				$ini_buf .= $key . '=' . $value . "\n";
			}

		} else {
			$ini_buf .= '[' . $key . ']' . "\n";
			
			foreach($value as $s_key => $s_value) {

				if(is_numeric($s_key)) {
					$ini_buf .= $s_value . "\n";
				} else {
					$ini_buf .= $s_key . '=' . $s_value . "\n";
				}
	
			} // End foreach section items
			
			//$ini_buf .= "\n";
		}

	} // end foreach
	
	$ini_buf = trim($ini_buf);
	$ini_buf .= "\n" . $this->end_of_file;
	
	if(!is_writable($file)) {
		trigger_error('File `'.$file.'` is not writable!', E_USER_WARNING);
		return false;
	} else {
		return file_put_contents($file, $ini_buf);
	}	
	
 } // end func file_save

/**
 * Destroy object values.
 * 
 * @access public
 * @return void
 */
 public function file_unload() {
	$this->__construct();
 } // end func file_unload

/**
 * Delete loaded ini file
 * 
 * @access public
 * @return bool
 */
 public function file_delete() {
	
	if(is_writable($this->file) == false) {
		return false;
	}
	
	$tmp_file = $this->file;
	$this->__construct();
	
	return unlink($tmp_file);
 } // end func file_delete

/**
 * Return loaded ini file name
 * 
 * @access public
 * @return string
 */
 public function file_name() {
	return $this->file;
 }

/**
 * Create section if not exists.
 * Items array is optional.
 *
 * @access public
 * @param string $section
 * @param array $items
 * @return bool
 */
 public function section_set($section, $items = array()) {

	$section = trim($section);

	if($this->section_exists($section) or !$this->is_valid($section)) {
		return false;
	}
	
	$this->ini_arr[$section] = array();
		
	if(count($items) > 0) {
		foreach($items as $item => $value) {
			if($this->is_valid($item)) {
				$this->item_set($item, $value, $section);
			}
		}
	}

	return true;
	
 } // end function section_set

/**
 * Return array of existing sections
 *
 * @access public
 * @return array
 */
 public function sections() {
	
 	$sections = array();

 	foreach($this->ini_arr as $section => $items) {
		if(!is_numeric($section)) {
			$sections[] = $section;
		}
	}
 	return $sections;
 	
 } // end function sections_list 
 
/**
 * Return section as array.
 * 
 * @access public
 * @param string $section
 * @return mixed array | bool
 */
 public function section_get($section) {
	
	if(isset($this->ini_arr[$section]) and is_array($this->ini_arr[$section])) {
		return $this->array_clean($this->ini_arr[$section]);
	} else {
		return false;
	} 
	
 } // end func section_get

/**
 * Check is section exists
 * 
 * @access public
 * @param string $section
 * @return bool
 */ 
 public function section_exists($section) {
	if(isset($this->ini_arr[$section]) and is_array($this->ini_arr[$section])) {
		return true;
	} else {
		return false;
	}
 } // end func section_exists

/**
 * Rename section name
 * 
 * @access public
 * @param string $section
 * @param string $new_section
 * @return bool
 */ 
 public function section_rename($section, $new_section = NULL) {
	
	$new_section = trim($new_section);
	$section = trim($section);
		
	if($this->section_exists($section) == false or 
							$this->is_valid($section) == false or 
							$this->is_valid($new_section) == false) {
		return false;
	}

	$this->ini_arr = $this->lib->data->array_key_rename($section, 
												$new_section, $this->ini_arr);
	
	return true;
		
} // end func section_rename

/**
 * Delete section
 * 
 * @access public
 * @param string $section
 * @return bool
 */
 public function section_delete($section) {
	if($this->section_exists($section)) {
		unset($this->ini_arr[$section]);
		return true;
	} else {
		return false;
	}
 } // end func section_delete

/**
 * Sort section item.
 * Foe now, this function will remoe all comments in sorted section.
 * If the argument $section_or_entire_ini_arr defined as true, then
 * function will sort entire ini file, else if section name specified, 
 * function will sort specified section.
 * 
 * @access public
 * @param mixed string | bool $section_or_entire_ini_arr
 * @return bool
 */
 public function section_items_sort($section_or_entire_ini_arr) {
	
	// case, when section name set
	if(is_string($section_or_entire_ini_arr)) {
		if($this->section_exists($section_or_entire_ini_arr) == true) {
			
			$this->ini_arr[$section_or_entire_ini_arr] = 
				$this->array_clean($this->ini_arr[$section_or_entire_ini_arr]);
				
			ksort($this->ini_arr[$section_or_entire_ini_arr]);
			return true;
		} else {
			return false;
		}
	} elseif($section_or_entire_ini_arr === true) {
		foreach($this->ini_arr as $key => $value) {
			if(is_array($value) and !is_numeric($key)) {
				$this->ini_arr[$key] = $this->array_clean($this->ini_arr[$key]);
				ksort($this->ini_arr[$key]);
			}
		}
		return true;
	} 
	
	return false;
 } // end func section_items_sort

/**
 * Set item value.
 * If the value defined as bool, then it will converted to 1 or 0.
 * 
 * @access public
 * @param string $item
 * @param mixed $value
 * @param string $section
 * @return bool
 */
 public function item_set($item, $value, $section = NULL) {
	
	if(!$this->is_valid($item)) {
		return false;
	}

	if(is_bool($value) and $value === true) {
		$value = '1';
	} elseif(is_bool($value) and $value === false) {
		$value = '0';
	}
	
	if(!is_null($section)) {
		if(!$this->is_valid($section)) {
			return false;
		}

		$this->ini_arr[$section][$item] = $value;
	} else {
		
		// Set this item on top, before any section start.
		if(!isset($this->ini_arr[$item]) and $this->have_sections == true) {
			$this->ini_arr = array($item => $value) + $this->ini_arr;
		} else {	 
			$this->ini_arr[$item] = $value;
		}	
	}
	
	return true;
	
 } // end func item_set

/**
 * Return item value
 * if second argument defined as true, then this function
 * will return first finded item in entire ini array
 * 
 * @access public
 * @param string $item
 * @param mixed string | bool $section_or_forcibly
 * @return mixed
 */ 
 public function item_get($item, $section_or_forcibly = NULL) {

	$item = trim($item);

	if($this->is_valid($item) == false) {
		return false;
	}
	
	// Find and return first item value of entire ini array.
	if($section_or_forcibly === true) {
		// search in main ini entire array
		
		if($this->item_exists($item) === true) {
			// exists in array root sections
			return $this->ini_arr[$item];
		} else {
			// search in sections
			foreach($this->ini_arr as $key => $value) {
				if(is_array($value) and is_string($this->item_exists($item, $key))) {
					return $value[$item];
				}
			}
		}
		
		return false;
	}

	// Find and return by item name only 
	if(is_null($section_or_forcibly) and $this->item_exists($item) === true) {
		return $this->ini_arr[$item];
	}
	
	if($this->is_valid($section_or_forcibly)) {
		
		$in_section = $this->item_exists($item, $section_or_forcibly);
		
		if(is_string($in_section)) {
			return $this->ini_arr[$section_or_forcibly][$item];
		}
	}
	
	return false;
 } // end func item_get

/**
 * Check is item exists
 * if item found in section, then return section name, else
 * return true if item found behind a section.
 * 
 * @access public
 * @param string $item
 * @param string $section
 * @return mixed string | bool 
 */
 public function item_exists($item, $section = NULL) {
	
	if(!$this->is_valid($item)) {
		return false;
	}
	// Find in entire array
	if(is_null($section) and isset($this->ini_arr[$item]) 
						 and !is_array($this->ini_arr[$item])) {
		return true;
	}
	
	// check section 
	if(!is_null($section) and $this->is_valid($section) == false) {
		return false;
	}
	
	if(isset($this->ini_arr[$section][$item])) {
		return $section;
	}

	return false;
} // end func item_exists

/**
 * Rename item.
 * 
 * @access public
 * @param string $item
 * @param string $new_item
 * @param string $section
 * @return mixed string | bool
 */
 public function item_rename($item, $new_item, $section = NULL) {
	
	// find replace in entire array
	if(is_null($section) and $this->item_exists($item)) {
		$this->ini_arr = $this->lib->data->array_key_rename($item, $new_item, 
															$this->ini_arr);	
		return true;
	}
	
	// check section
	if(!is_null($section) and $this->is_valid($section) == false) {
		return false;
	}
	
	// Search in section if isset
	if($this->item_exists($item, $section)) {
		$this->ini_arr[$section] = $this->lib->data->array_key_rename($item, 
										$new_item, $this->ini_arr[$section]);
		return $section;
	}
	
	return false;
	
 } // end func item_rename

/**
 * Delete item
 * 
 * @access public 
 * @param string $item
 * @param string $section
 * @return mixed string | bool
 */
 public function item_delete($item, $section = NULL) {
	
	// find replace in entire array
	if(is_null($section) and $this->item_exists($item)) {
		unset($this->ini_arr[$item]);
		return true;
	}
	
	// check section
	if(!is_null($section) and $this->is_valid($section) == false) {
		return false;
	}
	
	// Search in section if isset
	if($this->item_exists($item, $section)) {
		unset($this->ini_arr[$section][$item]);
		return $section;
	}
	
	return false;
	
 } // end func item_delete

/**
 * Add comment
 * 
 * @access public
 * @param string $line
 * @param string $section
 * @return void
 */
 public function comment_set($line, $section = NULL) {
	
	if(!is_null($section)) {
		$this->ini_arr[$section][] = $line;
	} else {
		$this->ini_arr[] = $line;
	}
	
 } // end func comment_set

/**
 * Check, is data comment.
 * 
 * @access public
 * @param mixed $line
 * @return bool
 */ 
 public function is_comment($line) {
 	if(substr(trim($line), 0, 1) == $this->comment_sign) {
		return true;
	} else {
		return false;
	} 
 } // end func is_comment
 
/**
 * Check is valid section or item name.
 * Will be string.
 * 
 * @access public
 * @param mixed $data
 * @return bool
 */
 public function is_valid($data) {
	if(trim($data) == '' or is_numeric($data) or 
								$this->is_comment($data) or is_bool($data)) {
		return false;
	} else {
		return true;
	}
 } // end func is_valid

/**
 * Check, is string end of file.  
 * 
 * @access public
 * @param mixed $line
 * @return bool
 */ 
 public function is_end_of_file($line) {
	if(substr(trim($line), 0, 13) == $this->comment_sign.' End of file') {
		return true;
	} else {
		return false;
	}
 } // end func is_end_of_file

/**
 * Return true if current file is writeable
 * 
 * @access public
 * @return bool
 * @since 1.2.0 
 */ 
 public function file_is_writable() {
	 
	 if($this->file == '') {
		 return false;
	 }
	 
	 if(!is_writable($this->file)) {
		 return false;
	 }
	 
	 return true;
	 
 } // End func file_is_writable
 
/**
 * clean array, remove comments.
 * 
 * @access protected
 * @param array $arr
 * @return mixed array | bool
 */
 protected function array_clean($arr) {
	if(!is_array($arr)) {
		return false;
	}
	
	foreach($arr as $key => $value) {
		if(is_int($key)) {
			unset($arr[$key]);
		}
	} // end foreach
	
	return $arr;
 } // end func array_clean

/* End of class ini_lib */
}

/* End of file */
?>