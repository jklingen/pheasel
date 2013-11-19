<!DOCTYPE html>
<html>
<head>
    <ph:config id="ini-i18n" template="examples" />
</head>
<body>

<ph:snippet id="nav-ini-i18n" />
<h1><ph:msg code="headline"/></h1>
<p><ph:msg code="info"/></p>

<p><ph:msg code="usecase.info.1"/> <a href ="{ph:url pageid=ini-i18n-use-cases}"><ph:msg code="usecase.info.2"/></a><ph:msg code="usecase.info.3"/></p>
<p><ph:msg code="markup.info.1"/> <a href ="{ph:url pageid=markup-i18n}"><ph:msg code="markup.info.2"/></a> <ph:msg code="markup.info.3"/></p>
<p><ph:msg code="otherfeatures"/></p>
<ul>
    <li><ph:msg code="otherfeatures.snippets"/></li>
    <li><ph:msg code="otherfeatures.templates"/></li>
</ul>

</body>
</html>