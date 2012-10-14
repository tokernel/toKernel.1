<?php
/**
 * toKernel - Universal PHP Framework.
 * Data pagination lib
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
 * @version    1.0.4
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * pagination_lib class
 * 
 * @author Razmik Davoyan <razmik@davoyan.name>
 */
class pagination_lib {

/**
 * Configuration array
 * 
 * @access protected
 * @var array
 */	
 protected $config = array();
 
/**
 * Class constructor
 * 
 * @access public
 * @return bool
 */
 public function __construct() {
 	
 	$this->config['display_numbers'] = true;
 	$this->config['before_main'] = '';
 	$this->config['prev_link'] = '&#8592';
 	$this->config['prev_link_class'] = '';
 	$this->config['numbers_link_class'] = '';
 	$this->config['break_class'] = '';
 	$this->config['current_class'] = '';
 	$this->config['next_link'] = '&#8594';
 	$this->config['next_link_class'] = '';
 	$this->config['after_main'] = '';
 	$this->config['js_function'] = '';
 	
 } // end constructor
 
/**
 * Return instance of this object
 * 
 * @access public
 * @return object
 */ 
 public function instance() {
 	$this->__construct();
 	return clone $this;
 }
 
/**
 * Return 0 if $offset is not match
 * 
 * @access public
 * @param mixed $offset
 * @return integer
 */	
 public function to_offset($offset) {
    
 	if(is_null($offset) || !is_numeric($offset) || $offset < 0) {
 		$offset = 0;
 	}

 	return $offset;

 } // end func to_offset

/**
 * Set/Configure pagination
 * 
 * @access public
 * @param array $config
 * @return bool
 */ 
 public function configure($config) {
 	if(is_array($config)) {
 		$this->config = array_merge($this->config, $config);
 		return true;
 	} else {
 		return false;
 	}
 } 
 
/**
 * Return pagination buffer as string
 * 
 * @access public
 * @param integer $total
 * @param integer $limit
 * @param integer $offset
 * @param string $base_url
 * @return mixed 
 */ 
 public function run($total, $limit, $offset, $base_url) {
 
 	if($offset < 0 || $total <= 0 || $limit <= 0 || $total <= $limit) {
    	return false;
    }

    if($total <= $offset) {
    	$offset = $total - 1;
    }

    $page_count = ceil($total / $limit);   
    
    $cur_page = ceil(($offset + 1) / $limit);

    if($this->config['current_class'] != '') {
    	$cur_class = 'class="'.$this->config['current_class'].'"';
    } else {
    	$cur_class = '';
    }
    
    if($cur_page != 1) {
    	
    	if($this->config['prev_link_class'] != '') {
    		$prev_class = 'class="'.$this->config['prev_link_class'].'"';
    	} else {
    		$prev_class = '';
    	}
    	
		$prev_link = $this->to_link($base_url . ($cur_page - 1), 
    								$prev_class, $this->config['prev_link']);

    } else {
    	$prev_link = '<span '.$cur_class.'>'.$this->config['prev_link'].'</span> ';
    }

    if($cur_page != $page_count) {
    	
    	if($this->config['next_link_class'] != '') {
    		$next_class = 'class="'.$this->config['next_link_class'].'"';
    	} else {
    		$next_class = '';
    	}

		$next_link = $this->to_link($base_url . ($cur_page + 1), 
    								$next_class, $this->config['next_link']); 
    } else {
    	$next_link = '<span '.$cur_class.'>'.$this->config['next_link'].'</span> ';
    }

    $buffer = $this->config['before_main'];
    
    $buffer .= $prev_link.' ';

    if($this->config['display_numbers'] == false) {
    	$buffer .= $next_link;
    	$buffer .= $this->config['after_main'];
        return $buffer; 
    }

    if($this->config['numbers_link_class'] != '') {
    	$num_class = ' class="'.$this->config['numbers_link_class'].'"';
    } else {
    	$num_class = '';
    }
    
    if($this->config['break_class'] != '') {
    	$break_class = 'class="'.$this->config['break_class'].'"';
    } else {
    	$break_class = '';
    }
    
    if(min(max($cur_page - 2, 1), max($page_count - 4, 1)) <= 5) {
    	for($i = 1; $i < min(max($cur_page - 2, 1), 
    										max($page_count - 4, 1)); $i++) {
    											
		$buffer .= $this->to_link($base_url.($i), $num_class, $i);
    		
    	}
    } else {
    	
		$buffer .= $this->to_link($base_url.'1', $num_class, 1);
		$buffer .= $this->to_link($base_url.'2', $num_class, 2);
		$buffer .= '<span '.$break_class.'>... </span>';
    }

    for($i = min(max($cur_page - 2, 1), max($page_count - 4, 1)); 
        $i <= min(max($cur_page + 2, 5), $page_count); $i++) {
            
        if($i == $cur_page) {
        	$buffer .= '<span '.$cur_class.'>'.$i.'</span> ';
        	continue;
        }       
        
		$buffer .= $this->to_link($base_url.($i), $num_class, $i);
                
    }


    if($i >= $page_count - 3) {
    	
		while($i <= $page_count) {
    		$buffer .= $this->to_link($base_url.($i), $num_class, $i);
			$i++;
    	}
		
    } else {
		$buffer .= '<span '.$break_class.'>...</span> ';
		$buffer .= $this->to_link($base_url . ($page_count - 1), 
									$num_class, ($page_count - 1));
		$buffer .= $this->to_link($base_url . ($page_count), 
									$num_class, $page_count);    	
    }

    $buffer .= $next_link;

    $buffer .= $this->config['after_main'];
    
    return $buffer; 
 } // end func run
 
/**
 * Create link simple or javascript
 * 
 * @access protected
 * @param link
 * @return string
 */ 
 protected function to_link($link, $class, $show) {
 	
	if($this->config['js_function'] != '') {
 		
 		$func = str_replace('{link}', $link, $this->config['js_function']);
 		
 		return '<a href="javascript:" ' . $class . ' onClick="' . $func . 
 				'"; return false;">' 
 				. $show.'</a> ';
 	} else {
 		return '<a href="' . $link . '" ' . $class . '>' . $show . '</a> '; 
 	}
 	 
 } // end func to_link
 
/* End of class pagination_lib */
}

/* End of file */
?>