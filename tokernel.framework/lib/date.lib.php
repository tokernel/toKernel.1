<?php
/**
 * toKernel - Universal PHP Framework.
 * Date processing class library
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
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Date class library.
 * This library uses DateTime and DateInterval classes
 *
 * @author David A.   <tokernel@gmail.com>
 * @author Karapet S. <join04@yahoo.com>
 */
class date_lib {

    /**
     * Return time elapsed from one date to another
     * if $tp == 's' return time in seconds
     * if $tp == 'm' return time in minutes
     * if $tp == 'i' return time in minutes
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
        $date1 = date_create($date1);
        $date2 = date_create($date2);

        // Invalid date
        if(!$date1 || !$date2){
            return false;
        }

        $interval = date_diff($date1, $date2);

        if(!$interval) {
            return false;
        }

        $diff = 0;
        switch($tp){
            case 'Y':
                $diff = $interval->y;
                break;
            case 'M':
                $diff = $interval->m;
                break;
            case 'w':
                $diff = floor($interval->days / 7);
                break;
            case 'd':
                $diff = $interval->days;
                break;
            case 'h':
                $diff = ($interval->days * 24) + $interval->h;
                break;
            case 'm':
            case 'i':
                $diff = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                break;
            case 's':
                $diff = ($interval->days * 24 * 60 * 60) + ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;
                break;
            default:
                trigger_error("Unknown format '$tp'.", E_USER_WARNING);
                return false;
        }

        // Difference is negative
        if($interval->invert == 1) {
            return -$diff;
        }

        return $diff;

    } // end of func diff

    /**
     * Checks if date has passed
     *
     * @access public
     * @param string $date1
     * @param string $date2 = null
     * @return bool
     */
    public function is_passed($date1, $date2 = null) {

    	$date1 = date_create($date1);

        if(is_null($date2)) {
            $date2 = date_create();
        } else {
            $date2 = date_create($date2);
        }

        // Invalid date
        if(!$date1 || !$date2) {
            return false;
        }

        if($date1 > $date2) {
            return true;
        }

        return false;

    } // end of func is_passed

    /**
     * Convert Date to UNIX timestamp
     *
     * 2015-09-22 - 1442865600
     * 22/09/2015 - 1442865600
     * 1442865600 - 1442865600
     *
     * @access public
     * @param string|int $date
     * @return mixed int | bool
     */
    public function to_timestamp($date) {

    	if(is_int($date)) {
            return $date;
        }

        $date = date_create($date);

        // Invalid date
        if(!$date) {
            return false;
        }

        return date_timestamp_get($date);

    } // end func to_timestamp

    /**
     * Check intersection of dates
     *
     * 1. Range of dates with range of dates.
     * 2. Date in range of dates.
     *
     * See more explanation in code.
     *
     * @access public
     * @param string $first_date_start
     * @param string $first_date_end
     * @param string $second_date_start
     * @param string $second_date_end = null
     * @return bool
     */
    public function is_intersects($first_date_start, $first_date_end, $second_date_start, $second_date_end = null) {
        // Process the arguments
        $first_date_start = date_create($first_date_start);
        $first_date_end = date_create($first_date_end);
        $second_date_start = date_create($second_date_start);

        // Invalid date
        if(!$first_date_start || !$first_date_end || !$second_date_start){
            return false;
        }

        if(!is_null($second_date_end)) {
            $second_date_end = date_create($second_date_end);
        }

        // Intersect with start part
        // Date  :		|----------|
        // Event :	|----------|

        if(
            ($second_date_start <= $first_date_end)
            and
            ($second_date_start >= $first_date_start)) {
            return true;
        }

        if(!$second_date_end) {
            return false;
        }

        // Intersect with End part
        // Date  :	|----------|
        // Event :		|----------|

        if(
            ($second_date_end <= $first_date_end)
            and
            ($second_date_end >= $first_date_start)) {
            return true;
        }

        // Overlap totally
        // Date  :		|----------|
        // Event :	|------------------|

        if(
            ($second_date_start <= $first_date_start)
            and
            ($second_date_end >= $first_date_end)) {
            return true;
        }

        return false;

    } // end func is_intersects

    /**
     * Check if date interval has weekends
     *
     * @access public
     * @param string $date_from
     * @param string $date_to
     * @return bool
     */
    public function has_weekend($date_from, $date_to) {
        $date_from = date_create($date_from);
        $date_to = date_create($date_to);

        // Invalid date
        if(!$date_from || !$date_to){
            return false;
        }

        // Incorrect date range
        if($date_from > $date_to) {
            return false;
        }

        // Dates are equal
        if($date_from == $date_to) {
            $day_of_week = date_format($date_from, 'w');
            if($day_of_week == 6 or $day_of_week == 0) {
                // It is weekend
                return true;
            }
            return false;
        }

        // Regular date range
        while($date_from <= $date_to) {
            $day_of_week = date_format($date_from, 'w');
            if($day_of_week == 6 or $day_of_week == 0) {
                // It is weekend
                return true;
            }

            date_modify($date_from, '+1 day');
        }

        return false;
    } // end func has_weekend

} // End class date_lib

// End of file
?>