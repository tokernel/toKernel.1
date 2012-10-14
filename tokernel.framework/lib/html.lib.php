<?php
/**
 * toKernel - Universal PHP Framework.
 * Class library for working with html document
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
 * @version    1.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * html_lib class
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class html_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access private
 */ 
 private $lib;

/**
 * Document title
 * 
 * @access private
 * @var string $title
 */
 private $title;

/**
 * Document meta content
 * 
 * @access private
 * @var array $meta
 */ 
 private $meta = array(
	'keywords' => '',
	'description' => '',
 );

/**
 * Javascript files list
 * attached to html document
 * 
 * @access private
 * @var array $attached_js
 */ 
 private $attached_js = array();

/**
 * CSS files list
 * attached to html document
 * 
 * @access private
 * @var array $attached_js
 */ 
 private $attached_css = array();

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
 * Class destructor
 * 
 * @access public
 * @return void
 */ 
 public function __destruct() {
	unset($this->title);
	unset($this->meta);
	unset($this->attached_css);
	unset($this->attached_js);
 } // end of func __destruct  
 
/**
 * Set title of html document
 * 
 * @access public
 * @param string $title
 * @return void
 */
 public function set_title($title) {
	$this->title = $title;
 } 

/**
 * Get title of html document
 * 
 * @access public
 * @return string
 */ 
 public function get_title() {
	return $this->title;
 }

/**
 * Print title of html document
 * 
 * @access public
 * @return void
 */ 
 public function print_title() {
	if(trim($this->title) != '') {
		echo "<title>".$this->title."</title>\n";
	}
 } 
 
/**
 * Attach JS or CSS file to document.
 * 
 * @access public
 * @param string $file_url
 * @param bool $append_url
 * @return bool
 */ 
 public function attach_file($file_url) {
   
 	/* Get file extension */
	$ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
	
	if($ext == 'js' and !in_array($file_url, $this->attached_js)) {
		$this->attached_js[] = $file_url;
	} elseif($ext == 'css' and !in_array($file_url, $this->attached_css)) {
		$this->attached_css[] = $file_url;
	}
	
	return true;
   
} // end of func attach_file

/**
 * Return array of attached files by type js | css
 * 
 * @access public
 * @param string $type
 * @return mixed array | bool
 */
 public function get_attached_files($type) {

 	if(strtolower($type) == 'css') {
		return $this->attached_css;
	} elseif(strtolower($type) == 'js') {
		return $this->attached_js;
	} else {
       return false;
    } // end checking file type
    
} // end of func get_attached_files  

/**
 * Print document attached files.
 * If type (js | css) is null, then print both.
 * 
 * 
 * @access public
 * @param string $type
 * @return void
 */
 public function print_attached_files($type = NULL) {
 	if(strtolower($type) == 'css') {
		/* print css */
		foreach($this->attached_css as $file) {
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . 
				  $file . "\" />\n";
		}
 	} elseif(strtolower($type) == 'js') {

 		/* print js */
 		foreach($this->attached_js as $file) {
			echo "<script type=\"text/javascript\" src=\"" . 
				 $file . "\"></script>\n";
		}
 	} elseif($type == NULL) {

	 	/* print css */
		foreach($this->attached_css as $file) {
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . 
				  $file . "\" />\n";
		}
		
	 	/* print js */
 		foreach($this->attached_js as $file) {
			echo "<script type=\"text/javascript\" src=\"" . 
				 $file . "\"></script>\n";
		}
		
	} // end of type

 } // end of func print_attached_files

/**
 * Set meta content for html document.
 * 
 * @access public
 * @param string $item
 * @param string $value
 * @return void
 */
 public function set_meta($item, $value) {
	$this->meta[$item] = str_replace('"', "'", $value);
 } 

/**
 * Get meta content from html document.
 * 
 * Return meta contents 
 * array if item is null.
 * 
 * @access public
 * @param string $item
 * @return mixed array | string
 */ 
 public function get_meta($item = NULL) {
	if(!is_null($item)) {
       return $this->meta[$item];
    } else { 
       return $this->meta;
    }
} // end of func get_meta

/**
 * Print meta content of html document with tags.
 * 
 * Print all meta contents 
 * is item is null.
 * 
 * @access public
 * @param string $item
 * @return void
 */
 public function print_meta($item = NULL) {
    
	if(!is_null($item)) {
		if($this->meta[$item] == '') {
			return;
		}

		echo "<meta name=\"" . $item . "\" content=\"" . 
		      $this->meta[$item] . "\" />\n";
    } else { 
       
		foreach($this->meta as $name => $content) {
			if($content != '') {
				echo "<meta name=\"" . $name . "\" content=\"" . 
				      $content . "\" />\n";
			}          
       } // end foreach

    } // end if item

 } // end of func print meta

/**
 * Return meta keywords from string 
 *
 * @access public
 * @param string $string
 * @param integer $keywords_count
 * @return string
 */
 public function make_keywords($string, $keywords_count = 12) {
    
 	/* Remove some symbols from content */
	$string = strip_tags($string);
	$string = str_replace("\n", "", $string);
	$string = str_replace("\r", "", $string);
	$string = str_replace(",", " ", $string);
    $string = str_replace('"', " ", $string);
	$string = str_replace("'", " ", $string);
	$string = str_replace(". ", " ", $string);
	$string = str_replace(".", " ", $string);
	$string = str_replace("  ", " ", $string);
	
	/* Make keywords array */
	$keywords_arr = array();
	$keywords_arr = explode(" ", $string);
	
	$cnt = 0;
	
	/* 
	 * Remove keywords from array which 
	 * lenght is less then 5 chars 
	 */
	foreach($keywords_arr as $key) {
        if(strlen($key) < 5 or strlen($key) > 15) {
		   unset($keywords_arr[$cnt]);
		} // end check key lenght
		$cnt++;
	} // end foreach
	
	sort($keywords_arr);
	$k_keywords_arr = array();
	foreach($keywords_arr as $key) {
	   
		if(!isset($k_keywords_arr[$key])) {
			$k_keywords_arr[$key] = 0;
		}
		
		$k_keywords_arr[$key] ++;
	}
	
	arsort($k_keywords_arr);
	
	if(count($k_keywords_arr) < $keywords_count) {
	   $keywords_count = count($k_keywords_arr);
	}
	
	/* Make keywords string */
	$kc = 0;
	$keywords = '';
	
	foreach($k_keywords_arr as $key => $value) {
	   $keywords .= $key.", ";
	   $kc++;
	   if($kc >= $keywords_count) {
	      break;
	   }
	}  
	 
	$keywords = trim($keywords, ", ");
	
	/** DEBUG **/
	/*
	foreach($keywords_arr as $key) {
	   echo $key."<br>";
	}
		
	echo "<br> ============================= <br>";
	
	$cnt = 0;
	foreach($k_keywords_arr as $key => $value) {
	   echo $key." -> ".$value."<br>";
	   $cnt++;
	}
	
	echo $cnt;
	/** End Debug **/
	
	unset($keywords_arr);
	unset($k_keywords_arr);
	
	if($keywords != '') {
	   return $keywords;
    } else {
	   return "";
	}
	
 } // end func make_keywords

/**
 * Return meta description from string
 *
 * @access public
 * @param string $string
 * @param integer $count
 * @return mixed string | bool
 */
 public function make_description($string, $count = 300) {

 	if(trim($string) == '') { 
	   return false; 
	}
    
	/* Remove some symbols from content */
	$string = strip_tags($string);
	$string = str_replace("\n", "", $string);
	$string = str_replace("\r", "", $string);
	$string = str_replace("**", "", $string);
	$string = str_replace("-*", "", $string);
	$string = str_replace("*-", "", $string);
	$string = str_replace("--", "", $string);
	
	$string = trim($string);
	$str_len = strlen($string);

	if($str_len <= $count) {
       return $string;
	}

	$string = substr($string, 0, $count);
	$pos = strrpos($string, " ");
	
	if(is_bool($pos) && !$pos) {
       // not found...
    } else {
       $string = substr($string, 0, $pos);
    }
    
	return $string;
 } // End of func make_description 

/**
 * Convert and return data for html output.
 * By default, will not encode 
 * data to html special chars.
 * 
 * @access public
 * @param string data to convert
 * @param bool encode data to special chars
 * @param array data to replace
 * @return string
 */
 public function format($data, $encode = false, $rep_arr = NULL) {
 	
 	/* Encode to html special chars. */
 	if($encode == true) {
 		$data = $this->lib->filter->encode_html_entities($data);
 	}
 	
 	/* Replace data if defined */
 	if(is_array($rep_arr)) {
 		foreach($rep_arr as $rf => $rt) {
 			$data = str_replace($rf, $rt, $data); 
 		}
 	}
 	
 	/* Convert "\n" to "<br />". */
 	$data = str_replace("\n", '<br />', $data);
 	
 	$data = trim($data);
 	
 	return $data;
 } // end func format  
 
/* End of class html_lib */ 
}

/* End of file */
?>