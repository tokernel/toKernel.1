<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for working with PHP Command line interface
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
 * @version    1.0.7
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * cli_lib class library 
 * 
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class cli_lib {
	
/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;
 
/**
 * Arguments and other options array
 * 
 * @access protected
 * @var array
 */ 
 protected $arg_arr;

/**
 * Configuration array
 * 
 * @access protected
 * @var array
 */ 
 protected $config_arr;

/**
 * Status of this class initialization
 * 
 * @access protected
 * @staticvar bool
 */ 
 protected static $initialized = false;
	 
/**
 * Is colored output enabled.
 * Detect this option by OS.
 * 
 * @access protected
 * @var bool
 */	
 protected $colored_output = false;
 	
/**
 * Foreground colors for CLI output
 * 
 * @access protected
 * @var array
 */	
 protected $fore_colors = array();
 
/**
 * Background colors for CLI output
 * 
 * @access protected
 * @var array
 */ 
 protected $back_colors = array();
 
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function  __construct() {
		
	$this->lib = lib::instance();
		
	/* Set default values */
	$this->arg_arr = array(
		'language_prefix' => 'en',
		'alias' => '',
		'id_addon' => '',
		'action' => '',
		'params' => array(),
	);
		
	/* Detect OS for colored output */
	if(strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {

		$this->colored_output = true;
	
		/* Set CLI text output Colors */
		$this->fore_colors['black'] = '0;30';
		$this->fore_colors['dark_gray'] = '1;30';
		$this->fore_colors['blue'] = '0;34';
		$this->fore_colors['light_blue'] = '1;34';
		$this->fore_colors['green'] = '0;32';
		$this->fore_colors['light_green'] = '1;32';
		$this->fore_colors['cyan'] = '0;36';
		$this->fore_colors['light_cyan'] = '1;36';
		$this->fore_colors['red'] = '0;31';
		$this->fore_colors['light_red'] = '1;31';
		$this->fore_colors['purple'] = '0;35';
		$this->fore_colors['light_purple'] = '1;35';
		$this->fore_colors['brown'] = '0;33';
		$this->fore_colors['yellow'] = '1;33';
		$this->fore_colors['light_gray'] = '0;37';
		$this->fore_colors['white'] = '1;37';
 
		$this->back_colors['black'] = '40';
		$this->back_colors['red'] = '41';
		$this->back_colors['green'] = '42';
		$this->back_colors['yellow'] = '43';
		$this->back_colors['blue'] = '44';
		$this->back_colors['magenta'] = '45';
		$this->back_colors['cyan'] = '46';
		$this->back_colors['light_gray'] = '47';

	} // end if colored output enabled
	
 } // end constructor

/**
 * Magic function get.
 * Return values from $arg_arr
 * 
 * @access public
 * @param string $item
 * @return mixed string | array | bool
 */ 
 public function __get($item) {
	switch($item) {
 		case 'all':
 			return $this->arg_arr;
 			break;
 		case 'command_line':
 			return $this->arg_arr['command_line']; 
 			break;
 		case 'lng': 
 		case 'language': 
 		case 'language_prefix':
 			return $this->arg_arr['language_prefix']; 
 			break;
 		case 'alias':
 			return $this->arg_arr['alias']; 
 			break;
 		case 'id_addon':
 		case 'addon':
 		case 'controller':
 			return $this->arg_arr['id_addon']; 
 			break;
 		case 'action':
 			return $this->arg_arr['action']; 
 			break;
 		case 'params':
 			return $this->arg_arr['params'];
 			break; 
 		case 'params_count':
 			return count($this->arg_arr['params']); 
 			break;	
 	}  // end switch
 	
 	if(isset($this->arg_arr['params'][$item])) {
 		return $this->arg_arr['params'][$item];
 	}
 	
 	return false;

 } // end func __get  
 
/**
 * Initialize CLI
 * This function will called from application instance at once.
 * 
 * @access public
 * @param array $args
 * @param array $config_arr
 * @return bool
 */ 
 public function init($args, $config_arr) {
 	
 	/* Return true if already initialized */
	if(self::$initialized == true) {
 		trigger_error('CLI initialization - ' . __CLASS__ . '->' . 
 				__FUNCTION__ . '() is already initialized!', E_USER_WARNING);
 				
		return true;
 	}
 	
 	tk_e::log_debug('Start with arguments "' . implode(' ' ,$args) . '"', 
 										__CLASS__.'->'.__FUNCTION__);
 					
 	/* Set configuration from application config - application.ini */
 	$this->config_arr = $config_arr;
 	
 	/* 
	 * Remove first element of $args array, 
	 * that will be file name (index.php).
	 */
 	if(isset($args[0])) {
 		array_shift($args);
 	} else {
 		tk_e::log_debug('Exit! Command line arguments is empty. Array $args[0].', 
 								__CLASS__.'->'.__FUNCTION__);
 										
 		trigger_error('Command line arguments is empty. Array $args[0].', 
 						E_USER_WARNING);
 						
 		$this->output_usage("Invalid Command line arguments!");
 		exit(TK_NO_ARGS);
 	}

 	/* Check arguments count */
 	if(!isset($args[0])) {
 		
 		tk_e::log_debug('Exit! Invalid Command line arguments.', 
 								__CLASS__.'->'.__FUNCTION__);
 								
 		tk_e::log("Invalid Command line arguments!", E_USER_NOTICE, 
 										__FILE__, __LINE__);
 		 									
 		$this->output_usage("Invalid Command line arguments!");
		exit(TK_NO_ARGS);
 	}
 	
	/* Show usage on screen and exit, if called help action. */
 	if(in_array($args[0], array('--help', '--usage', '-help', '-h', '-?'))) {
		
 		tk_e::log_debug('Exit! Show usage/help.', 
 								__CLASS__.'->'.__FUNCTION__);
 		
 		$this->output_usage();
		exit(0);
	}

	/* Try to get aliasing values */
	$alias_word = str_replace('--', '', $args[0]);
	
	if($this->has_aliasing($alias_word) == false) {
		$this->arg_arr['alias'] = '';
	}
 	
	unset($alias_word);
	
	/* 
	 * Start parsing command line 
	 * arguments uses the "long option" syntax. 
	 */
	$parsed_args_arr = array();
	
	tk_e::log_debug('Parsing arguments', __CLASS__.'->'.__FUNCTION__);
 								
	for($i = 0; $i < count($args); $i+=2) {
		
		/* 
		 * if an argument expression started with 
		 * '--' symbols, then set it as argument 
		 * name, else it is argument value. 
		 */
		if(substr($args[$i], 0, 2) == '--') {
			$k = substr($args[$i], 2, strlen($args[$i]));
			if(isset($args[$i+1])) {
				$parsed_args_arr[$k] = $args[$i+1];
			} else {
				$parsed_args_arr[$k] = '';
			}	
			$k = ''; 
		} 
	} // end for arguments 
								
	/* 
	 * Set application parameters, if 
	 * they not set by aliasing option. 
	 */
	if($this->arg_arr['id_addon'] == '' and isset($parsed_args_arr['addon']) 
										and $parsed_args_arr['addon'] != '') {
											
		$this->arg_arr['id_addon'] = $parsed_args_arr['addon'];
		
		tk_e::log_debug('Detected id_addon - "'.$this->arg_arr['id_addon'].'" '
					, __CLASS__.'->'.__FUNCTION__);
	}
	
	if($this->arg_arr['action'] == '' and isset($parsed_args_arr['action']) 
									  and $parsed_args_arr['action'] != '') {

		$this->arg_arr['action'] = $parsed_args_arr['action'];

		tk_e::log_debug('Detected action - "'.$this->arg_arr['action'].'" '
					, __CLASS__.'->'.__FUNCTION__);
	}
    
	/* 
	 * Show usage on screen and exit, if 
	 * --addon or --action arguments is empty. 
	 */
	if($this->arg_arr['id_addon'] == '' or $this->arg_arr['action'] == '') {
		
		tk_e::log_debug('Exit! Invalid Command line arguments.', 
 								__CLASS__.'->'.__FUNCTION__);
 								
		tk_e::log("Invalid Command line arguments!", E_USER_NOTICE, 
							__FILE__, __LINE__);
		
	   	$this->output_usage("Invalid Command line arguments!");
		exit(TK_NO_ARGS);
	}

	/* Define language */
	if(isset($parsed_args_arr['lng'])) {
	   /* 
	    * check, is received language 
	    * allowed, else set default language. 
	    */
	   if(!in_array($parsed_args_arr['lng'], explode('|', 
	      							$config_arr['cli_allowed_languages']))) {
	      								
		  $this->arg_arr['language_prefix'] = $config_arr['cli_default_language'];
		  
		  tk_e::log_debug('Detected incorrect language prefix - "' . 
		  					$parsed_args_arr['lng'] . '" set default - "'. 
		  					$this->arg_arr['language_prefix'].'"', 
 							__CLASS__.'->'.__FUNCTION__);
 							
	   } else {
		  $this->arg_arr['language_prefix'] = $parsed_args_arr['lng'];
		  
		  tk_e::log_debug('Detected language prefix - "' . 
		  					$this->arg_arr['language_prefix'].'"', 
 							__CLASS__.'->'.__FUNCTION__);
	   }
	   
	} else {
		$this->arg_arr['language_prefix'] = $config_arr['cli_default_language'];
		
		tk_e::log_debug('Set language prefix - "' . 
		  					$this->arg_arr['language_prefix'].'" by default', 
 							__CLASS__.'->'.__FUNCTION__);
	}

	/* 
	 * Remove Language, Addon id, Action from 
	 * $parsed_args_arr array, for  
	 * parsing next optional parameters.   
	 */
	unset($parsed_args_arr['addon']);
	unset($parsed_args_arr['action']);
	unset($parsed_args_arr['lng']);

	/* Set optional parameters from cli */ 
	if(count($this->arg_arr['params']) > 0) {
	    /* 
	     * Merge parameters from aliasing and command line.
	     * NOTE: The identical parameters will be replaced 
	     * by aliasing on cli, because aliasing is so main.  
	     */
		$this->arg_arr['params'] = array_merge($parsed_args_arr, 
												$this->arg_arr['params']);
												
	} else {
		$this->arg_arr['params'] = $parsed_args_arr;
	}
	
	unset($parsed_args_arr);
	
	self::$initialized = true;
	
	$deb_tmp_str = '';
	
	foreach($this->arg_arr['params'] as $key => $value) {
		$deb_tmp_str .= $key . '=' .$value.' ';
	}
	
	tk_e::log_debug('End with params - "' . trim($deb_tmp_str) . '"', 
 							__CLASS__.'->'.__FUNCTION__);
	
 	unset($deb_tmp_str);
 							
	return true;
 } // end func init 
 
/**
 * Read data from command line
 * 
 * @access public
 * @return string
 */ 
 public function in() {
	$handle = trim(fgets(STDIN));
	return $handle;
 } // end func in

/**
 * Output colored string to screen if $this->colored_output is true. 
 * Else, if OS is WIN, just output string without colors. 
 * 
 * @access public
 * @param string $data
 * @param string $fore_color
 * @param string $back_color
 * @return void
 */
 public function out($string, $fore_color = NULL, $back_color = NULL) {
	
 	/* Output string to screen without colors */
 	if(!$this->colored_output) {
 		/* Output to screen */
 	    fwrite(STDOUT, $string);
 	    return;
 	}
 	
 	$colored_string = "";

	/* Check if given foreground color found */
	if(isset($this->fore_colors[$fore_color]) and !empty($fore_color)) {
		$colored_string .= "\033[".$this->fore_colors[$fore_color]."m";	
 	}
	
	/* Check if given background color found */
	if(isset($this->back_colors[$back_color]) and !empty($back_color)) {
		$colored_string .= "\033[" . $this->back_colors[$back_color] . "m";
	}
 
	/* Add string and end coloring */
	$colored_string .=  $string . "\033[0m";
	
	/* Output to screen */
 	fwrite(STDOUT, $colored_string);
 
 } // end func out

/**
 * Return text with colored format.
 * This funtion not working in Win. OS.
 * 
 * @access public
 * @param string $string
 * @param string $fore_color
 * @param string $back_color
 * @return string
 */ 
 public function get_ct($string, $fore_color = NULL, $back_color = NULL) {
 	
 	/* Return string without colors */
 	if(!$this->colored_output) {
 	    return $string;
 	}
 	
 	$colored_string = "";

	/* Check if given foreground color found */
	if(isset($this->fore_colors[$fore_color]) and !empty($fore_color)) {
		$colored_string .= "\033[".$this->fore_colors[$fore_color]."m";	
 	}
	
	/* Check if given background color found */
	if(isset($this->back_colors[$back_color]) and !empty($back_color)) {
		$colored_string .= "\033[" . $this->back_colors[$back_color] . "m";
	}
 
	/* Add string and end coloring */
	$colored_string .=  $string . "\033[0m";
	
	return $colored_string;
 } // end func get_ct
 
/**
 * Output CLI Usage on screen
 * 
 * @access public
 * @param string $show_message
 * @return void
 */
 public function output_usage($show_message = NULL) {
 	
 	/* Output copyright info */
 	$message = '';
 	
 	$message .= TK_NL." -";
 	$message .= TK_NL." | toKernel - Universal PHP Framework v".TK_VERSION;
	$message .= TK_NL." | Copyright (c) " . date('Y') . " toKernel <framework@tokernel.com>";
	$message .= TK_NL." | "; 
	$message .= TK_NL." | Running in " . php_uname();
	$message .= TK_NL." - ";
	
	$this->out($message, 'brown');
    
	/* Output message if not empty */
	if(!empty($show_message)) {
		/* Make colored text */
		$show_message = TK_NL . TK_NL . ' ' . 
		                $this->get_ct(' ' . $show_message . ' ', 
		                                  'white', 'red');
		$this->out($show_message);
	}
	
	/* Output usage info */
	$message = TK_NL;
	$message .= TK_NL . " Usage: /usr/bin/php " .
	            TK_APP_PATH . $_SERVER['PHP_SELF'];

	$message .= TK_NL . "    --addon addon_name ";
	$message .= TK_NL . "    --action action_name ";
   
	if($this->config_arr['cli_parse_language'] == true) {
		$message .= TK_NL . "    [--lng language_prefix]";
	}
   
	$message .= TK_NL . "    [--arg name] [--arg value]";
	$message .= TK_NL;
	$message .= TK_NL;

	$this->out($message, 'white');
 
 } // end func output_usage

/**
 * Print colors on screen
 * 
 * @access public
 * @return void
 */ 
 public function output_colors() {
 	if(count($this->fore_colors) <= 0) {
 		$this->out(TK_NL);
 		$this->out('Can not output colors. It is not supported by OS.');
 		$this->out(TK_NL);
 	}
 	
 	$this->out(TK_NL);
 	$this->out(' [Forecolors] ' . "\t" . '[Backcolors]', 'black', 'yellow');
 	$this->out(TK_NL);
 	
 	$i = 0;
 	
 	reset($this->fore_colors);
 	reset($this->back_colors);
 	
 	ksort($this->fore_colors);
 	ksort($this->back_colors);
 	
 	for($i = 0; $i < count($this->fore_colors); $i++) {
 		$this->out(' '. key($this->fore_colors) . " ", key($this->fore_colors));
 		
 		if(key($this->back_colors)) {
 			$j = '';
 			$val = 15 - strlen(key($this->fore_colors));
 			for($k = 0; $k < $val; $k++) {
 				$j .= ' ';
 			}
 			$this->out(' ' . $j);
 			$this->out(' ' . key($this->back_colors) . ' ', 'black', key($this->back_colors));
 		}
 		
 		$this->out(TK_NL);
 		next($this->fore_colors);
 		next($this->back_colors);
 	}
 	
 } // end func output_colors
 
/**
 * Set alias values if exists
 * 
 * @access protected
 * @param string $alias_word
 * @return bool
 */ 
 protected function has_aliasing($alias_word) {
 	
 	if(trim($alias_word) == '') {
 		return false;
 	}
 	
	$aliasing_arr = $this->lib->alias->get($alias_word);
	
	if(!is_array($aliasing_arr)) {
		
		tk_e::log_debug('Aliasing by word "'.$alias_word.'" not detected' 
									, __CLASS__ . '->' . __FUNCTION__);
									
		return false;	
	}
	
	tk_e::log_debug('Detected aliasing word "'.$alias_word.'"' 
									, __CLASS__ . '->' . __FUNCTION__);
									
	/* 
	 * Aliasing array exist, now setting 
	 * application parameters from that.
	 */
	$this->arg_arr['alias'] = $alias_word;
	
	if(!isset($aliasing_arr['id_addon']) or $aliasing_arr['id_addon'] == '') {
		trigger_error('Item "id_addon" is empty for alias "'.$alias_word.'"!', 
						E_USER_WARNING);
		return false;
	} else {
		$this->arg_arr['id_addon'] = $aliasing_arr['id_addon'];

		tk_e::log_debug('Set id_addon - "' . $this->arg_arr['id_addon'] . 
					'" from aliasing', __CLASS__ . '->' . __FUNCTION__);
	}
	
	if(!isset($aliasing_arr['action']) or $aliasing_arr['action'] == '') {
		trigger_error('Item "action" is empty for alias "'.$alias_word.'"!', 
						E_USER_WARNING);
		return false;
	} else {
		$this->arg_arr['action'] = $aliasing_arr['action'];
	
		tk_e::log_debug('Set action - "' . $this->arg_arr['action'] . 
					'" from aliasing', __CLASS__ . '->' . __FUNCTION__);
	}
	
	/* Set parameters */
	if(isset($aliasing_arr['params']) and $aliasing_arr['params'] != '') {

		$this->arg_arr['params'] = explode('/', $aliasing_arr['params']);
		$this->arg_arr['params'] = $this->parse_params_assoc(
												$this->arg_arr['params']
												);
												
		tk_e::log_debug('Set params - "' . $aliasing_arr['params'] . 
						'" from aliasing', __CLASS__ . '->' . __FUNCTION__);
												
	} else {
		$this->arg_arr['params'] = array();
		
		tk_e::log_debug('Params not defined in aliasing' 
						, __CLASS__ . '->' . __FUNCTION__);
	}

	tk_e::log_debug('End with alias - "'.$alias_word.'" ' 
						, __CLASS__ . '->' . __FUNCTION__);
		
	return true;
	
 } // end func has_aliasing

/**
 * Parse array to assoc
 * 
 * @access protected
 * @param array $arr
 * @return mixed array | bool
 */ 
 protected function parse_params_assoc($arr) {
	
 	$ret_arr = array();
	
 	for($i = 0; $i < count($arr); $i+=2) {
		/* Clean key name */
		
 		$arr[$i] = $this->lib->filter->strip_chars($arr[$i], 
											array('-', '_', '.')
											); 
		
		if(trim($arr[$i] and isset($arr[$i+1])) != '') {
			$ret_arr[$arr[$i]] = $arr[$i+1];
		}
	} // end for

	return $ret_arr;
	
 } // end func parse_params_assoc
 
/**
 * Return language prefix
 * 
 * @access public
 * @return string 
 */ 
 public function language_prefix() {
 	return $this->arg_arr['language_prefix'];
 }

/**
 * Set language prefix
 * 
 * @access public
 * @param string $language_prefix
 * @return void
 */
 public function set_language_prefix($language_prefix) {
 	$this->arg_arr['language_prefix'] = $language_prefix;
 }
  
/**
 * Return addon id 
 * 
 * @access public
 * @return string
 */ 
 public function id_addon() {
 	return $this->arg_arr['id_addon'];
 }

/**
 * Return action of addon
 * 
 * @access public
 * @return string
 */ 
 public function action() {
 	return $this->arg_arr['action'];
 }
 
/**
 * Return parameter value by name or parameters array
 * 
 * @access public
 * @param string $item
 * @return mixed array | string | bool
 */ 
 public function params($item = NULL) {
 	
 	/* Return parameters array */
 	if(is_null($item)) {
 		return $this->arg_arr['params'];
 	}
 	
 	/* Return parameter value by name */ 
 	if(isset($this->arg_arr['params'][$item])) {
 		return $this->arg_arr['params'][$item];
 	}
 	
 	/* Parameter not exists */
 	return false;
 
 } // end func params

/**
 * Return parameters count
 * 
 * @access public
 * @return integer
 */ 
 public function params_count() {
 	return count($this->arg_arr['params']);
 }
  
/**
 * Return alias name 
 * 
 * @access public
 * @return string
 */ 
 public function alias() {
 	return $this->arg_arr['alias'];
 }

/* End of class cli_lib */
}

/* End of file */
?>