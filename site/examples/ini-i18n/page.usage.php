<!DOCTYPE html>
<html>
<head>
    <ph:config id="ini-i18n-usage" template="examples" />
</head>
<body>

<ph:snippet id="nav-ini-i18n" />

<h1><ph:msg code="headline"/></h1>

<ph:msg code="lang1.info1"/> <a href="{ph:url pageid=ini-i18n}"><ph:msg code="lang1.name"/></a> <ph:msg code="lang1.info2"/>
<ul>
    <li><ph:msg code="lang1.markup.tmpl"/></li>
    <li><ph:msg code="lang1.markup.nav"/></li>
    <li><ph:msg code="lang1.markup.page"/></li>
    <li><ph:msg code="lang1.markup.ini"/></li>
</ul>
<ph:msg code="lang2.info1"/> <a href="{ph:url pageid=ini-i18n}"><ph:msg code="lang2.name"/></a> <ph:msg code="lang2.info2"/>
<ul>
    <li><ph:msg code="lang2.markup.tmpl"/></li>
    <li><ph:msg code="lang2.markup.nav"/></li>
    <li><ph:msg code="lang2.markup.page"/></li>
    <li><ph:msg code="lang2.markup.ini"/></li>
</ul>
<p><ph:msg code="explanation"/></p>

</body>
</html>