# rms (Release Management System)

rms-c3 ist eine Contao3-Erweiterung (zur [Contao 4 Version](https://gitlab.com/srhinow/contao-rms-bundle)), welches es Benutzern einer bestimmten Benutzergruppe erlaubt, Freigaben zu verwalten. Alle Redakteur-Zugänge die nicht als Super-Redakteurgruppe in dem Freigabeeinstellungen zugewiesen wurden, können zwar Änderungen an Inhalten vornehmen oder wenn es ihnen erlaubt ist auch neue Inhalte erstellen. Diese werden aber nach dem speichern nicht Live angezeigt sondern stehen in der Freigabeliste. Damit der jeweilige Freigabe-Redakteur nicht nach jedem speichern eine Email bekommt, hat der normale Redakteur die Möglichkeit in der Bearbeiten-Ansicht eine Freigabe-Notiz zu hinterlassen und eine checkbox um die Nachricht an den Freigabe-Redakteur zusenden. Außerdem sind Inhalte die auf Freigabe warten, in der Listenansicht im jeweiligen Bereich mit einem roten Hinweis gekennzeichnet. 

Die Freigaben sind im Moment für die Bereiche 

- Artikel, 
- alle Inhaltselemente egal unter welchen Elterntabelle, 
- News, 
- Newsletter, 
- FAQ (ab 3.2)
- Calender-Events 

umgesetzt worden.

Das Modul muss nach der Installation zuerst unter "System" / "Einstellungen" "Freigabe-Modul" mit einer checkbox aktiviert werden. Diese kann dort jederzeit global wieder deaktiviert werden. Dabei werden keine bestehenden rms-Tabelleninhalte gelöscht. Es wird einfach nicht ausgeführt.

Die Zuweisung der Redakteure findet immer in den Root-Einstellungen des jeweiligen Bereiches statt. z.B. bei den News sind die "Freigabe-Einstellungen" in den jeweiligen News-Archiv-Einstellungen oder bei Artikel und deren Inhaltslemente, ist es die ROOT-Page eines jeden Seitenbaumes. So können verschiedene Bereiche verschiedenen Verantwortlichen zugewiesen werden. Der zugewiesene Freigabe-Redakteur sieht dann unter der Freigabe-Verwaltung auch nur die Freigaben die für ihn bestimmt sind. Die Felder für die Redakteure und die Freigabe-Redakteure müssen noch in den Benutzer(gruppen)-Rechten zugewiesen werden. (TIPP: man sucht in den Berechtigungseinstellungen im Browser nach "Freigabe" und arbeitet sich so durch die einzelnen Bereiche).

In dem BE-Modul 'Freigabe-Anfragen' hat der Freigabe-Redakteur die Möglichkeit, sich eine Frontendvorschau anzeigen zulassen, eine Diff-Ansicht der Änderungen, den Inhalt direkt zu editieren und per Checkbox wiederum den Redaktuer über die Reaktion per Email zu informieren. Sobald eine Anfrage im Bearbeiten-Modus gespeichert wurde, erscheint Dieses in der Liste (grün) als bearbeitet markiert. Zur besseren Übersicht kann auch nach dem Bearbeiten-Status gefiltert werden. Sobald eine Freigabe-Anfrage mit dem Häkchen in der Freigabe-Liste bestätigt wird, wird der Inhalt Live gestellt und der Eintrag aus der Freigabeliste gelöscht. Wenn der Freigabe-Eintrag gelöscht wird, wird in bestehenden Elementen der Freigabemodus zurück gesetzt, neu angelegte Elemente gelöscht und der Eintrag aus der Freigabeliste entfernt.

Weitere globale rms-Einstellungen befinden sich "Freigabe-Anfragen" / "Freigabe-Einstellungen". Dort muss zuerst die Benutzergruppe mit den Freigabeberechtigungen angegeben werden. Weiterhin muss man dort einen Fallback-Freigabe-Redakteur zuweisen (kann auch jemand mit Admin-Rechten sein). Dort wird z.B. die Email-Adresse verwendet falls durch welchen Umstand auch immer, zwar der Bereich als Freigabe-gechützt markiert ist aber der zugewiesene Benutzer nicht existiert. Weiterhin kann man dort Email-Adressen komma-getrennt eintragen, die bei JEDER Freigabe mitbenachrichtigt werden sollen.  


## finanziert durch

Die erste Umsetzung der rms-Erweiterung für Contao 2.11.x ist durch Anforderungen in einem Auftrag und mit der finanziellen Unterstützung der Büchereizentrale Niedersachsen (http://www.bz-niedersachsen.de) entstanden.

Die Umsetzung der rms-Erweiterung für Contao 3 und die Erweiterung um eine "Unterschiede anzeigen" - Funktion, wurde durch Cyrill Weiss (http://www.cyrillweiss.ch/) in Auftrag gegeben und finanziert.

Die Umarbeitung der rms-Erweiterung, so das zukünftig pro Bereich ein eigener Freigabe-Redakteur zugewiesen werden kann, wurde durch Michael Roedhamer (http://pixelkinder.com) in Auftrag gegeben und finanziert.


## Versionshinweis

**HINWEIS ZUM 3.2-UPDATE VON EINER ÄLTEREN VERSION
Da in der Version grundsätzlich der Umgang mit der Vorschau und den Orten wo Einstellungen gespeichert werden, sich geändert hat, sollte es keine Einträge mehr unter Freigabe-Anfragen geben. Wie und wo nach einem Update die Einstellungen zuprüfen sind, lesen Sie unter Konfiguration.**

Das Modul 'rms' wurde für Contao 3.x komplett umgeschrieben. Außerdem benötigt rms-c3 nun keinen externen DC-Treiber mehr, sondern besitzt nun einen eigenen. Dieser 'DC_rmsTable.php' ist eine angepasste Variante von DC_Table.php. Die Datei liegt innerhalb von der Erweiterung rms/drivers und muss nicht extra platziert werden.

rms-c3 ist **NICHT** abwärtskompatibel. Beim Update von einer älteren Version, dürften (NICHT GETESTET) keine Datenverluste von evtl. bestehenden Einstellungen oder Datensätzen entstehen. Es sind nur Felder hinzugekommen aber keine Spalten gelöscht oder umbenannt.


## Installation

1. Den Inhalt von https://github.com/srhinow/rms-c3 in das Verzeichnis TL_ROOT/system/modules/rms/ kopieren.
2. Danbank  aktualisieren z.B. unter der Erweiterungsverwaltung ->Datenbank aktualisieren
3. Unter Einstellungen (Contao-BE) ziemlich weit unten "Freigabe-Modul" -> Freigabemodul aktivieren
4. Unter dem neuen Backendmodul-Menüpunkt "Freigabe-Anfragen" -> Freigabe-Einstellungen die Einstellungen vornehmen.


## Update

Um die alte Version für Contao2.x zu entfernen, am besten den kompletten alten Erweiterungsordner /system/modules/rms löschen. Danach diese Erweitrung unter dem gleichen Pfad kopieren als auch wieder /system/modules/rms. Danach unter Erweiterungsverwaltung die Datenbank aktualisieren.


## Konfiguration

bis rms-Version 3.1.x (Contao-Version sollte ab 3.0 sein)

1. Um das Freigabemodul nach der Installation zu aktivieren, gehen Sie im Backend unter System/Einstellungen -> Abschnitt "Freigabe-Modul" und 
 setzen den Haken vor "Freigabe-Modus aktivieren" und speichern Sie die Einstellungen. Danach sollte Backend im Bereich "Inhalte" der Punkt
"Freigabe-Anfragen" zusehen sein.
2. Legen Sie im Bereich Benutzerverwaltung/ Benutzergruppen eine gruppe an die als Freigabe-Redaktionsgruppe dienen soll. Danach weisen Sie einem Benutzer diese Gruppe zu.
3. Wechseln Sie nun in das Backend-Modul "Freigabe-Anfragen" und dort unter "Freigabe-Einstellungen" legen Sie die Gruppe (Freigabe-Redaktionsgruppe), eine Emailadresse, die aktiven Module und die freigabegeschützte Seitenstruktur zu und speichern die Einstellungen. 

ab rms-Version 3.2-beta1 (Contao-Version sollte ab 3.0 sein)

1. Um das Freigabemodul nach der Installation zu aktivieren, gehen Sie im Backend unter System/Einstellungen -> Abschnitt "Freigabe-Modul" und setzen den Haken vor "Freigabe-Modus aktivieren" und speichern Sie die Einstellungen. Daraufhin werden diverse Tabellen mit zusätzlichen Feldern befülllt. Anschließend sollte Backend im Bereich "Inhalte" der Punkt "Freigabe-Anfragen" zusehen sein.
2. Legen Sie im Bereich Benutzerverwaltung/ Benutzergruppen eine Gruppe an, die als Freigabe-Redaktionsgruppe dienen soll. Danach weisen Sie allen Benutzern, welche für Freigaben verantworltich seien sollen, diese Gruppe zu.
3. Wechseln Sie nun in das Backend-Modul "Freigabe-Anfragen" und dort unter "Freigabe-Einstellungen", legen Sie die Gruppe (Freigabe-Redaktionsgruppe), einen Fallbackredakteur, zusätzliche Emailempfänger die bei Freigaben informiert werden sollen und zu ignorirende Felder fest.
4. Anders als in älteren Versionen werden die verantwortlichen Freigabe-Redakteure immer in den obersten Ebenen festgelegt ob der Bereich geschützt sein soll, durch wen administriert, und (nur bei Modulen) welche Weiterleitungsseite als Vorschau verwendet werden soll.

  * für Artikel und Inhaltselemente eines bestimmten Seitenstrukturbaumes werden die Einstellungen in dem Seitenstruktur/Startpunkt einer Seite festgelegt
  * für News ist es das jeweilige News-Archiv
  * für die Events in dem jeweilig zugehörigen Kalender
  * für Newsletter in dem jeweiligen Verteiler
  * für FAQ in der zugehörigen FAQ-Kategorie

**Zusatz:** für eigene Module können freigaben genauso aufgebaut werden, da ich es so flexibel strukturiert habe das er sich immer die oberste Ebene ermittelt (per ptable) und dort nach den Einstellungen sucht


## Entwickler-Informationen

Ich habe versucht das Modul soweit wie möglich offen für weitere zu schützende Erweiterungen zuhalten.

Es wird, wenn die Freigabeverwaltung für den Bereich aktiv ist, werden Callbacks geladen. Er versucht erst eine Methode "onEditCallback" und eine Methode "onSubmitCallback" in einer Klasse mit folgenden Namensaufbau zufinden [table].'_rms'. Sollte es diese nicht geben greift er auf die gleichnamigen Methoden in der rmsDefaultCallbacks.php zurück. Es muss hier also keine eigene Logik vorhanden sein, wenn die Funktion so wie sie vorhanden ist genügt.
(Quelle: rmsHelper->handleBackendUserAccessControlls())

Weiterhin gibt es zusätzliche HOOKS um zu pruefen ob Inhalte Freigabegeschützt sind. Diese werden zwingend für eigene Erweiterungen benötigt:

1. $GLOBALS['TL_HOOKS'] ['rmsIsContentProtected'] Falls eine Erweiterung auch auf die tl_content-Tabelle zurückgreift. Übergibt die aktuelle Tabelle und überprüft selbst auf den GET-Paramter do
2. $GLOBALS['TL_HOOKS'] ['rmsIsTableProtected'] Wird für alle Erweiterungs-DCA benötigt welche nicht tl_content sind. Übergibt die aktuelle Tabelle und überprüft auf die übergebene Tabelle
3. Für bestimmte Module gibt es für die Frontendausgabe bestimmte zusätzliche Formatierungen die beim überschreiben der rms-Daten verloren gehen bevor diese ans Template geschickt werden. Für diesen Fall prüfe ich ob es eine Methode 'modifyForPreview' in der Class $objTemplate->rms_ref_table.'_rms' existiert. Als Beispiel für die News lege ich die Methode modifyForPreview in der Klasse tl_news_rms an und setzte dort einen Template-Parameter 'linkHeadline' neu.



