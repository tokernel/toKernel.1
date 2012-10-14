<?php 
/*
 * toKernel - Universal PHP Framework.
 * Debug information box for http mode.
 * 
 * Note: All variables in this file defined in tk_e::show_debug_info() method.
 * file - tokernel/path/kernel/error.http.class.php
 * 
 * Restrict direct access to this file
 */
defined('TK_EXEC') or die('Restricted area.');
?>
<style>
._debug_main {
	background-color:#F5F5F5;
	padding:0px;
	width:100%;
	border-top:#D4D4D4 solid 1px;
	margin-top:10px; 
	height:auto;
	float:left;
}

._debug_main h1 {
	font-family:Arial, Helvetica, sans-serif;
	font-size:16px;
	font-weight:bold;
	margin-left:10px;
	margin-top:10px;
}

#_debug_info_box {
	float:left;
	margin:10px;
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
}
._debug_screen {
	height:300px;
	overflow:scroll;
	border:#D4D4D4 solid 1px;
	padding-right:10px;
}
</style>
<div style="height:1px;clear:both; display:block;"></div>
<div style="margin:0px auto 0px auto;">
 <div class="_debug_main">
  <h1>toKernel - Debug information</h1>
	<div id="_debug_info_box">
	 <p><strong>Runtime Duration</strong><br />
	  <?php echo TK_RUN_DURATION; ?> seconds
	 </p>
	 <p><strong>Memory usage</strong><br />
	  <?php echo $memory_usage; ?> kb
	 </p>
	 <p><strong>Application run mode</strong><br />
	 <?php echo $app_mode; ?>
	 </p>
	 <p><strong>Hooks enabled</strong><br />
	 <?php echo $allow_hooks; ?>
	 </p>
	 <p><strong>Cache</strong><br />
	  <?php 
		echo $cache_mode;
		?><br />
		Output from cache: 
		<?php 
		echo $output_from_cache;
		?>
	  </p>
	</div>
	<div id="_debug_info_box">	
	 <p><strong>Called addon, action </strong><br />
	  <?php echo $addon_action_info; ?> 
		</p>
		<p><strong>Parameters: <?php echo $app_params_count; ?></strong><br />
		<?php 
		echo $app_params; 
	    ?>
	    </p>
		<p><strong>Language</strong><br />
		 <?php echo $app_language; ?>
		</p>
	</div>
	<div id="_debug_info_box">
	 <p><strong>Loaded libraries</strong><br />
	  <?php echo $loaded_libs; ?>
	 </p>
	</div>
	<div id="_debug_info_box">	
	 <p><strong>Loaded addons</strong><br />
	 <?php echo $loaded_addons; ?>
	 </p>
	</div>
	<div id="_debug_info_box">	
	 <p><strong>Benchmark dump</strong></p>
	 <?php 
	  echo $benchmark_dump;
	 ?>
	</div>
	 <?php if(!is_null($debug_buffer_str)) { ?>
	<div style="height:1px;clear:both; display:block;"></div>
	<div id="_debug_info_box">
	<h1>Debug screen</h1>
	 <div class="_debug_screen">
	<?php 
	  echo $debug_buffer_str;
	 ?>
	 </div>
	</div>
	<?php } ?>
	<div style="height:1px;clear:both; display:block;"></div>
  </div>
</div>