<?php
/**
 * toKernel - Universal PHP Framework.
 * Templates interpreter library
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
 * @copyright  Copyright (c) 2018 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * template_lib class
 *
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class template_lib {

    /**
     * Library object for working with
     * libraries in this class
     *
     * @var object
     * @access protected
     */
    protected $lib;

    /**
     * Template parsing variables
     *
     * @var array
     * @access protected
     */
    protected $template_vars = array();

    /**
     * Template buffer to parse and interpret
     *
     * @var string
     * @access protected
     */
    protected $buffer;

    /**
     * Class constructor
     *
     * @access public
     */
    public function __construct() {
        $this->lib = lib::instance();
    } // end of func __construct

    /**
     * Clone the object
     *
     * @access protected
     * @return void
     */
    protected function __clone() {
        $this->buffer = '';
        $this->template_vars = array();
    }

    /**
     * Return template file path if exists
     *
     * @access public
     * @param string $template_name
     * @param mixed string | null $mode
     * @return mixed string|boolean
     */
    public function exists($template_name, $mode = NULL) {

        $template_file = $template_name . '.tpl.php';

        /* Create template directory path */
        $templates_dir = 'templates' . TK_DS;

        /* Check run mode and create template directory path */
        if(TK_RUN_MODE == 'cli') {

            /* If mode is not null, then append to directory */
            if(!is_null($mode)) {
                $templates_dir .= $mode . TK_DS;
            }

        } else {

            if(is_null($mode)) {
                $mode = TK_FRONTEND;
            }

            $templates_dir .= $mode . TK_DS;

        }

        /* Set template filename */
        $app_template_file = TK_CUSTOM_PATH . $templates_dir . $template_file;

        if(is_file($app_template_file)) {
            return $app_template_file;
        }

        return false;

    } // end func exists

    /**
     * Load template file and return cloned instance of this object.
     *
     * @access public
     * @param string $template_file
     * @param mixed array | null $template_vars
     * @return mixed object | bool
     */
    public function instance($template_file, array $template_vars = array()) {

        $obj = clone $this;

        $obj->load($template_file, $template_vars);

        return $obj;

    } // end func instance

    /**
     * Load template to bufer by template file path
     *
     * @access public
     * @param string $template_file
     * @param array $template_vars
     * @return string
     */
    public function load($template_file, array $template_vars = array()) {

        // Set template variables to parse, if not null.
        if(!empty($template_vars)) {
            $this->template_vars = $template_vars;
        }

        /* Get template buffer */
        ob_start();

        require($template_file);
        $this->buffer = ob_get_contents();
        ob_end_clean();

        return $this->buffer;

    } // end func load

    /**
     * Extract Widget defined tag to array with values
     * Example:
     * <!-- widget addon="my_addon" module="my_module" action="my_action" params="param1=param1_value|param2=param2_value" -->
     * Extract to:
     * Array(
     *    "addon" => "my_addon",
     *    "module" => "my_module",
     *    "action" => my_action"
     *    "params" => Array(
     *        "param1" => "param1_value",
     *        "param2" => "param2_value"
     *     )
     * )
     *
     * Note: "module" and "params" attributes in optional.
     *
     * @access public
     * @param string $widget_tag
     * @return array
     */
    public function extract_widget_tag($widget_tag) {

        $pattern = '/(\w+)=[\'"]([^\'"]*)/';

        preg_match_all($pattern, $widget_tag, $matches, PREG_SET_ORDER);

        $widget_data = [];

        foreach($matches as $match){
            $widget_data[$match[1]] = $match[2];
        }

        // Parse params is defined
        if(isset($widget_data['params']) and $widget_data['params'] != '') {

            $params = explode('|', $widget_data['params']);
            $parsed_params = array();

            foreach ($params as $param) {

                $tmp = explode('=', $param);

                if(count($tmp) > 1) {
                    $parsed_params[$tmp[0]] = $tmp[1];
                } else {
                    $parsed_params[] = $tmp[0];
                }
            }

            $widget_data['params'] = $parsed_params;
        }

        return $widget_data;

    } // End func extract_widget_tag

    /**
     * Run widget tag
     *
     * @access public
     * @param array $widget_data
     * @return string
     */
    public function run_widget_tag(array $widget_data) {

        if(empty($widget_data['addon'])) {
            trigger_error('Addon definition required to run addon widget!', E_USER_ERROR);
        }

        if(empty($widget_data['action'])) {
            trigger_error('Action definition required to run addon widget!', E_USER_ERROR);
        }

        $addon_name = $widget_data['addon'];
        $action = $widget_data['action'];

        if(isset($widget_data['params'])) {
            $params = $widget_data['params'];
        } else {
            $params = NULL;
        }

        ob_start();

        if(!isset($widget_data['module'])) {
            $this->lib->addons->$addon_name->$action($params);
        } else {
            $module_name = $widget_data['module'];
            $this->lib->addons->$addon_name->$module_name->$action($params);
        }

        $buffer = ob_get_clean();

        return $buffer;
    }

    /**
     * Interpret Template buffer and return result
     *
     * Example of widget definition tag in template file:
     * <!-- widget addon="my_addon" action="my_action" params="param1=param1_value|param2=param2_value" -->
     * <!-- widget addon="my_addon" module="my_module" action=my_action" "param1=param1_value|param2=param2_value" -->
     *
     * NOTE: It is possible to call addon widget in template file,
     * without widget tag definition. For example:
     * <?php $this->addons->my_addon->my_action(array('param1' => 'param1_value', 'param2' => param2_value)); ?>
     * <?php $this->addons->my_addon->my_module->my_action(array('param1' => 'param1_value', 'param2' => param2_value)); ?>
     * <?php $this->my_action(array('param1' => 'param1_value', 'param2' => param2_value)); ?>
     *
     * @access public
     * @param array $template_vars
     * @param string $replace_this_widget
     * @return string
     */
    public function run(array $template_vars = array(), $replace_this_widget = '') {

        // Append new incoming variables to template vars
        if(!empty($template_vars)) {
            $this->template_vars = array_merge($this->template_vars, $template_vars);
        }

        $buffer = $this->buffer;

        preg_match_all('/(<!-- widget (?(?=<!--)(?R)|.)*?-->)/s', $buffer, $widgets);

        if(!empty($widgets)) {
            foreach($widgets[0] as $widget) {

                $parsed_widget_tags = $this->extract_widget_tag($widget);

                if($parsed_widget_tags['addon'] == '__THIS__') {
                    $buffer = str_replace($widget, $replace_this_widget, $buffer);
                } else {
                    $buffer = str_replace($widget, $this->run_widget_tag($parsed_widget_tags), $buffer);
                }


            }
        }

        if(!empty($this->template_vars)) {
            foreach($this->template_vars as $item => $value) {
                $buffer = str_replace('{var.'.$item.'}', $value, $buffer);
            }
        }

        return $buffer;

    } // End func run

} // End class template_lib