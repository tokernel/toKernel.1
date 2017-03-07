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
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.4.0
 *
 * This Library uses application languages.
 *
 * Possible rules:
 *
 * 'required' => true, // if set value as false, the validation will passed as true.
 * 'minlength' => 5, // String length should be minimum of 5 characters
 * 'maxlength' => 10, // String length should be maximum of 10 characters
 * 'rangelength' => array(5, 10), // String length should be in range of 5-10 characters
 * 'min' => 5, // Number value should be minimum 5
 * 'max' => 10, // Number value should be maximum 10
 * 'range'	=> array(5, 10), // Number value should be in range of 5-10
 * 'equal_to' => 'password', // The value should be equal to value of element named "password" (this is useful for password confirmation)
 * 'different_from' => 'phone1', // The value should be different from value of element named "phone1" (this is useful to except inserting same values in different elements)
 * 'in' => array('home', 'garage', 'office'), // Is equal to one of array elements
 * 'date' => 'Y-m-d', // Date in given format
 * 'date_iso' => true, // Date in ISO standard Y-m-d (i.e. 2014-02-07)
 * 'date_before' => date('Y-m-d'), // Date is earlier than the given date
 * 'date_after' => date('Y-m-d'), // Date is later than the given date
 * 'number' => true, // Any type of number (i.e. 55 | 55.55)
 * 'digits' => true, // Integer number only (i.e. 55)
 * 'alpha' => true, // String with letters only: a-zA-Z (i.e. abc | ABC | abcXYZ )
 * 'alphanumeric' => true, // String and integer numbers only (i.e. ABC435test546)
 * 'email' => true, // Should be valid Email address
 * 'creditcard' => true, // Should be valid Credit card number (i.e. 378734493671000 | 6011000990139424)
 * 'url' => true, // Should be valid URL
 * 'regex' => /^[A-Za-z_]{4,8}$/, // Matches to regular expression pattern
 *
 * This rule you can use in javascript validation (i.e. jQuery validation plugin).
 *
 * 'remote' => array(
 *     'url' => 'http://example.com/user/check_email_exists/',
 *     'message_manual' => 'This is manual message',
 *     'message_email_already_registered' => 'email',
 * ),
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Form validation class library.
 *
 * @author David A. <tokernel@gmail.com>
 * @author Karapet S. <join04@yahoo.com>
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
        'minlength',
        'maxlength',
        'rangelength',
        'min',
        'max',
        'range',
        'number',
        'digits',
        'alpha',
        'alphanumeric',
        'url',
        'creditcard',
        'date_iso',
        'date',
        'date_before',
        'date_after',
        'equal_to',
        'different_from',
        'in',
        'regex',
        'remote' // This rule is possible to use for javascript validation
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
     * Custom messages for rules
     *
     * @access protected
     * @var array
     * @since Version 2.0.0
     */
    protected $rule_custom_messages = array();

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
     * Class destructor
     *
     * @access public
     * @return void
     */
    public function __destruct() {
        $this->rules = array();
        $this->messages = array();
        $this->rule_custom_messages = array();
        $this->validation_result = true;
    }

    /**
     * Return a new clean instance of this object
     *
     * @access public
     * @param mixed array | NULL $rules
     * @param mixed array | NULL $custom_messages
     * @return object
     * @since Version 2.0.0
     */
    public function instance($rules, $custom_messages = array()) {

        $obj = clone $this;
        $obj->__destruct();

        if(!is_null($rules)) {
            $obj->add_rules($rules, $custom_messages);
        }

        return $obj;

    } // End func instance

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
     * @param mixed array | NULL $custom_messages
     * @return void
     */
    public function add_rules($rules, $custom_messages = array()) {
        $this->rules = $rules;
        $this->rule_custom_messages = $custom_messages;
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
     * @param mixed string | NULL $tag
     * @return boolean
     */
    public function print_message($element, $tag = NULL) {

        $messages = $this->get_messages($element);

        if(!$messages) {
            return false;
        }

        if(is_null($tag)) {
            $tag = '<label for="'.$element.'" class="error">{var.messages}</label>';
        }

        $messages_str = '';

        foreach($messages as $message) {
            $messages_str .= $message . '<br />';
        }

        $messages_str = rtrim($messages_str, '<br />');

        $messages_str_result = str_replace($tag, '{var.messages}', $messages_str);

        echo $messages_str_result;

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

        $value = $this->lib->filter->post($element);

        // Pass empty value if not marked as required
        if($rule != 'required' and ($value === '' || $value === false)) {
            return true;
        }

        // required
        if($rule == 'required') {

            // Check rule only if value is true
            if($rule_values == false) {
                return true;
            }

            if($this->lib->valid->required($value) == true) {
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

        // equal_to
        if($rule == 'equal_to') {
            if($value == $this->lib->filter->post($rule_values)) {
                return true;
            }
        }

        // different_from
        if($rule == 'different_from') {
            if($value != $this->lib->filter->post($rule_values)) {
                return true;
            }
        }

        // in
        if($rule == 'in') {
            if(in_array($value, $rule_values)) {
                return true;
            } else {

                /* Define message for "in" rule.
                 * Example:
                 *
                 * Rule defined as: 'in' => array('Home', 'Garage', 'Office')
                 * Will display message as: Possible values to enter is: Home, Garage, Office
                 */
                $rule_values = implode(', ', $rule_values);

            }
        }

        // date
        if($rule == 'date') {
            if($this->lib->valid->date($value, $rule_values) == true) {
                return true;
            }
        }

        // date_iso
        if($rule == 'date_iso') {
            if($this->lib->valid->date_iso($value) == true) {
                return true;
            }
        }

        // date_before
        if($rule == 'date_before') {
            if($this->lib->date->is_passed($value, $rule_values) == false) {
                return true;
            }
        }

        // date_after
        if($rule == 'date_after') {
            if($this->lib->date->is_passed($value, $rule_values) == true) {
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

        // alphanumeric
        if($rule == 'alphanumeric') {
            if($this->lib->valid->alpha_numeric($value)) {
                return true;
            }
        }

        // email
        if($rule == 'email') {
            if($this->lib->valid->email($value) and $value != '') {
                return true;
            }
        }

        // creditcard
        if($rule == 'creditcard') {
            if($this->lib->valid->credit_card($value) == true) {
                return true;
            }
        }

        // url
        if($rule == 'url') {
            if($this->lib->valid->url($value) == true) {
                return true;
            }
        }

        // regex
        if($rule == 'regex') {
            if(preg_match($rule_values, $value) == 1) {
                return true;
            }
        }

        // remote
        if($rule == 'remote') {
            // We're not validating this rule here.
            // It is defined to use in javascript validation
            return true;
        }

        if(isset($this->rule_custom_messages[$element][$rule])) {
            // Message set by custom messages array.
            // NOTICE: The message should be defined as is, without translation.
            $this->messages[$element][] = $this->rule_custom_messages[$element][$rule];
        } else {
            // Get message from default.
            $this->messages[$element][] = $this->message_localized($rule, $rule_values);
        }

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
