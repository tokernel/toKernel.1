<?php
/**
 * toKernel - Universal PHP Framework.
 * Class library for working with templates.
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
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2013 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.6
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 * @todo       Change String expressions like - !_TK_NL_@ to others.
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * template_lib class
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class template_lib {

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
 * @access private
 */ 
 private $app;

/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
	$this->lib = lib::instance();
    $this->app = app::instance();
 } // end of func __construct
 
/**
 * Parse widget defination tag and return array.
 * Example of widget definition tag:
 * <!-- widget addon="some_addon" action="some_action" 
 *      params="param1=param1_value|param2=param2_value" -->
 *
 * @access public
 * @param string $str (widget defination tag)
 * @return mixed array | false
 */
 public function parse_widget_tag($str) {
	
 	$w_arr = array();
	
	/* get addon ID */
	$pos = strpos($str, 'addon="');
	if($pos === false) {
	   return false;	
	}
	
	$tmp = substr($str, ($pos + strlen('addon="')), strlen($str));
	$tmp = substr($tmp, 0, strpos($tmp, '"'));
	
	if(trim($tmp) == '') { 
	   return false;	
	} else {
	   $w_arr['id_addon'] = $tmp;
	}
	
	/* get addon action */
	$pos = strpos($str, 'action="');
    if($pos === false) {
	   $w_arr['action'] = '';
	} else {
	   $tmp = substr($str, ($pos + strlen('action="')), strlen($str));
	   $tmp = substr($tmp, 0, strpos($tmp, '"'));
	
	   if(trim($tmp) == '') { 
	      $w_arr['action'] = '';
	   } else {
	      $w_arr['action'] = $tmp; 
	   }
	} // end if pos.
	
	/* get addon params */
	$pos = strpos($str, 'params="');
    if($pos === false) {
	   $w_arr['params'] = array();
	} else {
	   $tmp = substr($str, ($pos + strlen('params="')), strlen($str));
	   $tmp = substr($tmp, 0, strpos($tmp, '"'));
	
	   if(trim($tmp) == '') { 
	      $w_arr['params'] = array();
	   } else {
	      $tmp = explode('|', $tmp);

	      foreach($tmp as $param) {
	      	 $ptmp = explode('=', $param);
	       	 if(trim($ptmp[0]) != '') {
	       	    if(isset($ptmp[1])) {
	       	 		$w_arr['params'][$ptmp[0]] = $ptmp[1];
	       	    } else {
	       	    	$w_arr['params'][$ptmp[0]] = NULL;
	       	    }	
	         }
		  } // end foreach 	
	      
	   }
	} // end if pos.
	
	return $w_arr;
 } // end func parse_widget_tag
 
/**
 * Parse defined widget tags in template content.
 * 
 * @access public
 * @param string $buffer content
 * @return mixed array | bool
 */
 public function parse_widget_tags($buffer) {
 	
 	$buffer = str_replace("\n", "!_TK_NL_@", $buffer);
	$buffer = str_replace("\r", "", $buffer);
	$buffer = str_replace('<!--', '<TK_SW', $buffer);
	
	$tmp_arr = explode('<TK_SW', $buffer);

	$widgets_arr = array();
	
	$i = 0;
	
	foreach($tmp_arr as $part) {
	
		/* 
		 * If the line is addon widget definition, get it.
		 * Example of widget definition tag in template file:
		 * <!-- widget addon="some_addon" action="some_action" 
		 *      params="param1=param1_value|param2=param2_value" -->
		 *      
		 * NOTE: It is possible to call addon widget in template file, 
		 * without widget tag definition. For example:
		 * <?php $this->lib->addons->my_addon->my_action(array('a' => 'b')); ?>
		 * <?php $this->my_action(array('a' => 'b')); ?>
		 * instead of widget tag. 
		 */
		$part = trim($part);
		
		if(substr($part, 0, 6) == 'widget') {
			$part = str_replace('widget', '', $part);
		    
			$pos = strpos($part, '-->');
			if($pos) {
				$part = substr($part, 0, $pos);
			}
			$part = trim($part);
		    
		    $tmp = $this->parse_widget_tag($part);

		    if(is_array($tmp)) { 
		    	
		    	$widgets_arr[] = $tmp;
		    	$i++;
		    }
		    
		    unset($tmp);
		    
	    } // end if widget
		
	} // end foreach
 	
 	return $widgets_arr;
 } // end func  

/**
 * Return template file with path
 * if file exist in application custom directory then return file.
 * else if file exist in framework directory then return file.
 * else return false.
 * 
 * @param string $id_addon
 * @param string $template
 */ 
 public function file_path($template = NULL) {
	
 	if(is_null($template)) {
		$template = $this->lib->url->action();
	}
	
	$template_file = $template . '.tpl.php';
	
	/* Create template path by application mode */
	if($this->app->get_mode() != '') {
		$templates_dir = 'templates' . TK_DS . $this->app->get_mode() . TK_DS; 
	} else {
		$templates_dir = 'templates' . TK_DS;
	}
	
	/* Set template filename from application custom dir. */ 
 	$app_template_file = TK_CUSTOM_PATH . $templates_dir . $template_file;

	/* Set template filename from framework dir. */ 
 	$tk_template_file = TK_PATH . $templates_dir . $template_file;

	/* 
	 * Define existing file for include.
	 * return empty string (as buffer),
	 * if template file not exists.  
	 */
 	if(is_file($app_template_file)) {
		$template_file = $app_template_file;
 	} elseif(is_file($tk_template_file)) {
 		$template_file = $tk_template_file;
 	} else {
 		$template_file = false;
 	}
 	
 	return $template_file;

} // end func file_path
 
/**
 * Interpreting template and return buffer
 * 
 * @access public 
 * @param string $buffer (template buffer)
 * @param string $replace_this replace "__THIS__" addon with this string
 * @return void
 */
 public function interpret($buffer, $replace_this = NULL) {

 	tk_e::log_debug('Start', get_class($this) . '->' . __FUNCTION__);
 	
 	$runned_widgets_count = 0;
 	
 	/* Replace/Remove some symbols in template buffer */ 
	$buffer = str_replace("\n", "!_TK_NL_@", $buffer);
	$buffer = str_replace("\r", "", $buffer);
	
	$buffer = str_replace('<!--', '<TK_CMT', $buffer);
	$tmp_arr = explode('<TK_CMT', $buffer);
	
	$template_buffer = '';
	
	foreach($tmp_arr as $part) {
	
		/* 
		 * If the line is addon widget definition, interpret it.
		 * Example of widget definition tag in template file:
		 * <!-- widget addon="some_addon" action="some_action" 
		 *      params="param1=param1_value|param2=param2_value" -->
		 *      
		 * NOTE: It is possible to call addon widget in template file, 
		 * without widget tag definition. For example:
		 * <?php $this->lib->addons->my_addon->my_action(array('a' => 'b')); ?>
		 * <?php $this->my_action(array('a' => 'b')); ?>
		 */
		
	$part = trim($part);

	if(strtolower(substr($part, 0, 6)) == 'widget') {
		$pos = strpos($part, '-->');
		$widget_part = substr($part, 0, $pos);
		
		$tmp_addon_data_arr = $this->parse_widget_tag($widget_part);
		
		if(trim($tmp_addon_data_arr['id_addon']) != '') {
		
			if($tmp_addon_data_arr['id_addon'] == '__THIS__') {
		     	
				tk_e::log_debug('Appending (main) addon result of __THIS__ ' . 
		    					$this->lib->url->id_addon() . '->' .
		    					$this->lib->url->action(). '('.implode(', ',
		    					$this->lib->url->params()).') ' .
		    					$tmp_addon_data_arr['action'] . '.',
		    					get_class($this) . '->' . __FUNCTION__);	
								
		    	$widget_buffer = '';
		    	
		    	if(is_null($replace_this)) {
		     		tk_e::log_debug('Empty __THIS__ widget buffer.', 
		     						get_class($this) . '->' . __FUNCTION__);
		     	}
		     	
		     	$widget_buffer .= $replace_this;
		     	
		     	$template_buffer .= $widget_buffer; 
		        unset($replace_this);
		        unset($widget_buffer);
			} else {
		
				tk_e::log_debug('Run widget - "' . 
								$tmp_addon_data_arr['id_addon'] . '->' . 
								$tmp_addon_data_arr['action'] . '" with params "' . 
								implode(', ', $tmp_addon_data_arr['params']) . 
								'".', get_class($this) . '->' . __FUNCTION__);
			
				$template_buffer .= $this->get_widget_runned_buffer(
														$tmp_addon_data_arr);
				$runned_widgets_count++;										
			}
		}
			
		unset($tmp_addon_data_arr);
		
		$template_buffer .= substr($part, $pos+3, strlen($part));
	} else {
		if($template_buffer != '') {
			$template_buffer .= '<!--' . $part;
		} else {
			$template_buffer .= $part;
		}	
	}
		
	} // end foreach
	
	$template_buffer = str_replace("!_TK_NL_@", "\n", $template_buffer);
	$template_buffer = str_replace("\n\n", "\n", $template_buffer);
 	
	tk_e::log_debug('End with running - ' . $runned_widgets_count . ' widgets.', 
					get_class($this) . '->' . __FUNCTION__);
	
	return $template_buffer;
	
} // end func interpret
 
/**
 * Run addon->method by widget definition and return buffer of result.
 * 
 * @access public
 * @param array $tmp_addon_data_arr
 * @return string
 */
 public function get_widget_runned_buffer($tmp_addon_data_arr) {

	if(!is_array($tmp_addon_data_arr) or 
		trim($tmp_addon_data_arr['id_addon']) == '') {
			
		tk_e::log_debug('Invalid addon widget to call `' . 
						$tmp_addon_data_arr['id_addon'] . '->' . 
						$tmp_addon_data_arr['action'].'()` ' . __CLASS__ . 
						'->' . __FUNCTION__.'() !', __CLASS__);
							
		trigger_error('Invalid addon widget to call `' . 
						$tmp_addon_data_arr['id_addon'] . '->' . 
						$tmp_addon_data_arr['action'].'()` ' . __CLASS__ . 
						'->' . __FUNCTION__.'() !', E_USER_WARNING);
		return ''; 			  	  
	}	
	
	$widget_buffer = '';
		       
	/* Call addon action */
	$addon = $this->lib->addons->$tmp_addon_data_arr['id_addon'];
 
	if(!is_object($addon)) {
	    tk_e::log_debug('Addon `'.$tmp_addon_data_arr['id_addon'].'` ' . 
	    				'is not an object to call in ' . __CLASS__ . 
	    				'->' . __FUNCTION__.'() !', __CLASS__);
		    				
	    trigger_error('Addon `'.$tmp_addon_data_arr['id_addon'].'` ' . 
	    			  'is not an object to call in ' . __CLASS__ . 
	    			  '->' . __FUNCTION__.'() !', E_USER_WARNING);
	/*
	 * 
	 * Commented this part, because of possible _call cases.
	 * 
	} elseif(!method_exists($addon, $tmp_addon_data_arr['action'])) {

		tk_e::log_debug('Method `' . $tmp_addon_data_arr['id_addon'] . 
						'->' . $tmp_addon_data_arr['action'].'()` ' . 
						'not exists to call in '.__CLASS__ . '->' . 
						__FUNCTION__.'() !', __CLASS__);
							
		trigger_error('Method `' . $tmp_addon_data_arr['id_addon'] . 
					  '->' . $tmp_addon_data_arr['action'].'()` ' . 
					  'not exists to call in '.__CLASS__ . '->' . 
				  	  __FUNCTION__.'() !', E_USER_WARNING);
	*/						
	} else {
			
		ob_start();
		$addon->$tmp_addon_data_arr['action']($tmp_addon_data_arr['params']);
		$widget_buffer .= ob_get_contents();
		ob_end_clean();
	}
	       
	return $widget_buffer;
	
} // end func get_widget_runned_buffer

/* End of class template_lib */
}

/* End of file */
?>