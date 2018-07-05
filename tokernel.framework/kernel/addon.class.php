<?php
/**
 * toKernel - Universal PHP Framework.
 * Base Addon class
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
 * @version    3.4.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 *
 * @todo Load - language, log and other object if only required.
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * addon class
 *
 * @author David A. <tokernel@gmail.com>
 */
abstract class addon {

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
     * Addon id
     *
     * @access protected
     * @var string
     */
    protected $id;

    /**
     * Addon configuration object
     *
     * @access protected
     * @var object
     */
    protected $config;

    /**
     * Addon log instance object
     *
     * @var object
     * @access protected
     */
    protected $log;

    /**
     * Addon language object
     *
     * @access protected
     * @var object
     */
    protected $language;

    /**
     * Array of loaded modules (objects).
     *
     * @access protected
     * @staticvar array
     */
    protected static $loaded_modules = array();

    /**
     * Class constructor
     *
     * @access public
     * @param array $params
     * @param object $config
     */
    public function __construct($params = array(), $config) {

        $this->lib = lib::instance();
        $this->app = app::instance();

        $this->params = $params;
        $this->config = $config;

        // Define addon id
        $this->id = substr(get_class($this), 0, -6);

        // Load log object for addon
        $log_ext = $this->app->config('log_file_extension', 'ERROR_HANDLING');
        $this->log = $this->lib->log->instance('addon_' . $this->id . '.' . $log_ext);

        $this->language = $this->lib->language->instance(
            $this->app->language(),
            array(
                TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id . TK_DS . 'languages' . TK_DS
            ),
            'Addon: ' . $this->id,
            true);

    } // end constructor

    /**
     * Class destructor
     *
     * @access public
     * @return void
     */
    public function __destruct() {
        unset($this->config);
        unset($this->log);
        unset($this->language);
    } // end destructor

    /**
     * Return module object
     *
     * @final
     * @access public
     * @param string $module
     * @return object | bool
     * @since 3.1.0
     */
    final public function __get($module) {
        return $this->load_module($module);
    } // End func __get

    /**
     * Load and return module as an object.
     *
     * By default, if module already loaded, the loaded object will be returned.
     * The 3th argument assumes that the module object will be defined as new.
     *
     * @final
     * @access public
     * @param string $id_module
     * @param array $params
     * @param bool $clone
     * @return object
     * @since 2.0.0
     */
    final public function load_module($id_module, array $params = array(), $clone = false) {

        if (trim($id_module) == '') {
            trigger_error('Called load_module with empty id_module for addon: ' . $this->id, E_USER_ERROR);
            return false;
        }

        // Check, is module exists
        if (!$this->module_exists($id_module)) {
            trigger_error('Module file `' . $id_module . '` not exists for addon `' . $this->id . '`.', E_USER_ERROR);
            return false;
        }

        $module_index = $this->id . '_' . $id_module;

        // Return module object, if it is already loaded and not requested for clone
        if (array_key_exists($module_index, self::$loaded_modules) and $clone == false) {
            return self::$loaded_modules[$module_index];
        }

        // Set module filename
        $module_file = $this->path() . 'modules' . TK_DS . $id_module . '.module.php';

        // Include module file and define class name
        require_once($module_file);
        $module_class = $module_index . '_module';

        // Check if module class exists
        if (!class_exists($module_class)) {
            trigger_error('Module class `' . $module_class . '` not exists in file '.$module_file.' for addon `' . $this->id . '`.', E_USER_ERROR);
            return false;
        }

        // Define new module object
        $module = new $module_class(
            $params,
            $this->id,
            $this->config,
            $this->log,
            $this->language,
            $module_index
        );

        // Module loaded as a singleton and will be appended into loaded modules array
        if ($clone == false) {
            self::$loaded_modules[$module_index] = $module;
        }

        tk_e::log_debug('Loaded module ' . $module_index . ' with params Array[' .
            count($params) . ']', get_class($this) . '->' . __FUNCTION__);

        // return module object
        return $module;

    } // end func load_module

    /**
     * Load view file for addon and return 'view' object.
     *
     * @final
     * @access public
     * @param string $file
     * @param array $values = array()
     * @return mixed string | false
     * @since 2.0.0
     */
    final public function load_view($file, array $values = array()) {

        // Parent view class included in tokernel.inc.php
        $view_file = $this->path() . 'views' . TK_DS . $file . '.view.php';

        // Check if file exists
        if (!is_file($view_file)) {

            tk_e::log_debug(
                'There are no view file `' . $view_file . '`" to load for addon: `'.$this->id.'`.',
                get_class($this) . '->' . __FUNCTION__
            );

            trigger_error('There are no view file `' . $view_file . '`" to load for addon: `'.$this->id.'`.', E_USER_ERROR);

            return false;
        }

        tk_e::log_debug(
            'Loaded view file `' . $view_file . '` for addon `'.$this->id.'` with params - Array[' . count($values) . ']',
            get_class($this) . '->' . __FUNCTION__);

        // Return view object
        return new view($view_file, $this->id, $this->language, $values);

    } // end func load_view

    /**
     * Check if module of addon exists
     *
     * @access public
     * @param string $id_module
     * @return bool
     * @since 2.0.0
     */
    public function module_exists($id_module) {

        if (trim($id_module) == '') {
            return false;
        }

        // Define module file path
        $module_file = $this->path() . 'modules' . TK_DS . $id_module . '.module.php';

        if (is_file($module_file)) {
            return true;
        }

        return false;

    } // end func module exists

    /**
     * Return addon configuration values.
     * Return config array if item is null and
     * section defined, else, return value by item
     *
     * @final
     * @access public
     * @param string $item
     * @param string $section
     * @return mixed
     */
    final public function config($item = NULL, $section = NULL) {

        // Check arguments
        if(is_null($item) and is_null($section)) {
            trigger_error('At least one argument requires to get config value for addon `'.$this->id.'` !', E_USER_ERROR);
            return false;
        }

        // Return item value
        if (!is_null($item)) {
            return $this->config->item_get($item, $section);
        }

        // Return section values
        if (is_null($item) and !is_null($section)) {
            return $this->config->section_get($section);
        }

    } // end func config

    /**
     * Return this addon id
     *
     * @final
     * @access public
     * @return string
     */
    final public function id() {
        return $this->id;
    }

    /**
     * Get language value by expression
     * Return language prefix if item is null.
     *
     * @access public
     * @param string $item
     * @return string
     */
    public function language($item = NULL) {

        if (is_null($item)) {
            return $this->lib->url->language_prefix();
        }

        if (func_num_args() > 1) {
            $l_args = func_get_args();

            unset($l_args[0]);

            if (is_array($l_args[1])) {
                $l_args = $l_args[1];
            }

            return $this->language->get($item, $l_args);
        }

        return $this->language->get($item);

    } // end func language

    /**
     * Return addon's url
     *
     * @access public
     * @return string
     * @since 3.2.0
     */
    public function url() {
        return $this->lib->url->base_url() . TK_CUSTOM_DIR . '/addons/' . $this->id . '/';
    }

    /**
     * Return addon's path
     *
     * @access public
     * @return string
     * @since 3.2.0
     */
    public function path() {
        return TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id . TK_DS;
    }

    /**
     * Return true, if addon controller accessed in URL with backend dir.
     * http://example.com/{backend_dir}/{addon_id} -> return true
     * http://example.com/{addon_id} -> Return false
     *
     * @access public
     * @return bool
     * @since 2.2.0
     */
    public function is_backend() {

        if ($this->app->config('backend_dir', 'HTTP') != $this->lib->url->backend_dir()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Display Error 404 page if addon controller does'nt accessed with backend URL.
     * http://example.com/{backend_dir}/{addon_id} -> Return true
     * http://example.com/{addon_id} -> Display Error 404 page.
     *
     * @access public
     * @return bool
     * @since 2.2.0
     */
    public function check_backend() {

        if ($this->app->config('backend_dir', 'HTTP') != $this->lib->url->backend_dir()) {
            $this->app->error_404(
                'Cannot access this page without backend URL. ' .
                'Should be: ' . $this->lib->url->base_url() . $this->app->config('backend_dir', 'HTTP') . '/'. $this->id . ' ' .
                'Instead of: ' . $this->lib->url->base_url() . $this->id);
            return false;
        }

        return true;
    }

    /**
     * Get addon modules
     *
     * @access public
     * @return array
     * @since 3.4.0
     */
    public function get_modules() {

        $path = $this->path() . 'modules' . TK_DS;
        $files = $this->lib->file->ls($path, '-', false, 'php');
        $modules = array();

        if (empty($files)) {
            return $modules;
        }

        foreach ($files as $file) {


            // Assume only file names ending with '*.module.php' are modules.
            if (substr($file, -11) == '.module.php') {
                $modules[] = substr($file, 0, -11);
            }
        }

        return $modules;

    } // End func get_modules

    /**
     * Final method to except creation of methods with reserved names.
     *
     * @access protected
     * @final
     * @return void
     */
    final protected function action_()
    {
    }

    /**
     * Final method to except creation of methods with reserved names.
     *
     * @access protected
     * @final
     * @return void
     */
    final protected function action_ax_()
    {
    }

    /**
     * Final method to except creation of methods with reserved names.
     *
     * @access protected
     * @final
     * @return void
     */
    final protected function cli_()
    {
    }

} // End of class addon

// End of file