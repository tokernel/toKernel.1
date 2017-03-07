<?php
/**
 * toKernel - Universal PHP Framework.
 * MySQL wrapper class library
 *
 * For the mysql Server configuration, this library uses /application/config/databases.ini file
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
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    3.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
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
     * Main libraries instance
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
     * Last affected rows
     *
     * @access protected
     * @var int
     */
    protected $last_affected_rows = 0;

    /**
     * Logging object
     *
     * @access protected
     * @var object
     */
    protected $log;

    /**
     * Connection section name in configuration file
     *
     * @access protected
     * @var string
     */
    protected $conn_section;

    /**
     * Class constructor
     *
     * @access public
     * @return object
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

        $this->conn_link = null;
        $this->conn_ini = null;
        $this->log = null;
        $this->err_message = '';

    } // end destructor

    /**
     * Return clone of this object
     *
     * @access public
     * @param mixed string | null $conn_section
     * @return object
     */
    public function instance($conn_section = null) {

        $n = clone $this;
        $n->__destruct();

        if(!is_null($conn_section)) {
            $n->configure($conn_section);
        }

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
     * @param string $conn_section
     * @return bool
     */
    public function configure($conn_section) {

        $uid = __CLASS__.'::'.__FUNCTION__.'()';

        $this->conn_section = $conn_section;

        $ini_file = TK_CUSTOM_PATH . 'config' . TK_DS . 'databases.ini';
        $mci = $this->lib->ini->instance($ini_file, $conn_section);

        if(!is_object($mci)) {
            $this->react_to_error('Can not access to MySQL Connection configuration values'.
                ' in file `' . $ini_file . '` with section `' . $conn_section . '` !');
        }

        $conn_ini = $mci->section_get($conn_section);

        // Check each item in configuration array of ini file.
        $required_config = array(
            'host',
            'database',
            'username',
            'password'
        );

        foreach($required_config as $config_item) {
            if(!isset($conn_ini[$config_item])) {

                $this->react_to_error('Invalid MySQL connection values! ' .
                    'item `'.$config_item.'` requires to be set ' .
                    'in file `' . $ini_file . '` with section `' . $conn_section . '` !');

                return false;
            }
        }

        // By default the character set is utf8
        if(!isset($conn_ini['charset'])) {
            $conn_ini['charset'] = 'utf8';
        }

        // By default errors will be logged
        if(!isset($conn_ini['log_errors'])) {
            $conn_ini['log_errors'] = 1;
        }

        // By default errors will be displayed
        if(!isset($conn_ini['display_errors'])) {
            $conn_ini['display_errors'] = 1;
        }

        // By default the benchmarking process not logging
        if(!isset($conn_ini['log_benchmarking'])) {
            $conn_ini['log_benchmarking'] = 0;
        }

        $this->conn_ini = $conn_ini;

        return true;

    } // end func configure

    /**
     * Return configuration value by item name
     *
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
     * Return "Yes" if connection password is not empty.
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
     * Connect to MysqL Server and select database
     *
     * @access public
     * @param string | null $conn_section
     * @return mixed resource | bool
     */
    public function connect($conn_section = NULL) {

        $uid = __CLASS__.'::'.__FUNCTION__.'()';
        $this->benchmark_start($uid);

        if(!function_exists('mysqli_connect' )) {
            $this->react_to_error('The `mysqli` adapter is not available.');
            return false;
        }

        if(!is_null($conn_section) and !$this->configure($conn_section)) {
            $this->react_to_error('The MySQL connection not configured.');
            return false;
        }

        if ($this->conn_ini['port'] != '') {
            $this->conn_ini['host'] .= ':' . $this->conn_ini['port'];
        }

        $this->conn_res = @mysqli_connect($this->conn_ini['host'], $this->conn_ini['username'],	$this->conn_ini['password']);

        if(!$this->conn_res) {
            $this->react_to_error();
            return false;
        }

        // Select database
        if(!mysqli_select_db($this->conn_res, $this->conn_ini['database'])) {
            $this->react_to_error();
            return false;
        }

        // Set character set
        if(!mysqli_set_charset($this->conn_res, $this->conn_ini['charset'])) {
            $this->react_to_error();
            return false;
        }

        $this->benchmark_set($uid, 'Connected to MySQL Server by configuration section', $this->conn_section);
        $this->benchmark_end($uid);

        return $this->conn_res;

    } // end func connect

    /**
     * If connection not established, try to connect.
     *
     * @access public
     * @return bool
     */
    public function reconnect() {

        // Already connected
        if($this->conn_res and mysqli_ping($this->conn_res) === true) {
            return true;
        }

        // Not configured
        if(!is_array($this->conn_ini)) {
            $this->react_to_error('MySQL Library not configured!');
            return false;
        }

        // Unable to connect
        if(!$this->connect()) {
            $this->react_to_error('Unable to connect to MySQL Server!');
            return false;
        }

        // Connection success
        return true;

    } // end func reconnect

    /**
     * Disconnect
     *
     * @access public
     * @return void
     */
    public function disconnect() {

        $uid = __CLASS__.'::'.__FUNCTION__.'()';
        $this->benchmark_start($uid);

        if($this->conn_res) {
            mysqli_close($this->conn_res);
        }

        $this->benchmark_set($uid, 'Disconnect from MySQL Server', 'close');
        $this->benchmark_end($uid);

    } // end disconnect

    /**
     * Regular mysql query
     *
     * @access public
     * @param string $query
     * @return mixed object | bool
     */
    public function query($query) {

        $uid = __CLASS__.'::'.__FUNCTION__ . $this->uid();
        $this->benchmark_start($uid);

        if(trim($query) == '') {
            $this->react_to_error('Empty query!');
            return false;
        }

        $this->reconnect();

        $result = mysqli_query($this->conn_res, $query);

        $this->last_affected_rows = mysqli_affected_rows($this->conn_res);

        if(!$result) {
            $this->react_to_error(null, $query);
        }

        $this->benchmark_set($uid, 'Result type', gettype($result));
        $this->benchmark_set($uid, 'Affected_rows', $this->last_affected_rows);
        $this->benchmark_set($uid, 'Query', $query);
        $this->benchmark_end($uid);

        return $result;

    } // end func query

    /**
     * Return only one value from result row.
     * Return NULL If record not found.
     *
     * @access public
     * @param string $query
     * @return mixed
     */
    public function result($query) {

        $this->reconnect();

        $result = $this->query($query);

        if($result === false) {
            $this->react_to_error(null, $query);
            $result_data = NULL;
        } elseif($result->num_rows == 0) {
            $result_data = NULL;
        } else {
            $result_data = mysqli_fetch_array($result, MYSQLI_NUM);
            $result_data = $result_data[0];
            $this->free_result($result);
        } // end if result.

        return $result_data;

    } // end func result

    /**
     * Return count(*) from table
     *
     * @deprecated
     * @access public
     * @param string $table
     * @param mixed array | null $where
     * @param mixed string | null $exp
     * @return mixed integer | bool
     */
    public function count($table, $where = NULL, $exp = 'AND') {

        $this->reconnect();

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

        $result = $this->result($query);

        return $result;

    } // end func count

    /**
     * Return array from by query string
     *
     * @access public
     * @param string $query
     * @return mixed array | bool
     */
    public function fetch_row($query) {

        $this->reconnect();

        $result = $this->query($query);

        if(!$result) {
            $this->react_to_error(null, $query);
            $ret_arr = false;
        } else {
            $ret_arr = mysqli_fetch_row($result);
            $this->free_result($result);
        }

        return $ret_arr;

    } // end fetch_row

    /**
     * Return assoc array by query string
     *
     * @access public
     * @param string $query
     * @return mixed array | bool
     */
    public function fetch_assoc($query) {

        $this->reconnect();

        $result = $this->query($query);

        if(!$result) {
            $this->react_to_error(null, $query);
            $ret_arr = false;
        } else {
            $ret_arr = mysqli_fetch_assoc($result);
            $this->free_result($result);
        }

        return $ret_arr;

    } // end fetch_assoc

    /**
     * Return object by query string
     *
     * @access public
     * @param string $query
     * @return mixed object | bool
     */
    public function fetch_object($query) {

        $this->reconnect();

        $result = $this->query($query);

        if(!$result) {
            $this->react_to_error(null, $query);
            $ret_obj = false;
        } else {
            $ret_obj = mysqli_fetch_object($result);
            $this->free_result($result);
        }

        return $ret_obj;

    } // end fetch_object

    /**
     * Return array of all records by query string
     *
     * @access public
     * @param string $query
     * @return mixed array | bool
     * @since 1.3.0
     */
    public function fetch_all_assoc($query) {

        $this->reconnect();

        $result = $this->query($query);

        if(!$result) {
            $this->react_to_error(null, $query);
            $ret_arr = false;
        } else {
            $ret_arr = array();

            while($row = mysqli_fetch_assoc($result)) {
                $ret_arr[] = $row;
            }

            $this->free_result($result);
        }

        return $ret_arr;

    } // End func fetch_all_assoc

    /**
     * Return array of all records as objects by query string
     *
     * @access public
     * @param string $query
     * @return mixed array | bool
     * @since 1.3.0
     */
    public function fetch_all_object($query) {

        $this->reconnect();

        $result = $this->query($query);

        if(!$result) {
            $this->react_to_error(null, $query);
            $ret_arr = false;
        } else {
            $ret_arr = array();

            while($obj = mysqli_fetch_object($result)) {
                $ret_arr[] = $obj;
            }

            $this->free_result($result);
        }

        return $ret_arr;

    } // end fetch_all_object

    /**
     * Return assoc array from result
     *
     * @access public
     * @param object $result
     * @return mixed array | bool
     */
    public function assoc($result) {

        if(!is_object($result)) {
            $this->react_to_error('Result set must be an object.');
            return false;
        }

        @$ret_arr = mysqli_fetch_assoc($result);

        if(!$ret_arr) {
            return false;
        }

        return $ret_arr;

    } // end assoc

    /**
     * Return object from result
     *
     * @access public
     * @param object $result
     * @return mixed object | bool
     */
    public function object($result) {

        if(!is_object($result)) {
            $this->react_to_error('Result set must be an object.');
            return false;
        }

        @$ret_obj = mysqli_fetch_object($result);

        if(!$ret_obj) {
            return false;
        }

        return $ret_obj;

    } // end object

    /**
     * Return Array from result
     *
     * @access public
     * @param object $result
     * @return mixed array | bool
     */
    public function row($result) {

        if(!is_object($result)) {
            $this->react_to_error('Result set must be an object.');
            return false;
        }

        @$ret_arr = mysqli_fetch_row($result);

        if(!$ret_arr) {
            return false;
        }

        return $ret_arr;

    } // end row

    /**
     * Return Fields count from result resource
     *
     * @access public
     * @param object $result
     * @return int | bool
     * @since 1.4.0
     */
    public function num_fields($result) {

        if(!is_object($result)) {
            $this->react_to_error('Result set must be an object.');
            return false;
        }

        @$number = mysqli_num_fields($result);

        if(!$number) {
            $this->react_to_error();
            $number = 0;
        }

        return $number;

    } // End func num_fields

    /**
     * Return Rows count from result resource
     *
     * @access public
     * @param object $result
     * @return integer | bool
     */
    public function num_rows($result) {

        if(!is_object($result)) {
            $this->react_to_error('Result set must be an object.');
            return false;
        }

        $number = mysqli_num_rows($result);

        return $number;

    } // End func num_rows

    /**
     * Return affected rows count from last query
     *
     * @access public
     * @return integer
     */
    public function affected_rows() {

        $this->reconnect();

        return $this->last_affected_rows;

    } // end func affected_rows

    /**
     * Escape string
     *
     * @access public
     * @param mixed $string
     * @return mixed string | bool
     */
    public function escape($string) {

        $this->reconnect();

        return mysqli_real_escape_string($this->conn_res, $string);

    } // end func escape

    /**
     * MySQL free result
     *
     * @access public
     * @param object $result
     * @return void
     */
    public function free_result($result) {

        if(!is_object($result)) {
            return NULL;
        }

        return mysqli_free_result($result);

    } // end object

    /**
     * Return last insert id
     *
     * @access public
     * @return mixed
     */
    public function insert_id() {

        $this->reconnect();

        $id = mysqli_insert_id($this->conn_res);

        return $id;

    } // end func insert_id

    /**
     * Check if the table name is empty
     *
     * @access protected
     * @param string $table
     * @return bool
     * @since version 3.0.0
     */
    protected function check_table($table) {

        if(trim($table) == '') {
            $this->react_to_error('Table name is empty.');
        }

        return true;

    } // End function check_table

    /**
     * Check if the data array is empty
     *
     * @access protected
     * @param array $arr
     * @return bool
     * @since version 3.0.0
     */
    protected function check_arr($arr) {

        if(!is_array($arr) or empty($arr)) {
            $this->react_to_error('Empty data/params Array.');
        }

        return true;

    } // End func check_arr

    /**
     * Insert data into database table
     *
     * @access public
     * @param string $table
     * @param array $data
     * @return int
     * @since version 3.0.0
     */
    public function insert($table, $data) {

        $this->check_table($table);
        $this->check_arr($data);

        $sql = 'insert into ' . $table . ' set ';
        $sql .= $this->build_data($data);

        $this->query($sql);

        return $this->insert_id();

    } // End func insert

    /**
     * Update data in database table
     *
     * @access public
     * @param string $table
     * @param array $data
     * @param array $where
     * @return int
     * @since version 3.0.0
     */
    public function update($table, $data, $where) {

        $this->check_table($table);
        $this->check_arr($data);
        $this->check_arr($where);

        $sql = 'update ' . $table . ' set ';
        $sql .= $this->build_data($data);
        $sql .= $this->build_where($where);

        $this->query($sql);

        return $this->affected_rows();

    } // End func update

    /**
     * Delete data in database table
     *
     * @access public
     * @param string $table
     * @param array $where
     * @return int
     * @since version 3.0.0
     */
    public function delete($table, $where) {

        $this->check_table($table);
        $this->check_arr($where);

        $sql = 'delete from ' . $table . ' ';
        $sql .= $this->build_where($where);

        $this->query($sql);

        return $this->affected_rows();

    } // End func delete

    /**
     * Return records count from database table
     *
     * @access public
     * @param string $table
     * @param mixed array | NULL $where
     * @return int
     * @since version 3.0.0
     */
    public function select_count($table, $where = NULL) {

        $this->check_table($table);

        $sql = "select count(*) as cnt from " . $table . " ";
        $sql .= $this->build_where($where);

        return $this->result($sql);

    } // End func count

    /**
     * Select row from database table as assoc array
     *
     * @access public
     * @param string $table
     * @param mixed array | NULL $where
     * @return array
     * @since version 3.0.0
     */
    public function select_assoc($table, $where = NULL) {

        $this->check_table($table);

        $sql = "select * from " . $table . " ";
        $sql .= $this->build_where($where);

        return $this->fetch_assoc($sql);

    } // End func select_assoc

    /**
     * Select row from database table as object
     *
     * @access public
     * @param string $table
     * @param mixed array | NULL $where
     * @return object
     * @since version 3.0.0
     */
    public function select_object($table, $where = NULL) {

        $this->check_table($table);

        $sql = "select * from " . $table . " ";
        $sql .= $this->build_where($where);

        return $this->fetch_object($sql);

    } // End func select_objects

    /**
     * Select rows from database table as assoc array
     *
     * @access public
     * @param string $table
     * @param mixed array | NULL $where
     * @return array
     * @since version 3.0.0
     */
    public function select_all_assoc($table, $where = NULL) {

        $this->check_table($table);

        $sql = "select * from " . $table . " ";
        $sql .= $this->build_where($where);

        return $this->fetch_all_assoc($sql);

    } // End func select_all_assoc

    /**
     * Select records from database table as objects
     *
     * @access public
     * @param string $table
     * @param mixed array | NULL $where
     * @return array
     * @since version 3.0.0
     */
    public function select_all_object($table, $where = NULL) {

        $this->check_table($table);

        $sql = "select * from " . $table . " ";
        $sql .= $this->build_where($where);

        return $this->fetch_all_object($sql);

    } // End func select_all_objects

    /**
     * Build where condition for query
     *
     * @access protected
     * @param mixed array | null $where
     * @return string
     * @since version 3.0.0
     */
    protected function build_where($where = NULL) {

        if(is_null($where)) {
            return '';
        }

        $sql = " where ";

        foreach($where as $field => $value) {
            $sql .= $field . " = '" . $this->escape($value) . "' and ";
        }

        $sql = rtrim($sql, 'and ');

        return $sql;
    }

    /**
     * Build data insert or update for query
     *
     * @access protected
     * @param array $data
     * @return array
     */
    protected function build_data($data) {

        if(!is_array($data) or empty($data)) {
            $this->react_to_error('Data array cannot be empty!');
            return false;
        }

        $sql = '';

        foreach($data as $field => $value) {
            $sql .= $field . " = '" . $this->escape($value) . "', ";
        }

        $sql = rtrim($sql, ', ');

        return $sql;

    } // End func build_data

    /**
     * Start the benchmark process
     *
     * @access protected
     * @param $uid
     * @return void
     * @since version 3.0.0
     */
    protected function benchmark_start($uid) {

        if($this->config('log_benchmarking') != 1) {
            return false;
        }

        $this->lib->benchmark->start($uid);

    } // End func benchmark_start

    /**
     * Set benchmark data
     *
     * @access protected
     * @param $uid
     * @param string $item
     * @param mixed $value
     * @return void
     * @since version 3.0.0
     */
    protected function benchmark_set($uid, $item, $value) {

        if($this->config('log_benchmarking') != 1) {
            return false;
        }

        $this->lib->benchmark->set($uid, $item, $value);

    } // End func benchmark_set

    /**
     * End the benchmark process and log
     *
     * @access protected
     * @param $uid
     * @return void
     * @since version 3.0.0
     */
    protected function benchmark_end($uid) {

        if($this->config('log_benchmarking') != 1) {
            return false;
        }

        $this->lib->benchmark->end($uid);
        $this->lib->benchmark->elapsed($uid);

        $buffer = $this->lib->benchmark->get($uid);

        // Define log object for benchmarking
        if(is_null($this->log)) {
            $this->log = $this->lib->log->instance('mysql.benchmarking.log');
        }

        foreach ($buffer as $item => $value) {
            $log_message = '['.$this->conn_section.']['.$uid.'] ' . $item . ': ' . $value;
            $this->log->write($log_message);
        }

    } // End func benchmark_end

    /**
     * Begin transaction
     *
     * @access public
     * @param bool $auto_commit = false
     * @return bool
     * @since version 3.0.0
     */
    public function begin_trans($auto_commit = false) {

        // Check connection
        $this->reconnect();

        if(!function_exists('mysqli_begin_transaction')) {
            return mysqli_autocommit($this->conn_res, $auto_commit);
        }

        mysqli_autocommit($this->conn_res, $auto_commit);

        $result = mysqli_begin_transaction($this->conn_res);

        if(!$result) {
            $this->react_to_error();
            return false;
        }

        return true;

    } // End func begin_trans

    /**
     * Commit transaction
     *
     * @access public
     * @return bool
     * @since version 3.0.0
     */
    public function commit_trans() {

        // Check connection
        $this->reconnect();

        $result = mysqli_commit($this->conn_res);

        if(!$result) {
            $this->react_to_error();
            return false;
        }

        mysqli_autocommit($this->conn_res, true);

        return $result;

    } // End func commit_trans

    /**
     * Roll back transaction
     *
     * @access public
     * @return bool
     * @since version 3.0.0
     */
    public function rollback_trans() {

        // Check connection
        $this->reconnect();

        $result = mysqli_rollback($this->conn_res);

        if(!$result) {
            $this->react_to_error();
            return false;
        }

        return true;

    } // End func rollback_trans

    /**
     * Return last error
     *
     * @access public
     * @return string
     */
    public function error() {
        return $this->err_message;
    }

    /**
     * Log error
     * Display error if enabled in configuration
     *
     * @access protected
     * @param mixed string | null $err_message
     * @param mixed string | null $query
     * @return void
     * @since version 3.0.0
     */
    protected function react_to_error($err_message = NULL, $query = NULL) {

        if(!is_null($err_message)) {
            $this->err_message = $err_message;
        } else {
            $this->err_message = mysqli_error($this->conn_res);
        }

        if(!is_null($query)) {
            $this->err_message .= TK_NL . "MySQL Query: " . $query . "\n";
        }

        // Initialize default value if the configuration still not complete
        if(empty($this->conn_ini)) {
            $this->conn_ini = array(
                'log_errors' => 1,
                'display_errors' => 1
            );
        }

        if($this->config('display_errors') == 1) {
            trigger_error("MySQL: " . $this->err_message, E_USER_ERROR);
        }

        if($this->config('log_errors') == 1) {
            tk_e::log("MySQL: " . $this->err_message, E_USER_ERROR);
        }

    } // End func react_to_error

    /* End of class mysql_lib */
}

/* End of file */
?>