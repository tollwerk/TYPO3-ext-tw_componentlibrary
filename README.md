# Szenarien

Komponenten können auf unterschiedliche Weisen im TYPO3 zustandekommen:

|Komponente    |Request|Templating-Variablen|Pfad                            |
|--------------|-------|--------------------|--------------------------------|
|Extbase       |Ja     |Ja (Controller-Args |Extension + Controller + Aktion |
|Partial       |Ja     |Ja (Argumente)      |Partial-Root + Partial-Pfad     |
|FLUIDTEMPLATE |Ja     |Ja (Kontext)        |Template-Pfad                   |
|TypoScript    |Ja     |? (current?)        |TypoScript-Pfad                 |
|Static        |-      |-                   |HTML-Pfad (oder inline)         |

# Was braucht Fractal?

1. Name
2. Template
3. Kontext (Variablen)
4. Titel
5. Ggf. Notiz / Dokumentation
6. (Preview) Layout
7. Status (WIP, Ready)
8. Preview template

# Scanner / Discovery

* [x] Der Scanner soll eine boolsche `valid`-Eigenschaft zurückgeben, die anzeigt, ob die Komponente Fehler aufweist, oder nicht. Falls ein Fehler vorliegt, dann soll eine `error`-Eigenschaft Aufschluss über die Fehlerursache geben.
* [x] Jedes `Component`-Objekt soll eine `request`-Eigenschaft vom Typ Extbase-Request haben. Dieses Objekt wird zur `request`-Kontextvariable serialisiert.
* [ ] Extbase-Komponenten müssen Extension, Plugin, Controller und Aktion gesetzt bekommen, sonst tritt beim Export ein Fehler auf.
* [ ] Extbase-Komponenten haben eine `setControllerActionArguments()`-Methode, die letztlich Eigenschaften auf das Request-Objekt überträgt.
* [ ] Komponenten exportieren standardmäßig ein einfaches HTML-Template, das sich als Preview-Template für die Komponente eignet.
	* [x] Es stellt im wesentlichen einen `<body>` zur Verfügung.
	* [x] Ihm können CSS-Stylsheets und JavaScripte angehängt werden.
	* [x] Es kann durch ein eigenes Template ersetzt werden.
	* [x] Es kann komplett deaktiviert werden.
	* [x] Es könnte ein Handlebars-Template sein, so dass Fractal direkt damit umgehen kann.
* [x] Komponenten haben einen Status, z.B. "Work in progress", "Ready" etc.
* [x] Komponenten können eine Variante exportieren.
* [x] Komponenten können assoziierte Dateien exportieren, die einfach bei den Komponenten abgelegt werden. Das könnten bspw. Stylesheets, JavaScripts etc. sein. Auch wenn diese im Preview-Template schon vorkommen, müssen sie nochmals explizit übergeben werden, weil sie z.B. von Fractal in den Komponentenordner mit kopiert werden.
* [x] Komponenten können Notizen exportieren (Markdown), die dann in einer `README.md` bei der Komponente gespeichert werden.

# Level 2

* Subkomponenten + Kontext (http://fractal.build/guide/components/sub-components)
