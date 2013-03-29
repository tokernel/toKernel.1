<?php
/**
 * toKernel - Universal PHP Framework.
 * FTP class library.
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
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
 defined('TK_EXEC') or die('Restricted area.');

/**
 * ftp_lib class
 * 
 * Connect to ftp server, upload, download files, etc...  
 * It is possible to work with multiple instances of this object.
 *  
 * @author Razmik Davoyan <razmik@davoyan.name>
 */ 
 class ftp_lib {

/**
 * Connection resource.
 * 
 * @var resource
 * @access private
 */ 	
 private $conn_id;
 
/**
 * FTP Account
 * 
 * @var string
 * @access private
 */
 private $username;
 
/**
 * Host name of FTP server.
 * 
 * @var string
 * @access private
 */
 private $host_name;
 
/**
 * System type identifier of 
 * the remote FTP server.
 * 
 * @var string
 * @access private
 */
 private $sys_type;


/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
	$this->conn_id = NULL;
 	$this->username = 'anonymous';
	$this->host_name = '';
	$this->sys_type = '';
 } 
 
/**
 * Return instance of this object.
 * 
 * @access public
 * @return object
 */
 public function instance() {
	$this->close();
 	return clone $this;
 }
        
/**
 * Conncet to FTP Server
 * 
 * @access public
 * @param string $host_name
 * @param string $port = '21'
 * @param bool $use_ssl
 * @return bool
 */
 public function connect($host_name, $port = '21', $use_ssl = false) {

	if(function_exists('ftp_ssl_connect') and $use_ssl == true) {
		$conn_id = @ftp_ssl_connect($host_name, $port);
	} else {
		$conn_id = @ftp_connect($host_name, $port);
	}

    if(!is_resource($conn_id)) {
		 return false;
	}

    $this->conn_id = $conn_id;
	$this->host_name = $host_name;
    
	$this->sys_type = ftp_systype($conn_id);

    return true;

 } // end func connect

/**
 * Login to FTP server.
 * 
 * @access public
 * @param string $username
 * @param string $password
 * @param bool $passive_mode = false
 * @param int timeout = 90
 * @return bool
 */
 public function login($username, $password, $passive_mode = false, $timeout = 90) {
        
	if(!is_resource($this->conn_id)) {
		return FALSE;
	}

    $log_res = @ftp_login($this->conn_id, $username, $password);

	if(!$log_res) {
		return false;
	}    

    $this->username = $username;

	if($passive_mode == true) {
		ftp_pasv($this->conn_id, true);
	}

     ftp_set_option($this->conn_id, FTP_TIMEOUT_SEC, $timeout);

	return true;

 } // end func login

/**
 * Disconnect from FTP server.
 * 
 * @access public
 * @return bool
 */
 public function close() {

	if(!is_resource($this->conn_id)) {
		return false;
	}

    if(@ftp_close($this->conn_id)) {

	    $this->__construct();

        return true;   

    }

    return false;

 } // end func close

/**
 * Return current directory
 * 
 * @access public
 * @return string
 */
 public function cur_dir() {

	if(!is_resource($this->conn_id)) {
     	return false;
	}

    return ftp_pwd($this->conn_id);

 } // end func cur_dir

/**
 * Change current directory
 * 
 * @access public
 * @param string $path
 * @return bool
 */
 public function ch_dir($path)  {
            
	if(!is_resource($this->conn_id)) {
		return false;
	}

    return @ftp_chdir($this->conn_id, $path);

 } // end func ch_dir

/**
 * Return file list
 * if $det == true, return a detailed list of files.
 * 
 * @access public
 * @param string $path = '.'
 * @param bool $detailed = false
 * @return array | false
 */ 
 public function file_list($path = '.', $detailed = false) {
        
	if(!is_resource($this->conn_id)) {
    	return false;
	}

	if($detailed === true) {
		return ftp_rawlist($this->conn_id, $path);
	}

	return ftp_nlist($this->conn_id, $path);

 } // end func file_list

/**
 * Allocates space for a file to be uploaded
 * 
 * @access public
 * @param mixed int | string
 * @return bool
 */ 
 public function mem_alloc($file_or_size) {
            
	if(!is_resource($this->conn_id)) {
		return true;
	}

    if(!is_int($file_or_size)) {

		if(!file_exists($file_or_size)) {
			return false;
        }
                
        $file_or_size = filesize($file_or_size);

	}

	return ftp_alloc($this->conn_id, $file_or_size);

 } // end func mem_alloc

/**
 * Requests execution of a command on the FTP server
 * 
 * @access public
 * @param string $command
 * @return bool
 */
 public function exec_cmd($command) {
        
 	if(!is_resource($this->conn_id) or trim($command) == '') {
		return true;
	}

    return @ftp_exec($this->conn_id, $command);

 } // end func exec_cmd

/**
 * Change file permissions on server
 * 
 * @access public
 * @param string $file
 * @param int $mode
 * @return int | bool
 */
 public function ch_mod($file, $mode) {

	if(!is_resource($this->conn_id)) {
    	return false;
	}

    return @ftp_chmod($this->conn_id, $mode, $file);
 } // end func ch_mod 

/**
 * Return file size
 *
 * @access public
 * @param string $path
 * @return int | bool
 */
 public function file_size($path) {
 
    if(!is_resource($this->conn_id)) {
        return false;
    }

    $res = @ftp_size($this->conn_id, $path); 

    if($res != '-1') {
        return $res;
    }

    return false;

 } // end func file_size

/**
 * Delete file on server
 * 
 * @access public
 * @param string $file
 * @return bool
 */
 public function delete_file($file) {
        
	if(!is_resource($this->conn_id)) {
		return true;
	 }

	return @ftp_delete($this->conn_id, $file);

 } // end func delete_file 
        
/**
 * Rename file on FTP server
 * 
 * @access public
 * @param string $old_name
 * @param string $new_name
 * @return bool
 */
 public function rename_file($old_name, $new_name) {

 	if(!is_resource($this->conn_id)) {
		return true;
	}

	return @ftp_rename($this->conn_id, $old_name, $new_name);

 } // end func rename_file
       
/**
 * Make directory on the FTP server
 * 
 * @access public
 * @param string $dir_name
 * @return bool
 */
 public function make_dir($dir_name) {
        
	if(!is_resource($this->conn_id)) {
		return true;
	}

	return @ftp_mkdir($this->conn_id, $dir_name);
	
 } // end func make_dir
        
/**
 * Removes the specified directory on the FTP server
 * 
 * @access public
 * @param string $dir_name
 * @return bool
 */
 public function rm_dir($dir_name) {
        
	if(!is_resource($this->conn_id)) {
		return false;
	}
    
    return @ftp_rmdir($this->conn_id, $dir_name);

 } // end func rm_dir 
        
/**
 * Upload file to FTP server
 * 
 * @access public
 * @param string $local_path
 * @param string $remote_path
 * @param int $mode
 * @return bool
 */
 public function upload_file($mode, $local_path, $remote_path = NULL) {
        
    if(!is_resource($this->conn_id) || !is_readable($local_path)) { 
    	return false;
	}

    if(is_null($remote_path)) {
		$remote_path = $local_path;
	}

    return @ftp_put($this->conn_id, $local_path, $remote_path, $mode);

 } // end func upload_file

/**
 * Download file from FTP server
 * 
 * @access public
 * @param string $remote_path
 * @param string $local_path 
 * @param int $mode
 * @return bool
 */
 public function download_file($mode, $remote_path, $local_path = NULL) {
        
 	if(!is_resource($this->conn_id)) {
		return false;
	}

    if(is_null($local_path)) {
		$local_path = $remote_path;
	}

	return @ftp_get($this->conn_id, $local_path, $remote_path, $mode);

 } // end func download_file

/**
 * Return system type identifier of the remote FTP server
 * 
 * @access public
 * @return string
 */
 public function system_type() {

	if(!is_resource($this->conn_id)) {
    	return false;
	}

	return $this->sys_type;

 } // end func system_type

/* End of class ftp_lib */
}

/* End of file ftp.lib.php */
?>
