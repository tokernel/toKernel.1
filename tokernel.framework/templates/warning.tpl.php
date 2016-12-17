<?php 
/**
 * toKernel - Universal PHP Framework.
 * Warning/Notice template file.
 *
 * @category   templates
 * @package    framework
 *
 * Note: All variables in this file defined in tk_e::show_error() method.
 * file - tokernel/path/kernel/error.http.class.php
 * 
 * Restrict direct access to this file
 */
defined('TK_EXEC') or die('Restricted area.');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $err_type ?></title>
<style>
body {
	background-color:#F5F5F5;
	padding:0px;
}
.main_div {
	margin-top:20px;
	border:#D4D4D4 solid 1px; 
	background-color:#FFFFFF;
	padding:20px;
}
.error_div {
	border-top:2px solid #FFCC33; 
	border-bottom:2px solid #FFCC33; 
	background-color: #FFFFCC; 
	font-weight:bold;
	padding-left:10px;
	padding-right:10px;
	font-family:Arial, Helvetica, sans-serif;
}
.signature {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
}
.signature:link {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
	text-decoration:none;
}
.signature:active {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
	text-decoration:none;
	color:#000000;
}
.signature:visited {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
	text-decoration:none;
	color:#000000;
}
.signature:hover {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size:14px;
	text-decoration:underline;
	color:#000000;
}
</style>
</head>
<body>
<div align="center">
<div align="left" class="main_div">
<h1><?php echo $err_type ?></h1>
<div class="error_div">
<p><?php echo $err_show_str; ?></p>
<p>
<?php 

if(is_array($trace)) {
	
	$err_show_str = '';
	$err_show_str .= '[Debug trace]';
	$err_show_str .= TK_NL;
	echo $err_show_str;
	
	foreach($trace as $i => $t) {
		
		if($t['function'] == 'trigger_error') {
			break;
		}
		if(!isset($t['class'])) {
			$t['class'] = '';
		}
		if(!isset($t['type'])) {
			$t['type'] = '';
		}
		if(!isset($t['file'])) {
			$t['file'] = '';
		}
		if(!isset($t['line'])) {
			$t['line'] = '';
		}
		
		$message = sprintf("#%d %s%s%s() called at %s:%d \n", $i,$t['class'], $t['type'],$t['function'],$t['file'],$t['line']);
		
		echo $message . TK_NL;
	}
	
}	
?>
</p>
</div>
<p>&nbsp;</p>
</div>
<p class="signature"><a href="http://www.tokernel.com" class="signature">toKernel - Universal PHP Framework. v<?php echo TK_VERSION; ?></a></p>
</div>
</body>
</html>