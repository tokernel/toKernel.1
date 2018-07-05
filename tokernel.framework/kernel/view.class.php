<?php
/**
 * toKernel - Universal PHP Framework.
 * Main View class for addons.
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
 * @copyright  Copyright (c) 2018 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.5.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * addon view
 *
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class view {

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
     * Addon language
     *
     * @access protected
     * @var object
     */
    protected $language;

    /**
     * Buffer (html content)
     *
     * @access private
     * @var string
     */
    protected $_buffer = NULL;

    /**
     * Mixed values
     *
     * @access protected
     * @var array
     */
    protected $values = array();

    /**
     * View file with full path
     *
     * @access protected
     * @var string
     */
    protected $file = '';

    /**
     * View's owner addon id
     *
     * @access protected
     * @var string
     */
    protected $id_addon = '';

    /**
     * Class construcor
     *
     * @param string $file
     * @param string $id_addon
     * @param object language_lib $language
     * @param array $values
     */
    public function __construct($file, $id_addon, language_lib $language, array $values) {

        $this->lib = lib::instance();
        $this->app = app::instance();

        $this->language = $language;
        $this->file = $file;
        $this->id_addon = $id_addon;
        $this->values = $values;

    } // end constructor

    /**
     * Class destructor
     *
     * @access public
     * @return void
     */
    public function __destruct() {

        unset($this->_buffer);
        unset($this->values);
        unset($this->file);
        unset($this->id_addon);

    } // end destructor

    /**
     * Return value by name
     *
     * @access public
     * @param string $item
     * @return mixed
     */
    public function __get($item) {
        return $this->get_value($item);
    } // end func __get

    /**
     * Set value by name
     *
     * @access public
     * @param string $item
     * @param mixed $value
     * @return void
     */
    public function __set($item, $value) {
        $this->set_value($item, $value);
    }

    /**
     * Unset a value by name
     *
     * @access public
     * @param string $item
     * @return bool
     * @since 1.3.0
     */
    public function __unset($item) {

        if(isset($this->values[$item])) {
            unset($this->values[$item]);
            return true;
        }

        return false;

    } // end func __unset

    /**
     * Check whether a variable has been defined
     *
     * @access public
     * @param string $item
     * @return bool
     * @since 1.3.0
     */
    public function __isset($item) {

        if(isset($this->values[$item])) {
            return true;
        } else {
            return false;
        }

    } // end func __isset

    /**
     * Reset all values
     *
     * @access public
     * @return void
     * @since 1.2.0
     */
    public function reset() {
        $this->values = array();
        $this->_buffer = NULL;
    }

    /**
     * Return id_addon of this view
     *
     * @access public
     * @return string
     */
    public function id_addon() {
        return $this->id_addon;
    }

    /**
     * Set value
     *
     * @access public
     * @param string $item
     * @param mixed $value
     * @return void
     */
    public function set_value($item, $value) {
        $this->values[$item] = $value;
    } // end func add_value

    /**
     * @access public
     * @param array $values
     * @return void
     * @since version 1.5.0
     */
    public function set_values(array $values) {

        if(!empty($values)) {
            $this->values = array_merge($this->values, $values);
        }

    } // End func set_values

    /**
     * Get value by name
     *
     * @access public
     * @param string $item
     * @return mixed
     */
    public function get_value($item) {

        if(isset($this->values[$item])) {
            return $this->values[$item];
        } else {
            trigger_error('Undefined item `' . $item . '` in view object!`', E_USER_NOTICE);
            return NULL;
        }

    } // end func get_value

    /**
     * Return all given values
     *
     * @access public
     * @return array
     * @since version 1.5.0
     */
    public function get_values() {
        return $this->values;
    }

    /**
     * Call Interpreter and output the content.
     *
     * @access public
     * @param array $values
     * @return void
     */
    public function output($values = array()) {

        $this->set_values($values);

        echo $this->run();

    } // End func show

    /**
     * Interpret view file and return buffer
     *
     * @access public
     * @param array $values
     * @return string
     */
    public function run(array $values = array()) {

        $this->set_values($values);

        ob_start();
        require($this->file);
        $this->_buffer .= ob_get_contents();
        ob_end_clean();

        /* Replace all values */
        if(!empty($this->values)) {

            foreach($this->values as $item => $value) {
	            $this->_buffer = str_replace('{var.'.$item.'}', $value, $this->_buffer);
            }
        }

        tk_e::log_debug('End for addon/view - "' . $this->id_addon . '". File - "' .
            basename($this->file) . '".',
            get_class($this) . '->' . __FUNCTION__);

        return $this->_buffer;

    } // End func run

    /**
     * Get language value by expression
     * Return language prefix if item is null.
     *
     * @final
     * @access public
     * @param string $item
     * @return string
     */
    final public function language($item = NULL) {

        if(is_null($item)) {
            return $this->lib->url->prefix();
        }

        if(func_num_args() > 1) {
            $l_args = func_get_args();

            unset($l_args[0]);

            if(is_array($l_args[1])) {
                $l_args = $l_args[1];
            }

            return $this->language->get($item, $l_args);
        }

        return $this->language->get($item);

    } // end func language

    /* End of class view */
}

/* End of file */
?>