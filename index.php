<?php
/**
 * toKernel - Universal PHP Framework.
 * Main index.php file for application.
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
 * @category   main
 * @package    framework
 * @subpackage loader
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/**
 * Define application path.
 */
define('TK_APP_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR); 

/*
 * Define application directory name (without directory separator), 
 * where customizable components are placed. 
 */
define('TK_CUSTOM_DIR', 'application');

/* Change current directory path to app path */
chdir(TK_APP_PATH);

/* Include framework loader */
require("tokernel.framework/tokernel.inc.php");

/* End of file */
?>