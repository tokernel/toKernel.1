<?php
/**
 * Example addon
 * This addon package demonstrates the main structure and usage of addons in toKernel Framework.
 *
 * @category   addon
 * @package    framework
 * @subpackage addon library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.7.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class example_addon extends addon {
	
	public function __construct($params, $config) {
		parent::__construct($params, $config);
	}

    /**
     * Defining methods for:
     * web accessible default (index action).
     * web accessible by name.
     * web accessible only by ajax request.
     * cli accessible (accessible only in command line interface).
     * none accessible (only callable method).
     */

	/**
     * web accessible default (index action).
     *
	 * Default action will called if action not specified in url
     * This is configured in /application/config/application.ini
     *
	 * http://localhost/my_project/ (access without addon or action in URL).
	 */
	public function action_index() {

	    echo 'Welcome! ' . TK_SHORT_NAME . '<br />';
	    echo TK_DESCRIPTION . '<br />';
	    echo 'Version ' . TK_VERSION;

	}

    /**
     * web accessible by name.
     *
     * This method is web accessible because of having "action_" prefix.
     *
     * http://localhost/my_project/example/accessible
     */
    public function action_accessible() {
        echo "Hello This is the web accessible method!";
    }

    /**
     * web accessible only by ajax request.
     *
     * This method is web accessible only by ajax requests because of having "action__ax_" prefix.
     *
     * http://localhost/my_project/example/accessible
     */
    public function action_ax_accessible() {
        echo "Hello This is the web ajax only accessible method!";
    }

	/**
     * Examples of CLI usage
     * Notice: To access (call/run) methods in command line, the methods names should start with "cli_" prefix.
     * See: http://tokernel.com/framework/documentation/class-libraries/cli
     *
     * Methods listed bellow, loads and using module /application/addons/example/modules/cli_example.module.php
     */

    /**
     * cli accessible (accessible only in command line interface).
     *
     * This method accessible only in command line interface because of having "cli_" prefix.
     *
     * Just display welcome message in CLI screen
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php --addon example --action welcome
     */
    public function cli_welcome() {
        $cli_module = $this->load_module('cli_example');
        $cli_module->welcome();
    }

    /**
     * Run CLI application in interactive mode (with inserting values)
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php --addon example --action interactive
     */
    public function cli_interactive() {
        $cli_module = $this->load_module('cli_example');
        $cli_module->interactive();
    }

    /**
     * Run CLI application with parameters
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php --addon example --action with_params --name David --email tokernel@gmail.com
     */
    public function cli_with_params() {
        $cli_module = $this->load_module('cli_example');
        $cli_module->with_params();
    }

    /**
     * Output CLI colors
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php --addon example --action colors
     */
    public function cli_colors() {
        $this->lib->cli->output_colors();
    }

    /**
     * Examples of MySQL Database library usage
     * Notice: Before run, please read and complete setup instructions /install/Install.txt
     * See also: http://tokernel.com/framework/documentation/class-libraries/mysql
     *
     * Actions bellow, loads and uses module /application/addons/example/modules/db_example.module.php
     *
     * Notice: To access web pages in addon library,
     * all methods accessible via web interface should have "action_" prefix.
     */

    /**
     * Insert record into MySQL Database table
     *
     * http://localhost/my_project/example/db_insert
     */
    public function action_db_insert() {
        $db_module = $this->load_module('db_example');
        $db_module->insert();
    }

    /**
     * Update record in MySQL Database table
     *
     * http://localhost/my_project/example/db_update
     */
    public function action_db_update() {
        $db_module = $this->load_module('db_example');
        $db_module->update();
    }

    /**
     * Delete record in MySQL Database table
     *
     * http://localhost/my_project/example/db_delete
     */
    public function action_db_delete() {
        $db_module = $this->load_module('db_example');
        $db_module->delete();
    }

    /**
     * Select records from MySQL Database table
     *
     * http://localhost/my_project/example/db_select
     */
    public function action_db_select() {
        $db_module = $this->load_module('db_example');
        $db_module->select();
    }

    /**
     * Other examples of mysql class library
     *
     * http://localhost/my_project/example/db_other
     */
    public function action_db_other() {
        $db_module = $this->load_module('db_example');
        $db_module->other();
    }

    /**
     * Examples of Templates and widgets usage
     * Actions bellow will demonstrate you the templates and widgets functionality in project.
     *
     * Notice: Template files located in /application/templates/frontend/ directory
     */

    /**
     * Load template with setting the name.
     *
     * http://localhost/my_project/example/template_by_name
     */
    public function action_template_by_name() {

        // Setting the application template to process.
        $this->app->set_template('example.my_template');

        // This output will display in template widget named "__THIS__".
        // See: /application/templates/frontend/example.my_template.tpl.php
        echo 'This is the addon action output.';
    }

    /**
     * Load template by default, because the action name template names are equal.
     *
     * http://localhost/my_project/example/template_by_default
     */
    public function action_template_by_default() {

        // We were not setting any template name, but the default template of this action
        // will be loaded, because the template and addon.action names are equal.

        // template name: example.by_default
        // addon.action name: example.by_default

        // This output will display in template widget named "__THIS__".
        // See: /application/templates/frontend/example.template_by_default.tpl.php
        echo 'This is the addon action output.';
    }

    /**
     * Load template with widgets
     *
     * http://localhost/my_project/example/template_with_widgets
     */
    public function action_template_with_widgets() {

        // Setting the application template to process.
        $this->app->set_template(
            'example.my_template_with_widgets',
            array(
                'name' => TK_SHORT_NAME,
                'version' => TK_VERSION
            )
        );

        // This output will display in template widget named "__THIS__".
        // See: /application/templates/frontend/example.my_template.tpl.php
        echo '[This is the addon action output.]';

    }

    /**
     * Widget without parameters.
     *
     * This widget method called from template file:
     * /application/templates/frontend/example.my_template_with_widgets.tpl.php
     *
     * as:
     * <!-- widget addon="example" action="widget_without_params" -->
     *
     * As you can see, this method is not possible to call by HTTP request because it not have "action_" prefix.
     */
    public function widget_without_params() {

        // This content will output in template file where the widget defined;
        echo '<h2>This is content from first widget</h2>';

    }

    /**
     * Widget with parameters.
     *
     * This widget method called from template file:
     * /application/templates/frontend/example.my_template_with_widgets.tpl.php
     *
     * as:
     * <!-- widget addon="example" action="widget_with_params" params="project=My Project|version=1.0.0 alpha" -->
     */
    public function widget_with_params($params) {

        // This content will output in template file where the widget defined;
        echo '<h2>This is content from second widget with parameters:</h2>';

        echo '<p>';
        foreach ($params as $item => $value) {
            echo $item . ': ' . $value . '<br />';
        }
        echo '</p>';

    }

    /**
     * Examples of views (View files) usage
     *
     * The views are HTML content formatted files with output content.
     */

    /**
     * Using just one simple view file.
     *
     * http://localhost/my_project/example/view_simple
     */
    public function action_view_simple() {

        // Load the view object
        $view = $this->load_view('simple');

        // Output the view HTML content to screen.
        $view->show();
    }

    /**
     * Using more then one views in views
     * In ths second view we will set parameters (to display).
     *
     * http://localhost/my_project/example/view_more_than_one
     */
    public function action_view_more_than_one() {

        // Load the view object
        $view1 = $this->load_view('simple');

        // Output the view HTML content to screen.
        $view1->show();

        // Load second view file
        $view2 = $this->load_view('simple_with_params');

        // Set values to view file
        $view2->project_name = 'My project';
        $view2->project_version = '1.0.0 alpha';

        // In this case, we will get the parsed content of view, then output.
        $parsed_view = $view2->run();

        echo $parsed_view;

    }

    /**
     * Example of run template with views
     *
     * http://localhost/my_project/example/template_with_view
     */
    public function action_template_with_view() {

        $this->app->set_template('example.my_template');

        // Load second view file
        $view = $this->load_view('simple_with_params');

        // define values for view file
        // In this case we will use another approach to set values to view
        $data = array(
            'project_name' => 'My project',
            'project_version' => '1.0.0 alpha'
        );

        // Get parsed HTML Content
        $parsed_view = $view->run($data);

        echo $parsed_view;
    }

    /**
     * View files used by module
     *
     * Each module can have own modules located in modules/{module_name}/views directory.
     * In our example, the module file is: /application/addons/example/modules/views_example.module.php
     * Where view files of module located in: /application/addons/example/modules/views_example/view/
     *
     * http://localhost/my_project/example/view_of_module
     */
    public function action_view_of_module() {

        $module = $this->load_module('views_example');
        $module->view_simple();

    }

    /**
     * toKernel framework also supports large scale of multi-language functionality.
     *
     * Main default language files located in /application/languages/
     * where each language file name denied as language prefix.
     *
     * For example, the English language file will be "en.ini"
     * and the Russian language file "ru.ini"
     *
     * Each of "addon" in toKernel framework also can have own language files.
     * This approach gives us possibility to separate addon language files from others.
     *
     */

    /**
     * Using Application main languages located in application/languages/
     * $this->app->language('your_language_expression')
     *
     * Using Addon specific languages located in application/addons/example/languages/
     * $this->language('your_language_expression')
     *
     * NOTICE: In Views and Modules you also can use $this->language() method.
     *
     * http://localhost/my_project/example/multi_language
     *
     */
    public function action_multi_language() {


        // Using
        /*
        $this->app->language('err_subject_production');
        $this->language('msg_error_encountered');
        $this->language(
             'my_project_name_is_with_version',
             'My project',
             '1.0.0 alpha'
        );
        */

        // NOTICE! In real live, the content is better to echo by view file, instead of echo each HTML tag.
        // This is Just en example action to demonstrate multi-language functionality.

        echo '<h1>Multi-languages usage</h1>';

        echo '<h2>Application Language usage</h2>';
        echo '<p>';
        echo 'The string (error message) listed bellow loaded from application language:<br/>';
        echo '<strong>' . $this->app->language('err_subject_production') . '</strong>';
        echo '</p>';

        echo '<h2>Addon Language usage</h2>';
        echo '<p>';
        echo 'The string (error message) listed bellow loaded from addon language:<br/>';
        echo '<strong>' . $this->language('msg_error_encountered') . '</strong>';
        echo '</p>';


        echo '<h2>Language usage with additional arguments in</h2>';
        echo '<p>';
        echo 'The string listed bellow loaded from addon language with additional 2 values:<br/>';
        echo 'We adding the project name and version into language string<br/>';
        echo '<strong>' .
                $this->language(
                    'my_project_name_is_with_version',
                    'My project',
                    '1.0.0 alpha'
                )
            . '</strong>';
        echo '</p>';
    }

    /**
     * Extending class libraries into application.
     *
     * In the toKernel framework we have shopping_cart library:
     *  /tokernel.framework/lib/shopping_cart.lib.php
     * and it is extended in application as:
     *  /application/lib/shopping_cart.lib.php
     *
     * The items_get_json() method added to extended library.
     *
     * http://localhost/my_project/example/extended_lib_usage
     */
    public function action_extended_lib_usage() {

        // Define shopping cart library
        $s = $this->lib->shopping_cart;

        // Reset! Clean session if already set.
        $s->reset();

        // Add products
        $s->item_set(
            $item = array(
                'price' => 25.5,
                'quantity' => 2,
                'name' => 'PHP book'
            )
        );

        $s->item_set(
            $item = array(
                'price' => 1500,
                'quantity' => 1,
                'name' => 'How to be happy?'
            )
        );

        // Get products
        // This method defined in parent class library
        print_r($s->items_get());

        // This method defined in extended class library
        print_r($s->items_get_json());

    }
	
	/**
	 * This example will demonstrate how to use the pagination class library.
	 *
	 * http://localhost/my_project/example/pagination_usage
	 */
    public function action_pagination_usage() {
    	
    	echo "<h1>Using Pagination library</h1>";
    	
    	// Defining a Pagination library object
    	$p = $this->lib->pagination->instance();
	
    	// Getting offset from URL parameters
	    $offset = $this->lib->url->params(0);
    	
	    // Total records (from database)
    	$total = 1420;
    	
    	// How many items on page
    	$limit = 10;
		    
    	// Base url for pagination
	    $base_url = $this->lib->url->url('example', 'pagination_usage', false, true);
    	
	    // Setting some configuration to pagination
	    $p->configure(
	    	array(
			    'prev_link' => '&#9668',
			    'next_link' => '&#9658'
		    )
	    );
	    
	    // Outputting the pagination content
    	echo $p->run($total, $limit, $offset, $base_url);
    	
    }

} // end class
?>