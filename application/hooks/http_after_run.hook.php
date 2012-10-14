<?php 
/**
 * toKernel - Universal PHP Framework.
 * Hook File for HTTP mode which will be run after main 
 * addon call. 
 * 
 * NOTE: Make sure that the option 'allow_http_hooks' 
 * defined as 1 in application configuration file.
 * 
 * NOTE: It is not possible to output any data in this stage.
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
 * @category   application
 * @package    toKernel
 * @subpackage hooks
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2012 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/*
 * Accessible objects.
 * 
 * $this->app
 * $this->lib
 * 
 * Examples:
 * 
 * $some_var = $this->lib->filter->clean_data('some_data');
 * $dt = $this->app->config('date_timezone', 'APPLICATION');
 */ 
 
 /* ... Hook code here ... */

/* End of file */
?>