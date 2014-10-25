<?php 
/**
 * toKernel - Universal PHP Framework.
 * Class library for working with MySQL Server
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
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @todo       Change the count() method functionality.
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * mysql_lib class
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class mysql_lib {

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
	unset($this->err_message);
	
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
 * @since 1.2.0
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
 		trigger_error('Can not access to MySQL Connection configuration values'.
 					  ' in file `' . $ini_file . '` with section `' .  
 						$conn_section . '` !', E_USER_ERROR);
 	}

 	$conn_ini = $mci->section_get($conn_section);
 	
 	if(!isset($conn_ini['host']) or !isset($conn_ini['database']) or 
 	   !isset($conn_ini['username']) or !isset($conn_ini['password'])) {
 	   	
 	   trigger_error('Invalid MySQL connection values ' . 
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
 	
 	if(!function_exists('mysqli_connect' )) {
 		trigger_error("The MySQL adapter `mysql` is not available.", E_USER_ERROR);
 		return false;
    }
    
 	if(is_array($conn_ini) and !$this->configure($conn_ini)) {
 		trigger_error("The MySQL connection not configured.", E_USER_ERROR);
 		return false;
 	}
 	
	if ($this->conn_ini['port'] != '') {
		$this->conn_ini['host'] .= ':' . $this->conn_ini['port'];
	}
		
 	$this->conn_res = @mysqli_connect($this->conn_ini['host'], 
 									$this->conn_ini['username'], 
 									$this->conn_ini['password']);

	if(!$this->conn_res) {
        $this->err_message = mysqli_error();
        trigger_error($this->err_message, E_USER_ERROR);
        return false;
    }
    
    if(!mysqli_select_db($this->conn_res, $this->conn_ini['database'])) {
        $this->err_message = mysqli_error();
        trigger_error($this->err_message, E_USER_ERROR);
        return false;
    }

    if(isset($this->conn_ini['charset']) and $this->conn_ini['charset']!= '') {
    	mysqli_set_charset($this->conn_res, $this->conn_ini['charset']);
    }
    
    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Host", $this->conn_ini['host']);
		$this->lib->benchmark->set($uid, 
					"Port", $this->conn_ini['port']);
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
 * If connection not established, try to connect.
 * 
 * @access public
 * @return bool
 */ 
 public function reconnect($benchmark = false) {
	
 	if($this->conn_res and mysqli_ping($this->conn_res) === true) {
 		return true;
 	}
 		
 	if(!is_array($this->conn_ini)) {
 		return false;
 	}
 	
 	if(!$this->connect($benchmark)) {
 		return false;
 	}
 	
	return true;
 	 	
 } // end func reconnect

/**
 * Disconnect 
 *
 * @access public
 * @param bool $benchmark
 * @return void
 */
 public function disconnect($benchmark = false) {
	
 	if($this->conn_res) {
       mysqli_close($this->conn_res);
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
 
/**
 * Regular mysql query
 *
 * @access public
 * @param string $query
 * @param bool $benchmark
 * @return mixed resource | bool
 */
 public function query($query, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
 	if(!$this->reconnect($benchmark)) {
		return false;
	}
	
	$result = mysqli_query($this->conn_res, $query);

    if(!$result) {
       $this->err_message = mysqli_error($this->conn_res);
    }
	
    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Affected_rows", mysqli_affected_rows($this->conn_res));
		$this->lib->benchmark->set($uid, 
					"Query", $this->lib->filter->clean_nl($query));
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
		
    return $result;

 } // end func query  
 
/**
 * Return last data from result
 *
 * @access public
 * @param string $query 
 * @param bool $benchmark
 * @return mixed 
 */
 public function result($query, $benchmark = false) {
	
	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
 	if(trim($query) == '' or !$this->reconnect($benchmark)) {
		if($benchmark) {
			$this->lib->benchmark->set($uid, "Empty query");
		}
		return false;
	}

    @$result = mysqli_query($this->conn_res, $query);
    
	if($result) {
        $data = mysqli_fetch_array($result, MYSQL_NUM);
        $data = $data[0];
        
		@mysqli_free_result($result);
		
    } else {
        $this->err_message = mysqli_error($this->conn_res);
        $data = false;
    } // end if result.

    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Affected_rows", mysqli_affected_rows($this->conn_res));
		$this->lib->benchmark->set($uid, 
					"Result", htmlspecialchars($data));
		$this->lib->benchmark->set($uid, 
					"Query", $this->lib->filter->clean_nl($query));
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
    return $data;

 } // end func result 

/**
 * Return array from by query string
 *
 * @access public
 * @param string $query
 * @param bool $benchmark
 * @return mixed array | bool
 */
 public function fetch_row($query, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
	$ret_arr = false;
	
 	if(!$this->reconnect($benchmark)) {
		$this->err_message = mysqli_error($this->conn_res);
		return false;
	}
 
    @$result = mysqli_query($this->conn_res, $query);
 
    if(!$result) {
		$this->err_message = mysqli_error($this->conn_res);
    } else {
		$ret_arr = mysqli_fetch_row($result);
		mysqli_free_result($result);
	}
    
    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Affected_rows", mysqli_affected_rows($this->conn_res));
		
		if(is_array($ret_arr)) {
			$this->lib->benchmark->set($uid, 
						"Count", count($ret_arr));
		} else {
			$this->lib->benchmark->set($uid, 
						"Count", "0");
		}
		
		$this->lib->benchmark->set($uid, 
					"Query", $this->lib->filter->clean_nl($query));
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
	return $ret_arr;
	
 } // end fetch_row 
  
/**
 * Return assoc array by query string
 *
 * @access public
 * @param string $query
 * @param bool $benchmark
 * @return mixed array | bool
 */
 public function fetch_assoc($query, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
	$ret_arr = false;
	
 	if(!$this->reconnect($benchmark)) {
		$this->err_message = mysqli_error($this->conn_res);
		return false;
	}

    @$result = mysqli_query($this->conn_res, $query);
 
    if(!$result) {
		$this->err_message = mysqli_error($this->conn_res);
	} else {
		$ret_arr = mysqli_fetch_assoc($result);
	    mysqli_free_result($result);
	}

    if($benchmark) {
		$this->lib->benchmark->set($uid, "Affected_rows", 
										mysqli_affected_rows($this->conn_res));
		
		if(is_array($ret_arr)) {
			$this->lib->benchmark->set($uid, "Count", count($ret_arr));
		} else {
			$this->lib->benchmark->set($uid, "Count", "0");
		}
		
		$this->lib->benchmark->set($uid, "Query", 
										$this->lib->filter->clean_nl($query));
		
		$this->lib->benchmark->set($uid, "Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
	return $ret_arr;
	
 } // end fetch_assoc 
 
/**
 * Return array of all records by query string
 *
 * @access public
 * @param string $query
 * @param bool $benchmark
 * @return mixed array | bool
 * @since 1.3.0
 */
 public function fetch_all_assoc($query, $benchmark = false) {

	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
 
	$ret_arr = false;
	
	if(!$this->reconnect($benchmark)) {
		$this->err_message = mysqli_error($this->conn_res);
		return false;
	}

	@$result = mysqli_query($this->conn_res, $query);
 
	if(!$result) {
		$this->err_message = mysqli_error($this->conn_res);
	} else {
		$ret_arr = array();
		
		while($row = mysqli_fetch_assoc($result)) {
			$ret_arr[] = $row;
		}
		
		mysqli_free_result($result);
	}
	
	if($benchmark) {
		$this->lib->benchmark->set($uid, "Affected_rows", 
										mysqli_affected_rows($this->conn_res));
		
		if(is_array($ret_arr)) {
			$this->lib->benchmark->set($uid, "Records", count($ret_arr));
		} else {
			$this->lib->benchmark->set($uid, "Records", "0");
		}
		
		$this->lib->benchmark->set($uid, "Query", 
										$this->lib->filter->clean_nl($query));
		
		$this->lib->benchmark->set($uid, "Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
	
	return $ret_arr;
	
 } // End func fetch_all_assoc
 
/**
 * Return object by query string
 *
 * @access public
 * @param string $query
 * @param bool $benchmark
 * @return mixed object | bool
 */
 public function fetch_object($query, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
 	if(!$this->reconnect($benchmark)) {
		$this->err_message = mysqli_error($this->conn_res);
		return false;
	}
 
    @$result = mysqli_query($this->conn_res, $query);
 
    if(!$result) {
		$this->err_message = mysqli_error($this->conn_res);
		return false;
    }

    $ret_obj = mysqli_fetch_object($result);
    mysqli_free_result($result);
    
    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Affected_rows", mysqli_affected_rows($this->conn_res));
		
		if(is_object($ret_obj)) {
			$is = 'Yes';
		} else {
			$is = 'No';
		}
		
		$this->lib->benchmark->set($uid, 
					"Is object", $is);
		$this->lib->benchmark->set($uid, 
					"Query", $this->lib->filter->clean_nl($query));
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
	return $ret_obj;
	
 } // end fetch_object 
 
/**
 * Return array of all records as objects by query string
 *
 * @access public
 * @param string $query
 * @param bool $benchmark
 * @return mixed array | bool
 * @since 1.3.0 
 */
 public function fetch_all_object($query, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
 	if(!$this->reconnect($benchmark)) {
		$this->err_message = mysqli_error($this->conn_res);
		return false;
	}
 
    @$result = mysqli_query($this->conn_res, $query);
 
    if(!$result) {
       $this->err_message = mysqli_error($this->conn_res);
	   return false;
    }

	$ret_arr = array();
	
	while($obj = mysqli_fetch_object($result)) {
		$ret_arr[] = $obj;
	}
	
    mysqli_free_result($result);
    
    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Affected_rows", mysqli_affected_rows($this->conn_res));
		
		$this->lib->benchmark->set($uid, 
					"Query", $this->lib->filter->clean_nl($query));
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
	return $ret_arr;
	
 } // end fetch_all_object
 
/**
 * Return count(*) from table
 * 
 * @access public
 * @param string $table
 * @param array $where
 * @param string $exp
 * @param bool $benchmark
 * @return mixed integer | bool 
 */ 
 public function count($table, $where = NULL, $exp = 'AND', $benchmark = false) {
 	
 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid();
		$this->lib->benchmark->start($uid);
	}	
	
 	if(!$this->reconnect($benchmark) or trim($table) == '') {
		return false;
	}
	
	if(strtoupper($exp) != 'AND' and strtoupper($exp) != 'OR') {
		return false;
	}
	
	$w = '';
	if(is_array($where)) {
		foreach($where as $field => $value) {
			$w .= $field . " = '" . $value . "' " . $exp . " ";
		}
		$w = rtrim($w, " " . $exp . " ");
		$w = " where " . $w;
	}
	
	$query = "select count(*) as cnt from " . $table . " " . $w; 
	
	$result = $this->result($query, $benchmark);
	
	if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Query", $this->lib->filter->clean_nl($query));
		$this->lib->benchmark->set($uid, 
					"Result", $result);
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
	return $result;
	 
 } // end func count
 
/**
 * Return assoc array from result
 *
 * @access public
 * @param resource $result
 * @param bool $benchmark
 * @return mixed array | bool
 */
 public function assoc($result, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
 	if(!$this->reconnect($benchmark) or !is_resource($result)) {
		return false;
	}
 
    @$ret_arr = mysqli_fetch_assoc($result);
    
    if(!$ret_arr) {
    	$this->err_message = mysqli_error($this->conn_res);
    }

    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Result resource", $result);
		
		if(is_array($ret_arr)) {
			$this->lib->benchmark->set($uid, 
						"Count", count($ret_arr));
		} else {
			$this->lib->benchmark->set($uid, 
						"Count", "0");
		}
		
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
	return $ret_arr;
	
 } // end assoc
 
/**
 * Return object from result
 *
 * @access public
 * @param resource $result
 * @param bool $benchmark
 * @return mixed object | bool
 */
 public function object($result, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
 	if(!$this->reconnect($benchmark) or !is_resource($result)) {
		return false;
	}
 
    @$ret_obj = mysqli_fetch_object($result);
    
    if(!$ret_obj) {
    	$this->err_message = mysqli_error($this->conn_res);
    }

    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Result resource", $result);
		$this->lib->benchmark->set($uid, 
					"Is object", is_object($ret_obj));
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
	return $ret_obj;
	
 } // end object 
 
/**
 * Return Array from result
 *
 * @access public
 * @param resource $result
 * @param bool $benchmark
 * @return mixed object | bool
 */
 public function row($result, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
 
 	if(!$this->reconnect($benchmark) or !is_resource($result)) {
		return false;
	}
 
    @$ret_arr = mysqli_fetch_row($result);
    
    if(!$ret_arr) {
    	$this->err_message = mysqli_error($this->conn_res);
    }
    
    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Result resource", $result);
		
		if(is_array($ret_arr)) {
			$this->lib->benchmark->set($uid, 
						"Count", count($ret_arr));
		} else {
			$this->lib->benchmark->set($uid, 
						"Count", "0");
		}	
		
		$this->lib->benchmark->set($uid, "Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }

	return $ret_arr;
	
 } // end row
  
/**
 * Return Fields count from result resource
 *
 * @access public
 * @param resource $result
 * @param bool $benchmark
 * @return integer
 * @since 1.4.0
 */
 public function num_fields($result, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
	if(!$this->reconnect($benchmark) or !is_resource($result)) {
		return false;
	}
	
	@$number = mysqli_num_fields($result);
    
	if(!$number) {
		$this->err_message = mysqli_error($this->conn_res);
		$number = 0;
    }

    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Result resource", $result);
		$this->lib->benchmark->set($uid, 
					"Number or fields", $number);
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
    return $number;
    
 } // End func num_fields
 
/**
 * Return Rows count from result resource
 *
 * @access public
 * @param resource $result
 * @param bool $benchmark
 * @return integer
 */
 public function num_rows($result, $benchmark = false) {

 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
	if(!$this->reconnect($benchmark) or !is_resource($result)) {
		return false;
	}
	
	@$number = mysqli_num_rows($result);
    
	if(!$number) {
		$this->err_message = mysqli_error($this->conn_res);
		$number = 0;
    }

    if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Result resource", $result);
		$this->lib->benchmark->set($uid, 
					"Number or rows", $number);
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
    
    return $number;
    
 } // End func num_rows

/**
 * Escape string
 * 
 * @access public
 * @param mixed $string
 * @return string
 */ 
 public function escape($string) {	
 	
 	if($this->reconnect()) {
		return mysqli_real_escape_string($this->conn_res, $string);
	}
	
	return mysqli_escape_string($string);
	
 } // end func escape
 
/**
 * MySQL free result
 *
 * @access public
 * @param resource $result
 * @param bool $benchmark
 * @return bool
 */
 public function free_result($result, $benchmark = false) {
 
 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
 	if(!$this->reconnect($benchmark) or !is_resource($result)) {
		return false;
	}
	
	if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Result resource", $result);
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
 
	return mysqli_free_result($result);
	
 } // end object  

/**
 * Return last insert id
 * 
 * @access public
 * @param bool $benchmark
 * @return mixed
 */
 public function insert_id($benchmark = false) {
 	
 	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid(); 
		$this->lib->benchmark->start($uid);
	}
	
 	if(!$this->reconnect($benchmark)) {
		return false;
	}
	
	$id = mysqli_insert_id($this->conn_res);
	
	if($benchmark) {
		$this->lib->benchmark->set($uid, 
					"Result ID", $id);
		$this->lib->benchmark->set($uid, 
					"Error", $this->err_message);
		$this->lib->benchmark->end($uid);
		$this->lib->benchmark->elapsed($uid);
    }
	
	return $id;
 } // end func insert_id

/**
 * Return affected rows from last query
 * 
 * @access public
 * @param bool $benchmark
 * @return mixed integer | bool
 */ 
 public function affected_rows($benchmark = false) {
 	
 	if(!$this->reconnect($benchmark)) {
		return false;
	}
	
	return mysqli_affected_rows($this->conn_res);
	
 } // end func affected_rows 

/**
 * Return last error
 * 
 * @access public
 * @return string
 */ 
 public function error($benchmark = false) {
 	
	if($benchmark) {
		$uid = __CLASS__.'::'.__FUNCTION__ . $this->uid();
		$this->lib->benchmark->set($uid, 
					"MySQL Error", mysqli_error($this->conn_res));
		$this->lib->benchmark->set($uid, 
					'$this->error', $this->err_message);
    }
    
 	return $this->err_message;
 }
 
/* End of class mysql_lib */
}

/* End of file */
?>