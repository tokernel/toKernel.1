<?php
/**
 * toKernel - Universal PHP Framework.
 * Class library for working with session.
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
 * @copyright  Copyright (c) 2015 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.2.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @todo       Make this library more secure.
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * session_lib class
 *
 * NOTE: For future, it is possible to modify
 * this class for working with cookies also.
 *
 * @author David A. <tokernel@gmail.com>
 * @author Patrick Isbendjian <pi@pisbtech.com> for v 1.1
 */
class session_lib {

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
	 * Session prefix
	 *
	 * @access protected
	 * @var string $sp
	 */
	protected $sp;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->app = app::instance();
		$this->lib = lib::instance();

		/* Set session save path if defined */
		$save_path = $this->app->config('session_save_path', 'SESSION');

		if($save_path != '') {
			session_save_path($save_path);
		}

		/*
		  * Only in this part of framework,
		  * session can be start
		  */
		session_start();

		/* Set session prefix */
		$app_session_prefix = $this->app->config('session_prefix', 'SESSION');

		if($app_session_prefix != '') {
			$this->sp = $app_session_prefix;
		} else {
			$this->sp = 'tokernel_';
		}

	} // End of func __construct

	/**
	 * Set session array values
	 *
	 * @access public
	 * @param array $data_arr
	 * @param string $section
	 * @return void
	 * @since v.1.2.0
	 */
	public function set_section($data_arr, $section) {
		$_SESSION[$this->sp . $section] = $data_arr;
	} // End func set_section

	/**
	 * Set session value.
	 *
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @param string $section
	 * @return void
	 */
	public function set($item, $value = '', $section = NULL) {

		if(!is_null($section)) {
			$_SESSION[$this->sp . $section][$item] = $value;
		} else {
			$_SESSION[$this->sp . $item] = $value;
		}

	} // end func set

	/**
	 * Get session value by item, subitem.
	 *
	 * @access public
	 * @param string $item
	 * @param string $section
	 * @return mixed
	 */
	public function get($item, $section = NULL) {

		/* Return value by section */
		if(!is_null($section) and isset($_SESSION[$this->sp .
			$section][$item])) {

			return $_SESSION[$this->sp . $section][$item];
		}

		if(isset($_SESSION[$this->sp . $item])) {
			return $_SESSION[$this->sp . $item];
		}

		return false;

	} // end func get

	/**
	 * Get section as array
	 *
	 * @access public
	 * @param string $section
	 * @return mixed array | mixed
	 */
	public function get_section($section) {
		if(isset($_SESSION[$this->sp . $section])) {
			return $_SESSION[$this->sp . $section];
		}

		return false;
	} // end func get_section

	/**
	 * Unset section
	 *
	 * @access public
	 * @param string $section
	 * @return bool
	 */
	public function remove_section($section) {
		if(isset($_SESSION[$this->sp . $section])) {
			unset($_SESSION[$this->sp . $section]);
			return true;
		} else {
			return false;
		}
	} // end func remove

	/**
	 * Remove session item
	 *
	 * @access public
	 * @param string $item
	 * @param string $section
	 * @return bool
	 */
	public function remove($item = NULL, $section = NULL) {

		/* return section */
		if(is_null($item) and !is_null($section)) {
			unset($_SESSION[$this->sp . $section]);
			return true;
		}

		if(!is_null($item) and !is_null($section)) {
			unset($_SESSION[$this->sp . $section][$item]);
			return true;
		}

		if(!is_null($item)) {
			unset($_SESSION[$this->sp . $item]);
			return true;
		}

		return false;

	} // end func remove

	/**
	 * Destroy session
	 * This will make the session.lib object useless
	 * You need to create a new instance if you need
	 * to recreate a new session later.
	 * Otherwise, use the regenerate() method
	 *
	 * @access public
	 * @return void
	 */
	public function destroy() {
		session_destroy();
	}

	/**
	 * Regenerate session id to discard
	 * current session data (maybe old) and start over
	 *
	 * @access public
	 * @return void
	 * @since  1.1.0
	 */
	public function regenerate() {
		foreach(array_keys($_SESSION) as $top_item){
			unset($_SESSION[$top_item]);
		}
		session_regenerate_id(true);
	}


	/* End of class session */
}

/* End of file */
?>