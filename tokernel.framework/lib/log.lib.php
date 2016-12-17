<?php
/**
 * toKernel - Universal PHP Framework.
 * Log processing library
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
 * @version    1.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * log_lib class
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class log_lib {

/**
 * Log file
 *
 * @access protected
 * @var string
 */	
 protected $log_file;
 
/**
 * Log file size
 * 
 * @access protected
 * @var integer
 */ 
 protected $file_size;

/**
 * Log files path
 *
 * @access protected
 * @var string
 */ 
 protected $log_path;

/**
 * Class constructor
 * 
 * @access public
 * @return void 
 */ 
 public function __construct() {
	$this->log_file = NULL;
	$this->file_size = 524288; // kb
	$this->log_path = TK_CUSTOM_PATH . 'log/';
 }

/**
 * Return instance of log object
 * 
 * @access public
 * @param string $log_file
 * @param integer $file_size
 * @param string $log_path
 * @return object
 */ 
 public function instance($log_file, $file_size = 524288, $log_path = NULL) {
		
	$this->log_file = $log_file;
	$this->file_size = $file_size;
		
	if(!is_null($log_path)) {
		$this->log_path = $log_path;
	}
		
	return clone $this;	
} // end func instance
	
/**
 * Get current log file.
 * If file is large, archive it, and create new file.
 *
 * @access protected
 * @return mixed bool | string
 */
 protected function get_current_file() {
		
	if(is_null($this->log_file)) {
		return false;
	}
		
	if(!is_file($this->log_path . $this->log_file)) {
   		
		if(!is_writeable($this->log_path)) {
   			return false;
   		} 
   		
		touch($this->log_path . $this->log_file);
		chmod($this->log_path . $this->log_file, 0777);
    		
   		return $this->log_file;
	}

	if(filesize($this->log_path . $this->log_file) > $this->file_size) {
			
		$this->file_compress($this->log_file, true);
		
		touch($this->log_path . $this->log_file);
   		chmod($this->log_path . $this->log_file, 0777);
    		
	} // end if archiving
		
	return $this->log_file;
		
 } // end func current_file
	
/**
 * Write data to current log file
 * 
 * @access public
 * @param string $log_message
 * @param mixed $date_mode
 * @return bool
 */
 public function write($log_message, $date_mode = true) {
		
	$log_file = $this->get_current_file();

	if(!is_writeable($this->log_path) or $log_file === false) {
   		trigger_error('Directory `' . $this->log_path . '` is not writeable!', E_USER_WARNING);
   		return false;
  	}
  	
	$log_file = $this->log_path . $this->log_file;
	
	$fr = fopen($log_file, "a");
	
	if($fr) {
    	
		if($date_mode == true) {
			$log_message = '[' . date('d-m-Y H:i:s') . 
							' ' . TK_RUN_MODE.'] ' .
							$log_message;
		}
		
    	fwrite($fr, $log_message . "\n");
    	fclose($fr);
    } else {
   		return false;
   	}
    	
   	return true;
   	
 } // end func write
	
/**
 * Return list of log files.
 * if extension not defined, then will 
 * return all files such as *.log, *.gz 
 * 
 * @access public
 * @param string $ext
 * @return mixed array | bool
 */
 public function files_list($ext = NULL) {
		
	if(is_null($ext)) {
		$glob_arr = glob($this->log_path . '*.*');
	} else {
		$ext = explode('|', $ext);
		
		$types = '';
		
		foreach($ext as $type) {
			$types .= $this->log_path.'*.'.$type.',';
		}
		
		$types = trim($types, ',');
		
		$glob_arr = glob("{".$types."}", GLOB_BRACE);
		
	} 
		
	if(!is_array($glob_arr)) {
		return false;
	}
		
	$files_arr = array();
		
	foreach($glob_arr as $file) {
		$files_arr[] = basename($file);
	}
		
	return $files_arr;
	
 } // end func files_list

/**
 * Remove log file (log | gz)
 * $file argument must be set without path.
 * 
 * @access public
 * @param string $file
 * @return bool
 * @since 1.1.0
 */ 
 public function file_remove($file) {
	
	 $file_path = $this->log_path . $file;
	 
	 if(!is_file($file_path)) {
		 trigger_error('File `'.$file.'` not found!', E_USER_WARNING);
	 }
	 
	 return unlink($file_path);
	 	 
 } // End func  
 
/**
 * Delete log files
 * 
 * @access public
 * @param string $ext
 * @return bool
 */ 
 public function files_clean($ext = NULL) {
		
 	$files_list = $this->files_list($ext);

	if(is_array($files_list)) {
		foreach($files_list as $log_file) {
			unlink($this->log_path . $log_file);
		}
			
		return true;
	} else {
		return false;
	}
 } // end func files_clean
 
/**
 * Compress log file to *.gz
 * Delete source file if $rem_file defined as true
 * 
 * @access public
 * @param string $log_file
 * @param bool $rem_file
 * @return bool 
 */ 
 public function file_compress($log_file, $rem_file = false) {
		
	if(!is_readable($this->log_path . $log_file)) {
		return false;
	}
		
	$file_content = file_get_contents($this->log_path . $log_file);
			
	// make file base name and extension
	$file_data = pathinfo($this->log_path . $log_file);
			
	$ext = $file_data['extension'];
	$basename = $file_data['filename'];
			
	$gz_log_file = $this->log_path . $basename . '.' . 
								date('Y-m-d_H.i.s') . '.' . $ext . '.gz';
									
	$file_content_gz = gzencode($file_content, 9);
			
	$fr = fopen($gz_log_file, "a");
    		
	if($fr) {
		fputs($fr, $file_content_gz);
    	fclose($fr);
    		
    	if($rem_file == true) {
    		unlink($this->log_path . $log_file);
    	}	
    		
    } else {
    	return false;
    }
    	
    return true;
		
 } // end func file_compress
	
/**
 * Uncompress archived log file (*.gz)
 * Delete source file if $rem_gz defined as true
 * 
 * @access public
 * @param string $arch_file
 * @param bool $rem_gz
 * @return bool
 */	
 public function file_uncompress($arch_file, $rem_gz = false) {
	
	if(!is_readable($this->log_path . $arch_file)) {
		return false;
	}
		
	$fr = gzopen($this->log_path . $arch_file, 'r');
	$file_content_gz = '';
	
	while(!feof($fr)) {
		$file_content_gz .= gzread($fr, 10000);
	}

	gzclose($fr);
		
	// put content
	$log_file = str_replace('.gz', '', $arch_file);
		
	$fr = fopen($this->log_path . $log_file, "a");

	if($fr) {
    	fputs($fr, $file_content_gz);
    	fclose($fr);
    		
    	if($rem_gz == true) {
    		unlink($this->log_path . $arch_file);
    	}	

	} else {
    	return false;
    }
    	
	return true;
 } // end func file_uncompress

/**
 * Return log file content as string or as an array
 * It is possible to read and return *.gz archived log file.
 * 
 * @access public
 * @param string $file
 * @param bool $as_array
 * @return mixed array | string | bool
 */ 
 public function file_read($file, $as_array = false) {
	
 	if(!is_readable($this->log_path . $file)) {
		return false;
	}
		
	if(substr($file, -3) == '.gz') {
		$lines = gzfile($this->log_path . $file);
	} else {
		$lines = file($this->log_path . $file);
	}
		
	if($as_array == false) {
		$file_content = '';

		foreach($lines as $line) {
			$file_content .= $line;
		}
			
		return $file_content;
	}
		
	return $lines;
 } // end func file_read
	
/**
 * Return current log file name
 * 
 * @access public
 * @return string
 */
 public function file_name() {
	return $this->log_file;
 }

/**
 * Return current log files path
 * 
 * @access public
 * @return string
 */
 public function log_path() {
		return $this->log_path;
 }
 
/* End of class log_lib */
}

/* End of file */
?>