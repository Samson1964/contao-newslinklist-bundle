# Nachrichten-Linkliste

## Version 1.0.4 (2021-11-09)

* Fix: Return value of Contao\CoreBundle\Routing\Page\PageRoute::getUrlSuffix() must be of the type string, null returned in /src/ContentElements/NewsLinkliste.php :: generateFrontendUrl (line 52) (An die Funktion wurde kein PageModel-Objekt übergeben)

## Version 1.0.3 (2021-07-15)

* Fix: tl_content.newslinklist_stopdate Standardwert korrigiert -> time() statt date('d.m.Y') ist richtig

## Version 1.0.2 (2021-02-25)

* Change: Stopdatum beim Kopieren des Inhaltselements nicht mitkopieren
* Add: Standardwert für Stopdatum ist das aktuelle Datum (funktioniert durch das load_callback nicht richtig)

## Version 1.0.1 (2020-06-25)

* Fix: Inhaltselement-Klasse nicht richtig referenziert

## Version 1.0.0 (2020-06-24)

* Initiale Version für Contao 4, migriert von Version 1.0.5 Contao 3
* Kleinere Fehler beseitigt
