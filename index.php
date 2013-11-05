<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="pheasel/core/resources/favicon.ico"/>
    <link rel="stylesheet" href="pheasel/core/resources/intro.css"/>
    <title>PHeasel is up and running, yeah.</title>
</head>

<body id="start">
<div class="top"></div>
<div class="bottom">
    <h1>Ooops!</h1>
    <h2>Nearly there... please check your config</h2>
    <p>
<?php
define('PHEASEL_ROOT', realpath(getcwd()));
define('PHEASEL_EXPORT_DIR', realpath(getcwd(). DIRECTORY_SEPARATOR . "site-export"));
require_once('pheasel/core/classes/PrerequisitesCheck.php');
$pc = new PrerequisitesCheck();

if($pc->has_problems()) {
echo $pc->get_problem_list();
} ?>
</div>
</body>
</html>

