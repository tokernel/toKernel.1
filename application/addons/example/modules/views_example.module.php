<?php
/**
 * Example Module library to demonstrate the view files usage by modules.
 * Each module can have own view files located in 'views' directory.
 *
 * In our example:
 * Module: /application/addons/example/modules/views_example.module.php
 * View files should be in: /application/addons/example/modules/views_example/views/
 *
 * @version 1.0.0
 */
/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class example_views_example_module extends module {

    public function __construct($attr, $id_addon, $config, $log, $language) {
        parent::__construct($attr, $id_addon, $config, $log, $language);
    }

    /**
     * Display view file located in module's views.
     * This will load the view file: /application/addons/example/modules/views_example/views/view_file_of_module.view.php
     */
    public function view_simple() {

        $view = $this->load_view('view_file_of_module');

        echo $view->show();
        return true;
    }

}
?>