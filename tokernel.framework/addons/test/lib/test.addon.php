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
	
	public function action_download() {
	
		$this->lib->file->download(TK_CUSTOM_PATH . 'uploads' . TK_DS . 'toKernel.v.1.0.0.pdf');
		return true;
		
	}
	
	/* Show picture with photo lib */
	public function action_photo() {
		
		$pic = $this->lib->url->params('pic');
		
		$w = $this->lib->url->params('w');
		$h = $this->lib->url->params('h');
		$q = $this->lib->url->params('q');
		$c = $this->lib->url->params('c');
		$o = $this->lib->url->params('o');
		
		$crop = explode(':', $c);
		
		if(!isset($crop[1]) or !isset($crop[0])) {
			trigger_error("Illegal crop ratio");
			return;
		}
		
		if(intval($crop[1] == 0) or intval($crop[0]) == 0) {
			trigger_error("Illegal crop ratio");
			return;
		}
		
		$crop_ratio = intval($crop[0]) / intval($crop[1]);
  
		$pic_source = TK_CUSTOM_PATH . 'tmp' . TK_DS . $pic;
  
		if(!file_exists($pic_source)) {
			return false;
		}
		
		$p_instance = $this->lib->photo->instance();
				
		$p_instance->render(
				array(
					'source' => $pic_source,
					'quality' => $q,
					'width' => $w,
					'height' => $h,
					'crop_ratio' => $crop_ratio,
					'offset' => $o,
		      )
		);
				
		return true;
		
	} // End func action_photo
	
} // end class
?>