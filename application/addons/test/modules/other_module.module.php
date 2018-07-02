<?php
/**
 * Other Test Module for Addon Test
 */
defined('TK_EXEC') or die('Restricted area.');

// Do not get confused for this name of class.
// It looks like: {addon_name}_{module_name}_module ;)
class test_other_module_module extends module {

    public function __construct($params, $id_addon, $config, $log, $language) {
        parent::__construct($params, $id_addon, $config, $log, $language);
    }

    public function get_name() {
        return TK_SHORT_NAME;
    }

    public function given_params() {
        // params inherited from parent
        return $this->params;
    }

    public function display_name() {
        echo TK_SHORT_NAME;
    }

}