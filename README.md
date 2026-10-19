# NH100 Route Planner 🚴‍♂️

Een webapplicatie om te bepalen wanneer de NH100 offroad route in Noord-Holland berijdbaar is, op basis van getij, seizoen en wind, met de weekenden voorop.

Live: https://mathijsoggel.nl/nh100/

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

`App\Services\RoutePlannerService` is de enige plek met deze regels; de pagina en de agenda-feed tonen alleen het resultaat. In volgorde:

1. **Groen** als er een laagwater (eb) valt tussen 08:00 en 15:00.
2. **Rood** ("Te dicht op vloed") als het laatste hoogwater vóór 10:30 minder dan 2 uur eerder was.
3. Anders telt de overlap tussen het strandvenster (10:30-12:00) en het eb-venster (4 uur voor tot 2 uur na laagwater): 90 minuten is **groen**, 30 tot 90 minuten **oranje**, minder **rood**.
4. Zonder getijdendata voor die dag: **rood** ("Geen data beschikbaar").

## Wat de pagina toont

- **Vandaag** en **komend weekend** (za en zo) bovenaan, met de volgende groene weekenddag en de eerstvolgende groene dag.
- Per dag: getijden met hoogte, **extra laag water** (laagwater op of onder -80 cm NAP, ongeveer de laagste 30% van de eb's overdag), zonsopkomst en de **wind om 11:00** voor de komende 16 dagen. Het strand rijd je van Wijk aan Zee naar Bergen aan Zee, dus wind uit het zuiden is rugwind en uit het noorden tegenwind.
- Desktop: maandkalender met de weekenden gemarkeerd. Mobiel: doorlopende lijst met een schakelaar "Alleen weekend". Klik op een dag voor details.
- **Agenda-abonnement** op `/agenda.ics` met de groene weekenddagen (`?dagen=alle` voor ook doordeweeks).

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
Actuele temperatuur en wind plus de uurverwachting voor 16 dagen voor Egmond aan Zee via [Open-Meteo](https://open-meteo.com) (geen API-key nodig), 15 minuten gecachet in `WindDataService`. Zonsopkomst rekent PHP zelf uit (`date_sun_info`), dus die is er voor elke dag.

## Deploy
Draait op hetzner-main onder `/var/www/html/mathijsoggel.nl/nh100` (Apache-alias `/nh100`, bestanden van `web-mathijsoggel`). Root heeft de GitHub-key, dus root pullt en de rest draait als de site-gebruiker:

```bash
cd /var/www/html/mathijsoggel.nl/nh100 && git pull --ff-only && chown -R web-mathijsoggel:web-mathijsoggel .
sudo -u web-mathijsoggel -H bash -c 'export HOME=/tmp/nh100-build; mkdir -p $HOME; cd /var/www/html/mathijsoggel.nl/nh100 && /usr/local/bin/composer install --no-dev -o && npm ci && npm run build && php artisan optimize'
rm -rf /tmp/nh100-build
```

## Todo
- [x] Wind data (belangrijk voor strand)
- [x] Delen (deelknop, preview-afbeelding voor WhatsApp e.d.)
- [x] Agenda-abonnement
- [ ] PWA (Progressive Web App) support
- [ ] H100 variant toevoegen
 

 ## Contact & Bijdragen
Verbeteringen en suggesties zijn welkom via mathijs@oggel-codelabs.nl