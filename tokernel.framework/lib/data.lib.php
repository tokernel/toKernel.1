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
 * data_lib class
 *  
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class data_lib {

/**
 * Create random generated password
 * 
 * @access public
 * @param int $length
 * @param bool $uppercase
 * @return string
 * @since 1.1.0
 */
 function create_password($length = 8, $uppercase = true) {
 
 	$chars = "abcdefghijkmnopqrstuvwxyz023456789~`!@#$%^&*()_|=-.,?';:]}[}";
 	$u_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
 	
 	if($uppercase == true) {
 		$chars .= $u_chars; 
 	}
 	
    srand((double)microtime()*1000000);

    $i = 0;

    $pass = '' ;

    while ($i < $length) {
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
 * @param int $length
 * @param bool $uppercase
 * @return string
 * @since 1.2.0
 */
 function create_username($length = 8, $uppercase = true) {
 
 	$chars = "abcdefghijkmnopqrstuvwxyz023456789_.";
 	$u_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
 	
 	if($uppercase == true) {
 		$chars .= $u_chars; 
 	}
 	
    srand((double)microtime()*1000000);

    $i = 0;

    $usr = '' ;

    while ($i < $length) {
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