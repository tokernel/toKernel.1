<?php
/**
 * toKernel - Universal PHP Framework.
 * File system class library
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
 * @version    1.3.4
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * file_lib class
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class file_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;
 
/**
 * Class constructor
 * 
 * @access public
 * @return voic
 */ 
 public function __construct() {
 	$this->lib = lib::instance();
 }
 
/**
 * Upload file
 *
 * @access public 
 * @param string $target_path
 * @param string $file_el_name Form element name
 * @param string $file_name new file name
 * @return mixed string | bool
 */
 public function upload($target_path, $file_el_name, $file_name = NULL) {
	
 	/* Check target path */
	if(!is_dir($target_path) or !is_writable($target_path)) { 
		return false;
	}
	
	$target_path = $this->to_path($target_path);
	
	/* Check temp file */
	if(trim($file_el_name) == '' or !isset($_FILES[$file_el_name])) { 
		return false; 
	}
	
	$file_tmp_name = $_FILES[$file_el_name]['tmp_name'];
		
	if(!is_file($file_tmp_name)) {
		return false;
	}

	/* Check new file name */
	if(is_null($file_name)) {
       $file_name = $_FILES[$file_el_name]['name'];
    }
    
    /* Strip chars expect -, _, . */
	$file_name = $this->lib->filter->strip_chars($file_name, 
													array('-', '_', '.'));

	if($this->ext($file_name) == '' or $this->strip_ext($file_name) == '') {
		$file_name = $this->uname() . "." . $this->ext($file_name);
	}
	
	// Check if File Exists, make a new unique name.
	while (file_exists($target_path . $file_name)) {
		$file_name = $this->uname() . "." . $this->ext($file_name);
	} // End While
	
	if(move_uploaded_file($file_tmp_name, $target_path . $file_name)) {
		return $file_name;
	} else {
		return false;
	}
	
 } // End func upload

/**
 * Download file
 * 
 * @access public
 * @param string $file
 * @param mixed $content
 * @return mixed
 */ 
 public function download($file, $content = NULL) {
 	
 	if(trim($file) == '') {
 		return false;
 	}
 	
 	if(!is_file($file) and is_null($content) ) {
 		return false;
 	}
 	
 	if(!is_null($content)) {
 		$size = strlen($content);
 	} else {
 		$size = filesize($file);
 	}
 	
	$filename = basename($file);
	$ext = $this->ext($file);
	
	/* Load mime type */
	if($ext != '' and is_readable(TK_PATH . 'config' . TK_DS . 'mimes.ini')) {
		
		$mimes = $this->lib->ini->instance(TK_PATH . 'config' . 
										   TK_DS . 'mimes.ini');
											
		if(is_object($mimes)) {
			$mime = $mimes->item_get($ext);
		}
	} 
	
	if($mime == '') {
		$mime = 'application/octet-stream'; 
	}
	
	if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== false) {
		
		header('Content-Type: "'.$mime.'"');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header("Content-Transfer-Encoding: binary");
		header('Pragma: public');
		header("Content-Length: ".$size);
		
	} else {
	
		header('Content-Type: "'.$mime.'"');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header("Content-Transfer-Encoding: binary");
		header('Expires: 0');
		header('Pragma: no-cache');
		header("Content-Length: ".$size);
	}

	if(!is_null($content)) {
		exit($content);
	} else {
		readfile($file);
		exit();	
	}
	
 } // end func download
 
/**
 * Check, Return path with '/' appended.
 * 
 * @access public
 * @param string $path
 * @return mixed string | bool
 */ 
 public function to_path($path) {
 	
 	$na = array('.', '..', './', '../', '/', '\\');
 	
 	if(trim($path) == '' or in_array($path, $na)) {
 		return false;
 	}
 	
 	$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
 	
 	if(substr($path, -1) != DIRECTORY_SEPARATOR) {
 		$path .= DIRECTORY_SEPARATOR;
 	}
 	
 	return $path;
 	
 } // end func to_path
 
 /**
 * Return unical name without extension. 
 * Ex: 45f7fd76
 *
 * @access public
 * @return string
 */
 public function uname() {
	
 	return substr(md5(time() . rand()), 3, 8);
 	
 } // End func uname
 
 /**
 * Check/Return file extension
 *
 * @access public
 * @param string $file
 * @param bool $check
 * @return mixed string | bool
 */
 public function ext($file, $check = NULL) {
	
 	$ext = pathinfo($file, PATHINFO_EXTENSION);
 	$ext = strtolower($ext);
	
 	if(!is_null($check)) {
 		$check = strtolower($check);
 		$check = explode('|', $check);
 		if(!in_array($ext, $check)) {
 			return false;
 		}
 	}
 	
 	return $ext;
 	
 } // End func ext
  
/**
 * Return file name without extension
 * 
 * @access public
 * @param string $file
 * @return string
 */
 public function strip_ext($file) {
 	return pathinfo($file, PATHINFO_FILENAME);
 } 

/**
 * Read file content
 *
 * @access public
 * @param string $file 
 * @return string | bool 
 */
 public function read($file) {
	
 	if(!is_readable($file) or !is_file($file)) {
 		return false;
 	}
 	
    return file_get_contents($file);
    
 } // end func read 
 
/**
 * Append data to file
 *
 * @access public
 * @param string $file 
 * @param string $content
 * @return bool 
 */
 public function append($file, $content) {
	
 	if(!is_writable($file) or !is_file($file)) {
 		return false;
 	}
 	
    $fh = fopen($file, 'a');
    
    if(!$fh) {
    	return false;
	}

	fwrite($fh, $content);
	fclose($fh); 
	 
	return true;
 } // end func append

/**
 * Write data to file (overwrite if file exists)
 *
 * @access public
 * @param string $file 
 * @param string $content
 * @return bool 
 */
 public function write($file, $content) {
	
 	return file_put_contents($file, $content);
    
 } // end func write 
 
/**
 * Return list of files, directories
 * 
 * @access public
 * @param string $dir
 * @param string $type = NULL | - | d
 * @param bool $adv = false
 * @param string $ext = NULL | jpg|gif|txt ...
 * @return mixed array | bool 
 */ 
 public function ls($dir, $type = NULL, $adv = false, $ext = NULL) {
 	
 	if(!is_dir($dir)) {
		return false;
	}

	$dir = $this->to_path($dir);
	$dh = opendir($dir);
	$files_arr = array();
	 
	clearstatcache();
	 
	while(false !== ($file = readdir($dh))) {
	   
		if($file != "." and $file != "..") {

			$perms = $this->perms($dir . $file);
			
			/* check type */
			if(!is_null($type) and $type != substr($perms, 0, 1)) {
				continue;
			} 
			
	 		/* check extension */
			if(!is_null($ext)) {
				if($this->ext($file, $ext) == false) {
					continue;
				}
			}
			
	 		$files_arr[] = $file;
	 		
		} // end checking 
       
	 } // end while
	 	 
	 /* advenced information */
	 if($adv != true) {
		return $files_arr;
	 }	

	 $a_files_arr = array();
	 	
	 foreach($files_arr as $file) {
			
	 	if(is_file($dir . $file)) {
	 		$a_files_arr[$file]['ext'] = $this->ext($file);
	 	} else {
	 		$a_files_arr[$file]['ext'] = false;
	 	}
	 		
		$a_files_arr[$file]['perms'] = $this->perms($dir . $file);
		$a_files_arr[$file]['owner'] = fileowner($dir . $file);
		$a_files_arr[$file]['group'] = filegroup($dir . $file);
	 		
	 	if(is_file($dir . $file)) {
	 		$a_files_arr[$file]['size'] = filesize($dir . $file);
	 	} else {
	 		$a_files_arr[$file]['size'] = false;
	 	}
	 		
	 	$a_files_arr[$file]['atime'] = date('M d Y H:m:s', 
	 											fileatime($dir . $file));
	 											
	 	$a_files_arr[$file]['mtime'] = date('M d Y H:m:s', 
	 											filemtime($dir . $file));
	}

	if(count($a_files_arr) > 0) {
		return $a_files_arr;
	}

	return false; 
	
 } // end func ls
 
/**
 * Return file permissions
 * 
 * @access public
 * @param string
 * @return mixed string | void
 */
 public function perms($file) {
 	
 	if(!file_exists($file)) {
 		return false;
 	}
 	
 	if(($perms = @fileperms($file)) == false) {
 		return false;
 	}

 	if(($perms & 0xC000) == 0xC000) {
 		// Socket
 		$info = 's';
 	} elseif(($perms & 0xA000) == 0xA000) {
 		// Link
 		$info = 'l';
 	} elseif(($perms & 0x8000) == 0x8000) {
 		// regular
 		$info = '-';
 	} elseif(($perms & 0x6000) == 0x6000) {
 		// Special block
 		$info = 'b';
 	} elseif(($perms & 0x4000) == 0x4000) {
 		// Directory
 		$info = 'd';
 	} elseif(($perms & 0x2000) == 0x2000) {
 		// Special symbol
 		$info = 'c';
 	} elseif(($perms & 0x1000) == 0x1000) {
 		// Поток FIFO
 		$info = 'p';
 	} else {
 		// Unknown
 		$info = 'u';
 	}

 	// Owner
 	$info .= (($perms & 0x0100) ? 'r' : '-');
 	$info .= (($perms & 0x0080) ? 'w' : '-');
 	$info .= (($perms & 0x0040) ?
             (($perms & 0x0800) ? 's' : 'x' ) :
             (($perms & 0x0800) ? 'S' : '-'));

    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
             (($perms & 0x0400) ? 's' : 'x' ) :
             (($perms & 0x0400) ? 'S' : '-'));

    // All other
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
             (($perms & 0x0200) ? 't' : 'x' ) :
             (($perms & 0x0200) ? 'T' : '-'));

    return $info;
    
 } // end func perms

/**
 * Return formated size
 * 
 * @access public
 * @param integer $bytes
 * @return string
 */ 
 public function format_size($bytes) {
 
 	if(!is_numeric($bytes)) {
 		return false;
 	}
 
 	$s = array('bytes', 'kb', 'MB', 'GB', 'TB', 'PB');
    $e = floor(log($bytes) / log(1024));
 
    $size = sprintf('%.2f '.$s[$e], ($bytes / pow(1024, floor($e))));
 
    return $size;

 } // End func format_size
 
/* End of class file_lib */
}

/* End of file */
?>