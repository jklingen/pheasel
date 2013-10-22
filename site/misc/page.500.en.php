<?php
/**
 * Custom 500 page, feel free to change it to fit your needs.
 *
 * Note (1): by default the error stack trace is only displayed in DEV mode, not to scare off visitors.
 *
 * Note (2): When exporting as HTML or PHP, this page will be exported e.g. to 500.html or 500.php, respectively.
 * But that does not mean that it will automatically be used when an internal server error occurs.
 *
 * If your web server has mod_rewrite enabled you need to add an .htaccess file specifying
 * ErrorDocument 500 /500.html
 *
 * If it does not, please get in touch with your hoster, there are probably has file name conventions for 500 pages,
 * e.g. that they must be called 500.html.
 *
 * Note (3): when used as described in (2), you might see broken links or references to javascript/stylesheets on
 * this page. If this is the case, please edit pheasel-config.php and insert a value for PHEASEL_CONTEXT_PATH, so
 * that the RequestHandler can insert references relative to the webserver root.
 */
?>
<!DOCTYPE html>
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