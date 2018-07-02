<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for Unit Testing
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
 * @copyright  Copyright (c) 2018 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.8.0
 * @todo       Generate backtrace
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * unit_test_lib class library
 *
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class unit_test_lib {

    /**
     * Expected types and values
     */
    const TYPE_OBJECT =   'TYPE_OBJECT';
    const TYPE_STRING =   'TYPE_STRING';
    const TYPE_BOOL =     'TYPE_BOOL';
    const TYPE_INT =      'TYPE_INT';
    const TYPE_ARRAY =    'TYPE_ARRAY';
    const TYPE_RESOURCE = 'TYPE_RESOURCE';

    const NUMERIC = 'NUMERIC';
    const NUMERIC_FLOAT = 'NUMERIC_FLOAT';
    const NUMERIC_DOUBLE = 'NUMERIC_DOUBLE';

    /**
     * All results of tests
     *
     * @access private
     * @var array
     */
    private $results = array();

    /**
     * Run Test by value and expected value
     *
     * @access public
     * @param mixed $value
     * @param mixed $expected_value
     * @param string $test_name
     * @return mixed
     */
    public function run_by_value($value, $expected_value, $test_name) {

        if($value === $expected_value) {
            $result = true;
            $label = 'OK';
        } else {
            $result = false;
            $label = 'NOK';
        }

        $this->results[$test_name] = array(
            'name' => $test_name,
            'given' => array(
                'value' => $this->actual_value($value),
                'type' => gettype($value)
            ),
            'expected' => array(
                'value' => $this->actual_value($expected_value),
                'type' => gettype($expected_value)
            ),
            'result' => $label,
            'test_by' => 'value'
        );

        return $result;

    } // End func run_by_value

    /**
     * Run Test by value and expected type of value
     *
     * @access public
     * @param mixed $value
     * @param string $expected_type
     * @param string $test_name
     * @return bool
     */
    public function run_by_type($value, $expected_type, $test_name) {

        $result = false;

        switch ($expected_type) {

            case unit_test_lib::TYPE_OBJECT:
                $result = is_object($value);
                break;

            case unit_test_lib::TYPE_STRING:
                $result = is_string($value);
                break;

            case unit_test_lib::TYPE_BOOL:
                $result = is_bool($value);
                break;

            case unit_test_lib::TYPE_INT:
                $result = is_int($value);
                break;

            case unit_test_lib::TYPE_ARRAY:
                $result = is_array($value);
                break;

            case unit_test_lib::TYPE_RESOURCE:
                $result = is_resource($value);
                break;

            case unit_test_lib::NUMERIC:
                $result = is_numeric($value);
                break;

            case unit_test_lib::NUMERIC:
                $result = is_numeric($value);
                break;

            case unit_test_lib::NUMERIC_FLOAT:
                $result = is_float($value);
                break;

            case unit_test_lib::NUMERIC_DOUBLE:
                $result = is_double($value);
                break;

        }

        if($result === true) {
            $label = 'OK';
        } else {
            $label = 'NOK';
        }

        $this->results[$test_name] = array(
            'name' => $test_name,
            'given' => array(
                'value' => $this->actual_value($value),
                'type' => gettype($value)
            ),
            'expected' => array(
                'type' => $expected_type
            ),
            'result' => $label,
            'test_by' => 'type'
        );

        return $result;

    } // End func run_by_type

    /**
     * Get all results
     *
     * @access public
     * @return array
     */
    public function get_results() {
        return $this->results;
    }

    /**
     * Reset Results
     *
     * @access public
     * @return void
     */
    public function reset() {
        $this->results = array();
    }

    /**
     * Return value if scalar.
     * Else return type
     *
     * @access private
     * @param mixed $value
     * @return string
     */
    private function actual_value($value) {

        if(is_numeric($value)) {
            return $value;
        }

        if(is_string($value)) {
            $return = str_replace(array("\n", "\r", "\t"), "", $value);
            $return = htmlspecialchars($return);
            $return = substr($return, 0, 50);
            if(strlen($value) > 50) {
                $return .= '...';
            }
            return $return;
        }

        if(is_bool($value)) {
            if($value === true) {
                return 'true';
            } else {
                return 'false';
            }
        }

        if(is_object($value)) {
            return 'Object: ' . get_class($value);
        }

        if(is_array($value)) {
            return 'Array: ['.count($value).']';
        }

        return gettype($value);

    }

} // End class unit_test_lib

// End of file