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
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.0.0
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
     * Library object for working with
     * libraries in this class
     *
     * @var object
     * @access private
     */
    private $lib;

   /**
    * Loaded image options
    *
    * @var array
    * @access protected
    */
    protected $img_opt = array();

    /**
     * Return Instance of Image library with loaded image file.
     *
     * @access public
     * @param string $src_file
     * @return image_lib object
     * @since 2.0.0
     */
    public function instance($src_file) {

        $obj = clone $this;
        $obj->load($src_file);
        return $obj;

    }

    public function __construct() {
        $this->lib = lib::instance();
    }

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

        $this->img_opt = $this->create_resource($src_file);

        if(empty($this->img_opt)) {
            return false;
        }

        return true;

    } // End func load

    /**
     * Check is real image file
     *
     * @access public
     * @param string $src_file
     * @return bool
     */
    public function is_image($src_file) {

        if(!is_readable($src_file)) {
            return false;
        }

        $prop = getimagesize($src_file);

        if(empty($prop)) {
            return false;
        }

        if($prop[2] != 1 and $prop[2] != 2 and $prop[2] != 3) {
            return false;
        }

        return true;

    } // end func is_image

    /**
     * Create image resource
     *
     * @access protected
     * @param string $src_file
     * @return mixed array | bool
     */
     protected function create_resource($src_file) {

        if(!is_readable($src_file)) {
            return false;
        }

        $prop = getimagesize($src_file);

        if(empty($prop)) {
            return false;
        }

        if($prop[2] != 1 and $prop[2] != 2 and $prop[2] != 3) {
            return false;
        }

        $type = substr($prop['mime'], strpos($prop['mime'], '/') + 1, strlen($prop['mime']));

        $resource = call_user_func('imagecreatefrom' . strtolower($type), $src_file);

        return array(
            'width' => $prop[0],
            'height' => $prop[1],
            'resource' => $resource,
            'mime' => $prop['mime'],
            'type' => $type,
            'dest_file' => $src_file
        );

     } // End func create_resource

    /**
     * Resize image
     *
     * @access public
     * @param int $size
     * @param bool $is_square
     * @param mixed array | bool $watermark
     * @param mixed array | bool $watermark_text
     * @return bool
     */
     public function do_resize($size, $is_square = false, $watermark = false, $watermark_text = false) {

        if(empty($this->img_opt)) {
            return false;
        }

        if ($size > $this->img_opt['width'] or $size > $this->img_opt['height']) {
            $options = $this->calculate_size($size);
        } elseif($is_square == true) {
            $options = $this->calculate_square_size($size);
        } else {
            $options = $this->calculate_size($size);
        }

        $this->resize_image($options);

        if(!empty($watermark['image'])) {

            if(empty($watermark['position'])) {
                $watermark['position'] = false;
            }

            if(empty($watermark['margin'])) {
                $watermark['margin'] = false;
            }

            $this->watermark_image($watermark['image'], true, $watermark['position'], $watermark['margin']);
        }

        if(!empty($watermark_text['text'])) {

            if(empty($watermark_text['font'])) {
                return false;
            }

            if(empty($watermark_text['size'])) {
                $watermark_text['size'] = 10;
            }

            if(empty($watermark_text['color'])) {
                $watermark_text['color'] = '000000';
            }

            if(empty($watermark_text['position'])) {
                $watermark_text['position'] = false;
            }

            if(empty($watermark_text['margin'])) {
                $watermark_text['margin'] = 0;
            }

            if(empty($watermark_text['angle'])) {
                $watermark_text['angle'] = 0;
            }

            $this->watermark_text($watermark_text['text'], $watermark_text['font'], $watermark_text['size'],
                $watermark_text['color'], $watermark_text['position'], $watermark_text['margin'], $watermark_text['angle'], true);
        }

        return true;

     } // end func do_resize

    /**
     * Resize image as square
     *
     * @access public
     * @param array $options
     * @return mixed array | bool
     */
    public function resize_image($options) {

        $default_options = array(
            'x1' => 0,
            'x2' => 0,
            'y1' => 0,
            'y2' => 0
        );

        $options = array_merge($default_options, $options);

        $gen_res = imagecreatetruecolor($options['width'], $options['height']);

        if($this->img_opt['type'] == 'png') {
            $this->transparent_png($gen_res);
        }

        if($this->img_opt['type'] == 'gif') {
            $this->transparent_gif($this->img_opt['resource'], $options['img_width'], $options['img_height']);
        }

        $this->img_opt['new_width'] = $options['width'];
        $this->img_opt['new_height'] = $options['height'];

        imagecopyresampled($gen_res, $this->img_opt['resource'],
                           $options['x1'], $options['y1'], $options['x2'], $options['y2'],
                           $options['width'], $options['height'],
                           $options['img_width'], $options['img_height']);

        $this->img_opt['result_resource'] = $gen_res;

        return true;

     } // End func resize_image

    /**
    * Calculate image size
    *
    * @access protected
    * @param int $size
    * @return mixed array | bool
    */
    protected function calculate_size($size) {

        if(!is_numeric($size) or $size <= 0) {
            return false;
        }

        $size = ceil($size);

        if($this->img_opt['width'] < $size and $this->img_opt['height'] < $size) {
            return array('width'=>$this->img_opt['width'], 'height'=>$this->img_opt['height'],
                          'img_width'=>$this->img_opt['width'], 'img_height'=>$this->img_opt['height']);
        }

        if($this->img_opt['width'] > $this->img_opt['height']) {
             $new_height = round($size * $this->img_opt['height']/$this->img_opt['width']);

             return array('width'=> $size, 'height'=> $new_height,
                          'img_width'=>$this->img_opt['width'], 'img_height'=>$this->img_opt['height']);

        } else {

            $width = round($this->img_opt['width'] * $size / $this->img_opt['height']);

            return array('width' => $width, 'height' => $size,
                         'img_width'=>$this->img_opt['width'], 'img_height'=>$this->img_opt['height']);
        }

    } // end func calculate_size

    /**
    * Calculate size for square image
    *
    * @access protected
    * @param int $size
    * @return mixed array | bool
    */
    protected function calculate_square_size($size) {

        $size = ceil($size);

        if(!is_numeric($size) or $size <= 0) {
            return false;
        }

        if ($this->img_opt['width'] >= $this->img_opt['height']) {

            $x = round ($this->img_opt['width'] / 2 - $this->img_opt['height'] / 2);

            $img_width = $this->img_opt['height'];
            $img_height = $this->img_opt['height'];

            return array('width'=>$size, 'height'=>$size, 'img_width'=>$img_width, 'img_height'=>$img_height, 'x2'=> $x);
        } else {

            $img_width = $this->img_opt['width'];
            $img_height = $this->img_opt['width'];

            return array('width'=>$size, 'height'=>$size, 'img_width'=>$img_width, 'img_height'=>$img_height);
        }
    } // end func calculate_size

    /**
    * Make image watermark
    *
    * @access public
    * @param string $w_image
    * @param bool $dynamic_watermark
    * @param mixed $pos
    * @param mixed $margin
    * @return bool
    */
    public function watermark_image($w_image, $dynamic_watermark = false, $pos = NULL, $margin = NULL) {

        if(empty($this->img_opt)) {
            return false;
        }

        $watermark = $this->create_resource($w_image);

        if (empty($watermark)) {
            return false;
        }

        if(!$margin or $pos == 'c') {
            $margin = 0;
        }

        if($dynamic_watermark == true) {
            $img_width = $this->img_opt['new_width'];
            $img_height = $this->img_opt['new_height'];
        } else {
            $img_width = $this->img_opt['width'];
            $img_height = $this->img_opt['height'];
        }

        /* Set watermark image position */
        switch($pos) {
            case 'c': // Center
               $start_width = (($img_width - $watermark['width']) / 2);
               $start_height = (($img_height - $watermark['height']) / 2);
               break;
            case 'tr': // Top Right
                $start_width = (($img_width - $watermark['width']) - $margin);
                $start_height = $margin;
               break;
            case 'br': // Bottom Right
                $start_width = (($img_width - $watermark['width']) - $margin);
                $start_height = (($img_height - ($watermark['height'] + $margin)));
               break;
            case 'tl': // Top Left
                $start_width  = $margin;
                $start_height = $margin;
               break;
            case 'bl': // Bottom Left
                $start_width = $margin;
                $start_height = (($img_height - ($watermark['height'] + $margin)));
               break;
            default: // Center
                $start_width = (($img_width - $watermark['width']) - $margin);
                $start_height = (($img_height - ($watermark['height'] + $margin)));
               break;
        }

        if($dynamic_watermark == true) {
            $img_res = $this->img_opt['result_resource'];
        } else {
            $img_res = $this->img_opt['resource'];
        }

        if ($this->img_opt['type'] == 'png') {
            $this->transparent_png($img_res);
        }

        if ($this->img_opt['type']=='gif') {
            $this->transparent_gif($img_res, $img_width, $img_height);
        }

        /* Copy watermark in image */
        imagecopy($img_res, $watermark['resource'], $start_width, $start_height, 0, 0, $watermark['width'], $watermark['height']);

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
    * @param bool $dynamic_watermark
    * @return bool
    */
    public function watermark_text($text, $font, $size = 10, $color = '000000', $pos = 'br', $margin = 0, $angle = 0, $dynamic_watermark = false) {

        if(empty($this->img_opt)) {
            return false;
        }

        if(!$this->lib->file->ext($font, 'ttf')) {
            return false;
        }

        if (is_numeric($angle) == false or is_numeric($margin) == false) {
            return false;
        }

        $box = imagettfbbox($size, $angle, $font, $text);

        $box_width =  $box[2] - $box[0];
        $box_height = $box[1] - $box[7];
        unset($box);

        if($dynamic_watermark == true) {
            $img_width = $this->img_opt['new_width'];
            $img_height = $this->img_opt['new_height'];
        } else {
            $img_width = $this->img_opt['width'];
            $img_height = $this->img_opt['height'];
        }

         /* Set watermark Text position */
        switch($pos) {
            case 'c': // CENTER
               $start_width =  (($img_width  - $box_width)/ 2);
               $start_height = (($img_height - $box_height)/ 2);
               break;
            case 'br': // Bottom Right
                $start_width  = ($img_width  - $box_width - $margin);
                $start_height = ($img_height - $margin);
               break;
            case 'tr': // Top Right
                $start_width  = ($img_width - $box_width - $margin);
                $start_height =  $box_height + $margin;
               break;
            case 'tl': // Top Left
                $start_width = $margin;
                $start_height = $margin + $box_height;
               break;
            case 'bl': // Bottom Left
                $start_width  = $margin;
                $start_height = ($img_height - $margin);
               break;
            default:
                $start_width  = ($img_width  - $box_width - $margin);
                $start_height = ($img_height - $margin);
               break;
        }

        if($dynamic_watermark == true) {
            $img_res = $this->img_opt['result_resource'];
        } else {
            $img_res = $this->img_opt['resource'];
        }

        $color = $this->get_color($color, $img_res);

        if(imagettftext($img_res, $size, $angle, $start_width, $start_height, $color, $font, $text)) {
            return true;
        }

        return true;

    } // end func watermark_text

    /**
    * Return image color
    *
    * @access protected
    * @param string $color
    * @param resource $resource
    * @return int
    */
    protected function get_color($color, $resource) {

        if(strlen($color) >= 7) {
            $color = substr($color, 1, 6);
        }

        sscanf($color, "%2x%2x%2x", $red, $green, $blue);

        return imagecolorallocate($resource, $red, $green, $blue);

    } // end func get_color

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
    * @param int $width
    * @param int $height
    * @return void
    */
    protected function transparent_gif($src_image, $width, $height) {

        $dst_image = imagecreatetruecolor($width, $height);
        $color_count = imagecolorstotal($src_image);

        if(!$color_count) {
            return false;
        }

        imagetruecolortopalette($dst_image, true, $color_count);
        imagepalettecopy($dst_image, $src_image);
        $transparent_color = imagecolortransparent($src_image);
        imagefill($dst_image, 0, 0, $transparent_color);
        imagecolortransparent($dst_image, $transparent_color);

    } // end func transparent_gif

   /**
    * Save image resource to file
    *
    * @access public
    * @param string $dest_file
    * @param bool $overwrite
    * @param bool $dynamic_save
    * @return bool
    */
    public function save($dest_file = NULL, $overwrite = false, $dynamic_save = false) {

        if(empty($this->img_opt)) {
            return false;
        }

        if($dest_file == NULL) {
            $dest_file = $this->img_opt['dest_file'];
        } elseif (is_dir($dest_file)) {
            $dest_file = $this->lib->file->to_path($dest_file);
            $dest_file = $dest_file . basename($this->img_opt['dest_file']);
        } else{

            if(!$this->lib->file->ext($dest_file, 'png|gif|jpg|jpeg')) {
                return false;
            }

        }

        if(is_file($dest_file) == true and $overwrite == false) {
            return false;
        }

        if($dynamic_save == true) {
            $resource = $this->img_opt['result_resource'];
        } else {
            $resource = $this->img_opt['resource'];
        }

        call_user_func('image'.strtolower($this->img_opt['type']), $resource, $dest_file);

        return true;

    } // end func save

   /**
    * output (show) image
    *
    * @access public
    * @param bool $output_name
    * @return void
    */
    public function output($output_name = false) {

        if(empty($this->img_opt)) {
            return false;
        }

        if ($output_name == false) {
            $output_name = basename($this->img_opt['dest_file']);
        } else {
            $output_name .= '.' . $this->img_opt['type'];
        }

        header('Content-type: ' . $this->img_opt['mime']);
        header('Content-Disposition: attachment; filename='.$output_name.';');
        call_user_func('image'.strtolower($this->img_opt['type']), $this->img_opt['resource']);

    } // end func output

    /**
     * Resize image
     *
     * @access public
     * @param array $size_options
     * @param string $destination
     * @param bool $random_name
     * @return sting
     */
    public function save_resized($size_options, $destination = NULL, $random_name = true) {

        if($destination != '') {
            $destination = $this->lib->file->to_path($destination);
        }

        $default_options = array(
            'crop_square' => false,
            'watermark' => false,
            'watermark_text' => false,
            'destination' => $destination
        );

        if($random_name == true) {
            $dest_file = $this->lib->file->uname() . '.' . $this->lib->file->ext($this->img_opt['dest_file']);
        } else {
            $dest_file = $this->img_opt['dest_file'];
        }

        foreach($size_options as $item) {

            $item = array_merge($default_options, $item);

            $this->do_resize($item['size'], $item['crop_square'], $item['watermark'], $item['watermark_text']);

            if($item['crop_square'] == true) {
                $save_file = $item['size'] . '_sq_' . $dest_file;
            } else {
                $save_file = $item['size'] . '_' . $dest_file;
            }

            $this->save($item['destination'] . $save_file, true, true);
        }

        return $dest_file;

    } // End func save_resized

}

/* End file image.lib.php */
?>