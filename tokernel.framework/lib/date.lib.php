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
 * @author      toKernel development team <framework@tokernel.com>
 * @copyright   Copyright (c) 2016 toKernel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version     1.3.0
 * @link        http://www.tokernel.com
 * @since       File available since Release 1.0.0
 * @todo        Refactor function diff()
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Date class library.
 *
 * @author David A. <tokernel@gmail.com>
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

		$date1 = $this->to_timestamp($date1);
		$date2 = $this->to_timestamp($date2);

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
				} elseif(date('j', $date2) == date('j', $date1)) {

					if(date('G', $date2) < date('G', $date1)) {
						$df--;
					} elseif(date('G', $date2) == date('G', $date1)) {

						if(date('i', $date2) < date('i', $date1)) {
							$df--;
						} elseif(date('i', $date2) == date('i', $date1)) {

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

				$diff = floor( $diff / (31536000 +
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
	 * Checks if date has passed
	 *
	 * @access public
	 * @param string $date1
	 * @param string $date2
	 * @return bool
	 */
	public function is_passed($date1, $date2 = NULL) {

		$date1 = $this->to_timestamp($date1);

		if(!is_null($date2)) {
			$date2 = $this->to_timestamp($date2);
		} else {
			$date2 = time();
		}

		if($date1 <= $date2) {
			return false;
		}

		return true;

	} // end of func is_passed

	/**
	 * Convert Date to timestamp
	 *
	 * 2016-09-22 - 1442865600
	 * 22/09/2016 - 1442865600
	 * 1442865600 - 1442865600
	 *
	 * @access public
	 * @param mixed
	 * @return int
	 */
	function to_timestamp($date) {

		if(is_int($date)) {
			return $date;
		}

		if(strpos($date, '/') !== false) {
			$tmp = explode('/', $date);
			$date = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
		}

		$date = strtotime($date);

		return $date;

	} // End func to_timestamp

	/**
	 * Check intersection of dates
	 *
	 * 1. Range of dates with range of dates.
	 * 2. Date in range of dates.
	 *
	 *  See more explanation in code.
	 *
	 * @access public
	 * @param mixed $first_date_start
	 * @param mixed $first_date_end
	 * @param mixed $second_date_start
	 * @param mixed $second_date_end = null
	 * @return bool
	 */
	function is_intersects($first_date_start, $first_date_end, $second_date_start, $second_date_end = NULL) {

		// Process the arguments
		$first_date_start = $this->to_timestamp($first_date_start);
		$first_date_end = $this->to_timestamp($first_date_end);
		$second_date_start = $this->to_timestamp($second_date_start);

		if(!is_null($second_date_end)) {
			$second_date_end = $this->to_timestamp($second_date_end);
		}

		// Intersect with start part
		// Date  :      |----------|
		// Event : |----------|

		if(
			($second_date_start <= $first_date_end)
			and
			($second_date_start >= $first_date_start)) {
			return true;
		}

		if(is_null($second_date_end)) {
			return false;
		}

		// Intersect with End part
		// Date  : |----------|
		// Event :      |----------|

		if(
			($second_date_end <= $first_date_end)
			and
			($second_date_end >= $first_date_start)) {
			return true;
		}

		// Overlap totally
		// Date  :   |----------|
		// Event : |--------------|

		if(
			($second_date_start <= $first_date_start)
			and
			($second_date_end >= $first_date_end)) {
			return true;
		}

		return false;

	} // End func is_intersects

	/**
	 * Check if date interval has weekends
	 *
	 * @access public
	 * @param mixed $date_from
	 * @param mixed $date_to
	 * @return bool
	 */
	public function has_weekend($date_from, $date_to) {

		$date_from = $this->to_timestamp($date_from);
		$date_to = $this->to_timestamp($date_to);

		// Incorrect date range
		if($date_from > $date_to) {
			return false;
		}

		// Date ranges is equal
		if($date_from == $date_to) {
			if(date('w', $date_from) == 6 or date('w', $date_from) == 0) {
				// it is weekend
				return true;
			}
			return false;
		}

		// Regular date range
		while($date_from < $date_to) {

			if(date('w', $date_from) == 6 or date('w', $date_from) == 0) {
				// it is weekend
				return true;
			}

			$date_from += 86400; // Increment 1 day.
		}

		return false;

	} // End func has_weekend

	/* End of class date_lib */
}

/* End of file date.lib.php */
?>
