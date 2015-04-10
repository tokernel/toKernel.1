<?php
/**
 * toKernel - Universal PHP Framework.
 * toKernel error handler, error exception class for HTTP mode.
 * Child of tk_e_core class.
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
 * @copyright  Copyright (c) 2015 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.2.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * tk_e class
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */
 class tk_e extends tk_e_core {
	
/**
 * Show error
 * 
 * @access protected
 * @param integer $err_code
 * @param string $err_message
 * @param string $file
 * @param integer $line
 * @return void
 */
 protected static function show_error($err_code, $err_message, $file = NULL, 
										$line = NULL, $trace = NULL) {

    $error_group = self::get_error_group($err_code);
    $err_type = self::get_error_type_text($err_code);
		
    $err_show_str = $err_message;
    
    if(isset($file) and self::$config['app_mode'] == 'development') {
    	$err_show_str .= TK_NL . 'File: ' . $file;
    }
    	
    if(isset($line) and self::$config['app_mode'] == 'development') {
    	$err_show_str .= TK_NL . 'Line: ' . $line;
    }
    	
    if(self::$config['app_mode'] == 'production') {
    		
    	if($err_code != 404) {
    			
    		$err_type = self::$config['err_subject_production'];
    		$err_show_str = self::$config['err_message_production']; 
    			
    	} else {
    			
    		$err_type = self::$config['err_404_subject'];
    		$err_show_str = self::$config['err_404_message'];
    		
    	}
		
		/* In the production mode, the trace will not be displayed */
		$trace = NULL;

    } 

    ob_clean();
    
    if(!headers_sent()) {
		
    	// Make sure the proper content type is sent with a 500 status
   		if($err_code != 404) {
    		header('HTTP/1.0 500 Internal Server Error', true, 500);
    	} else {
    		header("HTTP/1.0 404 Not Found", true, 404);
    	}
	}
    		
    $err_tpl_file = self::get_error_template_file($error_group);
    
	if(is_array($trace)) {
		$trace = array_reverse($trace);
	}
		
    if($err_tpl_file != false) {
		require($err_tpl_file);
    } else {
    	echo TK_NL;
    	echo '<strong>';
    	echo $err_type . TK_NL;
    	echo '</strong>';
    	echo $err_show_str . TK_NL;
    	echo TK_NL;
    }
    	
    self::$error_displayed = true;

    self::log_debug('', ':============= HALTED WITH ERROR ! =============');
    
    if(self::$config['debug_mode'] == 1) {
    	self::show_debug_info();
    }
    	
    exit(1);
    
 } // end func show_error
    
/**
 * Get error template file
 * 
 * @access protected
 * @param string $err_group
 * @return mixed
 */ 
 protected static function get_error_template_file($err_group) {
	
	switch($err_group) {
		
		case 'error':
			$error_file = 'error.tpl.php';
			break;
		case 'error_404':
			$error_file = 'error_404.tpl.php';
			break;
		default:
			$error_file = 'warning.tpl.php';
			break;	
	}
	
	if(defined('TK_CUSTOM_PATH')) {
		$app_error_file = TK_CUSTOM_PATH . 'templates' . TK_DS . $error_file;
	} else {
		$app_error_file = '';
	}
	
 	$tk_error_file = TK_PATH . 'templates' . TK_DS . $error_file;

	if(is_file($app_error_file)) {
		$err_file = $app_error_file;
	} elseif(is_file($tk_error_file)) {
		$err_file = $tk_error_file;
	} else {
		return false;
	}

	return $err_file;
	
 } // end func get_error_template_file
 
 
/**
 * Error handler
 * 
 * @access public
 * @param integer $err_code
 * @param string $err_message
 * @param string $file
 * @param integer $line
 * @return bool
 */  
 public static function error($err_code, $err_message, $file = NULL, $line = NULL) {

 	$error_group = self::get_error_group($err_code);
    self::log($err_message, $err_code, $file, $line);
    
	$trace = debug_backtrace(false);
	
    if(self::$config['show_notices'] == '1' and $error_group == 'notice') { 
    	self::show_error($err_code, $err_message, $file, $line, $trace);
    } 
    	 
    if(self::$config['show_warnings'] == '1' and $error_group == 'warning') { 
    	self::show_error($err_code, $err_message, $file, $line, $trace);
    } 
    	
    if(self::$config['show_errors'] == '1' and $error_group == 'error') {
   		self::show_error($err_code, $err_message, $file, $line, $trace);
    } 
    	
    if(self::$config['show_unknown_errors'] == '1' and 
    											$error_group == 'unknown') {
    												 
    	self::show_error($err_code, $err_message, $file, $line, $trace);
    } 
    	
   	if(self::$config['show_errors_404'] == '1' and $error_group == 'error_404') { 
    	self::show_error($err_code, $err_message, $file, $line, $trace);
    } 
    	
   	return true;
 } // end func error

/**
 * Shutdown handler
 * 
 * @static
 * @access public
 * @return void
 */ 
public static function shutdown() {
	
	$error = error_get_last();
	
	if($error !== NULL and self::$error_displayed === true) {
		self::log($error['message'], $error['type'], 
				  $error['file'],	$error['line']);
		exit(1);
	}
	
	if($error !== NULL and self::$error_displayed === false) {
		self::error($error['type'], $error['message'], 
					$error['file'],	$error['line']);
		exit(1);
	}
	
} // end func shutdown 

/**
 * Show debug informarion
 * 
 * @static
 * @access public
 * @return void
 */
public static function show_debug_info() {
	
	$app = app::instance();
	$lib = lib::instance();
	
	if(!defined('TK_END_RUN')) {
		define('TK_END_RUN', round(microtime(true),3));
		define('TK_RUN_DURATION', '(defined in ' . __CLASS__ . '::' . 
				__FUNCTION__ . ')' . TK_NL . 
				round((TK_END_RUN - TK_START_RUN), 3));
	}
	
	$memory_usage = round(memory_get_usage() / 1024, 2);
	
	$app_mode = self::$config['app_mode'];
	$cache_mode = $app->config('cache_expiration', 'CACHING');
	
	if($cache_mode == '0') {
		$cache_mode = 'Disabled';
	} elseif($cache_mode == '-1') {
		$cache_mode = 'Never expire';
	} else {
		$cache_mode = 'Expire after: '.$cache_mode.' minute(s)';
	}
	
	if($app->cached_output() == true) { 
		$output_from_cache = 'Yes';
	} else {
		$output_from_cache = 'No';
	}
		
	if($lib->url->id_addon() != '') {
		$addon_action_info = $lib->url->id_addon().'::'.$lib->url->action();
	} else {
		$addon_action_info = '';
	}

	$app_params = '';
	
	foreach($lib->url->params() as $p_k => $p_v) {
		$app_params .= $p_k . '=' . $p_v . TK_NL;
	}
	
	$app_language = $app->language();
	
	$loaded_libs = implode(TK_NL, $lib->loaded());
	$loaded_addons = implode(TK_NL, $lib->addons->loaded());
	
	$benchmark_dump = '';
	foreach($lib->benchmark->get() as $b_key => $b_data) {
		$benchmark_dump .= '<p><strong>' . $b_key . '</strong>'.TK_NL;
		foreach($b_data as $o_key => $o_value) {
			$benchmark_dump .= $o_key . ': ' . $o_value . TK_NL;
	 	} 
		$benchmark_dump .= '</p>'; 
	} 
		
	$allow_hooks = $app->config('allow_http_hooks', 'HTTP');
	if($allow_hooks == 1) {
		$allow_hooks = 'yes';
	} else {
		$allow_hooks = 'no';
	}
	
	/* Show Debug screen */
	$debug_buffer_str = NULL; 
	
	if(count(self::$debug_buffer) > 0) {
		$debug_buffer_str .= '<ol>';
		foreach(self::$debug_buffer as $debug_msg) {
			$debug_buffer_str .= '<li> ' . $debug_msg . '</li>';
		}
		$debug_buffer_str .= '</ol>';
	}
	
	/* Include debug template file */
	if(is_file(TK_CUSTOM_PATH . 'templates' . TK_DS . 'debug.tpl.php')) {
		require(TK_CUSTOM_PATH . 'templates' . TK_DS . 'debug.tpl.php');
	} else {
		require(TK_PATH . 'templates' . TK_DS . 'debug.tpl.php');
	}	
	
} // end func show_debug_info

/* End of class tk_e */
}

/* End of file e.http.class.php */
?>