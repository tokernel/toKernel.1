<?php
// Some addon for testing

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class test_addon extends addon {
	
	public function __construct($params, $config) {
		parent::__construct($params, $config);
	}

	/**
	 * Default action which will be called if action not specified in url 
	 * www.example.com/test
	 */
	public function action_index() {
		
		$this->lib->html->set_title('Welcome to toKernel');
		$this->breadcrumbs->add('testing');
		
		$view = $this->load_view('test');
		$view->message = 'Welcome!';
		echo $view->run();
		
	}
	
	/**
	 * Action that working with form 
	 * www.example.com/test/form
	 */
	public function action_form() {
		
		$this->lib->html->set_title('Testing form');
	
		/* Action 'form' uses the template file of action 'index' */
		$this->app->set_template('test.index');
		
		$view = $this->load_view('form');
		
		if($this->app->request_method() == 'POST') {
			
			$name = $this->lib->filter->post('name');
			$message = $this->lib->filter->post('message');
			
			if($name != '') {		
				$view->message = 'Your name is ' . $name . '.';
			} else {
				$view->message = 'Empty values!';
			}	
		} else {
			$view->message = 'Enter your name and message.';
		}
		
		echo $view->run();
		
		return true;
	}
	
	/**
	 * Call this action from command line interface 
	 * php index.php --addon test --action test
	 */
	public function cli_test() {
		
		$this->lib->cli->out('Welcome! ' . TK_SHORT_NAME, 'green');
		$this->lib->cli->out(TK_NL);
		$this->lib->cli->out(TK_DESCRIPTION, 'green');
		$this->lib->cli->out(TK_NL);
		$this->lib->cli->out('Version ' . TK_VERSION, 'white');
		$this->lib->cli->out(TK_NL);
		
		return true;
	}
	
	/**
	 * Call this action from command line interface 
	 * This action working with interactive mode
	 * php index.php --addon test --action test_interactive
	 */
	public function cli_test_interactive() {
		
		$this->lib->cli->out('Hello!', 'yellow');
		$this->lib->cli->out(TK_NL);
		$this->lib->cli->out('Enter your name: ', 'white');
		$name = $this->lib->cli->in();
		$this->lib->cli->out('Hello ' . $name, 'green');
		$this->lib->cli->out(TK_NL);
		
		return true;
	}
			
} // end class
?>