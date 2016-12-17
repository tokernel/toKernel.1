<?php
/**
 * toKernel - Universal PHP Framework.
 * Class for compressing content, such as javascript and css
 *
 * - Remove comments
 * - Remove more than one whitespaces
 * - Remove tabs, new lines
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
 * @version    1.1.4
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.6.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * compress_lib class
 *
 * @author David A. <tokernel@gmail.com>
 */
class compress_lib {

	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;

	/**
	 * File extension allowed to compress
	 *
	 * @access protected
	 * @var array
	 */
	protected $file_types = array('js', 'css');

	/**
     * Class constructor
	 *
	 * @access public
	 * @return void
	 */
	public function  __construct() {
		$this->lib = lib::instance();
	}

	/**
	 * Compress javascript content
     *
     * Due to an issues with preg_replace() function in WinX OS,
     * which causes segmentation fault, this is method compresses javascript code more accurate.
	 *
	 * @access public
	 * @param string $buffer
	 * @return string
	 */
	public function javascript($buffer) {

        // Temporary replace HTTP:// and HTTPS:// with other chars
        $buffer = str_replace('http://', 'http:@@', $buffer);
        $buffer = str_replace('https://', 'http:^^', $buffer);

        // Remove comment blocks
        while($clean_buffer = $this->remove_first_js_comments_block($buffer)) {
            $buffer = $clean_buffer;
        }

        $lines = explode("\n", $buffer);

        if(empty($lines)) {
            return '';
        }

        $new_buffer = '';

        // Remove line comments
        foreach($lines as $line) {

            $com_pos = strpos($line, "//");

            if($com_pos !== false) {
                $line = substr($line, 0, $com_pos);
            }

            $line = trim($line);

            if($line != '') {
                $new_buffer .= $line;
            }

        }

        $buffer = $new_buffer;

        // Remove tabs, new lines
        $buffer = str_replace("\t", '', $buffer);
        $buffer = str_replace(' = ', '=', $buffer);

        // Restore HTTP:// and HTTPS://
        $buffer = str_replace('http:@@', 'http://', $buffer);
        $buffer = str_replace('http:^^', 'https://', $buffer);

		return $buffer;

	} // End func javascript

    /**
     * Remove first Javascript comment block
     *
     * @param string $buffer
     * @return mixed bool | string
     */
    protected function remove_first_js_comments_block($buffer) {

        $first_pos = strpos($buffer, '/*');

        if($first_pos === false) {
            return false;
        }

        $next_pos = strpos($buffer, '*/');

        if($next_pos === false) {
            return false;
        }

        $next_pos = $next_pos - $first_pos;
        $next_pos += 2;

        $str_to_replace = substr($buffer, $first_pos, $next_pos);

        $buffer = str_replace($str_to_replace, '', $buffer);

        return $buffer;

    } // End func remove_first_js_comments_block

	/**
	 * Compress css content
	 *
	 * @access public
	 * @param string $buffer
	 * @return string
	 */
	public function css($buffer) {

		/* remove comments */
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

		/* remove tabs, spaces, new lines, etc. */
		$buffer = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $buffer);

		/* remove other spaces before/after ; */
		$buffer = preg_replace(array('(( )+{)','({( )+)'), '{', $buffer);
		$buffer = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $buffer);
		$buffer = preg_replace(array('(;( )+)','(( )+;)'), ';', $buffer);

		return $buffer;

	} // End func css

	/**
	 * Compress file
	 * Type will detected automatically
	 * Compress and save file if destination file set.
	 * Return compressed file content if destination file not set.
	 *
	 * @access public
	 * @param string $source_file
	 * @param mixed $destination_file
	 * @return mixed
	 */
	public function file($source_file, $destination_file = NULL) {

        // Detect type
        $type = $this->lib->file->ext(basename($source_file));

		$content = '';

		if(substr($source_file, 0, 2) == '//' or substr($source_file, 0, 4) == 'http') {
			$content .= $this->remote_file($source_file);
		} else {

			// Check if type allowed
			if(!in_array($type, $this->file_types)) {
				trigger_error('Invalid file type: ' . $type . '(File: ' . $source_file . ')', E_USER_ERROR);
			}

			// Check file
			if(!is_readable($source_file) or !is_file($source_file)) {
				trigger_error("File: " . $source_file . " doesn't exists!", E_USER_ERROR);
			}

			// Load content
			$content .= $this->lib->file->read($source_file);

		}

		// Compress by type
		if($type == 'js') {
			$content = $this->javascript($content);
		}

		if($type == 'css') {
			$content = $this->css($content);
		}

		// Save to file if specified
		if(!is_null($destination_file)) {
			$this->lib->file->write($destination_file, $content);
			return true;
		}

		// Return content
		return $content;

	} // End func file

	/**
	 * Build combined files content from batch.
	 * Save combined content to destination file if specified.
	 * Return combined content if destination file not specified.
	 *
	 * $source_files associative array should be defined as:
	 *
	 * array(
	 *     'filename1.js' => true // means compress, than combine
	 *     'filename1.js' => false // means just combine the file without compression
	 * )
	 *
	 * @access public
	 * @param array $source_files
	 * @param mixed $destination_file = NULL
	 * @return mixed
	 */
	public function files($source_files, $destination_file = NULL) {

		// Check if array not empty
		if(empty($source_files)) {
			trigger_error("Empty files list!", E_USER_ERROR);
		}

		$content = '';

		// Build/combine content with all files
		foreach($source_files as $source_file => $do_compress) {

			if($do_compress == true) {
				$content .= $this->file($source_file);
			} else {

                $content .= "\n";

				if(substr($source_file, 0, 2) == '//' or substr($source_file, 0, 4) == 'http') {
					$content .= $this->remote_file($source_file);
				} else {
					$content .= $this->lib->file->read($source_file);
				}

			}

		} // End foreach

		// Return content, if destination file not specified
		if(is_null($destination_file)) {
			return $content;
		}

		// Save file
		$this->lib->file->write($destination_file, $content);

		// Return file name
		return $destination_file;

	} // End func files

	/**
	 * Get remote file to process
	 *
	 * @access protected
	 * @param string $url
	 * @return mixed sting | false
	 * @since 1.1.0
	 */
	protected function remote_file($url) {

		$ch = curl_init();
		$timeout = 5;

		$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);

		return $data;

	} // End func remote_file

} // End class compress_lib

// End of file
?>
