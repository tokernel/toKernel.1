<h1>View file with values</h1>
<p>Example content of view file with values set by addon's action.</p>
<p>
    <!-- First option to get value set by action -->
    Project name: <?php echo $this->project_name; ?><br />
    <!-- Second option to get value set by action -->
    Project version: {var.project_version}<br />
</p>