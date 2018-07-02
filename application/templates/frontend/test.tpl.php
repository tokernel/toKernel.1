<?php
/**
 * toKernel - Universal PHP Framework.
 * Test template file.
 *
 * @category   templates
 * @package    framework
 *
 * Restrict direct access to this file
 */
defined('TK_EXEC') or die('Restricted area.');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- some other comment -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Test template</title>
</head>
<body>
<h1>Test template</h1>
<p>This template loaded by name specified in addon's testing.</p>

<!-- This is the addon's action output defined as widget. -->
<!-- widget addon="__THIS__" -->
<!-- just a comment -->
<!-- widget addon="example" action="widget_with_params" params="a=1|b=2" -->
<!-- widget addon="example" module="views_example" action="view_simple" params="a=1|b=2|k=11" -->
<!-- widget addon="test" module="other_module" action="display_name" -->
<!-- widget addon="test" action="display_params" params="a=1|b=2" -->
<!-- widget addon="example" module="views_example" action="view_simple" -->
<!-- widget addon="test" module="test_module" action="display_params" params="ddd=222" -->
<!-- _widget addon="test" action="display_params" params="aaa=999" -->

<div>This is a with value {var.a}</div>
<div>This is a with value {var.b}</div>
<div>This is a with value {var.c}</div>
</body>
</html>