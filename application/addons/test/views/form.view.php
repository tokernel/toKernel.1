<h1>
	<?php echo $this->message; ?>
</h1>
<form method="post" name="test_form">
<div>
	<label>Name</label><br />
	<input type="text" name="name" id="name" />
</div>	
<div>
	<label>Message</label><br />
	<textarea name="message" id="message"></textarea>
</div>	
<div>
	<input type="submit" name="submit_data" id="submit_data" value=" Submit" />
</div>
</form>