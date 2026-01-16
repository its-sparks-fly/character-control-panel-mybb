# Character Control Panel
Dieses Plugin erweitert das AdminCP um eine neue Funktion, durch die RPG-Charakter-Einstellungen von User-Einstellungen getrennt werden können. So ermöglicht es das Character Control Panel beispielsweise, Profilfelder, die mit Charakter-Einstellungen zusammenhängen (z.B. Avatarperson, Kurzbeschreibung) vom UserCP zu trennen, um Spieler:innen größeren Überblick über ausfüllbare Felder zu ermöglichen. Das Character Control Panel wird als https://euerforum.de/character.php aufgerufen.
# Funktionen
Das "CharaCP" bietet diverse Funktionen, durch die es sich ganz individuell gestalten lässt. 

## Unterseiten mit Profilfeldern
Im Admin Control Panel können Unterseiten für das Character Control Panel erstellt werden, auf denen einzelne Profilfelder ausgegeben werden können. <u>Beispiel:</u> Alle Profilfelder, die sich auf das Äußere des Charakters beziehen, können einer Unterseite "Aussehen bearbeiten" zugeordnet werden. <b>Das CharaCP übernimmt alle Einstellungen der Profilfelder</b>, die im AdminCP angegeben wurden, was individuelle Konfigurierbarkeit pro Nutzer:innengruppe ermöglicht. Zudem können jeder Seite Nutzer:innengruppen zugeordnet werden, sodass je Gruppe verschiedene CharaCP-Unterseiten angezeigt werden können. Die Zuordnung von Profilfeldern kann direkt bei der Erstellung von Profilfeldern erfolgen, aber auch separat in den CharaCP-Einstellungen.

<center>
  <img src="https://eightletters.de/screens/plugins/ccp/ccp1.PNG" />

  <img src="https://eightletters.de/screens/plugins/ccp/ccp2.PNG" />

  <img src="https://eightletters.de/screens/plugins/ccp/ccp3.PNG" />

  <img src="https://eightletters.de/screens/plugins/ccp/ccp4.PNG" />
</center>

## Anhängen von UserCP-Plugins
Plugins, die über UserCP-Funktionen verfügen, können bei Bedarf an das Character Control Panel angehangen werden. Somit lässt sich das <a href="https://github.com/its-sparks-fly/interview-mybb" target="_blank">Interview-Plugin</a> beispielsweise über character.php?action=interview statt wie für gewöhnlich über usercp.php?action=interview aufrufen, sodass alle Charakter-Angaben gebündelt an einem Ort zu finden sind. <u>Fortgeschritten:</u> Diese Funktion ersetzt die in den Plugins angegebenen usercp-Hooks durch character-Hooks. Im AdminCP erfolgt zudem eine Angabe, ob in den Plugins darüber hinaus auf das UserCP verwiesen wird - beispielsweise in Redirects. Plugins mit Formularen sollten außerdem auch bzgl. ihrer Templates auf Verweise auf usercp.php überprüft werden. Das Ersetzen erfolgt nicht automatisch, da es durchaus möglich ist, dass Plugins bewusst auf Funktionen des UserCPs zugreifen - <b>Plugins mit Verweise auf das UserCP sollten händisch kontrolliert und angepasst werden</b>, um Fehler zu vermeiden. <u>Beim Anhängen von Plugins an das Character Control Panel unterstütze ich nicht</u>, die Nutzung der Funktion erfolgt auf eigene Gefahr. <b>Bei Deinstallation des CharaCP werden angehängte Plugins automatisch zurück an das UserCP gehangen.</b>

<center>
<img src="https://eightletters.de/screens/plugins/ccp/ccp5.PNG" />

<img src="https://eightletters.de/screens/plugins/ccp/ccp6.PNG" />
</center>

## Erstellen eigener Code-Seiten
Das Character Control Panel kann zudem um eigene Seiten erweitert werden, auf denen PHP-Code ausgeführt wird. Die Nutzung dieser Funktion richtet sich an <u>fortgeschrittene Nutzer:innen</u> - eigene Code-Seiten benötigen eigene Templates, die nach Belieben mit Variablen aus dem angegebenen PHP-Code befüllt werden können. Auch der Zugriff auf eigene Funktionen kann je nach Nutzer:innengruppe limitiert werden. 

<center>
<img src="https://eightletters.de/screens/plugins/ccp/ccp7.PNG" />

<img src="https://eightletters.de/screens/plugins/ccp/ccp8.PNG" />
</center>

## CharaCP-Navigation
Grundsätzlich baut sich die Navigation des Character Control Panels sich automatisch aus allen angehängten Seiten (Profilfelder, Plugins, eigene Funktionen) und orientiert sich dabei auch an den eingestellten Gruppenberechtigungen. Wer zusätzlich z.B. Listen in der Navigation aufführen möchte, oder Probleme damit hat, alle angehängten Plugins in der Navigation darzustellen, kann im Admin Control Panel die self-building Navigation händisch erweitern. 

<center>
<img src="https://eightletters.de/screens/plugins/ccp/ccp9.PNG" />

<img src="https://eightletters.de/screens/plugins/ccp/ccp10.PNG" />
</center>

# Neue Datenbank-Tabellen
<ul>
  <li /> PREFIX_character_pages
  <li /> PREFIX_character_pages_fields
  <li /> PREFIX_character_pages_code
  <li /> PREFIX_character_nav
</ul>

# Neue Templates
<ul>
  <li /> character
  <li /> character_header
  <li /> character_nav
  <li /> character_nav_pages
  <li /> character_nav_title
  <li /> character_page
</ul>

# Neue Hooks
Das Character Control Panel kann durch Plugins erweitert werden. Dafür bietet es folgende Plugin-Hooks:
<ul> 
  <li /> character_start => character.php; Zeile 30
  <li /> character_end => character.php; Zeile 243
  <li /> character_menu => inc/functions_character.php; Zeile 26
  <li /> character_menu_built => inc/functions_character.php; Zeile 29
  <li /> character_menu_pages => inc/functions_character.php; Zeile 51  
  <li /> character_menu_plugins => inc/functions_character.php; Zeile 62
  <li /> character_menu_code => inc/functions_character.php; Zeile 80
</ul>
