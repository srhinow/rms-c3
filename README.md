---------------------------
rms (Release Management System)
---------------------------

rms-c3 ist eine Contao3-Erweiterung, welches es bestimmten Benutzergruppen erlaubt Freigaben zu verwalten. Alle Redakteur-Zugänge die nicht als Super-Redakteurgruppe in dem Freigabeeinstellungen zugewiesen wurden, können zwar Änderungen an Inhalten vornehmen, diese werden aber nach dem speichern nicht Live angezeigt sondern stehen in der Freigabeliste. Außerdem sind Inhalte die auf Freigabe warten, in der Listenansicht im jeweiligen Bereich mit einem roten Hinweis gekennzeichnet. 
Die Freigabe ist im Moment für die Bereiche Inhalte unterhalb von Artikeln, News, Newsletter und Calender-Events umgesetzt worden.


---------------------------
Versionshinweis
---------------------------

Das Modul 'rms' wurde für Contao 3.x komplett umgeschrieben. Außerdem benötigt rms-c3 nun keinen externen DC-Treiber mehr, sondern besitzt nun einen eigenen. Dieser 'DC_rmsTable.php' ist eine angepasste Variante von DC_Table.php. Die Datei liegt innerhalb von der Erweiterung rms/drivers und muss nicht extra platziert werden.

rms-c3 ist NICHT abwärtskompatibel. Beim Update von einer älteren Version, dürften (NICHT GETESTET) keine Datenverluste von evtl. bestehenden Einstellungen oder Datensätzen entstehen. Es wurden nur Felder hinzugekommen aber keine Spalten gelöscht oder umbenannt.

----------------------------
INSTALLATION
----------------------------
1. Den Inhalt von https://github.com/srhinow/rms-c3 in das Verzeichnis TL_ROOT/system/modules/rms/ kopieren.
2. Danbank  aktualisieren z.B. unter der Erweiterungsverwaltung ->Datenbank aktualisieren
3. Unter Einstellungen (Contao-BE) ziemlich weit unten "Freigabe-Modul" -> Freigabemodul aktivieren
4. Unter dem neuen Backendmodul-Menüpunkt "Freigabe-Anfragen" -> Freigabe-Einstellungen die Einstellungen vornehmen.

----------------------------
UPDATE
----------------------------
Um die alte Version für Contao2.x zu entfernen, am besten den kompletten alten Erweiterungsordner /system/modules/rms löschen. Danach diese Erweitrung unter dem gleichen Pfad kopieren als auch wieder /system/modules/rms. Danach unter Erweiterungsverwaltung die Datenbank aktualisieren.

