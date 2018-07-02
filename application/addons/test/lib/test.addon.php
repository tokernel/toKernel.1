<?php
/**
 * Test addon
 * Addon package to run and test toKernel functionality in different PHP Versions.
 *
 * Tests:
 *
 * HTTP/CLI Modes:
 * - Addons usage
 * - Modules usage
 * - Libs usage
 * - DB CRUD
 *
 * @category   addon
 * @package    framework
 * @subpackage addon library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2018 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.8.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class test_addon extends addon {
	
	public function __construct($params, $config) {
		parent::__construct($params, $config);
	}

	// ---- HTTP Mode testing ----

	public function action_addons() {

	    $t = $this->lib->unit_test;

        echo "Testing addon" . TK_NL;

        // Test addon
        $t->run_by_value($this->lib->addons->test->get_number(), 5, 'Get Addon method returned value.');
        $t->run_by_type($this->load_view('test'), unit_test_lib::TYPE_OBJECT, 'Load Addon view file and get object.');
        $t->run_by_type($this->load_module('test_module'), unit_test_lib::TYPE_OBJECT, 'Load Addon module and get object.');
        $t->run_by_type($this->load_template('test'), unit_test_lib::TYPE_STRING, 'Load Template and get string buffer.');
        $t->run_by_value($this->module_exists('test_module'), true, 'Check if module test_module exists.');
        $t->run_by_type($this->config('version', 'CORE'), unit_test_lib::TYPE_STRING, 'Check if Addon config method returns string.');
        $t->run_by_type($this->config(NULL, 'CORE'), unit_test_lib::TYPE_ARRAY, 'Check if Addon config method returns array.');
        $t->run_by_value($this->id(), 'test', 'Check if Addon method id() returns "test".');
        $t->run_by_type($this->language(), unit_test_lib::TYPE_STRING, 'Check if Addon method language() returns type string.');
        $t->run_by_value($this->language('test'), 'Test', 'Check if Addon method language("test") returns "Test".');
        $t->run_by_value($this->language('next_v_generation_of_tk', TK_VERSION, TK_SHORT_NAME), 'Next '.TK_VERSION.' generation of '.TK_SHORT_NAME, 'Check if Addon method language("test", "param1", "param2") returns exact string.');
        $t->run_by_type($this->url(), unit_test_lib::TYPE_STRING, 'Check if Addon method url() returns string.');
        $t->run_by_type($this->path(), unit_test_lib::TYPE_STRING, 'Check if Addon method path() returns string.');
        $t->run_by_value(is_dir($this->path()), true, 'Check if Addon method path() returns correct existing path.');
        $t->run_by_value($this->is_backend(), false, 'Check if Addon loaded from backend url. (should return false).');
        $t->run_by_type($this->get_modules(), unit_test_lib::TYPE_ARRAY, 'Check if returned modules list is array.');

        echo '<pre>';
        print_r($t->get_results());
        $t->reset();
        echo '</pre>';


    }

    public function action_views() {

        $t = $this->lib->unit_test;

        echo "Testing Views" . TK_NL;

        $view = $this->load_view('test', array('number' => 555, 'text' => 'Hello'));

        $t->run_by_type($view, unit_test_lib::TYPE_OBJECT, 'Load Addon view file and get object.');

        $view->other_number = 999;
        $view->set_value('number3', 777);

        $buffer = $view->run();
        $t->run_by_type($buffer, unit_test_lib::TYPE_STRING, 'Run View file and get runned buffer string.');
        $t->run_by_type($view->id_addon(), unit_test_lib::TYPE_STRING, 'Run View object\'s id_addon() and get type string');
        $t->run_by_value($view->id_addon(), $this->id(), 'Run View object\'s id_addon() method and get exact from $this->id().');
        $t->run_by_value($view->get_value('number3'), 777, 'Run View object\'s get_value() method and get exact number.');
        $t->run_by_type($view->get_values(), unit_test_lib::TYPE_ARRAY, 'Run View object\'s get_values() method and get array.');
        $t->run_by_value($view->reset(), NULL, 'Run View object\'s reset() method and get NULL.');

        echo '<pre>';
        print_r($t->get_results());
        $t->reset();
        echo '</pre>';

        echo "View file parsed content".TK_NL;
        echo '<hr>';
        echo '<pre>';
        echo htmlspecialchars($buffer);
        echo '</pre>';
        echo '<hr>';

    }

    public function action_modules() {

        $t = $this->lib->unit_test;

        echo "Testing addon module from outside" . TK_NL;

        // Test Module
        $t->run_by_value($this->lib->addons->test->test_module->get_number(), 5, 'Get module method returned value.');
        //$t->run_by_value($this->lib->addons->test->test_module->check_backend(), true, 'Module method check_backend() returned true.');
        //$t->run_by_type($this->lib->addons->test->test_module->check_backend(), unit_test_lib::TYPE_BOOL, 'Module method check_backend() returned boolean.');
        $t->run_by_type($this->lib->addons->test->test_module->config('version', 'CORE'), unit_test_lib::TYPE_STRING, 'Check if Addon/Module config method returns string.');
        $t->run_by_type($this->lib->addons->test->test_module->config(NULL, 'CORE'), unit_test_lib::TYPE_ARRAY, 'Check if Addon/Module config method returns array.');
        $t->run_by_type($this->lib->addons->test->test_module->id_addon(), unit_test_lib::TYPE_STRING, 'Check if Module method id_addon() returns string.');
        $t->run_by_value($this->lib->addons->test->test_module->id_addon(), $this->id(), 'Check if Module method id_addon() returns exact value.');
        $t->run_by_type($this->lib->addons->test->test_module->language(), unit_test_lib::TYPE_STRING, 'Check if Module method language() returns type string.');
        $t->run_by_value($this->lib->addons->test->test_module->language('test'), 'Test', 'Check if Module method language("test") returns "Test".');
        $t->run_by_value($this->lib->addons->test->test_module->language('next_v_generation_of_tk', TK_VERSION, TK_SHORT_NAME), 'Next '.TK_VERSION.' generation of '.TK_SHORT_NAME, 'Check if Module method language("test", "param1", "param2") returns exact string.');
        $t->run_by_type($this->lib->addons->test->test_module->load_module('other_module', array('a' => 1)), unit_test_lib::TYPE_OBJECT, 'Check if Module method load_module() returns type object.');
        $t->run_by_type($this->lib->addons->test->other_module->given_params(), unit_test_lib::TYPE_ARRAY, 'Check if Module parameters is array and not empty.');
        $t->run_by_type($this->lib->addons->test->test_module->load_module('other_module', array('a' => 2), true), unit_test_lib::TYPE_OBJECT, 'Check if Module method load_module() returns type object cloned.');
        $view = $this->lib->addons->test->other_module->load_view('test', array('name' => TK_SHORT_NAME));
        $t->run_by_type($view, unit_test_lib::TYPE_OBJECT, 'Check if Module method load_view() returns type object.');
        $buffer = $view->run();
        $t->run_by_type($buffer, unit_test_lib::TYPE_STRING, 'Check if View file method run() returns type string.');

        echo '<pre>';
        print_r($t->get_results());
        $t->reset();
        echo '</pre>';

        $t->reset();

        // Run tests inside
        $this->lib->addons->test->test_module->run_test();

    }

    public function action_libs() {

    }

    public function action_all() {

        $this->action_addons();
        $this->action_views();
        $this->action_modules();
        $this->action_libs();

    }

    public function get_number() {
        return 5;
    }

    // ---- CLI Mode testing ----

    public function cli_addons() {

    }

    public function cli_modules() {

    }

    public function cli_libs() {

    }

    public function cli_crud() {

    }

    public function cli_all() {

        $this->action_addons();
        $this->action_modules();
        $this->action_libs();

    }

    public function display_params($params) {
        print_r($params);
    }

} // end class
?>