<?php
/**
 * toKernel - Universal PHP Framework.
 * Parent addon class for addons.
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
 * @category   framework
 * @package    toKernel
 * @subpackage kernel
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2013 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    3.3.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * 
 * @todo Load - language, log and other object if only required.
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * addon class
 *  
 * @author David A. <tokernel@gmail.com>
 */
abstract class addon {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;
 
/**
 * Main Application object for 
 * accessing app functions from this class
 * 
 * @var object
 * @access protected
 */ 
 protected $app;
   
/**
 * Addon id
 * 
 * @access protected
 * @var string
 */
 protected $id;
 
/**
 * Addon configuration object
 * 
 * @access protected
 * @var object
 */ 
 protected $config;
 
/**
 * Addon allowed actions and params
 * 
 * @access protected
 * @var object 
 */ 
 protected $actions;
		 
/**
 * Addon log instance
 * 
 * @var object
 * @access protected
 */ 
 protected $log; 
 
/**
 * Addon language
 * 
 * @access protected
 * @var object
 */ 
 protected $language;
 
/**
 * Addon status
 * Loaded from framework or application addons directory
 * 
 * @access protected
 * @var bool
 */ 
 protected $loaded_from_app_path;
  
/**
 * Array of loaded modules (objects).
 * 
 * @access protected
 * @staticvar array
 */	
 protected static $loaded_modules = array();
  
/**
 * Class construcor
 * 
 * @access public
 * @param array
 * @param oject
 * @return void
 */
 public function __construct($params = array(), $config) {
 	
 	$this->lib = lib::instance();
	$this->app = app::instance();
	
 	$this->params = $params;
	$this->config = $config;
		
	/* Define addon id */
 	$tmp_id = get_class($this);
 	
 	if(substr($tmp_id, -10) == '_ext_addon') {
 		$this->id = substr($tmp_id, 0, -10);
 	} else {
 		$this->id = substr($tmp_id, 0, -6);
 	}
 	
 	/* Define loaded path */
 	$app_addon_lib = TK_CUSTOM_PATH . 'addons' . TK_DS . 
	                                  $this->id . TK_DS . 'lib' . TK_DS .  
	                                  $this->id . '.addon.php';
	if(is_file($app_addon_lib)) {
		$this->loaded_from_app_path = true;
	} else {
		$this->loaded_from_app_path = false;
	}
 	
 	$this->log = $this->lib->log->instance('addon_' . $this->id . '.log');
 	
	$this->language = $this->lib->language->instance(
						$this->app->language(), 
						array(
     						TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id . 
     						TK_DS . 'languages' . TK_DS,
     						
     						TK_PATH . 'addons' . TK_DS . $this->id . 
     						TK_DS . 'languages' . TK_DS, 
     					),
     					'Addon: '. $this->id,
						true);
	
	/* Addon actions/params configuration file */
	$addon_custom_actions_file = TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id . 
						  		TK_DS . 'config' . TK_DS . 'actions.ini';  
	
	$addon_tk_actions_file = TK_PATH . 'addons' . TK_DS . $this->id . 
						  	TK_DS . 'config' . TK_DS . 'actions.ini';
	
	// Load if exists
	if(is_file($addon_custom_actions_file)) {
		$this->actions = $this->lib->ini->instance($addon_custom_actions_file, NULL, false);
	} elseif(is_file($addon_tk_actions_file)) {
		$this->actions = $this->lib->ini->instance($addon_tk_actions_file, NULL, false);
	} else {
		$this->actions = NULL;
	}
	
 } // end constructor

/**
 * Class destructor
 * 
 * @access public
 * @return void
 */
 public function __destruct() {
 	unset($this->config);
 	unset($this->log);
 	unset($this->language);
 } // end destructor
 
/**
 * Return module if exists
 * 
 * @final
 * @access public
 * @param string $module
 * @return object | bool
 * @since 3.1.0
 */
 final public function __get($module) {
 	return $this->load_module($module);
 } // End func __get 
 
/**
 * Load and return this addon's module object.
 * Include module class from application dir if exists, else include from 
 * framework dir. Return false, if module file not exists in both dirs. 
 * 
 * If argument $clone is true, then this will clone and return new object
 * of module. Else, the module object will returned from loaded modules.
 * 
 * @final
 * @access public
 * @param string $id_module
 * @param mixed $params
 * @param bool $clone
 * @return object
 * @since 2.0.0
 */
 final public function load_module($id_module, $params = array(), $clone = false) {

	if(trim($id_module) == '') {
		trigger_error('Called load_module with empty id_module!', E_USER_ERROR);
		return false;
    }
 
	/* Check, is module exists */
 	if(!$this->module_exists($id_module)) {
		trigger_error('Module file for `' . $id_module . 
					 '` not exists for addon `'.$this->id.'`.', E_USER_ERROR);
    	return false;
	}
	
 	/* Return module object, if it is already laoaded */
	if(array_key_exists($id_module, self::$loaded_modules) and $clone == false) {
		return self::$loaded_modules[$id_module];
	}
	
	/* Set module filename for application custom dir. */
	$app_mod_file = TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id . 
	                TK_DS . 'modules' . TK_DS . $id_module.'.module.php';

	/* Set module filename for framework dir. */
	$tk_mod_file = TK_PATH . 'addons' . TK_DS . $this->id . TK_DS . 
	               'modules' . TK_DS . $id_module.'.module.php'; 
	
	/* Get class real name, remove path */
	$id_module = basename($id_module);
	
	$loaded_from_custom = NULL;
	
	/* Parrent module class included in tokernel.inc.php */
	
	// case 1. module exists only in tokernel
	if(is_file($tk_mod_file) and !is_file($app_mod_file)) {
		require_once($tk_mod_file);
		$loaded_from_custom = false;
		$module_class = $id_module . '_module';
	}
	
	// case 2. module exists only in application custom dir
	if(!is_file($tk_mod_file) and is_file($app_mod_file)) {
		require_once($app_mod_file);
		$loaded_from_custom = true;
		$module_class = $id_module . '_module';
	}
	   
	// case 3. both exists. inheriting.
	if(is_file($tk_mod_file) and is_file($app_mod_file)) {
		require_once($tk_mod_file);
		require_once($app_mod_file);
		$loaded_from_custom = true;
		$module_class = $id_module . '_ext_module';
	}
	
    /* Return false, if module class not exists */
    if(!class_exists($module_class)) {
    	trigger_error('Module class `' . $module_class . 
    					'` not exists for addon `'.$this->id.'`.', E_USER_ERROR);
    	return false;
    }
    
    /* Load new addon object into loaded addons array */
    $module = new $module_class($params, $this->id, 
										$this->config, 
    									$this->log,	$this->language, $id_module);
    									
    /* Module loaded as singlton and will be appended to loaded modules array */
    if($clone == false) {
		self::$loaded_modules[$id_module] = $module; 
    } 
    
    if(is_array($params)) {
    	$params_ = implode(',', $params);
    } else {
    	$params_ = (string)$params;
    }
    
    if(!$loaded_from_custom) {
    
    	tk_e::log_debug('"'.$id_module.'" from framework path with params - "' . 
    					$params_ .'"', get_class($this) . '->' . __FUNCTION__);
    } else {
    	tk_e::log_debug('"'.$id_module.'" from application path with params - "' . 
    					$params_ .'"', get_class($this) . '->' . __FUNCTION__);
    }				
    
	/* return module object */
	return $module;
      
} // end func load_module

/**
 * Load main template file for addon and return buffered data.
 * Include template file from application dir if exists, else include 
 * from framework dir. Return empty string (as buffer), if template 
 * file not exists in both directories. 
 * 
 * @final
 * @access public
 * @param string $template
 * @return string
 */
 final public function load_template($template = NULL) {
	
 	/* 
 	 * Set template as app->action() 
 	 * if it is null. 
 	 */
 	if(is_null($template)) {
 		$template = $this->app->action();
 	}
 	
 	$template_file = $this->lib->template->file_path($template);
 	
 	if($template_file == false) {
 		tk_e::log_debug('There are no template file "'.$template.'" to load.' 
    					, get_class($this) . '->' . __FUNCTION__);			
 		return '';
 	}
 	
 	/* Get template buffer */
 	ob_start();
 	
 	$addon_template_buffer = '';
 	
 	tk_e::log_debug('Start "'.$template.'"', 
 					get_class($this) . '->' . __FUNCTION__);
    									
 	require($template_file);
	$addon_template_buffer .= ob_get_contents();
	ob_end_clean();

	tk_e::log_debug('End with size: - ' . strlen($addon_template_buffer) . 
					' bytes.', get_class($this) . '->' . __FUNCTION__);				
					
	return $addon_template_buffer;
 } // end func load_template
 
/**
 * Load view file for addon and return 'view' object.
 * Include view file from application dir if exists, 
 * else include from framework dir. Return false, if 
 * view file not exists in both directories. 
 *
 * @final
 * @access public
 * @param string $file
 * @param array $parameters = array()
 * @return mixed string | false
 * @since 2.0.0
 */
 final public function load_view($file, $params = array()) {

 	/* Parrent view class included in tokernel.inc.php */
 	$app_view_file = TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id . TK_DS .
					 'views' . TK_DS . $file . '.view.php';
	
	$tk_view_file = TK_PATH . 'addons' . TK_DS . $this->id . TK_DS .
					'views' . TK_DS . $file . '.view.php';

	/* 
	 * Define existing file for include.
	 * return false if view file not exists.
	 */                
	if(is_file($app_view_file)) {
		$file_to_load = $app_view_file;
		$loaded_from_custom = true;
	} elseif(is_file($tk_view_file)) {
		$file_to_load = $tk_view_file;
		$loaded_from_custom = false;
	} else {
	
		$loaded_from_custom = false;
		
		tk_e::log_debug('There are no view file "'.$file.'" to load.', 
						get_class($this) . '->' . __FUNCTION__);
    									
		trigger_error('There are no view file `' . $file . '.view.php` ' . 
					  'for addon `'.$this->id.'`.', E_USER_ERROR);
		
		return false;
	}

 	if(is_array($params)) {
    	$params_ = implode(',', $params);
    } else {
    	$params_ = (string)$params;
    }
    
	if(!$loaded_from_custom) {
    
    	tk_e::log_debug('"'.$file.'" from framework path with params - "' . 
    					$params_ .'"', get_class($this) . '->' . __FUNCTION__);
    } else {
    	tk_e::log_debug('"'.$file.'" from application path with params - "' . 
    					$params_ .'"', get_class($this) . '->' . __FUNCTION__);
    }

	/* Return view object */
	return new view($file_to_load, $this->id, $this->config, 
					$this->log,	$this->language, $params);
	
 } // end func load_view

/**
 * Return true if module of this addon exists.
 * 
 * @access public
 * @param string $id_module
 * @param bool $custom_only
 * @return bool
 * @since 2.0.0
 */ 
 public function module_exists($id_module, $custom_only = false) {
	
 	if(trim($id_module) == '') {
 		return false;
 	}
 	
 	/* Set module filename for application custom dir. */
	$app_mod_file = TK_CUSTOM_PATH . 'addons' . TK_DS . $this->id . 
	                TK_DS . 'modules' . TK_DS . $id_module.'.module.php';

	/* Set module filename for framework dir. */
	$tk_mod_file = TK_PATH . 'addons' . TK_DS . $this->id . TK_DS . 
	               'modules' . TK_DS . $id_module.'.module.php'; 
	
	/* check for custom only */
	if(!is_file($app_mod_file) and $custom_only == true) {
		return false;
	}
	
	/* check, is module file exist in application or framework dir. */
	if(!is_file($tk_mod_file) and !is_file($app_mod_file)) {
		return false;
	}
 
	return true;
 
 } // end func module exists
 
/**
 * Return addon configuration values.
 * Return config array if item is null nad 
 * section defined, else, return value by item
 * 
 * @final
 * @access public
 * @param string $item
 * @param string $section
 * @return mixed
 */   
 final public function config($item = NULL, $section = NULL) {
	
 	if(isset($item)) {
 		return $this->config->item_get($item, $section);
 	}
 	
 	if(!isset($item) and isset($section)) {
 		return $this->config->section_get($section); 
 	}
 	
 	return false;

 } // end func config
   
/**
 * Return this addon id
 * 
 * @final
 * @access public
 * @return string
 */
 final public function id() {
	return $this->id;
 }

/**
 * Get language value by expression
 * Return language prefix if item is null.
 *  
 * @access public
 * @param string $item
 * @return string
 */ 
 public function language($item = NULL) {
 	
 	if(is_null($item)) {
 		return $this->lib->url->language_prefix();
 	}
 	
 	if(func_num_args() > 1) {
 		$l_args = func_get_args();
 	
 		unset($l_args[0]);
 		
 		if(is_array($l_args[1])) {
 			$l_args = $l_args[1];
 		}
 		
 		return $this->language->get($item, $l_args);
 	}
 	
 	return $this->language->get($item);
 	
 } // end func language

/**
 * Return addon's url by loaded stage
 * Detect if addon loaded from application directory or from framework.
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function url() {
 	if($this->loaded_from_app_path == true) {
 		return $this->app_url();
 	} else {
 		return $this->tk_url();
 	}
 }
 
/**
 * Return addon's url from application path
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function app_url() {
 	if(TK_CUSTOM_DIR != '') {
 		return $this->lib->url->base_url() . 
 						TK_CUSTOM_DIR . '/addons/' . $this->id . '/';
 	} else {
 		return $this->lib->url->base_url() . 'addons/' . $this->id . '/';
 	}
 } 

/**
 * Return addon's url from framework path
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function tk_url() {
 	return $this->lib->url->base_url() . TK_DIR . '/addons/' . $this->id . '/';
 }

/**
 * Return addon's path by loaded stage
 * Detect if addon loaded from application directory or from framework.
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function path() {
 	if($this->loaded_from_app_path == true) {
 		return $this->app_path();
 	} else {
 		return $this->tk_path();
 	}
 }
 
/**
 * Return addon's path from application directory
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function app_path() {
 	if(TK_CUSTOM_DIR != '') {
 		return TK_APP_PATH . TK_CUSTOM_DIR . TK_DS . 
 							'addons' . TK_DS . $this->id . TK_DS;
 	} else {
 		return TK_APP_PATH . 'addons' . TK_DS . $this->id . TK_DS;
 	}
 } 

/**
 * Return addon's path from framework directory
 * 
 * @access public
 * @return string
 * @since 3.2.0
 */ 
 public function tk_path() {
 	return TK_PATH . 'addons' . TK_DS . $this->id . TK_DS;	
 }
  
/**
 * Return true if addon loaded from application directory
 * 
 * @access public
 * @return bool
 * @since 3.2.0
 */
 public function loaded_from_app_path() { 
	return $this->loaded_from_app_path;
 }
 	 
/**
 * Return true if addon called from backend url or 
 * backend_dir is empty (not set) in configuration. 
 * Else, return false.  
 * 
 * @access public
 * @return bool
 * @since 2.2.0
 */ 
 public function is_backend() {
 	if($this->app->config('backend_dir', 
 							'HTTP')	!= $this->lib->url->backend_dir()) {
		return false;
	} else {
		return true;
	}
 } 

/**
 * Return true if addon's action called from backend url
 * or backend_dir is empty (not set) in configuration. 
 * Else, redirect to error_404  
 * 
 * @access public
 * @return bool
 * @since 2.2.0
 */
 public function check_backend() {
 	if($this->app->config('backend_dir', 
 								'HTTP') != $this->lib->url->backend_dir()) {
 	
 		$this->app->error_404('Cannot call method of class `' . 
 										get_class($this).'` by this url.');
 		return false;
 	}
 	
 	return true;
 }
 
/**
 * Check params count (min/max) if action in actions.ini exists
 * 
 * @access public
 * @param string $action
 * @param int $params_count
 * @return bool
 * @since 3.3.0
 */
 public function params_count_allowed($action, $params_count) {
	 
	 if(!is_object($this->actions)) {
		 return true;
	 }
	 
	 $action_section = $this->actions->section_get($action);
	 
	 if(isset($action_section['params_min'])) {
		 if($params_count < $action_section['params_min']) {
			 return false;
		 }
	 }
	 
	 if(isset($action_section['params_max'])) {
		 if($params_count > $action_section['params_max']) {
			 return false;
		 }
	 }
	 	 
	 return true;
	 
 } // End func params_count_allowed
 
/**
 * Check params if action in actions.ini exists
 * 
 * @access public
 * @param string $action
 * @param int $params_count
 * @return bool
 * @since 3.3.0
 */
 public function params_allowed($action, $params_arr) {
	 
	 if(!is_object($this->actions)) {
		 return true;
	 }
	 
	 $action_section = $this->actions->section_get($action);
	 
	 if(!isset($action_section['params'])) {
		 return true;
	 }
	
	 if($action_section['params'] == '') {
		 return true;
	 }
	 
	 $allowed_params = explode('|', $action_section['params']);
		 
	 foreach($params_arr as $param) {
		 if(!in_array($param, $allowed_params)) {
			 return false;
		 }
	 }

	 return true;
	 
 } // End func params_allowed
 
/**
 * Check action if exists in actions.ini
 * 
 * @access public
 * @param string $action
 * @return bool
 * @since 3.3.0
 */
 public function action_allowed($action) {
	 
	 if(!is_object($this->actions)) {
		 return true;
	 }
	 
	 if(!$this->actions->section_exists($action)) {
		 return false;
	 }
	 
	 return true;
	 
 } // End func action_allowed
 
/**
 * Exception for not creating function 
 * 'action_' in any addon class
 * 
 * @access protected
 * @final
 * @return void
 */
 final protected function action_() {}

/**
 * Exception for not creating function 
 * 'action_ax_' in any addon class
 * 
 * @access protected
 * @final
 * @return void
 */
 final protected function action_ax_() {}
 
/**
 * Exception for not creating function 
 * 'cli_' in any addon class
 * 
 * @access protected
 * @final
 * @return void
 */
 final protected function cli_() {}

/* End of class addon */
}
 
/* End of file */
?>