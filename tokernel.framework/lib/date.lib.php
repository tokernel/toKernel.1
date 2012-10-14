<?php
/**
 * toKernel - Universal PHP Framework.
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
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
 defined('TK_EXEC') or die('Restricted area.');

/**
 * DATE class library.
 * 
 * Show How much time elapsed from one date to another in seconds, minutes, etc... 
 * Check, if date has passed or not.  
 *
 * @author Razmik Davoyan <razmik@davoyan.name>
 */ 
class date_lib { 

/**
 * Return time elapsed from one date to another
 * if $tp == 's' return time in seconds
 * if $tp == 'm' return time in minutes
 * if $tp == 'h' return time in hours
 * if $tp == 'd' return time in days
 * if $tp == 'w' return time in weeks
 * if $tp == 'M' return time in months
 * if $tp == 'Y' return time in years
 * 
 * @access public
 * @param string $date1
 * @param string $date2
 * @param string $tp = 's'
 * @return int | false
 */
public function diff($date1, $date2, $tp = 's') {

	$date1 = strtotime($date1);

	$date2 = strtotime($date2);

        if($date1 == -1 || $date2 == -1) {
            return false;
        }

        $diff = $date2 - $date1;

        if($diff < 0) {
            return false;
        }

        switch($tp) {
            
        case 's':
            break;
        case 'm':
            $diff = floor($diff/60);
            break;
        case 'h':
            $diff = floor($diff/3600);
            break;
        case 'd':
            $diff = floor($diff/86400);
            break;
        case 'w':
            $diff = floor($diff/604800); 
            break;
        case 'M':

            $df = date('n', $date2) - date('n', $date1);

            if(date('j', $date2) < date('j', $date1)) {
                $df--; 
            }
            elseif(date('j', $date2) == date('j', $date1)) {
                if(date('G', $date2) < date('G', $date1)) {
                    $df--;
                }
                elseif(date('G', $date2) == date('G', $date1)) {
                    if(date('i', $date2) < date('i', $date1)) {
                        $df--;
                    }
                    elseif(date('i', $date2) == date('i', $date1)) {
                        if(date('s', $date2) < date('s', $date1)) {
                            $df--;
                        }
                    }
                }
            }

            $diff = max((date('Y', $date2) - date('Y', $date1)), 0)*12 + $df;

            break;
        case 'y':
            $vis_count = 0;
            
            for($i = date('Y', $date1) + 1; $i < date('Y', $date2); $i++) {
            
                if(($i%4) == 0) {
                    $vis_count++;
                }
                
            }

            $diff = floor($diff/  
                (31536000 + 
                min( (strtotime('29.02.'.date('Y', $date1))/$date1), 
                    (1 - min((date('Y', $date1)%4), 1)) 
                ) +
                min( ($date2/strtotime('29.02.'.date('Y', $date2))), 
                    (1 - min((date('Y', $date2)%4), 1)) , $diff 
                ) +
                $vis_count) );

            break;
        default:
            return false;
        }

        return $diff;

} // end of func diff

/**
 * Checks if the date has passed or not
 * 
 * @access public
 * @param string $date1
 * @param string $date2
 * @return bool
 */
public function is_passed($date1, $date2 = NULL) {

	if(is_null($date2)) {
		$date2 = date('d.m.Y');
	} 

	if(strtotime($date1) <= strtotime($date2)) {
       return false;
    }

   	return true;

} // end of func is_passed

/* End of class date_lib */
}

/* End of file date.lib.php */
?>
