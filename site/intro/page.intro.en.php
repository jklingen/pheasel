${config
id=   pheaselintro
url=  /
name= Intro
}$
<html>
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="${resource url=/pheasel/core/resources/favicon.ico}$"/>
    <title>PHeasel is up and running, yeah.</title>
    <style>
        body {
            font-family:Helvetica, Arial, sans-serif;
            position:relative;
            width:100%;
            background-color: #fffaee;;
        }
        div.top {
            margin-top:13.25%;
            height:28.15%;
            width:100%;
            background-color:#530;
        }
        div.bottom {
            position:relative;
            margin:auto;
            width:42em;
        }
        h1 {
            position:absolute;
            top:-1.5em;
            right:0;
            margin:0;
            font-family: Georgia, serif;
            color:#FFFAEE;
            font-size:5.5em;
            font-weight:normal;
        }
        h2 {
            text-align:right;
            font-size:2.1em;
            margin:0.6em 0 1em 0;
            font-family: Georgia, serif;
            font-weight:normal;
        }
        div.bottom img {
            position:absolute;
            top:-7em;
            font-family: Georgia, serif;
        }
        p {
            margin-top:1em;
            font-size:1em;
            line-height:1.5em;
        }
        .bottom a {
            color:#070;
            text-decoration:none;
        }
        .bottom a:hover {
            text-decoration:underline;
        }
        .bottom a:before {
            content: '\00BB\202F';
        }
    </style>
</head>
<body id="start">
<div class="top"></div>
<div class="bottom">
    <h1>Yay!</h1>
    <h2>PHeasel is up and running,<br/>ready for you to get started.</h2>
    <img src="${resource url=/pheasel/core/resources/pheasel-logo.png}$">
    <p>
        You should have a look at our <a href="http://pheasel.org/getting-started/?ref=intro">getting started guide</a> first, if you haven't yet.<br/>
        Consult the <a href="http://pheasel.org/reference/?ref=intro">online reference</a> for more detailed information.
    </p>
    <p>
        Find the local working directory for your site at <code><?=PHEASEL_PAGES_DIR?></code>
    </p>
    <p>Thanks for using PHeasel - have fun.</p>
</div>





</body>
</html>