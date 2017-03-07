<?php
/**
 * toKernel - Universal PHP Framework.
 * toKernel error handler, error exception class for CLI mode.
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
 * @category   kernel
 * @package    framework
 * @subpackage kernel
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
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
 * Show error on command line
 * 
 * @access protected
 * @param integer $err_code
 * @param string $err_message
 * @param string $file = NULL
 * @param integer $line = NULL
 * @param mixed $trace = NULL
 * @return void
 */
 protected static function show_error($err_code, $err_message, $file = NULL, 
										$line = NULL, $trace = NULL) {
    	
 	$error_group = self::get_error_group($err_code);
    $err_type = self::get_error_type_text($err_code);
		
    $err_show_str = ' ' . $err_message;
    	
    if(isset($file) and self::$config['app_mode'] == 'development') {
    	$err_show_str .= TK_NL . ' File: ' . $file;
    }
    	
    if(isset($line) and self::$config['app_mode'] == 'development') {
    	$err_show_str .= TK_NL . ' Line: ' . $line;
    }
    
	if(self::$config['app_mode'] == 'production') {
    		
    	$err_type = self::$config['err_subject_production'];
    	$err_show_str = self::$config['err_message_production']; 
		
		/* In the production mode, the trace will not be displayed */
		$trace = NULL;
    } elseif(is_array($trace)) {
		
		$trace = array_reverse($trace);
		
		$err_show_str .= TK_NL;
		$err_show_str .= '[Debug trace]';
		$err_show_str .= TK_NL;
		
		foreach($trace as $i => $t) {
		
			if($t['function'] == 'trigger_error') {
				break;
			}
			
			if(!isset($t['class'])) {
				$t['class'] = '';
			}
			
			if(!isset($t['type'])) {
				$t['type'] = '';
			}
			
			if(!isset($t['file'])) {
				$t['file'] = '';
			}
			
			if(!isset($t['line'])) {
				$t['line'] = '';
			}
		
			$err_show_str .= sprintf("#%d %s%s%s() called at %s:%d \n", $i,$t['class'], $t['type'],$t['function'],$t['file'],$t['line']) . TK_NL;
		}
	}

    /* Show colored text message */
    if(strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
    			
    	$colored_string = TK_NL;
    	$colored_string .= " \033[1;37m";
    	$colored_string .= "\033[41m";
    	$colored_string .= " " . $err_type . " " . "\033[0m" . TK_NL . TK_NL;
    		    			
    	$colored_string .= "\033[1;37m";
    	$colored_string .= $err_show_str . "\033[0m" . TK_NL . TK_NL;
    			
    	$colored_string .= "\033[0;33m";
    	$colored_string .= " toKernel - Universal PHP Framework. v" . 
    											TK_VERSION . TK_NL;
    											 
    	$colored_string .= " http://www.tokernel.com" . "\033[0m" . 
    											TK_NL . TK_NL;

		fwrite(STDERR, $colored_string);
				    			
    } else {
    			
    	$string = TK_NL;
    	$string .= ' ' . $err_type . TK_NL;
    	$string .= $err_show_str . TK_NL . TK_NL;
    	$string .= " toKernel - Universal PHP Framework. v" . 
    									TK_VERSION . TK_NL;
    									 
    	$string .= " http://www.tokernel.com" . TK_NL . TK_NL;
    			
    	echo $string;
    }
    	
    self::$error_displayed = true;
    
    self::log_debug('', ':============= HALTED WITH ERROR ! =============');
    
	exit(1);
 
 } // end func show_error
	
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
    	
    if(self::$config['show_notices'] == true and $error_group == 'notice') { 
    	self::show_error($err_code, $err_message, $file, $line, $trace);
    } 
    	 
    if(self::$config['show_warnings'] == true and $error_group == 'warning') { 
    	self::show_error($err_code, $err_message, $file, $line, $trace);
    } 
    	
    if(self::$config['show_errors'] == true and $error_group == 'error') {
   		self::show_error($err_code, $err_message, $file, $line, $trace);
    } 
    	
    if(self::$config['show_unknown_errors'] == true and 
    											$error_group == 'unknown') {
    												 
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
	
	if($error !== NULL and self::$error_displayed === false) {
		
		self::error($error['type'], $error['message'], 
					$error['file'], $error['line']);
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
		define('TK_END_RUN', round(microtime(true), 3));
		define('TK_RUN_DURATION', '(defined in ' . __CLASS__ . '::' . 
				__FUNCTION__ . ')' . TK_NL . 
				round((TK_END_RUN - TK_START_RUN), 3));
	}
	
	$memory_usage = round(memory_get_usage() / 1024, 2);
	
	$app_mode = self::$config['app_mode'];
	
	if($lib->cli->id_addon() != '') {
		$addon_action_info = $lib->cli->id_addon().'::'.$lib->cli->action();
	} else {
		$addon_action_info = '';
	}

	$app_params = '';
	
	foreach($lib->cli->params() as $p_k => $p_v) {
		$app_params .= $p_k . '=' . $p_v . TK_NL;
	}
	
	$app_language = $app->language();
	
	$loaded_libs = implode(', ', $lib->loaded());
	$loaded_addons = implode(', ', $lib->addons->loaded());
	
	$benchmark_dump = '';
	foreach($lib->benchmark->get() as $b_key => $b_data) {
		$benchmark_dump .= '[' . $b_key . ']'.TK_NL;
		foreach($b_data as $o_key => $o_value) {
			$benchmark_dump .= $o_key . ': ' . $o_value . TK_NL;
	 	} 
	} 
	
	$allow_hooks = $app->config('allow_cli_hooks', 'CLI');
	if($allow_hooks == 1) {
		$allow_hooks = 'yes';
	} else {
		$allow_hooks = 'no';
	}
	
	$lib->cli->out(TK_NL);
	$lib->cli->out(' toKernel - Debug information ', 'black', 'yellow');
	$lib->cli->out(TK_NL);
	
	$lib->cli->out(TK_NL);
	$lib->cli->out('Runtime Duration: ', 'brown');
	$lib->cli->out(TK_RUN_DURATION, 'white');
	$lib->cli->out(' seconds', 'brown');
	$lib->cli->out(TK_NL);
	$lib->cli->out('Memory usage: ', 'brown');
	$lib->cli->out($memory_usage, 'white');
	$lib->cli->out(' kb', 'brown');
	$lib->cli->out(TK_NL);
	$lib->cli->out('Application run mode: ', 'brown');
	$lib->cli->out($app_mode, 'white');
	$lib->cli->out(TK_NL);
	$lib->cli->out('Hooks enabled: ', 'brown');
	$lib->cli->out($allow_hooks, 'white');
	$lib->cli->out(TK_NL);
	$lib->cli->out('Called addon, action: ', 'brown');
	$lib->cli->out($addon_action_info, 'white');
	$lib->cli->out(TK_NL);
	$lib->cli->out('Language: ', 'brown');
	$lib->cli->out($app_language, 'white');
	$lib->cli->out(TK_NL);
	$lib->cli->out(TK_NL);
	$lib->cli->out('[Parameters]', 'brown');
	$lib->cli->out(TK_NL);
	$lib->cli->out($app_params, 'white');
	$lib->cli->out(TK_NL);
	$lib->cli->out('[Loaded libraries]', 'brown');
	$lib->cli->out(TK_NL);
	$lib->cli->out($loaded_libs, 'white');
	$lib->cli->out(TK_NL);
	$lib->cli->out(TK_NL);
	$lib->cli->out('[Loaded addons]', 'brown');
	$lib->cli->out(TK_NL);
	$lib->cli->out($loaded_addons, 'white');
	$lib->cli->out(TK_NL);
	$lib->cli->out(TK_NL);
	$lib->cli->out('[Benchmark dump]', 'brown');
	$lib->cli->out(TK_NL);
	$lib->cli->out($benchmark_dump, 'white');
	$lib->cli->out(TK_NL);
	
	/* Print Debug screen */
	$ln = 1;
	if(count(self::$debug_buffer) > 0) {
		$lib->cli->out(TK_NL);
		$lib->cli->out('[Debug screen]', 'brown');
		
		foreach(self::$debug_buffer as $debug_msg) {
			$lib->cli->out(TK_NL);
			if(strlen($ln) == 1) {
				$lib->cli->out(' ');
			}
			$lib->cli->out($ln . '. ' . $debug_msg, 'white');
			$ln++;
		}
		$lib->cli->out(TK_NL);
	}
	
	$lib->cli->out(TK_NL);
	$lib->cli->out(' End of debug information ', 'black', 'yellow');
	$lib->cli->out(TK_NL);
	
} // end func show_debug_info

/* End of class tk_e */
}

/* End of file e.cli.class.php */
?>