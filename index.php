<!DOCTYPE html>
<html>
<head></head>
<body>
<h1>Nearly there...</h1>
<p>... just one thing: your web server is not configured to allow URL rewriting yet, which is an essential part of PHeasel. That is why you are just looking at this boring page instead of a fancy welcome page.</p>
<?php 
$mod_rewrite_found = false;
$mods = apache_get_modules(); 
for($i=0; $i<count($mods); $i++) {
	if($mods[$i]=='mod_rewrite') {
		$mod_rewrite_found = true;
		break;
	}
}
if($mod_rewrite_found) { ?>
<p><strong>mod_rewrite</strong> is installed and enabled, which is fine.<br>
But you probably have to make sure that <strong>AllowOverride</strong> is set to <strong>All</strong></p>
<?php } else { ?>
<p>Please make sure that <strong>mod_rewrite</strong> is enabled.</p>
<?php } ?>
<p>Thanks!</p>
<p>P.S. PHeasel is awaiting you eagerly &lt;3</p>

</body>
</html> 
