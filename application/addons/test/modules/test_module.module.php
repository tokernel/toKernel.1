<?php
/**
 * Test Module for Addon Test
 */
defined('TK_EXEC') or die('Restricted area.');

// Do not get confused for this name of class.
// It looks like: {addon_name}_{module_name}_module ;)
class test_test_module_module extends module {

    public function __construct($attr, $id_addon, $config, $log, $language) {
        parent::__construct($attr, $id_addon, $config, $log, $language);
    }

    public function get_number() {
        return 5;
    }

    public function display_params($params) {
        print_r($params);
    }

    public function run_test() {

        echo "<h1>Testing addon module inside</h1>";

        $t = $this->lib->unit_test;

        // Test Module
        $t->run_by_value($this->get_number(), 5, 'Get module method returned value.');
        //$t->run_by_value($this->check_backend(), true, 'Module method check_backend() returned true.');
        //$t->run_by_type($this->check_backend(), unit_test_lib::TYPE_BOOL, 'Module method check_backend() returned boolean.');
        $t->run_by_type($this->config('version', 'CORE'), unit_test_lib::TYPE_STRING, 'Check if Addon/Module config method returns string.');
        $t->run_by_type($this->config(NULL, 'CORE'), unit_test_lib::TYPE_ARRAY, 'Check if Addon/Module config method returns array.');
        $t->run_by_type($this->id_addon(), unit_test_lib::TYPE_STRING, 'Check if Module method id_addon() returns string.');
        $t->run_by_value($this->id_addon(), 'test', 'Check if Module method id_addon() returns exact value.');
        $t->run_by_type($this->language(), unit_test_lib::TYPE_STRING, 'Check if Module method language() returns type string.');
        $t->run_by_value($this->language('test'), 'Test', 'Check if Module method language("test") returns "Test".');
        $t->run_by_value($this->language('next_v_generation_of_tk', TK_VERSION, TK_SHORT_NAME), 'Next '.TK_VERSION.' generation of '.TK_SHORT_NAME, 'Check if Module method language("test", "param1", "param2") returns exact string.');
        $t->run_by_type($this->load_module('other_module', array('a' => 1)), unit_test_lib::TYPE_OBJECT, 'Check if Module method load_module() returns type object.');
        $t->run_by_type($this->lib->addons->test->other_module->given_params(), unit_test_lib::TYPE_ARRAY, 'Check if Module parameters is array and not empty.');
        $t->run_by_type($this->load_module('other_module', array('a' => 2), true), unit_test_lib::TYPE_OBJECT, 'Check if Module method load_module() returns type object cloned.');
        $view = $this->load_view('test', array('name' => TK_SHORT_NAME));
        $view->version = TK_VERSION;
        $t->run_by_type($view, unit_test_lib::TYPE_OBJECT, 'Check if Module method load_view() returns type object.');
        $buffer = $view->run();
        $t->run_by_type($buffer, unit_test_lib::TYPE_STRING, 'Check if View file method run() returns type string.');

        $t->display_results();
        $t->reset();

        echo "<h2>View file parsed content</h2>";
        echo '<hr>';
        echo '<pre>';
        echo htmlspecialchars($buffer);
        echo '</pre>';
        echo '<hr>';

    }

}