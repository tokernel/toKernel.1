<?php
/**
 * toKernel - Universal PHP Framework.
 * Base module class
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
 * @version    2.3.6
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * module class
 *
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class module {

    /**
     * Status of module
     *
     * @access protected
     * @staticvar bool
     */
    protected static $initialized;

    /**
     * Library object for working with
     * libraries in this class
     *
     * @var object
     * @access protected
     */
    protected $lib;

    /**
     * Application object for accessing
     * application functions in this class
     *
     * @var object
     * @access protected
     */
    protected $app;

    /**
     * This module id
     *
     * @access protected
     * @var string
     */
    protected $id;

    /**
     * Addon id
     *
     * @access protected
     * @var string
     */
    protected $id_addon;

    /**
     * Addon configuration object
     *
     * @access protected
     * @var object
     */
    protected $config;

    /**
     * Addon log instance
     *
     * @var object
     * @access protected
     */
    protected $log;

    /**
     * Addon language
     *
     * @access protected
     * @var object
     */
    protected $language;

    /**
     * Parameters
     *
     * @access protected
     * @var array
     */
    protected $params;

    /**
     * Class Constructor
     *
     * @param mixed $params = NULL
     * @param string $id_addon
     * @param object $config
     * @param object $log
     * @param object $language
     */
    public function __construct($params = NULL, $id_addon, ini_lib $config, log_lib $log, language_lib $language) {

        // Define main objects
        $this->lib = lib::instance();
        $this->app = app::instance();

        // Define Addon ID
        $this->id_addon = $id_addon;

        // Define Addon configuration object
        $this->config = $config;

        // Define Addon log object
        $this->log = $log;

        $this->params = $params;

        // Define module id
        $this->id = substr(get_class($this), 0, -7);

        // Initialize language
        $this->language = $this->lib->language->instance(
            $this->app->language(),
            array(
                TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id_addon .
                TK_DS . 'languages' . TK_DS,

                TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id_addon .
                TK_DS . 'modules' . TK_DS . $this->id .
                TK_DS . 'languages' . TK_DS

            ),
            'Addon: ' . $this->id_addon . ' Module: ' . $this->id,
            true);

        self::$initialized = true;

    } // End class constructor

    /**
     * Load module by parent addon object
     *
     * @final
     * @access public
     * @param string $id_module
     * @param array $params
     * @param bool $clone
     * @return object
     */
    final public function load_module($id_module, $params = array(), $clone = false) {
        $parent_addon = $this->id_addon;
        return $this->lib->addons->$parent_addon->load_module($id_module, $params, $clone);
    }

    /**
     * Load view file for module and return `view` object.
     *
     * @final
     * @access public
     * @param string $file
     * @param array $params = array()
     * @return mixed
     * @since 2.1.0
     */
    final public function load_view($file, $params = array()) {

        $view_dir = $this->id;

        // Remove addon name from class name
        $view_dir = substr($view_dir, (strlen($this->id_addon) + 1), 100);

        // Parent view class included in tokernel.inc.php
        $parent_addon = $this->id_addon;
        $view_file = $this->lib->addons->$parent_addon->path() . 'modules' . TK_DS .$view_dir . TK_DS . 'views' . TK_DS . $file . '.view.php';

        // Check if view file exists
        if(!is_file($view_file)) {

            tk_e::log_debug(
                'View file `'.$view_file.'` not found for module '.get_class($this),
                get_class($this) . '->' . __FUNCTION__
            );

            trigger_error('View file `'.$view_file.'` not found for module '.get_class($this), E_USER_ERROR);

            return false;
        }

        tk_e::log_debug(
            'Loaded view file ' . $view_file . ' with params: Array['.count($params).']',
            get_class($this) . '->' . __FUNCTION__
        );

        // Return view object
        return new view($view_file, $this->id, $this->language, $params);

    } // end func load_view

    /**
     * Display Error 404 page if addon controller does'nt accessed with backend URL.
     * http://example.com/{backend_dir}/{addon_id} -> Return true
     * http://example.com/{addon_id} -> Display Error 404 page.
     *
     * @access public
     * @return bool
     * @since 2.3.0
     */
    public function check_backend() {

        if ($this->app->config('backend_dir', 'HTTP') != $this->lib->url->backend_dir()) {
            $this->app->error_404(
                'Cannot access this page without backend URL. ' .
                'Should be: ' . $this->lib->url->base_url() . $this->app->config('backend_dir', 'HTTP') . '/'. $this->id_addon . ' ' .
                'Instead of: ' . $this->lib->url->base_url() . $this->id_addon);
            return false;
        }

        return true;

    } // End func check_backend

    /**
     * Return addon configuration values
     *
     * @final
     * @access public
     * @param string $item = NULL
     * @param string $section = NULL
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
     * Return addon id of this module
     *
     * @access public
     * @return string
     */
    public function id_addon() {
        return $this->id_addon;
    }

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

} // End of class module

// End of file
