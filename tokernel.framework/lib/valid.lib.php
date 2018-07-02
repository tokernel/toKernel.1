<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for validate some data types
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
 * @version    1.4.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Data validation class library
 *
 * @author David A. <tokernel@gmail.com>
 * @author Karapet S. <join04@yahoo.com>
 */
class valid_lib {

    /**
     * Check is valid credit card.
     * Return card type if number is true.
     *
     * @access public
     * @param mixed string | integer
     * @return mixed string | bool
     */
    public function credit_card($data) {

        if(!is_scalar($data)) {
            return false;
        }

        $card_type = '';
        $card_regexes = array(
            "/^4\d{12}(\d\d\d){0,1}$/" => 'visa',
            "/^5[12345]\d{14}$/"       => 'mastercard',
            "/^3[47]\d{13}$/"          => 'amex',
            "/^6011\d{12}$/"           => 'discover',
            "/^30[012345]\d{11}$/"     => 'diners',
            "/^3[68]\d{12}$/"          => 'diners',
        );

        foreach($card_regexes as $regex => $type) {
            if(preg_match($regex, $data)) {
                $card_type = $type;
                break;
            }
        }

        if(!$card_type) {
            return false;
        }

        /*  mod 10 checksum algorithm  */
        $revcode = strrev($data);
        $checksum = 0;

        for($i = 0; $i < strlen($revcode); $i++) {
            $current_num = intval($revcode[$i]);
            if($i & 1) {  /* Odd  position */
                $current_num *= 2;
            }
            /* Split digits and add. */
            $checksum += $current_num % 10;
            if($current_num >  9) {
                $checksum += 1;
            }
        }

        if($checksum % 10 == 0) {
            return $card_type;
        } else {
            return false;
        }

    } // end func credit_card

    /**
     * Check is username 6-128 char,
     * will start with char A-Z, a-z.
     *
     * @access public
     * @param string $data
     * @param integer $min
     * @param integer $max
     * @return bool
     */
    public function username($data, $min = 6, $max = 128) {

        if(!is_scalar($data)) {
            return false;
        }

        $min = (string)($min - 1);
        $max = (string)($max - 1);

        if(preg_match('/^[A-Za-z]{1}+[a-zA-Z0-9_.-]{'.$min.','.$max.'}$/i', $data)) {
            return true;
        } else {
            return false;
        }

    } // End func username

    /**
     * Check password strength
     *
     * Return values
     * -1 = not match
     * 1  = weak
     * 2  = not weak
     * 3  = acceptable
     * 4  = strong
     *
     * @since 1.1.0
     * @access public
     * @param string $data
     * @param integer $min
     * @param integer $max
     * @return int
     */
    public function password_strength($data, $min = 6, $max = 128) {

        if(!is_scalar($data)) {
            return false;
        }

        if(strlen($data) < $min or strlen($data) > $max) {
            return -1;
        }

        $strength = 0;
        $patterns = array('#[a-z]#','#[A-Z]#','#[0-9]#','/[¬!"£$%^&*()`{}\[\]:@~;\'#<>?,.\/\\-=_+\|]/');

        foreach($patterns as $pattern) {
            if(preg_match($pattern, $data)) {
                $strength++;
            }
        }

        return $strength;

    } // end func password_strength

    /**
     * Check is valid e-mail address
     *
     * @access public
     * @param string $data
     * @return bool
     */
    public function email($data) {

        if(!is_scalar($data)) {
            return false;
        }

        if(preg_match("/^[a-z0-9_\.-]+@([a-z0-9]+([\-]+[a-z0-9]+)*\.)+[a-z]{2,6}$/i", $data) and strlen($data) < 100) {
            return true;
        } else {
            return false;
        }

    } // End func email

    /**
     * Check is valid.
     * By default, will check from 1.
     *
     * @access public
     * @param integer $data
     * @param integer $min
     * @return bool
     */
    public function id($data, $min = 1) {
        return $this->digits($data, $min);
    } // end func id

    /**
     * Check is integer, with string type
     *
     * @access public
     * @param mixed $data
     * @param integer $min
     * @param integer $max
     * @return bool
     */
    public function digits($data, $min = -1, $max = -1) {

        if(!is_scalar($data)) {
            return false;
        }

        if(!preg_match("/^[0-9]+$/", $data)) {
            return false;
        }

        if($max > -1 and $data > $max) {
            return false;
        }

        if($min > -1 and $data < $min) {
            return false;
        }

        return true;

    } // End func digits

    /**
     * Check is correct IP Address
     *
     * @access public
     * @param mixed $data
     * @return bool
     */
    public function ip($data) {

        if(!is_scalar($data)) {
            return false;
        }

        $val_0_to_255 = "(25[012345]|2[01234]\d|[01]?\d\d?)";
        $pattern = "#^($val_0_to_255\.$val_0_to_255\.$val_0_to_255\.$val_0_to_255)$#";

        if(preg_match($pattern, $data, $matches)) {
            return true;
        } else {
            return false;
        }

    } // End func ip

    /**
     * Check is valid hostname.
     *
     * @access public
     * @param mixed $data
     * @return bool
     */
    public function http_host($data) {

        if(!is_scalar($data)) {
            return false;
        }

        if(preg_match('/^\[?(?:[a-z0-9-:\]_]+\.?)+$/', $data)) {
            return true;
        } else {
            return false;
        }

    } // end func http_host

    /**
     * Check value between a, b values.
     *
     * @access public
     * @param integer $data
     * @param integer $a
     * @param integer $b
     * @return bool
     */
    public function between($data, $a, $b) {

        if(!is_scalar($data)) {
            return false;
        }

        if($data >= $a and $data <= $b) {
            return true;
        } else {
            return false;
        }

    } // end func between

    /**
     * Check string is alpha-numeric
     * with underscores and dashes.
     *
     * @access public
     * @param string $data
     * @return bool
     */
    public function alpha_numeric($data) {

        if(!is_scalar($data)) {
            return false;
        }

        if(preg_match("/^([-a-z0-9_-])+$/i", $data)) {
            return true;
        } else {
            return false;
        }

    } // end func alpha_numeric

    /**
     * Check, is string contains only alpha
     *
     * @access public
     * @param string $data
     * @return bool
     */
    public function alpha($data) {

        if(!is_scalar($data)) {
            return false;
        }

        if(preg_match("/^([a-z])+$/i", $data)) {
            return true;
        } else {
            return false;
        }

    } // end func alpha

    /**
     * Check data with required length.
     *
     * @access public
     * @param string $data
     * @param int $min
     * @param int $max
     * @return bool
     * @since 1.2.0
     */
    public function required($data, $min = -1, $max = -1) {

        if(!is_scalar($data)) {
            return false;
        }

        if(mb_strlen(trim($data)) == 0) {
            return false;
        }

        if($min != -1 and mb_strlen(trim($data)) < $min) {
            return false;
        }

        if($max != -1 and mb_strlen(trim($data)) > $max) {
            return false;
        }

        return true;

    } // End func required

    /**
     * Check is valid url
     *
     * @access public
     * @param string $data
     * @return bool
     * @since 1.3.0
     */
    public function url($data) {

        if(!is_scalar($data)) {
            return false;
        }

        if(!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$data)) {
            return false;
        }

        return true;

    } // End func url

    /**
     * Check is valid ISO date
     * Format: YYYY-MM-DD (eg 1997-07-16)
     *
     * @access public
     * @param string $data
     * @return bool
     * @since 1.3.0
     */
    public function date_iso($data) {

        if(!is_scalar($data)) {
            return false;
        }

        $time = strtotime($data);

        if(date('Y-m-d', $time) == $data) {
            return true;
        } else {
            return false;
        }

    } // End func date_iso

    /**
     * Check is valid date in given format(s)
     *
     * @access public
     * @param string $data
     * @param string|array $format
     * @return bool
     * @since 1.4.0
     */
    public function date($data, $format = 'Y-m-d') {

        if(!is_scalar($data)) {
            return false;
        }

        if(is_array($format)) {
            foreach($format as $f) {
                $d = date_create_from_format($f, $data);
                if($d && date_format($d, $f) === $data) {
                    return true;
                }
            }
        }
        else {
            $d = date_create_from_format($format, $data);
            if($d && date_format($d, $format) === $data) {
                return true;
            }
        }

        return false;

    } // end func date

    /* End class valid_lib */
}

/* End of file */
?>