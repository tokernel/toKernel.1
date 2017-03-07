<?php
/**
 * Example Module library to run the application in CLI (Command line interface).
 *
 * @version 1.0.0
 */
/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class example_cli_example_module extends module {

    public function __construct($attr, $id_addon, $config, $log, $language) {
        parent::__construct($attr, $id_addon, $config, $log, $language);
    }

    /**
     * Just display welcome message in CLI screen
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php --addon example --action welcome
     */
    public function welcome() {

        $this->lib->cli->out('Welcome! ' . TK_SHORT_NAME, 'green');
        $this->lib->cli->out(TK_NL);
        $this->lib->cli->out(TK_DESCRIPTION, 'green');
        $this->lib->cli->out(TK_NL);
        $this->lib->cli->out('Version ' . TK_VERSION, 'white');
        $this->lib->cli->out(TK_NL);

        return true;
    }

    /**
     * Run CLI application in interactive mode (with inserting values)
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php --addon example --action interactive
     */
    public function interactive() {

        $this->lib->cli->out('Hello!', 'yellow');
        $this->lib->cli->out(TK_NL);
        $this->lib->cli->out('Enter your name: ', 'white');
        $name = $this->lib->cli->in();
        $this->lib->cli->out('Hello ' . $name, 'green');
        $this->lib->cli->out(TK_NL);

        return true;
    }

    /**
     * Run CLI application with parameters
     *
     * Run this method calling:
     * # php {path_to_root_of_your_project}/index.php --addon example --action with_params --name David --email tokernel@gmail.com
     */
    public function with_params() {

        $this->lib->cli->out('Hello!', 'yellow');
        $this->lib->cli->out(TK_NL);
        $this->lib->cli->out('You were called this application with parameters listed bellow:', 'white');
        $this->lib->cli->out(TK_NL);

        $params = $this->lib->cli->params();

        if(empty($params)) {
            $this->lib->cli->out('There are no params!', 'red');
            $this->lib->cli->out(TK_NL);
            exit(1);
        }

        foreach($params as $item => $value) {
            $this->lib->cli->out($item . ': ' . $value, 'light_cyan');
            $this->lib->cli->out(TK_NL);
        }

        $this->lib->cli->out(TK_NL);

        return true;
    }

}
?>