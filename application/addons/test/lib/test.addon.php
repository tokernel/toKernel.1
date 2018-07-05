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

        echo "<h1>Testing addon</h1>";

        // Test addon
        $t->run_by_value($this->lib->addons->test->get_number(), 5, 'Get Addon method returned value.');
        $t->run_by_type($this->load_view('test'), unit_test_lib::TYPE_OBJECT, 'Load Addon view file and get object.');
        $t->run_by_type($this->load_module('test_module'), unit_test_lib::TYPE_OBJECT, 'Load Addon module and get object.');
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
        //$t->run_by_value($this->check_backend(), true, 'Addon method check_backend() returned true.');

        $t->display_results();
        $t->reset();

    }

    public function action_views() {

        $t = $this->lib->unit_test;

        echo "<h1>Testing Views</h1>";

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

        $t->display_results();
        $t->reset();

        echo "<h2>View file parsed content</h2>";
        echo '<hr>';
        echo '<pre>';
        echo htmlspecialchars($buffer);
        echo '</pre>';
        echo '<hr>';

    }

    public function action_modules() {

        $t = $this->lib->unit_test;

        echo "<h1>Testing addon module from outside</h1>";

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

        $t->display_results();
        $t->reset();

        // Run tests inside
        $this->lib->addons->test->test_module->run_test();

    }

    public function action_libs() {
        $this->test_lib_file();
    }

    public function test_lib_file() {

        $t = $this->lib->unit_test;

        echo "<h1>Testing 'file' Library</h1>";

        $f = $this->lib->file;
        $tmp_path = sys_get_temp_dir();
        $tmp_file = 'toKernel.test.txt';

        $t->run_by_type($tmp_path, unit_test_lib::TYPE_STRING, 'Get tmp dir of system and return string.');
        $t->run_by_type($f->to_path('/var/www'), unit_test_lib::TYPE_STRING, 'Method to_path() should return String.');
        $t->run_by_value($f->to_path('/var/www'), '/var/www/', 'Method to_path() should convert /var/www to /var/www/ and return string.');
        $t->run_by_type($f->uname(), unit_test_lib::TYPE_STRING, 'Method uname() should return string.');
        $t->run_by_value(strlen($f->uname()), 8, 'Method uname() should return string with 8 chars.');
        $t->run_by_value($f->ext('test.php'), 'php', 'Method ext(test.php) should return php.');
        $t->run_by_value($f->ext('test.tar.gz'), 'gz', 'Method ext(test.tar.gz) should return gz.');
        $t->run_by_value($f->ext('test.tar.gz', 'gz'), 'gz', 'Method ext(test.tar.gz, gz) should return gz.');
        $t->run_by_value($f->ext('test.tar.gz', 'mz'), false, 'Method ext(test.tar.gz, mz) should return false.');
        $t->run_by_value($f->ext('test.tar.gz', 'mz|gz|pz'), 'gz', 'Method ext(test.tar.gz, mz|gz|pz) should return gz.');
        $t->run_by_value($f->ext('test.zip', 'mz|gz|pz'), false, 'Method ext(test.zip, mz|gz|pz) should return false.');
        $t->run_by_value($f->strip_ext('test.zip'), 'test', 'Method strip_ext(test.zip) should return test.');
        $t->run_by_value($f->write($tmp_path.'/'.$tmp_file, 'Hello!'), 6, 'Method write() should return 6.');
        $t->run_by_value($f->read($tmp_path.'/'.$tmp_file), 'Hello!', 'Method read() should return `Hello!`.');
        $t->run_by_type($f->append($tmp_path.'/'.$tmp_file, 'How are you?'), unit_test_lib::TYPE_INT, 'Method append() should return integer.');
        $t->run_by_type($f->ls($tmp_path), unit_test_lib::TYPE_ARRAY, 'Method ls('.$tmp_path.') should return array.');
        $t->run_by_value($f->ls($tmp_path.'123/456/789/'), false, 'Method ls('.$tmp_path.'/123/456/789/) should return false.');
        $t->run_by_type($f->ls(TK_PATH.'lib', '-', false, 'php'), unit_test_lib::TYPE_ARRAY, 'Method ls('.TK_PATH.'lib, -, false, php) should return array.');
        $t->run_by_type($f->ls(TK_PATH.'lib', '-', false, 'php|ini|conf'), unit_test_lib::TYPE_ARRAY, 'Method ls('.TK_PATH.'lib, -, false, php|ini|conf) should return array.');
        $t->run_by_value($f->ls(TK_PATH.'lib', '-', false, 'zip'), array(), 'Method ls('.TK_PATH.'lib, -, false, zip) should return empty array.');
        $t->run_by_type($f->ls(TK_PATH.'lib', '-', true, 'gif|php|ini|conf'), unit_test_lib::TYPE_ARRAY, 'Method ls('.TK_PATH.'lib, -, false, gif|php|ini|conf) should return array.');
        $t->run_by_type($f->ls(TK_PATH, 'd'), unit_test_lib::TYPE_ARRAY, 'Method ls('.TK_PATH.', d) should return array.');
        $t->run_by_type($f->ls(TK_PATH, 'd', true), unit_test_lib::TYPE_ARRAY, 'Method ls('.TK_PATH.', d, true) should return array.');
        $t->run_by_type($f->perms($tmp_path.'/'.$tmp_file), unit_test_lib::TYPE_STRING, 'Method perms() should return String.');
        $t->run_by_type($f->format_size(5840), unit_test_lib::TYPE_STRING, 'Method format_size(5840) should return String.');
        $t->run_by_type($f->format_size(58406565656565), unit_test_lib::TYPE_STRING, 'Method format_size(58406565656565) should return String.');
        $t->run_by_type($f->formatted_size_to_bytes('5.70 kb'), unit_test_lib::NUMERIC_DOUBLE, 'Method formatted_size_to_bytes(5.70 kb) should return Double.');
        $t->run_by_type($f->formatted_size_to_bytes('1 GB'), unit_test_lib::NUMERIC_DOUBLE, 'Method formatted_size_to_bytes(1 GB) should return Double.');
        $t->run_by_type($f->file_upload_max_size(), unit_test_lib::NUMERIC_DOUBLE, 'Method file_upload_max_size() should return Double.');

        $t->display_results();
        $t->reset();

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