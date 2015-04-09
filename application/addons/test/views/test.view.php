<h1>
	<?php echo $this->message; ?>
</h1>
<p>
	<?php echo TK_SHORT_NAME; ?> <?php echo TK_DESCRIPTION; ?> <br />Version <?php echo TK_VERSION; ?>
</p>
<p>
	<a href="<?php echo $this->lib->url->url(true, 'download'); ?>">Project description</a>
</p>
<h2>Test for photo lib.</h2>
<img src="<?php echo $this->lib->url->url('test', 'photo', array('pic' => 'yerevan.jpg', 'w' => 200, 'h' => 200, 'q' => 100, 'c' => '50:50', 'o' => 50), true); ?>" alt="Yerevan" />
