<?php
/**
 * toKernel - Universal PHP Framework.
 * Example template file.
 *
 * @category   templates
 * @package    framework
 *
 * Restrict direct access to this file
 */
defined('TK_EXEC') or die('Restricted area.');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>My template with widgets</title>
</head>
<body>
    <h1>My template with widgets</h1>
    <p>This template loaded by name specified in addon's action method.</p>
    <p>This template contains widgets with demonstration of usage.</p>

    <!-- This is the addon's action output defined as widget. -->
    <!-- widget addon="__THIS__" -->

    <!-- Display widget without parameters -->
    <!-- widget addon="example" action="widget_without_params" -->

    <!-- Display widget with parameters -->
    <!-- widget addon="example" action="widget_with_params" params="project=My Project|version=1.0.0 alpha" -->

    <div>
        Framework: {var.name} {var.version}
    </div>
</body>
</html>