<?php
/**
 * toKernel - Universal PHP Framework.
 * Image processing class library
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
 * image_lib class
 * 
 * Load image, resize, create thumbnail and more... 
 *  
 * @author Arshak Ghazaryan <khazaryan@gmail.com>
 */
class image_lib {

/**
 * Loaded image options
 * 
 * @var array
 * @access protected
 */
 protected $img_opt;
 
/**
 * R of RGB
 * 
 * @var int
 * @access protected
 */ 
 protected $r;

/**
 * G of RGB
 * 
 * @var int
 * @access protected
 */ 
 protected $g;

/**
 * B of RGB
 * 
 * @var int
 * @access protected
 */ 
 protected $b;

/**
 * Load image before processing
 * 
 * @access public
 * @param string $src_file
 * @return bool
 */
 public function load($src_file) {
	
 	if(!is_readable($src_file)) {
		return false;
	}
 	
 	if(is_array($this->img_opt = $this->create_resource($src_file)) == false) {
		return false;
	}
	
	return true;
 } // end func load

/**
 * Check is real image file
 * 
 * @access protected
 * @param string $src_file
 * @return bool 
 */ 

 public function is_image($src_file) {
	if(!is_readable($src_file)) {
		return false;
	}

	$prop = getimagesize($src_file);
	
	if($prop[2] != 1 and $prop[2] != 2 and $prop[2] != 3) {
		return false;
	} else {
		return true;
	}
 } // end func is_image
 
/**
 * Create image resource
 * 
 * @access protected
 * @param string $src_file
 * @return array 
 */ 
 protected function create_resource($src_file) {
	if(!is_readable($src_file)) {
		return false;
	}

	$prop = getimagesize($src_file);

	if($prop[2] != 1 and $prop[2] != 2 and $prop[2] != 3) {
		return false;
	}
	 
	$type = substr($prop['mime'], strpos($prop['mime'], '/')+1, 
									strlen($prop['mime']));
									
	$resource = call_user_func('imagecreatefrom'.strtolower($type), $src_file);
	 
	return array('width'=>$prop[0], 'height'=>$prop[1], 'resource'=>$resource, 
			     'mime'=>$prop['mime'], 'type'=>$type, 'dest_file'=>$src_file);
 } // end func create_resource

/**
 * Resize image
 * 
 * @access public
 * @param int $size
 * @param bool $is_square
 * @return bool
 */ 
 public function resize($size, $is_square = false) {
	if(is_array($this->img_opt) == false) {
		return false;
	}

	$x = 0;
	$y = 0;
	
 	if($is_square == true) {
		if ($this->img_opt['width']>=$this->img_opt['height']) {

			if ($size=='') {
				$new_size['width']  = $this->img_opt['height'];
				$new_size['height'] = $this->img_opt['height'];
			} else {
				$new_size['width']  = $size;
				$new_size['height'] = $size;
			}
				
			$x = round ($this->img_opt['width'] / 2 - 
						$this->img_opt['height'] / 2);
						
			$this->img_opt['width'] = $this->img_opt['height'];
		
		} else {

			if ($size=='') {
				$new_size['width']  = $this->img_opt['width'];
				$new_size['height'] = $this->img_opt['width'];
			} else {
				$new_size['width']  = $size;
				$new_size['height'] = $size;
			}

			$y = round ($this->img_opt['height'] / 2 - 
						$this->img_opt['width'] / 2);
						
			$this->img_opt['height'] = $this->img_opt['width'];
		}
			
	} else {
		
		if(is_array($new_size = $this->calculate_size($size)) == false) {
			return false;
		}
	}
	
	$gen_res = imagecreatetruecolor($new_size['width'], $new_size['height']);
	
 	if ($this->img_opt['type']=='png') {
		$this->transparent_png($gen_res);
	}

	if ($this->img_opt['type']=='gif') {
		$this->transparent_gif($this->img_opt['resource'], 
								$this->img_opt['dest_file']);
	}
	
	imagecopyresampled($gen_res, $this->img_opt['resource'], 0, 0, $x, $y, 
						$new_size['width'], $new_size['height'], 
						$this->img_opt['width'], $this->img_opt['height']);
	
	
	imagedestroy($this->img_opt['resource']);
	
	$this->img_opt['resource'] = $gen_res;
	$this->img_opt['width']    = $new_size['width'];
	$this->img_opt['height']   = $new_size['height'];
	
	unset($new_size);
	return true;
	
 } // end func resize

/**
 * Calculate image size
 * 
 * @access protected
 * @param int $size
 * @return mixed array | bool
 */ 
 protected function calculate_size($size) {
	$size = round($size);

	if(!is_numeric($size) or $size<=0) {
		return false;
	}

	if ($this->img_opt['width'] < $size or $this->img_opt['height'] < $size) {
		return array($this->img_opt['width'], $this->img_opt['height']);
	}

	if ($this->img_opt['width'] > $this->img_opt['height']) {
		
		return array('width'=>$size, 'height'=>round($size * 
							$this->img_opt['height']/$this->img_opt['width']));
	
	} else {
		
		return array('width'=>round($this->img_opt['width'] * $size / 
						$this->img_opt['height']), 'height'=>$size);	
	}
 } // end func calculate_size

/**
 * Make image watermark
 * 
 * @access public
 * @param string $w_image
 * @param string $pos 
 * @return bool 
 */ 
 public function watermark_image($w_image, $pos = false, $margin = false) {
	if (is_array($this->img_opt) == false) {
		return false;	
	}
	
	if (($watermark = $this->create_resource($w_image)) == false) {
		return false;
	}

	if($margin == false or $pos == 'c' or is_numeric($margin) == false)  { 
		$margin = 0;
	}
	
	/* Set watermark image position */
	switch($pos) {
	case 'c': // CENTER
       $startwidth = (($this->img_opt['width'] - $watermark['width']) / 2); 
       $startheight = (($this->img_opt['height'] - $watermark['height']) / 2); 
       break;
    case 'tr': // Top Right
       $startwidth = (($this->img_opt['width'] - $watermark['width']) - $margin); 
       $startheight = $margin; 
	   break;
    case 'br': // Bottom Right
       $startwidth = (($this->img_opt['width'] - $watermark['width']) - $margin); 
       $startheight = (($this->img_opt['height'] - ($watermark['height'] + $margin))); 
       break;
    case 'tl': // Top Left
       $startwidth  = $margin; 
       $startheight = $margin;
	   break;
    case 'bl': // Bottom Left
	   $startwidth = $margin;
       $startheight = (($this->img_opt['height'] - 
       						($watermark['height'] + $margin))); 
       break;
	default:
	   $startwidth = (($this->img_opt['width'] - $watermark['width']) - $margin);
	    
       $startheight = (($this->img_opt['height'] - 
       					($watermark['height'] + $margin))); 
	   break;
	}
	
 	if ($this->img_opt['type']=='png') {
		$this->transparent_png($this->img_opt['resource']);
	}
 	
	if ($this->img_opt['type']=='gif') {
		$this->transparent_gif($this->img_opt['resource'], 
								$this->img_opt['dest_file']);
	}

	/* Copy watermark in image */
	imagecopy($this->img_opt['resource'], $watermark['resource'], $startwidth, 
				$startheight, 0, 0, $watermark['width'], $watermark['height']);
				
	/* destroy watermark image */
	imagedestroy($watermark['resource']);
	unset($watermark);
	return true;
	
 } // end func watermark_image

/**
 * Make text watermark
 * 
 * @access public
 * @param string $text
 * @param string $font (font file path)
 * @param int $size
 * @param string $color
 * @param string $pos
 * @param int $margin
 * @param int $angle
 * @return bool
 */  
 public function watermark_text($text, $font, $size, $color = '000000', 
 										$pos = 'br', $margin = 10, $angle = 0) {
 											
	if(is_array($this->img_opt) == false) {
		return false;
	}
	
	if (strtolower(substr($font, -3))!= 'ttf') {


		return false;	
	}
	
	if (is_numeric($angle) == false or is_numeric($margin) == false) {
		return false;	
	}
	
	$box = imagettfbbox($size, $angle, $font, $text);
	
	$box_width =  $box[2] - $box[0];
	$box_height = $box[1] - $box[7];
	unset($box);
	
	/* Set watermark Text position */
	switch($pos) {
	case 'c': // CENTER
       $startwidth =  (($this->img_opt['width']  - $box_width)/ 2); 
       $startheight = (($this->img_opt['height'] - $box_height)/ 2); 
       break;
    case 'br': // Bottom Right
       $startwidth  = ($this->img_opt['width']  - $box_width - $margin); 
       $startheight = ($this->img_opt['height'] - $margin); 
	   break;
    case 'tr': // Top Right
       $startwidth  = ($this->img_opt['width'] - $box_width - $margin); 
       $startheight =  $box_height + $margin;
       break;
    case 'tl': // Top Left
       $startwidth = $margin;
       $startheight = $margin + $box_height;
	   break;
    case 'bl': // Bottom Left
	   $startwidth  = $margin; 
       $startheight = ($this->img_opt['height'] - $margin); 
       break;
	default:
       $startwidth  = ($this->img_opt['width']  - $box_width - $margin); 
       $startheight = ($this->img_opt['height'] - $margin); 
	   break;
	}

	$color = $this->return_color($color, $this->img_opt['resource']);

 	if ($this->img_opt['type']=='png') {
		$this->transparent_png($this->img_opt['resource']);
	}
 	
	if ($this->img_opt['type']=='gif') {
		$this->transparent_gif($this->img_opt['resource'], 
								$this->img_opt['dest_file']);
	}

	imagettftext($this->img_opt['resource'], $size, $angle, $startwidth, 
									$startheight, $color, $font, $text);
	return true; 
 } // end func watermark_text

/**
 * Create image border
 * 
 * @access public
 * @param string $color
 * @param bool $px
 * @return bool
 */ 
 public function border($color, $px=false) {
	
 	if (is_array($this->img_opt) == false) {
		return false;	
	}
	
	if ($px == false or is_numeric($px)) { 
		$num = 1;
	} else {
		return false;	
	}
	
	do {
		imagerectangle($this->img_opt['resource'], $num-1, $num-1, 
						$this->img_opt['width'] - $num, 
						$this->img_opt['height'] - $num, 
						$this->return_color($color, 
						$this->img_opt['resource']));		
		$num++;
	} while ($num <= $px);
	
	return true; 
 } // end func border

/**
 * Return image color
 * 
 * @access protected
 * @param string $color
 * @param resource $resource
 * @return int
 */ 
 protected function return_color($color, $resource) {
	if(strlen($color) >= 7) { 
		$color = substr($color, 1, 6);
	}
  	
	sscanf($color, "%2x%2x%2x" , $red , $green , $blue);
    return imagecolorallocate($resource, $red, $green, $blue);
 } // end func return_color
 
/**
 * Create transparent png image 
 * 
 * @access protected
 * @param resource $resource
 * @return void
 */
 protected function transparent_png($resource) {
 	imagealphablending($resource, false);
	imagesavealpha($resource, true);
 } // end func transparent_png

/**
 * Create transparent gif image 
 * 
 * @access protected
 * @param string $src_image
 * @return void
 */ 
 protected function transparent_gif($src_image) {
	
 	$dst_image = imagecreatetruecolor($this->img_opt['width'], 
 										$this->img_opt['height']);
 										
	$colorcount = imagecolorstotal($src_image);
	imagetruecolortopalette($dst_image,true,$colorcount);
	imagepalettecopy($dst_image,$src_image);
	$transparentcolor = imagecolortransparent($src_image);
	imagefill($dst_image,0,0,$transparentcolor);
	imagecolortransparent($dst_image,$transparentcolor);
	 
 } // end func transparent_gif

/**
 * Make image grayscale
 * 
 * @access protected
 * @return void
 */ 
 protected function grayscale() {
   $pixel_average = ($this->r + $this->g + $this->b) / 3;
   $this->pixel($pixel_average, $pixel_average, $pixel_average);
 } // end func greyscale

/**
 * Add noise to the image
 * 
 * @access protected
 * @param int $factor
 * @return void
 */ 
 protected function add_noise($factor) {
	$random = mt_rand(-$factor, $factor);
	$this->pixel($this->r + $random, $this->g + $random, $this->b + $random );
 } // end func add_noise

/**
 * Adjusts image brightness
 * 
 * @access protected
 * @param int $factor
 * @return void
 */ 
 protected function adjust_brightness($factor) { 
    return $this->pixel($this->r + $factor, $this->g + $factor, $this->b + $factor); 
 } // end func adjust_brightness
 
/**
 * turns to the negative image
 * 
 * @access protected
 * @param int $factor
 * @return void
 */
 protected function negative() { 
    $this->pixel(255 - $this->g, 255 - $this->r, 255 - $this->b); 
 } // end func negative

/**
 * Convert image to black and white
 * 
 * @access protected
 * @param int $factor
 * @return void
 */
 protected function black_and_white($factor) { 
    $pixel_total = ($this->r + $this->g + $this->b);

    if ($pixel_total > (((255 + $factor) / 2) * 3)) { 
        // White
        $this->r = 255; $this->g = 255; $this->b = 255; 
    } else { 
        $this->r = 0; $this->g = 0; $this->b = 0; 
    }
 } // end func black_and_white

/**
 * Adjust image gamma
 * 
 * @access protected
 * @param int $factor
 * @return void
 */
 protected function gamma($factor) { 
    $this->pixel(pow($this->r / 255, $factor) * 255, 
		pow($this->g / 255, $factor) * 255, pow($this->b / 255, $factor) * 255 ); 
 } // end func black_and_white

/**
 * Random adds to the picture and black points
 * 
 * @access protected
 * @param int $factor
 * @return void
 */ 
 protected function salt_and_pepper($factor) { 
	$black = (int)($factor/2 + 1); 
    $white = (int)($factor/2 - 1); 
	
	$random = mt_rand(0, $factor); 
    $new_channel = false; 
    if ($random == $black) { 
		$new_channel = 0;
	} 
    
	if ($random == $white) { 
		$new_channel = 255;
	} 
    
	if (is_int($new_channel)) { 
        $this->pixel($new_channel, $new_channel, $new_channel); 
    }
 } // end func salt_and_pepper

/**
 * Swap color in some places on image
 * 
 * @access protected
 * @param int $factor
 * @return void
 */ 
  protected function swap_colors($factor) {
    switch ($factor) {         
        case 'rbg': $this->pixel($this->r, $this->b, $this->g); break;         
        case 'bgr': $this->pixel($this->b, $this->g, $this->r); break;         
        case 'brg': $this->pixel($this->b, $this->r, $this->g); break;
        case 'gbr': $this->pixel($this->g, $this->b, $this->r ); break;
        case 'grb': $this->pixel($this->g, $this->r, $this->b); break;
    }
 } // end func swap_colors

/**
 * Removes selected color on image
 * 
 * @access protected
 * @param int $factor
 * @return void
 */ 
 protected function remove_color($factor) { 
    if ($factor == 'r') { 
		$this->r = 0;
	} 
    
	if ($factor == 'g') { 
		$this->g = 0;
	} 
    
	if ($factor == 'b') { 
		$this->b = 0;
	} 
    
	if ($factor == 'rb' or $factor == 'br') { 
		$this->r = 0;
		$this->b = 0;
	} 
    
	if ($factor == 'rg' or $factor == 'gr') { 
		$this->r = 0; 
		$this->g = 0;
	} 
    
	if ($factor == 'bg' or $factor == 'gb') { 
		$this->b = 0; 
		$this->g = 0;
	} 
    
 } // end func remove_color

/**
 * set selected color maximum on image 
 * 
 * @access protected
 * @param int $factor
 * @return void
 */ 
  protected function max_color($factor) { 
    if ($factor == 'r') { 
		$this->r = 255; 
	} 
    
	if ($factor == 'g') { 
		$this->g = 255; 
	} 
    
	if ($factor == 'b') { 
		$this->b = 255; 
	} 
    
	if ($factor == 'rb' or $factor == 'br') {
		$this->r = 255; 
		$this->b = 255;
	} 
    
	if ($factor == 'rg' or $factor == 'gr') { 
		$this->r = 255; 
		$this->g = 255;
	} 
    
	if ($factor == 'bg' or $factor == 'gb') { 
		$this->b = 255; 
		$this->g = 255;
	} 
    
 } // end func max_color

/**
 * set pixel colors
 * 
 * @access protected
 * @param int $r
 * @param int $g
 * @param int $b 
 * @return void
 */ 
 protected function pixel($r, $g, $b) {
        if ($r > 255) {
			$this->r = 255;
		} elseif ($r < 0) {
			$this->r = 0;
		} else {
			$this->r = (int)$r;			
		}

        if ($b > 255) {
			$this->b = 255;
		} elseif ($b < 0) {
			$this->b = 0;
		} else {
			$this->b = (int)$b;
		}

        if ($g > 255) {
			$this->g = 255;
		} elseif ($g < 0) {
			$this->g = 0;
		} else {
			$this->g = (int)$g;
		}
 } // end func pixel

/**
 * save image resource to file
 * 
 * @access public
 * @param string $dest_file
 * @param string $overwrite
 * @return bool
 */ 
 public function save($dest_file = NULL, $overwrite = false) {
	if (is_array($this->img_opt) == false) {
		return false;	
	}
	
	if($dest_file == NULL) {
	  	$dest_file = $this->img_opt['dest_file'];
	} elseif (is_dir($dest_file)) {
		if(substr($dest_file, -1) != '/') { 
			$dest_file.= '/';
		}
		$dest_file = $dest_file.basename($this->img_opt['dest_file']);
  	} else{
		$type = substr($dest_file, (strpos($dest_file, '.')+1), strlen($dest_file));
		if (strtolower($type)!='png' and strtolower($type)!='gif' 
				and strtolower($type)!='jpg' and strtolower($type)!='jpeg') {
			return false;
		}
    }
  
  	if (is_file($dest_file) == true and $overwrite == false) {
		return false;

  	}
  
  	call_user_func('image'.strtolower($this->img_opt['type']), 
  						$this->img_opt['resource'], $dest_file);
	return true;
 } // end func save
 
/**
 * output (show) image
 * 
 * @access public
 * @param string $output_name
 * @return void
 */ 
 public function output($output_name = false) {
	if (is_array($this->img_opt) == false) {
		return false;	
	}
	
	if ($output_name == false) { 
		$output_name = basename($this->img_opt['dest_file']);
	} else {
		$output_name.='.'.$this->img_opt['type'];
	}
	
	header ("Content-type: $this->img_opt['mime']");
	header('Content-Disposition: attachment; filename='.$output_name.';');
	call_user_func('image'.strtolower($this->img_opt['type']), 
						$this->img_opt['resource']);

 } // end func output

/**
 * Add effect to image
 * 
 * @access public
 * @param string $effect_name
 * @param string $factor 
 * @return bool
 */
 public function image_effect($effect_name, $factor = false) {
	 if (is_array($this->img_opt) == false) {
		return false;
	 }
	 
	 if (in_array($effect_name , get_class_methods(__CLASS__)) == false) {
		return false;		 
	 }

	 $new_image = imagecreatetruecolor($this->img_opt['width'], 
	 			$this->img_opt['height']);
        
	 for ($x = 0; $x < $this->img_opt['width']; $x++) {
          for ($y = 0; $y < $this->img_opt['height']; $y++) {
              $rgb = imagecolorat($this->img_opt['resource'], $x, $y);
              $r = ($rgb >> 16) & 0xFF;
              $g = ($rgb >> 8) & 0xFF;
              $b = $rgb & 0xFF;
			  $this->pixel($r, $g, $b);
				
			  if ($effect_name == 'grayscale') {
				 $this->grayscale();
			  } elseif ($effect_name == 'add_noise') {
				 $this->add_noise($factor);
			  } elseif ($effect_name == 'adjust_brightness') {
				 $this->adjust_brightness($factor);
			  } elseif ($effect_name == 'negative') {
				 $this->negative();
			  } elseif ($effect_name == 'black_and_white') {
				 $this->black_and_white($factor);
			  } elseif ($effect_name == 'gamma') {
				 $this->gamma($factor);
			  } elseif ($effect_name == 'salt_and_pepper') {
				 $this->salt_and_pepper($factor);
			  } elseif ($effect_name == 'swap_colors') {
				 $this->swap_colors($factor);
			  } elseif ($effect_name == 'remove_color') {
				 $this->remove_color($factor);
			  } elseif ($effect_name == 'max_color') {
				 $this->max_color($factor);
			  } else {
				 return false;	
			  }
			  
			  $color = imagecolorallocate($this->img_opt['resource'], 
			  								$this->r, $this->g, $this->b);
			  								
              imagesetpixel($new_image, $x, $y, $color);
            }
        }

   imagedestroy($this->img_opt['resource']);
   $this->img_opt['resource'] = $new_image;
   return true;
 } // end func image_operation 
 
/* End class image_lib */ 
}

/* End file image.lib.php */
?>