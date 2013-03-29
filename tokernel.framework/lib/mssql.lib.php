<?php 
/**
 * toKernel - Universal PHP Framework.
 * Class library for working with MS-SQL Server
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
 * @since      File available since Release 1.2.1
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * mssql_lib class
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class mssql_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;
	
/**
 * Connection data
 * 
 * @access protected
 * @var array
 */
 protected $conn_ini;
	
/**
 * Connection resource
 * 
 * @access protected
 * @var resource
 */
 protected $conn_res;

/**
 * Last Error message
 * 
 * @access protected
 * @var string
 */
 protected $err_message; 

/**
 * Class constructor
 * 
 * @access public
 * @return void
 */
 public function __construct() {
 	$this->lib = lib::instance();
 } 
 
/**
 * Class destructor
 * 
 * @access public
 * @return void
 */
 public function __destruct() {
 	
 	unset($this->conn_link);
 	unset($this->conn_ini);
	//unset($this->err_message);
	
 } // end destructor 
 
/**
 * Return clone of this object
 * 
 * @access public
 * @return object 
 */
 public function instance() {
 	
	$n = clone $this;
	$n->__destruct();
 	
	return $n;
 } // end func instance 
 
/**
 * Return Unique ID for each benchmark step.
 * 
 * @access protected
 * @return string
 */
 protected function uid() {
 	return '(); - ID ('.uniqid().')';
 } 
 
/**
 * Set connection parameters
 * 
 * @access public
 * @param array $conn_ini
 * @return bool
 */ 
 public function configure($conn_section) {
 	
 	$ini_file = TK_CUSTOM_PATH . 'config' . TK_DS . 'databases.ini';
 	$mci = $this->lib->ini->instance($ini_file, $conn_section);
 
 	if(!is_object($mci)) {
 		trigger_error('Can not access to MS-SQL Connection configuration values'.
 					  ' in file `' . $ini_file . '` with section `' .  
 						$conn_section . '` !', E_USER_ERROR);
 	}

 	$conn_ini = $mci->section_get($conn_section);
 	
 	if(!isset($conn_ini['servername']) or !isset($conn_ini['database']) or 
 	   !isset($conn_ini['username']) or !isset($conn_ini['password'])) {
 	   	
 	   trigger_error('Invalid MS-SQL connection values ' . 
 	   				 'in file `' . $ini_file . '` with section `' .  
 					  $conn_section . '` !', E_USER_ERROR);
 					  
 		return false;
 	} 
 	
 	$this->conn_ini = $conn_ini;
 	
 	return true;
 	
 } // end func configure

/**
 * @access public
 * @param string $item
 * @return mixed string | bool
 */ 
 public function config($item) { 
 	if(isset($this->conn_ini[$item])) {
 		return $this->conn_ini[$item];
 	} else {
 		return false;
 	}
 } // end func config
 
/**
 * Connect to MysqL Server and select database
 * 
 * @access public
 * @param array $conn_ini
 * @param bool $benchmark
 * @return mixed resource | bool
 */ 
 public function connect($conn_ini = NULL, $benchmark = false) {
 	
 	if($benchmark) {
 		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
 		$this->lib->benchmark->start($uid);
 	}
 	
 	if(!function_exists('mssql_connect' )) {
 		trigger_error("The MS-SQL adapter `mssql` is not available.", E_USER_ERROR);
 		return false;
    }
    
 	if(is_array($conn_ini) and !$this->configure($conn_ini)) {
 		trigger_error("The MS-SQL connection not configured.", E_USER_ERROR);
 		return false;
 	}
 	
 	$this->conn_res = mssql_connect($this->conn_ini['servername'], 
 									$this->conn_ini['username'], 
 									$this->conn_ini['password']);

	if(!$this->conn_res) {
        $this->err_message = mssql_get_last_message();
        trigger_error($this->err_message, E_USER_ERROR);
        return false;
    }
    
    if(!mssql_select_db($this->conn_ini['database'], $this->conn_res)) {
        $this->err_message = mssql_get_last_message();
        trigger_error($this->err_message, E_USER_ERROR);
        return false;
    }
    
    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Servername", $this->conn_ini['servername']);
		$this->lib->benchmark->set($uid, 
					"Database", $this->conn_ini['database']);
		$this->lib->benchmark->set($uid, 
					"Username", $this->conn_ini['username']);
		$this->lib->benchmark->set($uid, 
					"Password", "Using password - " . $this->using_password());
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
    return $this->conn_res;
 	
 } // end func connect 

/**
 * Disconnect 
 *
 * @access public
 * @param bool $benchmark
 * @return void
 */
 public function disconnect($benchmark = false) {
	
 	if($this->conn_res) {
       mssql_close($this->conn_res);
    }
	
    if($benchmark) {
    	$this->lib->benchmark->set(__CLASS__, 'method', __FUNCTION__);
    }
    
} // end disconnect
 
/**
 * Return "Yes" if connection password is not epty.
 *
 * @access protected
 * @return mixed string | bool
 */
 protected function using_password() {
	
 	if(!isset($this->conn_ini['password'])) {
 		return false;
 	}
 	
 	if($this->conn_ini['password'] != '') {
 		return 'Yes';
 	} else {
 		return 'No';
 	}
 
 } // end func using_password 
 
/* End of class mssql_lib */
}

/* End of file */
?>