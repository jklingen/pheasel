<?php
/**
 * Custom 500 page, feel free to change it to fit your needs.
 * Note: by default the error stack trace is only displayed in DEV mode, not to scare off visitors.
 */
?>
<html>
<head>
	<ph:config id="500" name="Internal Server Error" />
    <title>Internal Server Error</title>
    <meta name="robots" content="noindex,follow"/>
</head>
<body>
<h1>Oooops - something went wrong :(</h1>
<p>Sorry, this should not have happened. If the problem persists, please let us know.</p>
<?php
if(PHEASEL_ENVIRONMENT==PHEASEL_ENVIRONMENT_DEV && isset(PageInfo::$current->data['exception'])) {
    $ex = PageInfo::$current->data['exception'];
    echo "<pre>\n";
    echo  $ex->getMessage() . "\n";
    echo  $ex->getTraceAsString() . "\n";
    echo "</pre>\n";
}
?>
</body>
</html>