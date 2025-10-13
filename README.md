# NH100 Route Planner 🚴‍♂️

Een moderne webapplicatie om te bepalen wanneer de NH100 offroad route in Noord-Holland berijdbaar is, rekening houdend met getijden en seizoensrestricties.

## Over de NH100 Route

De NH100 is een 100km offroad trainingsroute door:
- Noordhollands Duinreservaat
- Het strand (Wijk aan Zee naar Bergen aan Zee)
- MTB parcours in de bossen van Schoorl en Bergen

## Restricties

De route heeft strikte voorwaarden:

### Seizoen Restricties
- **1 oktober - 1 mei (winter)**: Strand hele dag toegankelijk
- **1 mei - 1 oktober (zomer)**: Strand alleen voor 10:00 uur toegankelijk

### Tijdsrestricties
- **07:00**: Aanbevolen starttijd
- **10:30**: Uiterste tijd om uit duinreservaat te zijn
- **10:00-12:00**: Strandgedeelte fietsen
- **12:00+**: MTB parcours

### Getijden Vereisten
Voor het strandgedeelte (10:00-12:00) moet het eb zijn:
- Maximaal 2 uur vóór eb
- Maximaal 2 uur ná eb
- Eb tijd tussen 8:00 en 14:00 ideaal

### Overig
- Duinkaart verplicht (€2,00 per dag)
- Controleer altijd actuele weersomstandigheden

## Twee Versies

### 1. `index.html` - Simpele Versie (Aanbevolen)
- Werkt direct zonder configuratie
- Gebruikt gesimuleerde getijden data
- Realistische eb/vloed patronen
- Perfect voor planning en testen

### 2. `index-with-api.html` - API Versie
- Optioneel gebruik van echte getijden API
- Configureerbaar via `config.js`
- Ondersteunt WorldTides API
- Fallback naar gesimuleerde data

## Gebruik

### Simpele Versie (Start hier!)

```bash
# Direct in browser openen
open index.html

# Of met een lokale server
python3 -m http.server 8000
# Navigeer naar http://localhost:8000
```

### API Versie Configureren

1. Open `config.js`
2. Zet `useRealAPI: true`
3. Voeg je WorldTides API key toe (gratis tier beschikbaar op worldtides.info)
4. Open `index-with-api.html`

```javascript
// In config.js
api: {
    useRealAPI: true,  // Zet op true
    worldTides: {
        key: 'jouw-api-key-hier'  // Vul in
    }
}
```

## Technologie

- **HTML5**: Structuur
- **Tailwind CSS**: Styling en responsive design
- **Vanilla JavaScript**: Logica en interactiviteit
- **Geen build stap nodig**: Direct te gebruiken

## Getijden Data

### Huidige Versie
De huidige versie gebruikt **gesimuleerde getijden data** die realistische eb/vloed patronen volgt:
- Eb cyclus van ~12 uur 25 minuten
- ~50 minuten verschuiving per dag
- Realistische waterstand hoogtes

### Echte API Integratie (Optioneel)

Voor productie gebruik kun je een echte getijden API integreren:

#### Opties:
1. **Rijkswaterstaat API** (gratis, Nederlandse wateren)
   - https://waterinfo.rws.nl/
   
2. **WorldTides API** (beperkt gratis)
   - https://www.worldtides.info/
   
3. **NIOZ** (Nederlands Instituut voor Onderzoek der Zee)
   - Voor Nederlandse kustwateren

## Aanpassingen

### Andere Locatie
Wijzig de coördinaten in `index.html`:
```javascript
const EGMOND_LAT = 52.6167;  // Breedtegraad
const EGMOND_LON = 4.6333;   // Lengtegraad
```

### Andere Tijden
Pas de tijdschecks aan in de `isRouteRideable()` functie.

## Browser Compatibiliteit

Werkt in alle moderne browsers:
- ✅ Chrome/Edge 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Mobile browsers

## Responsive Design

Geoptimaliseerd voor:
- 📱 Mobiele telefoons (320px+)
- 📱 Tablets (768px+)
- 💻 Laptops (1024px+)
- 🖥️ Desktop (1280px+)

## Screenshots

### Desktop
- Overzichtelijke header met route informatie
- Grote "Vandaag" status kaart
- Grid met 30 dagen kalender (3 kolommen)

### Tablet
- 2 kolommen kalender grid
- Goed leesbare tekst en iconen

### Mobiel
- 1 kolom layout
- Touch-vriendelijke interface
- Compact maar informatief

## Waarschuwing

⚠️ **Belangrijk**: Deze tool is een hulpmiddel voor planning. Controleer altijd:
- Actuele weersomstandigheden
- Strand toegankelijkheid
- Duinreservaat openingstijden
- Je eigen veiligheid en conditie

## Licentie

Vrij te gebruiken voor persoonlijk gebruik.

## Contact & Bijdragen

Verbeteringen en suggesties zijn welkom!

