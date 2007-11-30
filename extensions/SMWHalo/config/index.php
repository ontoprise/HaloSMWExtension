<html>
<head>
	<script type="text/javascript" src="index.js"></script>
</head>
<body>
	<h1>SMW Halo configuration page</h1>
	<form name="opt_params">
	<h2>Required configurations</h2>
	<p style="margin-left: 20px">
		Location of PHP-Interpreter: <input name="phpInterpreter" type="file" size="70"/>
	</p>
	<h2>Optional configurations</h2>
	<p style="margin-left: 20px">
	
		Gardening Bot delay: <input id="gbd" type="text" size="10" value="100"/> in ms<br><br>
		Enable semantic Auto-completion: <input id="ac" type="checkbox"/><br><br>
		Enable deploy version: <input id="ed" type="checkbox" checked/> (recommend)<br><br>
		Enable logging: <input id="el" type="checkbox"/> (not recommend)<br><br>
		Use standard collation:  <input id="sc" type="checkbox" checked/> (recommend) <input id="collation" type="text" size="15" value="latin1_bin" disabled/><br><br>
		Keep gardening console:  <input id="kgc" type="checkbox"/> (not recommend) <br><br>
		<input id="update" type="submit" value="Update LocalSettings"/> 
	
	</p>
	</form>
	<?php
	   // PHP code which modifies LocalSettings.php
	   
	?>
</body>

</html>