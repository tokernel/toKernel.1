<?php
/**
 * toKernel - Universal PHP Framework.
 * Parent abstract class for toKernel error handler, error exception.
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
 * @subpackage kernel
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2012 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.2.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * tk_e_core class
 * 
 * @abstract 
 * @author David Ayvazyan <tokernel@gmail.com>
 */ 
abstract class tk_e_core extends ErrorException {

/**
 * Is any error displayed 
 * 
 * @access protected
 * @staticvar bool
 */	
 protected static $error_displayed = false;

/**
 * Is error class configured by application
 * 
 * @access protected
 * @staticvar bool
 */
 protected static $configured = false;

/**
 * Debug information buffer
 * 
 * @access protected
 * @staticvar array
 */
 protected static $debug_buffer = array();
  
/**
 * Error types
 * 
 * @access protected
 * @staticvar array
 */ 
 protected static $error_types = array(
	E_NOTICE => 		'Notice',
	E_USER_NOTICE => 	'User Notice',

    E_WARNING => 		'Warning',
	E_USER_WARNING => 	'User Warning',
	E_CORE_WARNING => 	'Core Warning',
	E_COMPILE_WARNING =>'Compile Warning',

	E_ERROR => 			'Fatal Error',
	E_USER_ERROR => 	'User Error',
	E_CORE_ERROR => 	'Core Error',
	E_COMPILE_ERROR =>	'Compile Error',
	E_RECOVERABLE_ERROR => 'Recoverable Error',
	E_PARSE => 			'Parse Error',
	E_STRICT => 		'Strict',
	
	TK_ERROR_404 => 	'404 - Page Not Found'
	
 );

/**
 * Configuration array
 * 
 * @access protected
 * @staticvar array
 */
 protected static $config = array(
	
	'app_mode' 				=> 'production',
	'debug_mode'			=> false,
	'debug_log'				=> false,

	'show_notices' 			=> true,
	'show_warnings' 		=> true,
	'show_errors' 			=> true,
	'show_unknown_errors'	=> true,
	'show_errors_404'		=> true,
	
	'log_notices'			=> true,
	'log_warnings'			=> true,
	'log_errors'			=> true,
	'log_unknown_errors'	=> true,
	'log_errors_404'		=> true,
	 
	'log_file_extension'    => 'log',

	'err_subject_production' => 'Error occurred!',
	'err_message_production' => 'An internal server error occurred. Please try again later.',
	
	'err_404_subject' => '404 - Page Not Found',
	'err_404_message' => 'Server cannot find the file you requested. File has either been moved or deleted, or you entered the wrong URL or document name.',
	 
 	'theme' => 'default',
 	'mode' => TK_FRONTEND,
);

/**
 * Configure run mode
 * 
 * @static
 * @access public
 * @param array $config
 * @return void
 */
 public static function configure_run_mode($config) {
 	self::$config = array_merge(self::$config, $config);
 } // end func configure_run_mode

/**
 * Configure error handling
 * 
 * @static
 * @access public
 * @param array $config
 * @return void
 */
 public static function configure_error_handling($config) {
	self::$config = array_merge(self::$config, $config);
 } // end func configure

/**
 * custom string representation of object
 * 
 * @access public
 * @return string
 */ 
 public function __toString() {

 	$class = '(Called '.__CLASS__.'::'.__FUNCTION__.') ';
    self::log($class . $this->message, $this->code, $this->file, $this->line);
    return $class . self::get_error_type_text($this->code) . ': '. 
    		$this->message. ' in ' . $this->file . ' on line ' . $this->line;
 } // end func __toString

/**
 * Exception handler
 * 
 * @static
 * @access public
 * @param object tk_e $e
 * @return void
 */ 
 public static function exception(tk_e $e) {
    self::log_debug('', ':=========== HALTED WITH EXCEPTION ! ===========');
 	self::log($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
   	$e->show_error($e->getCode(),$e->getMessage(),$e->getFile(),$e->getLine(), $e->getTrace());
 } // end func exception
    
/**
 * Return error type by code
 * 
 * @static
 * @access public
 * @param integer $error_code
 * @return string
 */
 public static function get_error_type_text($error_code) {
 	
 	if(!isset(self::$error_types[$error_code])) {
 		return "Unknown Error";
 	}
	
 	return self::$error_types[$error_code]; 
 	
 } // end func get_error_type_text
 
/**
 * Return error group by code 
 * 
 * @static
 * @access public
 * @param integer $err_code
 * @return string
 */ 
 public static function get_error_group($err_code) {
 
 	switch($err_code) {
 		case E_NOTICE:
 		case E_USER_NOTICE:
 			$err_group = 'notice';
 			break;
 		case E_WARNING:
 		case E_USER_WARNING:
 		case E_CORE_WARNING:
 		case E_COMPILE_WARNING:
 			$err_group = 'warning';
 			break;
 		case E_ERROR:
 		case E_USER_ERROR:
 		case E_CORE_ERROR:
 		case E_COMPILE_ERROR:
 		case E_RECOVERABLE_ERROR:
 		case E_PARSE:
 		case E_STRICT:
 			$err_group = 'error';
 			break;
 		case TK_ERROR_404:
 			$err_group = 'error_404';
 			break;
 		default:
 			$err_group = 'unknown';
 			break; 	
 	} // end switch 
 	
 	return $err_group;
	
 } // end func get_error_group_text

/**
 * Log message
 * Logging only messages by type 
 * which allowed in configuration 
 * 
 * @param string $err_message
 * @param integer $err_code
 * @param string $file
 * @param integer $line
 */ 
 public static function log($err_message, $err_code = NULL, $file = NULL, 
 																$line = NULL) {
 	
 	if(!is_writeable(TK_CUSTOM_PATH . 'log')) {
		return false;
 	}
 																
	if($err_code == 404 and TK_RUN_MODE != 'cli') {
 		$err_message .= ' URL: ' . $_SERVER['QUERY_STRING'];
 	}

 	$error_group = self::get_error_group($err_code);
	$log_message = '';

    $err_type = self::get_error_type_text($err_code);
	$log_message .= $err_type . ': ' . $err_message;
		
 	if($file != '') {
 		$log_message .= ' in ' . $file;
 	}
 	
 	if($line != '') {
 		$log_message .= ' on line ' . $line;
 	}
 		
 	if(self::$config['log_notices'] != true and $error_group == 'notice') { 
    	return true;
    }
    	
    if(self::$config['log_warnings'] != true and $error_group == 'warning') { 
    	return true;
    }
    	
    if(self::$config['log_errors'] != true and $error_group == 'error') { 
    	return true;
    }
    	
    if(self::$config['log_unknown_errors'] != true and $error_group == 'unknown') { 
   		return true;
   	}
    	
    if(self::$config['log_errors_404'] != true and $error_group == 'error_404') { 
  		return true;
   	}

   	$lib = lib::instance();
   	$log = $lib->log->instance($error_group . '.' . 
   								self::$config['log_file_extension']);
   								
   	$log->write($log_message);
    	
    //error_log($log_message);
    return true;
 		 
 } // end func log
 
/**
 * Logging debug information
 * 
 * @param string $message
 * @param string $category
 * @param string $file
 * @param integer $line
 */ 
 public static function log_debug($message, $category = NULL, 
 									$file = NULL, $line = NULL) {

 	if(is_null($category)) {
 		$category = '~Global';
 	}

 	$log_message = '';

    $log_message .= $category . ': ' . $message;
		
 	if($file != '') {
 		$log_message .= ' in ' . $file;
 	}

 	if($line != '') {
 		$log_message .= ' on line ' . $line;
 	}

 	if(self::$config['debug_mode'] == '1') {
 		self::$debug_buffer[] = $log_message;
 	}
 										
 	if(self::$config['debug_log'] != true) { 
    	return true;
 	}
 	
 	$lib = lib::instance();
    $log = $lib->log->instance('debug.' . self::$config['log_file_extension']);
    $log->write($log_message);

    return true;

 } // end func log_debug
 
/* End of class tk_e_core */
}

/* End of file e.core.class.php */
?>