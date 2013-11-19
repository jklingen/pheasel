<!DOCTYPE html>
<html>
<head>
    <ph:config id="markup-i18n-use-cases" name="Use cases" url="/examples/markup-internationalization/use-cases/" template="examples" />
</head>
<body>

<ph:snippet id="nav-markup-i18n" />
<h1>Use cases for markup internationalization</h1>
<p>Markup internationalization is the most straightforward way to localize a markup file:  you simple copy and rename it,
and then translate its textual content. Of course, this means that you are duplicating the file's HTML tags.</p>
<p>This solution is a perfect fit, if your markup file contains a lot of text and few, semantic HTML markup, say paragraphs,
line breaks, links and maybe some images or similar. The more HTML tags and the less textual content your markup file contains,
the more likely it is that you are going to prefer  <a href ="{ph:url pageid=ini-i18n}">INI internationalization</a> over this method.</p>
<p> <a href ="{ph:url pageid=markup-i18n-usage}">Using markup internationalization</a> is a piece of cake.</p>

</body>
</html>