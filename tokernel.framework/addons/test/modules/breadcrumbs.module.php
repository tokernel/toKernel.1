<?php

 /**
  * Breadcrumbs module
  */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

 class breadcrumbs_module extends module {

 private $bc = array();
 
 public function __construct($attr, $id_addon, $config, $log, $language) {
	parent::__construct($attr, $id_addon, $config, $log, $language);
 }

 public function add($label, $url = NULL) {
	$this->bc[$label] = $url;
 }
 
 public function remove($label) {
	if(isset($this->bc[$label])) {
		unset($this->bc[$label]);
		return true;
	} else {
		return false;
	}
 }
 
 public function run() {
	
	$buffer = '';
	
	foreach($this->bc as $label => $url) {
		
		if($url != '') {
			$buffer .= '<a href="'.$url.'">' . $label . '</a>';
		} else {
			$buffer .= $label;
		}
		
	}
	
	return $buffer;
	
 }

}
?>