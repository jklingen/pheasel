<!DOCTYPE html>
<html>
<head>
    <ph:config id="markup-i18n-usage" name="Usage" url="/examples/markup-internationalization/usage/" template="examples" />
</head>
<body>

<ph:snippet id="nav-markup-i18n" />
<h1>How to use markup internationalization</h1>
The English <a href="{ph:url pageid=markup-i18n}">overview page</a> of this example consists of three markup files:
<ul>
    <li>tmpl.example1.<strong>en</strong>.php</li>
    <li>snip.navi.php</li>
    <li>page.overview.<strong>en</strong>.php</li>
</ul>
The  <a href="{ph:url pageid=markup-i18n lang=de}">German version</a> of the same page uses three markup files, too:
<ul>
    <li>tmpl.example1.<strong>de</strong>.php</li>
    <li>snip.navi.php</li>
    <li>page.overview.<strong>de</strong>.php</li>
</ul>
You can see that the markup files for the page and its template have been duplicated for the translation. Note that
both languages use the same (non-localized) snippet for the navigation. PHeasel takes care of labelling the navigation links,
so there is no textual content in this file, and thus no need to clone it.

</body>
</html>