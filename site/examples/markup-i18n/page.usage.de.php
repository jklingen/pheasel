<!DOCTYPE html>
<html>
<head>
    <ph:config id="markup-i18n-usage" name="Verwendung" url="/beispiele/markup-internationalisierung/verwendung/" template="examples" />
</head>
<body>

<ph:snippet id="nav-markup-i18n" />
<h1>Verwendung der Markup-Internationalisierung</h1>
Die <a href="{ph:url pageid=markup-i18n}">deutschsprachige Übersichtsseite</a> dieses Beispiels besteht aus drei Markup-Dateien::
<ul>
    <li>tmpl.example1.<strong>de</strong>.php</li>
    <li>snip.navi.php</li>
    <li>page.overview.<strong>de</strong>.php</li>
</ul>
Die <a href="{ph:url pageid=markup-i18n lang=en}">englischsprachige Version</a> verwendet ebenfalls drei Markup-Dateien:
<ul>
    <li>tmpl.example1.<strong>en</strong>.php</li>
    <li>snip.navi.php</li>
    <li>page.overview.<strong>en</strong>.php</li>
</ul>
Wie Du siehst, wurden die Markup-Dateien für die Seite und ihr Template für die Übersetzung dupliziert. Beachte, dass
beide Sprache das selbe (nicht-lokalisierte) Snippet für die Navigation verwenden. PHeasel übernimmt die Beschriftung
der Navigationslinks, daher gibt es keinen Textinhalt in dieser Datei und somit keinen Grund, sie zu kopieren.

</body>
</html>