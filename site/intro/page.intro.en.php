<ph:config
id=   pheaselintro
url=  /
name= Intro
>
<!DOCTYPE html>
<html>
<head>
    <?php
    require_once(PHEASEL_ROOT . '/pheasel/core/classes/PrerequisitesCheck.php');
    $pc = new PrerequisitesCheck();
    ?>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="<ph:resource url=/pheasel/core/resources/favicon.ico>"/>
    <link rel="stylesheet" href="<ph:resource url=/pheasel/core/resources/intro.css>"/>
    <title>PHeasel is up and running, yeah.</title>
</head>

<body id="start">
<div class="top"></div>
<div class="bottom">
<?php
if($pc->has_problems()) { ?>
    <h1>Ooops!</h1>
    <h2>Nearly there... please check your config</h2>
    <p>
        <? echo $pc->get_problem_list() ?>
    </p>
<?php } else { ?>
    <h1>Yay!</h1>
    <h2>PHeasel is up and running,<br/>ready for you to get started.</h2>
    <img src="{ph:resource url=/pheasel/core/resources/pheasel-logo.png/}">
    <p>
        You should have a look at our <a href="http://pheasel.org/getting-started/?ref=intro">getting started guide</a> first, if you haven't yet.<br/>
        Consult the <a href="http://pheasel.org/reference/?ref=intro">online reference</a> for more detailed information.
    </p>
    <p>
        Find the local working directory for your site at <code><? echo PHEASEL_PAGES_DIR?></code><br/>
        Be sure to have a look at the <a href="{ph:url pageid=examples}">examples</a>, you can find the markup files in the <code>examples</code> subdirectory.
    </p>
    <p>Thanks for using PHeasel - have fun.</p>
<?php } ?>
</div>





</body>
</html>