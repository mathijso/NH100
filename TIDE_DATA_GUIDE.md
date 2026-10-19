# Getijden Data Handleiding

## Bron

Rijkswaterstaat Waterwebservices (`ddapi20`), gratis en zonder key:

- Endpoint: `https://ddapi20-waterwebservices.rijkswaterstaat.nl/ONLINEWAARNEMINGENSERVICES/OphalenWaarnemingen`
- Locatie: `ijmuiden.buitenhaven`
- Metadata: compartiment `OW`, grootheid `WATHTE`, groepering `GETETBRKD2` (getijextreem berekend), procestype `astronomisch`

De eerdere handmatige bestanden voor 2025 en 2026 waren regel voor regel gelijk aan wat deze API teruggeeft, alleen 31 december ontbrak.

## Ophalen

```bash
php artisan tides:fetch 2027
```

Schrijft `public/data/2027.csv`. Met `--path=` kies je een andere map. Het command faalt (en schrijft niets) als de API minder dan 1300 extremen teruggeeft, dan is het jaar nog niet (volledig) gepubliceerd.

De API levert alleen tijdstip en hoogte, niet of het hoog- of laagwater is. Omdat die elkaar afwisselen, bepaalt het command dat aan de buren: hoger dan beide buren is HW, lager is LW. Daarom wordt er een dag extra aan beide kanten opgehaald.

## Formaat

Puntkomma-gescheiden, nieuwste eerst, tijden in Nederlandse tijd (zomer- en wintertijd):

```csv
Datum;Nederlandse tijd;Hoogwater/laagwater;Waarde
31/12/2027;18:00;HW;114 cm
31/12/2027;13:38;LW;-81 cm
```

`TideDataService` leest alle `YYYY.csv` in `public/data/`, slaat regels zonder `HW`/`LW` over en groepeert de getijden per datum. `RoutePlannerService` beoordeelt daarmee elke dag. Herstarten of cache legen is niet nodig.
