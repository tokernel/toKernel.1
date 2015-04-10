<?php
/**
 * toKernel - Universal PHP Framework.
 * Form validation class library.
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
 * @copyright  Copyright (c) 2015 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.4.0
 * 
 * This Library uses applicatoin languages.
 * 
 * Possible rules:
 * 
 * 'required' => true,
 * 'email' => true,
 * 'remote' => array(
 * 				'url' => 'http://example.com/user/check_email_exists/',
 * 				'message_manual' => 'This is manual message',
 * 				'message_email_already_registered' => 'email',
 *			),
 * 'minlength' => 5, // String lenght
 * 'maxlength' => 10, // String lenght
 * 'rangelength' => array(5, 10), // String lenght
 * 'min' => 5, // Number
 * 'max' => 10, // Number
 * 'range'	=> array(9, 99), // Number range
 * 'url' => true,
 * 'date_iso' => true, // 2014-02-07
 * 'number' => true, // 55, 55.55
 * 'digits' => true, // 55
 * 'creditcard' => true, // 378734493671000 | 6011000990139424
 * 'equal_to' => 'password',
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * form_validation_lib class
 * 
 * Form validation class library.
 *  
 * @author David A. <tokernel@gmail.com>
 */ 
class form_validation_lib {

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
 * Rules reference.
 * 
 * @access protected
 * @var array
 */	
 protected $rules_ref = array(
	'required',
	'email',
	'remote',
	'minlength',
	'maxlength',
	'rangelength',
	'min',
	'max',
	'range',
	'url',
	'date_iso',
	'number',
	'digits',
    'alpha',
	'creditcard',
	'equal_to'
 );
 
/**
 * Rules array
 * 
 * @access protected
 * @var array
 */ 
 protected $rules = array();
 
/**
 * Messages array
 * 
 * @access protected
 * @var array
 */ 
 protected $messages = array(); 
  
/**
 * Final validation result
 * 
 * @access protected
 * @var boolean
 */ 
 protected $validation_result = true;
 
 
/**
 * Class constructor
 * 
 * @access public
 * @return void
 */ 
 public function __construct() {
	
 	$this->app = app::instance();
    $this->lib = lib::instance();
	
 } // end func __construct 
 
 
/**
 * Add validation rules
 * 
 * Example:
 * 
 * $this->lib->form_validation->add_rules(
 *		'email' => array (
 *			'email' => true,
 *			'required' => true
 *		),
 *		'price' => array(
 *			'range'	=> array(9, 99),
 *			'required' => true
 *		)
 * );
 * 
 * @access public
 * @param array $rules
 * @return void
 */ 
 public function add_rules($rules) {
	 $this->rules = $rules;
 } // End func add_rule
 
/**
 * Run Form validation
 * This method can be called ater HTTP request mode checking.
 * 
 * @access public
 * @return boolean
 */ 
 public function run() {
	 
	 foreach($this->rules as $element => $rules) {
		 foreach($rules as $rule => $rule_values) {
			 $this->validate_rule($element, $rule, $rule_values);
		 }
	 }
	 
	 if(!empty($this->messages)) {
		 return false;
	 }
	 
	 return $this->validation_result;
	 
 } // End func run

/**
 * Get error messages
 * 
 * @access public
 * @param mixed $element
 * @return mixed  
 */ 
 public function get_messages($element = NULL) {
	 
	 if(is_null($element)) {
		 return $this->messages;
	 }
	 
	 if(isset($this->messages[$element])) {
		 return $this->messages[$element];
	 }
	 
	 return false;
	 
 } // End func get_messages
 
/**
 * Print message with html tag for element
 * This method can be called under the html form element
 * 
 * @access public
 * @param string $element
 * @return boolean 
 */ 
 public function print_message($element) {
	 
	 $messages = $this->get_messages($element);
	 
	 if(!$messages) {
		 return false;
	 }
	 
	 $messages_str = '<label for="'.$element.'" class="error">';
		 
	 foreach($messages as $message) {
		$messages_str .= $message . '<br />';
	 }
		 
	 $messages_str = rtrim($messages_str, '<br />');
	 $messages_str .= '</label>';
	
	 echo $messages_str;
	 
	 return true;
	 	 
 } // End func print_message
 
/**
 * Set message for element
 * 
 * @access public
 * @param string $element
 * @param string $message 
 * @return void
 */ 
 public function set_message($element, $message) {
	$this->messages[$element][] = $message;
 } // End func set_message
 
/**
 * Validate element value and set message
 * 
 * @access protected
 * @param string $element
 * @param string $rule
 * @param mixed $rule_values
 * @return boolean 
 */ 
 protected function validate_rule($element, $rule, $rule_values) {
	 
	 if(!in_array($rule, $this->rules_ref)) {
		 trigger_error('Rule `'.$rule.'` not exists!', E_USER_WARNING);
	 }
	 
	 // Don't checking remote rules here.
	 if($rule == 'remote') {
		 return true;
	 }
	 
	 $value = $this->lib->filter->post($element);
	 
	 // required
	 if($rule == 'required') {
		 if($this->lib->valid->required($value) == true) {
		 	 return true;
		 }
	 }
	 
	 // email
	 if($rule == 'email') {
		 if($this->lib->valid->email($value) and $value != '') {
			 return true;
		 }
	 }
	 
	 // minlength
	 if($rule == 'minlength') {
		 if($this->lib->valid->required($value, $rule_values) == true) {
			 return true;
		 }	 
	 }
	 
	 // maxlength
	 if($rule == 'maxlength') {
		 if($this->lib->valid->required($value, -1, $rule_values) == true) {
			 return true;
		 }
	 }
	 
	 // rangelength
	 if($rule == 'rangelength') {
		 if($this->lib->valid->required($value, $rule_values[0], $rule_values[1]) == true) {
			 return true;
		 }
	 }
	 
	 // min
	 if($rule == 'min') {
		 if($value >= $rule_values and is_numeric($value)) {
			 return true;
		 }
	 }
	 
	 // max
	 if($rule == 'max') {
		 if($value <= $rule_values and is_numeric($value)) {
			 return true;
		 }
	 }
	 
	 // range
	 if($rule == 'range') {
		 if($value >= $rule_values[0] and $value <= $rule_values[1] and is_numeric($value)) {
			 return true;
		 }
	 }
	 
	 // url
	 if($rule == 'url') {
		 if($this->lib->valid->url($value) == true) {
			 return true;
		 }
	 }
	 
	 // date_iso
	 if($rule == 'date_iso') {
		 if($this->lib->valid->date_iso($value) == true) {
			 return true;
		 }
	 }
	 
	 // number
	 if($rule == 'number') {
		 if(is_numeric($value)) {
			 return true;
		 }
	 }
	 
	 // digits
	 if($rule == 'digits') {
		 if($this->lib->valid->digits($value) == true) {
			 return true;
		 }
	 }

     // alpha
     if($rule == 'alpha') {
         if($this->lib->valid->alpha($value) == true) {
             return true;
         }
     }

	 // creditcard
	 if($rule == 'creditcard') {
		 if($this->lib->valid->credit_card($value) == true) {
			 return true;
		 }
	 }
	 
	 // equal_to
	 if($rule == 'equal_to') {
		 if($value == $this->lib->filter->post($rule_values)) {
			 return true;
		 }
	 }
	 
	 $this->messages[$element][] = $this->message_localized($rule, $rule_values);
	 $this->validation_result = false;
			 
 } // End func validate_rule
 
/**
 * Return translated message
 * 
 * @access protected
 * @param string $rule
 * @param mixed $second_args
 * @return string 
 */ 
 
 protected function message_localized($rule, $second_args = NULL) {
	 return $this->app->language('_' . $rule, $second_args);
 }
 
} // End class form_validation_lib
?>
