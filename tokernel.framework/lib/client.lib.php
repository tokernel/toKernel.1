<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for collecting information of the OS, browser, 
 * mobile device of the client.
 * File framework/config/platforms.ini required as reference.
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
 * @version    1.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.4
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * client_lib class
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class client_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;

/**
 * Platforms ini file
 * 
 * @access protected
 * @var string
 */ 
 protected $ini_file;
 
/**
 * Platforms/Browsers configuration object
 * 
 * @access protected
 * @var object
 */ 
 protected $ini_object;
 
/**
 * Client data
 * 
 * @access protected
 * @var string
 */ 
 protected $client;
 
/**
 * Client platform
 * 
 * @access protected
 * @var string
 */ 
 protected $platform;
		 
/**
 * Is browser accessed
 * 
 * @access protected
 * @var bool
 */ 
 protected $is_browser;
 
/**
 * Browser version
 * 
 * @access protected
 * @var string
 */ 
 protected $browser_version;

/**
 * Browser
 * 
 * @access protected
 * @var string
 */ 
 protected $browser;

/**
 * Is mobile accessed
 * 
 * @access protected
 * @var bool
 */ 
 protected $is_mobile;

/**
 * Mobile
 * 
 * @access protected
 * @var string
 */ 
 protected $mobile;
 
/**
 * Accepted character sets
 * 
 * @access protected
 * @var array
 */ 
 protected $charsets;
 
/**
 * Accepted languages
 * 
 * @access protected
 * @var array
 */ 
 protected $languages = array();
 
/**
 * Is robot
 * 
 * @access protected
 * @var bool
 */ 
 protected $is_robot;

/**
 * Robot
 * 
 * @access protected
 * @var string
 */ 
 protected $robot;
 
/**
 * Class constructor
 * 
 * @access public
 * @return bool
 */ 
 public function __construct() {

	$this->lib = lib::instance();
	
	/* File platforms.ini located in framework's config directory */
	$this->ini_file = TK_PATH . 'config' . TK_DS . 'platforms.ini';
	
	$this->platform = 'Unknown Platform';
	$this->is_browser = false;
	$this->browser_version = '';
	$this->browser = '';
	$this->is_mobile = false;
	$this->mobile = '';
	
	$this->is_robot = false;
	$this->robot = '';
	
	$this->init();

 } // End constructor

/**
 * Initialize client data
 * 
 * @access protected
 * @return bool
 */
 protected function init() {
	
	/* If running in CLI define OS only */
	if(TK_RUN_MODE == 'cli') {
		$this->platform = PHP_OS;
		return true;
	}
	
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$this->client = trim($_SERVER['HTTP_USER_AGENT']);
	} else {
		return false;
	}
	
	/* Load platforms.ini file to ini object */
	$this->ini_object = $this->lib->ini->instance($this->ini_file);
	
	if(!is_object($this->ini_object)) {
		
		tk_e::log_debug('File `'.$this->ini_file.'` not exists!',
 						__CLASS__ . '->' . __FUNCTION__);
		
		trigger_error('File `'.$this->ini_file.'` not exists!', E_USER_WARNING);
	}

	$this->define_platform();
	$this->define_browser();
	$this->define_robot();

	return true;

 } // End func init
 
/**
 * Define platform
 * 
 * @access protected
 * @return bool
 */
 protected function define_platform() {

	$platforms = $this->ini_object->section_get('PLATFORMS');
	
	if(!is_array($platforms)) {
		return false;
	}
	
	foreach($platforms as $key => $val) {
		if (preg_match("|" . preg_quote($key) . "|i", $this->client)) {
			$this->platform = $val;
			return true;
		}
	 }

	return false;
 
 } // End func define_platform

/**
 * Define Browser
 * 
 * @access protected
 * @return bool
 */
 protected function define_browser() {

	$browsers = $this->ini_object->section_get('BROWSERS');
	
	if(!is_array($browsers)) {
		return false;
	}
	
	foreach($browsers as $key => $val) {
		if (preg_match("|" . preg_quote($key) . ".*?([0-9\.]+)|i", $this->client, $match)) {
			$this->is_browser = true;
			$this->browser_version = $match[1];
			$this->browser = $val;
			$this->define_mobile();
			return true;
		}
	}

	return false;
	
 } // End func define_browser
 
/**
 * Define Mobile
 * 
 * @access protected
 * @return bool
 */
 protected function define_mobile() {
	
	$mobiles = $this->ini_object->section_get('MOBILES');
	
	if(!is_array($mobiles)) {
		return false;
	}
		
	foreach($mobiles as $key => $val) {
		if (false !== (strpos(strtolower($this->client), $key))) {
			$this->is_mobile = true;
			$this->mobile = $val;
			return true;
		}
	}

	return false;

 } // End func define_mobile
 
/**
 * Define accepted character sets
 *
 * @access protected
 * @return void
 */
 protected function define_charsets() {

	if(isset($_SERVER['HTTP_ACCEPT_CHARSET']) 
		 and $_SERVER['HTTP_ACCEPT_CHARSET'] != '') {

		$charsets = preg_replace('/(;q=.+)/i', '', 
						strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])));

		$this->charsets = explode(',', $charsets);

	} else {
		$this->charsets = array();
	}

} // End func define_charsets

/**
 * Define accepted languages
 *
 * @access protected
 * @return void
 */
 protected function define_languages() {

	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) 
		 and $_SERVER['HTTP_ACCEPT_LANGUAGE'] != '') {

		$languages = preg_replace('/(;q=[0-9\.]+)/i', '', 
					 strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));

		$this->languages = explode(',', $languages);
	} else {
		$this->languages = array();
	}

 } // End func define_languages
	
/**
 * Define Robot
 *
 * @access protected
 * @return bool
 */
 protected function define_robot() {

	$robots = $this->ini_object->section_get('ROBOTS');
	
	if(!is_array($robots)) {
		return false;
	}

	foreach ($robots as $key => $val) {
		if (preg_match("|" . preg_quote($key) . "|i", $this->client)) {
			$this->is_robot = true;
			$this->robot = $val;
			return true;
		}
	}
	
	return false;
	
 } // End func define_robot

/**
 * Return is browser
 *
 * @access public
 * @return bool
 */
 public function is_browser() {
	return $this->is_browser;
 }

/**
 * Return is mobile
 *
 * @access public
 * @return bool
 */
 public function is_mobile() {
	return $this->is_mobile;
 }
 
/**
 * Return is Robot
 *
 * @access public
 * @return bool
 */
 public function is_robot() {
	return $this->is_robot;
 }

/**
 * Check is Accept language exists
 *
 * @access public
 * @param mixed $lng = 'en'
 * @return bool
 */
 public function is_accept_language($lng = 'en') {

	$lng = strtolower($lng);

	if(in_array($lng, $this->languages(), true) == true) {
		return true;
	}

	return false;

 } // End func is_accept_language 

/**
 * Check is Accept character set
 *
 * @access public
 * @param mixed $charset = 'utf-8'
 * @return bool
 */
 public function is_accept_charset($charset = 'utf-8') {

	$charset = strtolower($charset);

	if(in_array($charset, $this->charsets(), true) == true) {
		return true;
	}

	return false;

 } // End func  is_accept_charset 
 
/**
 * Return defined platform
 *
 * @access public
 * @return string
 */
 public function platform() {
	return $this->platform;
 }
 
/**
 * Return defined browser
 *
 * @access public
 * @return string
 */
 public function browser() {
	return $this->browser;
 }
 
/**
 * Return defined browser version
 *
 * @access public
 * @return string
 */
 public function browser_version() {
	return $this->browser_version;
 }

/**
 * Return defined mobile
 *
 * @access public
 * @return string
 */
 public function mobile() {
	return $this->mobile;
 }

/**
 * Return Robot
 *
 * @access public
 * @return string
 */
 public function robot() {
	return $this->robot;
 }
 
/**
 * Return accepted Character Sets
 *
 * @access	public
 * @return	array
 */
 public function charsets() {
	
	if (!is_array($this->charsets)) {
		$this->define_charsets();
	}

	return $this->charsets;
	
 } // End func charsets

/**
 * Return accepted languages
 *
 * @access	public
 * @return	array
 */
 public function languages() {

	if (count($this->languages) == 0) {
		$this->define_languages();
	}

	return $this->languages;

 } // End func languages

/**
 * Return whole string of client
 *
 * @access public
 * @return string
 */
 public function user_agent() {
	return $this->client;
 }

/**
 * Return array of defined client information
 * 
 * @access public
 * @return array
 */
 public function client_arr() {
	
	$c_arr = array();

	$c_arr['platform'] = $this->platform();
	$c_arr['is_browser'] = $this->is_browser();
	$c_arr['browser'] = $this->browser();
	$c_arr['browser_version'] = $this->browser_version();
	$c_arr['is_mobile'] = $this->is_mobile();
	$c_arr['mobile'] = $this->mobile();
	$c_arr['is_robot'] = $this->is_robot();
	$c_arr['robot'] = $this->robot();
	$c_arr['charsets'] = $this->charsets();
	$c_arr['languages'] = $this->languages();
	$c_arr['user_agent'] = $this->user_agent();

	return $c_arr;

 } // End func client_arr
 
/**
 * Return string of defined client information
 * 
 * @access public
 * @return atring
 */
 public function client_str() {
	
	$buf = 'Client Information' . TK_NL;
	
	$c_arr = $this->client_arr();
	
	foreach($c_arr as $key => $value) {
		
		if(is_array($value) == true) {
			$value = implode(', ', $value);
		}
		
		if(is_bool($value) == true) {
			if($value == true) {
				$value = 'Yes';
			} else {
				$value = 'No';
			}
		}
		
		$key = ucfirst($key);
		$key = str_replace('_', ' ', $key);
		
		$buf .= $key . ' : ' . $value . TK_NL;
		
	} // End foreach client values
		
	return $buf;

 } // End func client_str
  
/* End of class client_lib */
}

/* End of file */
?>