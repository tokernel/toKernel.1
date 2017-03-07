<?php
/**
 * toKernel - Universal PHP Framework.
 * Hooks class library
 * In fact, it is not possible to output any data in hooks, 
 * such as echo 'some data'; in HTTP mode.
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
 * @version    1.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * hooks class
 *
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class hooks {
	
/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;
 
/**
 * Main Application object for 
 * accessing app functions from this class
 * 
 * @var object
 * @access protected
 */ 
 protected $app;
   	
/**
 * Status of hooks loaded
 * 
 * @access private
 * @staticvar bool
 */	
 private static $_loaded = false;
	
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */
 public function __construct() {

 	/* Check, is hooks is already loaded */
	if(self::$_loaded == true) {
		trigger_error('Hooks is already loaded in app::run().', E_USER_ERROR);
	}
		
	$this->lib = lib::instance();
	$this->app = app::instance();
		
	self::$_loaded = true;
	
 } // end constructor
	
/**
 * Hook before run main callable addon in HTTP and CLI modes
 * 
 * @access public
 * @return void
 */
 public function before_run() {

 	$hook_file = TK_CUSTOM_PATH . 'hooks/before_run.hook.php';
	
 	if(is_file($hook_file)) {

 		if(TK_RUN_MODE != 'cli') {
 			ob_start();
 		}
 		
 		require($hook_file);

 		if(TK_RUN_MODE != 'cli') {
 			ob_clean();
 		}
 		
	}
 } // end func before_run 
 
/**
 * Hook after run main callable addon in HTTP and CLI modes
 * 
 * @access public
 * @return void
 */
 public function after_run() {

 	$hook_file = TK_CUSTOM_PATH . 'hooks/after_run.hook.php';
	
 	if(is_file($hook_file)) {
		
 		if(TK_RUN_MODE != 'cli') {
 			ob_start();
 		}
 		
 		require($hook_file);
 		
 		if(TK_RUN_MODE != 'cli') {
 			ob_clean();
 		}
 		
	}
 } // end func after_run 
 
/**
 * Hook before run main callable addon in CLI mode
 * 
 * @access public
 * @return void
 */
 public function cli_before_run() {

 	$hook_file = TK_CUSTOM_PATH . 'hooks/cli_before_run.hook.php';
	
 	if(is_file($hook_file)) {
		require($hook_file);
	}
 } // end func cli_before_run
	
/**
 * Hook after run main callable addon in CLI mode
 * 
 * @access public
 * @return void
 */ 
 public function cli_after_run() {

 	$hook_file = TK_CUSTOM_PATH . 'hooks/cli_after_run.hook.php';
	
 	if(is_file($hook_file)) {
		require($hook_file);
	}
 } // end func cli_after_run
	
/**
 * Hook before run main callable addon in HTTP mode
 * 
 * @access public
 * @return void
 */
 public function http_before_run() {

 	$hook_file = TK_CUSTOM_PATH . 'hooks/http_before_run.hook.php';

 	if(is_file($hook_file)) {
		ob_start();
		require($hook_file);
		ob_clean();
	}
 } // end func 

/**
 * Hook after run main callable addon in HTTP mode
 * 
 * @access public
 * @return void
 */
 public function http_after_run() {

 	$hook_file = TK_CUSTOM_PATH . 'hooks/http_after_run.hook.php';
	
 	if(is_file($hook_file)) {
		ob_start();
		require($hook_file);
		ob_clean();
	}
 } // end func http_after_run

/**
 * Hook before output in HTTP mode
 * 
 * @access public
 * @return void
 */
 public function http_before_output() {

 	$hook_file = TK_CUSTOM_PATH . 'hooks/http_before_output.hook.php';
	
 	if(is_file($hook_file)) {
		ob_start();
		require($hook_file);
		ob_clean();
	}
 } // end func http_before_output
	
/**
 * Hook after output in HTTP mode
 * 
 * @access public
 * @return void
 */
 public function http_after_output() {

 	$hook_file = TK_CUSTOM_PATH . 'hooks/http_after_output.hook.php';
	
 	if(is_file($hook_file)) {
		ob_start();
		require($hook_file);
		ob_clean();
	}
 } // end func http_after_output	
	
/* End of class hooks */
}

/* End of file */
?>