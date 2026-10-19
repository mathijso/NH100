# NH100 Route Planner 🚴‍♂️

Een moderne webapplicatie om te bepalen wanneer de NH100 offroad route in Noord-Holland berijdbaar is, rekening houdend met getijden en seizoensrestricties.

## Over de NH100 Route

De NH100 is een 100km offroad trainingsroute door:
- Noordhollands Duinreservaat
- Het strand (Wijk aan Zee naar Bergen aan Zee)
- MTB parcours in de bossen van Schoorl en Bergen

## Restricties

- **1 oktober - 1 mei (winter)**: strand hele dag toegankelijk
- **1 mei - 1 oktober (zomer)**: strand alleen voor 10:00 uur toegankelijk, de planner geeft dan altijd rood
- Je moet voor **10:30** uit het duinreservaat zijn, het strandgedeelte rijd je tussen 10:30 en 12:00
- Duinkaart verplicht (€2,00 per dag)

## Zo beoordeelt de planner een winterdag

Zie `isRouteRideable()` in `resources/js/nh100.js`. In volgorde:

1. **Groen** als er een laagwater (eb) valt tussen 08:00 en 15:00.
2. **Rood** ("Te dicht op vloed") als het laatste hoogwater vóór 10:30 minder dan 2 uur eerder was.
3. Anders telt de overlap tussen het strandvenster (10:30-12:00) en het eb-venster (4 uur voor tot 2 uur na laagwater): 90 minuten is **groen**, 30 tot 90 minuten **oranje**, minder **rood**.
4. Zonder getijdendata voor die dag: **rood** ("Geen data beschikbaar").

## Waarschuwing

⚠️ **Belangrijk**: Deze tool is een hulpmiddel voor planning. Controleer altijd:
- Actuele weersomstandigheden
- Strand toegankelijkheid
- Duinreservaat openingstijden
- Je eigen veiligheid en conditie


## Data

### Getijden
Astronomische hoog- en laagwatertijden van IJmuiden buitenhaven, van Rijkswaterstaat. Per jaar één CSV in `public/data/YYYY.csv`; alle jaren in die map worden automatisch ingeladen.

Nieuw jaar toevoegen (Rijkswaterstaat publiceert het volgende jaar meestal in het najaar):

```bash
php artisan tides:fetch 2028
```

Details over bron en formaat: [TIDE_DATA_GUIDE.md](TIDE_DATA_GUIDE.md).

### Weer
Actuele temperatuur, wind en zonsopgang voor Egmond aan Zee via [Open-Meteo](https://open-meteo.com) (geen API-key nodig), 15 minuten gecachet in `WindDataService`.

## Deploy
Draait op hetzner-main onder `/var/www/html/mathijsoggel.nl/nh100` (Apache-alias `/nh100`, gebruiker `web-mathijsoggel`):

```bash
sudo -u web-mathijsoggel bash -c 'cd /var/www/html/mathijsoggel.nl/nh100 && git pull && composer install --no-dev -o && npm ci && npm run build && php artisan optimize'
```

## Todo
- [x] Wind data (belangrijk voor strand)
- [ ] Delen functionaliteit
- [ ] PWA (Progressive Web App) support
- [ ] SEO functionaliteit
- [ ] H100 variant toevoegen
 

 ## Contact & Bijdragen
Verbeteringen en suggesties zijn welkom via mathijs@oggel-codelabs.nl