<?php 
/**
 * toKernel - Universal PHP Framework.
 * Framework loader.
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
 * @version    2.0.2
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */ 

/**
 * This file will be included in application's index.php
 * Example: require('path/to/framework/dir/tokernel.inc.php');
 *
 * Restrict direct access to this file.
 */
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	die('Restricted area.');
}

/* Define start time for debug information */
define('TK_START_RUN', round(microtime(true), 3));

/*
 * In many files may exists this line to restrict direct access.
 * 
 * defined('TK_EXEC') or die('Restricted area.');
 * 
 * This defination allows to run below included files.
 */
define('TK_EXEC', true);

/*
 * Detect is application run from command line interface
 * or accessed by http.
 * 
 * Note: There are differences in CLI and HTTP application libraries' functions. 
 */
if(!empty($argc) and php_sapi_name() == 'cli') {
	
	/* Define new line character */
	define('TK_NL', "\n");
	
	/* Define Framework's run mode */
	define('TK_RUN_MODE', 'cli');
    
	/* Define error exit code */
	define('TK_NO_ARGS', 10);
	
	/*
	 * Execute for unknown time, or forever for CLI.
	 * 
	 * Note: The default limit is 30 seconds, defined in php.ini 
	 * as max_execution_time = 30
	 * 
	 * Note: This function has no effect when PHP is running in safe mode. 
	 * There is no workaround other than turning off safe mode or changing 
	 * the time limit in the php.ini. 
	 */
	set_time_limit(0);
	
	/* Set some configurations for CLI mode */
	ini_set('track_errors', true);
    ini_set('html_errors', false);
    
} else {

	/* Define new line character */
	define('TK_NL', "<br />");
	
	/* Define application run mode */
	define('TK_RUN_MODE', 'http');
	
	$argv = NULL;
	
	/* Prepare to compress content, if the extension "zlib" is loaded */
	if(extension_loaded('zlib') and isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
		if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
		   	ob_start("ob_gzhandler");
		   	ob_start();
		   	define('TK_GZIP_OUTPUT', true);
		}
	}
	
	if(!defined('TK_GZIP_OUTPUT')) {
		ob_start();
		define('TK_GZIP_OUTPUT', false);
	}
	
} 

/* 
 * define default date, timezone before setting this 
 * value from running application configuration 
 */
ini_set('date.timezone', 'America/Los_Angeles');

/*
 * toKernel Framework path (This file path).
 * The path is optional. You can place the framework (tokernel) 
 * directory where you want, and include it in application's
 * index.php file. 
 * It is also possible to place the framework directory outside 
 * of DocumentRoot.
 * 
 * For example:
 * 
 * /var/www/html/tokernel/
 * /var/www/html/_application_dir/tokernel/
 * /var/lib/tokernel/
 * /home/_your_username/tokernel/
 * /home/_your_username/public_html/tokernel/
 * /home/_your_username/public_html/_application_dir/tokernel/
 */
define('TK_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/*
 * toKernel directory name
 */
define('TK_DIR', basename(dirname(__FILE__)));

/* Include framework constants file */
require(TK_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.php');

/*
 * Detect PHP version and exit application if not compatible.
 * Note: Constant PHP_VERSION_REQUIRED defined in config/constants.php
 */
if(version_compare(PHP_VERSION, TK_PHP_VERSION_REQUIRED, '<')) {
	die(TK_NL. 'toKernel - Universal PHP Framework v' . TK_VERSION . '.' . 
		TK_NL . 'PHP Version ' . PHP_VERSION . ' is not compatible. ' . 
		TK_NL . ' Version ' . TK_PHP_VERSION_REQUIRED . ' or newer Required.');
}

/* 
 * Directory path for running application customized components
 * 
 * For example: /var/www/html/your_application/app_framework_custom_dir/ 
 * 
 * TK_CUSTOM_DIR is the directory name defined in app index.php. 
 * For example: app_custom_dir
 * TK_CUSTOM_PATH is the full path of application directory.
 * For example: /var/www/html/your_application/app_framework_custom_dir/
 * 
 * @todo It is possible to change this constant name in the future.
 */ 
if(TK_CUSTOM_DIR != '') {
	define('TK_CUSTOM_PATH', TK_APP_PATH . TK_CUSTOM_DIR . TK_DS);
} else {
	define('TK_CUSTOM_PATH', TK_APP_PATH);
}

/* Include application constants file */
require(TK_CUSTOM_PATH . 'config' . TK_DS . 'constants.php');
		
/* Load main library class from Framework's kernel. */
require_once(TK_PATH . 'kernel' . TK_DS . 'lib.class.php');
$lib = lib::instance();

/* Include parent error handler class. */
require(TK_PATH . 'kernel'.TK_DS.'e.core.class.php');

/* 
 * Include extended error handler class depending 
 * on application run mode (CLI or HTTP)
 */
require(TK_PATH . 'kernel'.TK_DS.'e.'.TK_RUN_MODE.'.class.php');

/* 
 * Set error and exception handlers.
 * 
 * Error options are defined in tk_e class by default.
 * They will be reconfigured on application instance loading.
 */
set_exception_handler(array('tk_e', 'exception'));
set_error_handler(array('tk_e', 'error'));

/*
 * Set shutdown handler.
 * 
 * When using CLI the shutdown function is not called if 
 * the process gets a SIGINT or SIGTERM. Only the natural 
 * exit of PHP calls the shutdown function.
 */  
register_shutdown_function(array('tk_e', 'shutdown'));

ini_set('log_errors', 0);
ini_set('display_errors', 1);

/* Load application configuration object */
$config = $lib->ini->instance(TK_CUSTOM_PATH . 'config' . TK_DS . 
								'tokernel.ini', 'RUN_MODE');

if(!is_object($config)) {
	trigger_error('Application configuration file is not ' . 
					'readable or corrupted.', E_USER_ERROR);
	exit(1);
}

/* Configure run mode for error and exception handlers */
tk_e::configure_run_mode($config->section_get('RUN_MODE'));
unset($config);

tk_e::log_debug('', ':==================== START ====================');

tk_e::log_debug('Configured Error Exception/Handler mode', 'Loader');

/* Include application core class. */
require_once(TK_PATH . 'kernel' . TK_DS . 'app.core.class.php');

/* 
 * Include extended application class depending 
 * on application run mode (CLI or HTTP)
 */
require_once(TK_PATH . 'kernel' . TK_DS . 'app.' . TK_RUN_MODE . '.class.php');

/* Include parent addon class */
require_once(TK_PATH . 'kernel' . TK_DS . 'addon.class.php');
	
/* Include parent module class */
require_once(TK_PATH . 'kernel' .TK_DS . 'module.class.php');

/* Include parent view class */
require_once(TK_PATH . 'kernel' . TK_DS . 'view.class.php');

tk_e::log_debug('Loading app instance', 'Loader');

/* 
 * Create application instance
 */
$app = app::instance($argv);

/* 
 * Run application once.
 * NOTE: All calls of this function after now will trigger error.
 */
if(!$app->run()) { 
	trigger_error('Run Application failed!', E_USER_ERROR);
}
 
/* Define end time/duration for debug information */ 
define('TK_END_RUN', round(microtime(true), 3));
define('TK_RUN_DURATION', round((TK_END_RUN - TK_START_RUN), 3));

tk_e::log_debug('Runtime duration: '.TK_RUN_DURATION.' seconds', 'Loader');
tk_e::log_debug('', ':===================== END =====================');

/* 
 * Show debug information if debug_mode is 
 * difined as 1 in configuration file.
 */
if($app->config('debug_mode', 'RUN_MODE') == 1) {
	
	tk_e::log_debug('Show debug information.', 'Loader');
	
	if(TK_RUN_MODE == 'cli') {
		
		/* In CLI mode debug information will be shown below */ 
		tk_e::show_debug_info();
		
	} elseif($app->is_ajax_request() == true) { 
		
		/* Show debug information for ajax requests */
		if($app->config('debug_mode_ajax', 'RUN_MODE') == '1') {
			tk_e::show_debug_info();
		}
				
	} else {
		tk_e::show_debug_info();
	}
}

/* End of file */
?>