<?php
/**
 * toKernel - Universal PHP Framework.
 * Universal data processing class
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
 * @copyright  Copyright (c) 2012 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.4.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * data_lib class
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class data_lib {

/**
 * Check, is associative array
 * 
 * @access public
 * @param array
 * @return bool
 */	
 public function is_assoc($arr) {
 	return array_keys($arr) !== range(0, count($arr) - 1);
 } // end func is_assoc

/**
 * Rename key in array
 * 
 * @access public
 * @param string $ext_key
 * @param string $new_key
 * @param array $arr
 * @return array | bool
 */ 
 public function array_key_rename($ext_key, $new_key, $arr) {

	if(!is_array($arr)) {
		return false;
	}
	
	$new_arr = array();

	foreach($arr as $key => $value) {
		if($key == $ext_key and is_string($key)) {
			$key = $new_key;
		}

		$new_arr[$key] = $value;
	} // end foreach

	return $new_arr;
 } // end func array_key_rename 

/**
 * Return position in array by key
 *
 * @access public
 * @param mixed $needle
 * @param array $array
 * @return int | bool
 * @since 1.3.0
 */
 function array_key_pos($needle, $array) {
	
	if(!is_array($array) or is_null($needle)) {
		return false;
	}
	
	$tmp = array_keys($array);
	$index = array_search($needle, $tmp);
	
	if($index !== false) {
		return $index + 1;
	} else {
		return false;
	}
	
 } // end func array_key_pos
 
/**
 * Create random generated password
 * 
 * @access public
 * @param int $lenght
 * @param bool $uppercase
 * @return string
 * @since 1.1.0
 */
 function create_password($lenght = 8, $uppercase = true) {
 
 	$chars = "abcdefghijkmnopqrstuvwxyz023456789~`!@#$%^&*()_|=-.,?';:]}[}";
 	$u_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
 	
 	if($uppercase == true) {
 		$chars .= $u_chars; 
 	}
 	
    srand((double)microtime()*1000000);

    $i = 0;

    $pass = '' ;

    while ($i < $lenght) {
        $num = rand(1, strlen($chars));
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;

 } // end func create_password

/**
 * Create random generated username
 * 
 * @access public
 * @param int $lenght
 * @param bool $uppercase
 * @return string
 * @since 1.2.0
 */
 function create_username($lenght = 8, $uppercase = true) {
 
 	$chars = "abcdefghijkmnopqrstuvwxyz023456789_.";
 	$u_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
 	
 	if($uppercase == true) {
 		$chars .= $u_chars; 
 	}
 	
    srand((double)microtime()*1000000);

    $i = 0;

    $usr = '' ;

    while ($i < $lenght) {
        $num = rand(1, strlen($chars));
        $tmp = substr($chars, $num, 1);
        $usr = $usr . $tmp;
        $i++;
    }

    return $usr;

 } // end func create_username
 
/**
 * Change any url to url tag
 * 
 * @access public
 * @param string $string
 * @return string
 * @since 1.4.0
 */
 function url2tag($string) {
	/* Regular Expression filter */
	$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

	/* Check if there is a url in the string */
	if(preg_match($reg_exUrl, $string, $url)) {
		return preg_replace($reg_exUrl, '<a href="'.$url[0].'">'.$url[0].'</a> ', $string);
	} else {
		return $string;
	}
 } // End func url2tag
	
/* End of class data_lib */
}

/* End of file */
?>