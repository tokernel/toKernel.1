<?php
/**
 * toKernel - Universal PHP Framework.
 * Photo file processing class library
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
 * @copyright  Copyright (c) 2015 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.1
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.1.3
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * photo_lib class
 * 
 * Display photos after resizing, cropping & sharpening them
 *  
 * @author Patrick Isbendjian <pi@pisbtech.com>
 * based on the "Smart Image Resizer" work by Joe Lencioni
 * http://www.cotonti.com/extensions/files-media/slir
 */
class photo_lib {

/**
 * Library object for working with 
 * libraries in this class
 * 
 * @var object
 * @access protected
 */ 
 protected $lib;

/**
 * Global rendering options
 * 
 * @var array
 * @access protected
 */
 static protected $default_opts;

 /**
  * Source image object
  *
  * @var array
  * @access private
  */
 private $source;
 
 /**
  * Requested options
  *
  * @var array
  * @access private
  */
  private $request;

 /**
  * Rendered image object
  *
  * @var array
  * @access private
  */
 private $rendered;
 
 /**
  * Cache directory
  *
  * @var string
  * @access private
  */
 private $cache_dir;

/**
 * Class constructor
 * 
 * @access public
 * @return void
 */
 public function __construct() {
 	
	$this->lib = lib::instance();
	
	/* Load default options */
	$ini_file = TK_CUSTOM_PATH . 'config' . TK_DS . 'photos.ini';
 	$lib_config = $this->lib->ini->instance($ini_file);
 
 	if(!is_object($lib_config)) {
 		trigger_error('Can not access to DEFAULT_CONFIG configuration values'.
 					  ' in file `' . $ini_file . '` !', E_USER_ERROR);
 	}

 	self::$default_opts = $lib_config->get_section('DEFAULT_CONFIG');
	
	/* Set cache dir path */
	$this->cache_dir = TK_CUSTOM_PATH . self::$default_opts['cache_dir'] . TK_DS;

	if(mt_rand(1, 1000) <= self::$default_opts['garbage_collect_prob']) {
		/*
		 Register this as a shutdown function so the additional 
		 processing time will not affect the speed of the request.
		 */
		register_shutdown_function(array($this, 'collect_garbage'));
	}
	
 } // End __construct
  
/**
 * Class destructor
 * 
 * @access public
 * @return void
 */
 public function __destruct() {
 
 	unset($this->source);
 	unset($this->rendered);
 	unset($this->request);
	
 } // end destructor

/**
 * Return clone of this object
 * 
 * @access public
 * @return object 
 */
 public function instance($config = NULL) {
 	
 	$new = clone $this;
 	$new->__destruct();
 	$new->configure($config);
	
	return $new;
	
 } // end func instance

/**
 * Set configuration parameters
 * 
 * @access private
 * @param array $options
 * @return bool
 */ 
 private function configure($config) {
 	
	if(is_array($config)) {
		self::$default_opts = array_merge(self::$default_opts, $config);
 	}
	
 	return true;
 	
 } // end func configure

 /**
 /* Render a photo using the requested settings
 /*
 /* @access public
 /* @param array $request
 /* @return handle to the rendered photo
 */
 public function render($request) {
 	
 	if(!$this->validate_request($request)) {
 		return false;
 	}
	
	/* Time to do the real rendering of the photo */
	ini_set('memory_limit', self::$default_opts['max_memory'] . 'M');
	if(!$this->get_source($this->request['source'])) {
		trigger_error("Can't access the requested photo at " . $this->request['source'], E_USER_ERROR);
	}
	
	/* 
	 If either a max width or max height are not specified or larger than
	 the source image we default to the dimension of the source image so
	 they do not become constraints on our resized image.
	 */
	if (!isset($this->request['width']) || $this->request['width'] 
			> $this->source['width']) {
		
		$this->request['width'] = $this->source['width'];
	}

	if (!isset($this->request['height']) ||  $this->request['height'] 
			> $this->source['height']) {
		
		$this->request['height'] = $this->source['height'];
	}

	/* Send original image back if there is no need to transform it */
	
	// no width change
	if(($this->request['width'] == $this->source['width']) &&					
		 // no height change
		($this->request['height'] == $this->source['height']) &&
		// no quality reduction
		($this->request['quality'] == 100) &&
		// no cropping requested
		($this->request['crop'] == 'none')){
		$this->serve_image($this->source, 'original');
		return true;
	}
	
	/*
	 Check if the request is cached and valid
	 If so, send it back to the user
	 */
	$image = $this->check_cache();
	if($image){
		$this->serve_image($image, 'cached');
		return true;
	}
	
	/* Photo should be rendered and the result sent back to the user */
	$this->create_rendered();
	$this->serve_image($this->rendered, 'fresh');
	
	return true;
	
 } // End func render

 /**
 /* Render a photo using the requested settings
 /*
 /* @access public
 /* @param array $request
 /* @return handle to the rendered photo
 */
 public function validate_request($request) {

	if(!function_exists('gd_info' )) {
 		trigger_error("GD support is not available for the 
						installed version of PHP.", E_USER_ERROR);
 		return false;
	}

	$gd = gd_info();
	
 	if((isset($gd["JPEG Support"]) && $gd["JPEG Support"] == false) || 
		(isset($gd["JPG Support"]) && $gd["JPG Support"] == false)) {
 		
		trigger_error("The GD library does not support JPEG images.", 
							E_USER_ERROR);
		
 		return false;
		
	} // validate_request

	/* OK, now let's check if the values for the request are meaningful */
	if(isset($request['width'])) {
		$request['width'] = intval($request['width']);
		if($request['width'] < 1) {
	 		trigger_error("The width of the image must 
							be strictly positive.", E_USER_ERROR);
	 		return false;
		}
	}
	
	if(isset($request['height'])) {
		$request['height'] = intval($request['height']);
		if($request['height'] < 1){
	 		trigger_error("The height of the image must 
							be strictly positive.", E_USER_ERROR);
	 		return false;
		}
	}
	
	if(isset($request['offset'])) {
		$request['offset'] = intval($request['offset']);
		if($request['offset'] < 0){
	 		trigger_error("The crop offset of the image must 
							be positive.", E_USER_ERROR);
	 		return false;
		}
	} else {
		$request['offset'] = 0;
	}
	
	if(isset($request['quality'])) {
		$request['quality'] = intval($request['quality']);
		if($request['quality'] < 0 || $request['quality'] > 100){
	 		trigger_error("The quality of the image must 
							be between 0 and 100.", E_USER_ERROR);
	 		return false;
		}
	} else {
		$request['quality'] = self::$default_opts['quality'];
	}
	
	if(!isset($request['crop_ratio']) || $request['crop_ratio'] == 0) {
		$request['crop'] = 'none';
	} else {
		$request['crop'] = 'custom';
	}
	
	$this->request = $request;
	
	return true;
	
 } // end func validate_request

/**
 * Load image before processing
 * 
 * @access protected
 * @param string $src_file
 * @return bool
 */
 protected function get_source($src_file) {
	
 	if(!is_readable($src_file)) {
		return false;
	}

	if(empty($this->source)) {
		
		$this->source['path'] = $src_file;
		$size = getimagesize($this->source['path'], $extra);
		
		if(!$size){
			return false;
		} else {
			$this->source['data'] = '';
			$this->source['width'] = $size[0];
			$this->source['height'] = $size[1];
			$this->source['imgtype'] = $size[2];
			$this->source['mime'] = $size['mime'];
			$this->source['extra'] = $extra;
			$this->source['ratio'] = $size[0]/$size[1];
			return true;
		}
	}
	
	return true;
	
 } // end func get_source

/**
 * Serve image
 * 
 * @access protected
 * @param string $img
 * @return bool
 */
 protected function serve_image($img, $origin) {
	 
 	if(empty($img['path'])){
 		ob_start();
 		imagejpeg($img['img'], null, $this->request['quality']);
 		$img['data'] = ob_get_clean();
 		$length = strlen($img['data']);
 		$lastmod = gmdate('D, d M Y H:i:s');
 		// Write the image in the cache
 		$path = $this->gen_cached_name();
 		file_put_contents($path, $img['data']); 		
 	} else {
	 	$length = filesize($img['path']);
	 	$lastmod = gmdate('D, d M Y H:i:s', filemtime($img['path']));
	}
	
 	$mime = $img['mime'];
 	header("Last-Modified: $lastmod GMT");
	header("Content-Type: $mime");
	header("Content-Length: $length");
  
	/*
	 Lets us easily know whether the image was rendered from scratch,
	 from the cache, or served directly from the source image 
	 */
	header("X-Content-Orig: $origin");

	/* Keep in browser cache how long? */
	header(sprintf('Expires: %s GMT', gmdate('D, d M Y H:i:s', 
					time() + self::$default_opts['cache_ttl'])));
	
	/*
	Public in the Cache-Control lets proxies know that it is okay to
	cache this content. If this is being served over HTTPS, there may be
	sensitive content and therefore should probably not be cached by
	proxy servers.
	*/
	header(sprintf('Cache-Control: max-age=%d, public', 
					self::$default_opts['cache_ttl']));

	if(empty($img['path'])){
		echo $img['data'];
	} else {
	 	readfile($img['path']);
	}
  
 } // end func serve_image

/**
 * Create rendered image
 * 
 * @access protected
 * @param  none
 * @return array 
 */ 
 protected function create_rendered() {
 	
	// Load the source file
	switch($this->source['imgtype']){
		case IMAGETYPE_JPEG:
			$img = imagecreatefromjpeg($this->source['path']);
			break;
		case IMAGETYPE_PNG:
			$img = imagecreatefrompng($this->source['path']);
			break;
		case IMAGETYPE_GIF:
			$img = imagecreatefromgif($this->source['path']);
			break;
	}
	
	// Step 1: crop the image to the desired ratio
	if($this->request['crop'] != 'none' && 
						$this->request['crop_ratio'] > $this->source['ratio']) {
		
		// Image is too tall, so we will crop vertically
		$this->source['crop_height'] = $this->source['width'] 
										/ $this->request['crop_ratio'];
		
		$this->source['crop_width'] = $this->source['width'];
		$off_w = 0;
		
		$off_h = min($this->request['offset'], $this->source['height'] 
										- $this->source['crop_height']);
		
	} elseif($this->request['crop'] != 'none' && 
						$this->request['crop_ratio'] < $this->source['ratio']) {
		
		// Image is too wide, so we will crop horizontally
		$this->source['crop_width'] = $this->source['height'] 
										* $this->request['crop_ratio'];

		$this->source['crop_height'] = $this->source['height'];
		
		$off_w = min($this->request['offset'], $this->source['width'] 
										- $this->source['crop_width']);
		$off_h = 0;
		
	} else {
		
		// nothing to crop, we already have the desired ratio
		$this->source['crop_width'] = $this->source['width'];
		$this->source['crop_height'] = $this->source['height'];
		$off_w = 0;
		$off_h = 0;
	}
	
	$cropped = imagecreatetruecolor($this->source['crop_width'], 
												$this->source['crop_height']);
	
	imagecopy($cropped, $img, 0, 0, $off_w, $off_h, $this->source['crop_width'], 
												$this->source['crop_height']);
	
	imagedestroy($img); // free up memory
	unset($img);
	
	/* 
	 Step 2: resize the image to the desired size, i.e. 
	 width & height < requested width & height
	 */
	if($this->source['crop_width'] <= $this->request['width'] && 
					$this->source['crop_height'] > $this->request['height']) {

		/* Image too tall, force the height & adjust width */
		$this->rendered['width'] = $this->source['crop_width'] 
					* $this->request['height'] / $this->source['crop_height'];
		
		$this->rendered['height'] = $this->request['height'];
		
	} elseif($this->source['crop_width'] > $this->request['width'] && 
					$this->source['crop_height'] <= $this->request['height']) {
		
		/* Image is too wide, force the width & adjust height */
		$this->rendered['width'] = $this->request['width'];
		
		$this->rendered['height'] = $this->source['crop_height'] * 
						$this->request['width'] / $this->source['crop_width'];

	} elseif($this->source['crop_width'] > $this->request['width'] && 
					$this->source['crop_height'] > $this->request['height']) {
		
		/* Image is both to tall and too wide */
		$f = min($this->request['width'] / $this->source['crop_width'], 
					$this->request['height'] / $this->source['crop_height']);
		
		$this->rendered['width'] = $f * $this->source['crop_width'];
		$this->rendered['height'] = $f * $this->source['crop_height'];
		
	} else {
		
		/* 
		 Image is too small in both dimensions: keep the cropped image as it is.
		 */
		$this->rendered['width'] = $this->source['crop_width'];
		$this->rendered['height'] = $this->source['crop_height'];
	}
	
	$this->rendered['img'] = imagecreatetruecolor($this->rendered['width'],
												$this->rendered['height']);
	
	imagecopyresampled($this->rendered['img'],
						$cropped,
						0, 0,
						0, 0,
						$this->rendered['width'], $this->rendered['height'],
						$this->source['crop_width'], $this->source['crop_height']
	);
	
	imagedestroy($cropped); // free up memory
	unset($cropped);
	
	/* Step 3: sharpen the image to produce optimal results */
	$src_area = $this->source['crop_width'] * $this->source['crop_height'];
	$dst_area = $this->rendered['width'] * $this->rendered['height'];
	$sharpness = $this->calculate_sharpness($src_area, $dst_area);
	$sharpen_matrix =  array(
							array(-1, -2, -1),
							array(-2, $sharpness + 12, -2),
							array(-1, -2, -1)
						);
	
	imageconvolution($this->rendered['img'],
						$sharpen_matrix,
						$sharpness,
						0
	);
	
	$this->rendered['path'] = '';
	$this->rendered['imgtype'] = IMAGETYPE_JPEG;
	$this->rendered['mime'] = image_type_to_mime_type(IMAGETYPE_JPEG);
	
	return true;
	
 } // end func create_rendered

/**
 * Calculates sharpness factor to be used to sharpen an image based on the
 * area of the source image and the area of the destination image
 *
 * @param integer $source_area			Area of source image
 * @param integer $destination_area	Area of destination image
 * @return integer Sharpness factor
 */
 private function calculate_sharpness($source_area, $destination_area){
	
	$final  = sqrt($destination_area) * (750.0 / sqrt($source_area));
	$a = 52;
	$b = -0.27810650887573124;
	$c = .00047337278106508946;

	$result = $a + $b * $final + $c * $final * $final;

	return max(round($result), 0);
	
 } // End func calculate_sharpness

/**
 * Check if photo is cached and valid
 * 
 * @access protected
 * @param  none
 * @return array 
 */ 
 protected function check_cache(){
 	
	// Get the file name in the cache
 	$path = $this->gen_cached_name();
 	
	if(!file_exists($path) || !is_readable($path)) {
 		return false;
 	}
	
 	if(filemtime($path) < filemtime($this->source['path'])) {
 		return false;
 	}
 	
	$cached = array('path', 'mime');
 	$cached['path'] = $path;
 	$size = getimagesize($path);
 	$cached['mime'] = $size['mime'];
 	
 	return $cached;

 } // End func check_cache

/**
 * Generate a name for caching
 * 
 * @access protected
 * @param  none
 * @return array 
 */ 
 protected function gen_cached_name(){
 	
	// generate the cached file name based on request params
 	$infos = array(
 		$this->source['path'],
 		$this->request['width'],
 		$this->request['height'],
 		$this->request['crop_ratio'],
 		$this->request['offset'],
 		$this->request['quality']
 	);
 	
 	$path = $this->cache_dir . hash('md4', serialize($infos));
 	
	return $path;
	
 } // end func gen_cached_name

/**
 * Callback function for Cache clean-up
 * If the random pick when imitializing the lib was positive
 * then this callback function was registered to run at shutdown time
 * and here we are!
 * 
 * @return void
 */
 public function collect_garbage() {
  
	/* Shut down the connection so the user can go about his or her business */
	header('Connection: close');
	ignore_user_abort(true);
	flush();

	$garb_collect_lock = $this->cache_dir . 'garb_collect.tmp';
	
	/* Check if the garbage collection is currently running */
	if(file_exists($garb_collect_lock) && filemtime($garb_collect_lock) 
													> time() - 7200) {
		return true;
	}
	
	/*
	If the file is more than 2 hours old, something 
	probably went wrong and we should run again anyway
	*/
	
	/* Create the lock file for garbage collection */
	touch($garb_collect_lock);
	
	$expiration  = time() - self::$default_opts['garbage_collect_ttl'];

	$log_f = $this->lib->log->instance('photo_lib.log');
	$log_f->write("Photo cache garbage collection started");
	
	$dir = $this->lib->file->ls($this->cache_dir);
	$cnt = 0;
	foreach($dir as $file){
		
		/* file hasn't been accessed since Cache Expiration Time */
		if(fileatime($this->cache_dir . $file) < $expiration) {
			unlink($this->cache_dir . $file);
			$cnt++;
		}
	}

	/* Delete the lock file for garbage collection */
	unlink($garb_collect_lock);
	unset($dir);
	$log_f->write("Photo cache garbage collection complete. $cnt photos were erased.");
	
 } // End func collect_garbage
 
}
/* End file photo.lib.php */
?>