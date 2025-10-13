# NH100 Route Planner - Nieuwe Features ✨

## Versie 2.0 Updates

### 🗺️ Route Informatie Sectie
- **Visuele route kaart** - Placeholder SVG geïntegreerd (vervang met je eigen nh100.png)
- **Komoot integratie** - Directe link naar volledige route navigatie
- **PWN Duinkaart** - Snelle link om verplichte toegangskaart te kopen
- **Route statistieken** - Afstand, terrein, geschatte duur

### 🌊 Uitgebreide Getijden Informatie
**Eb én Vloed tijden** worden nu beide getoond, zodat gebruikers zelf een inschatting kunnen maken:

#### Vandaag Sectie:
- 🌊 **Eb tijden** - Alle laagwater tijden met waterstand
- 🌊 **Vloed tijden** - Alle hoogwater tijden met waterstand
- Gebruikers kunnen nu zelf beoordelen of de timing goed is

#### Kalender Dagen:
- Eerste eb tijd van de dag
- Eerste vloed tijd van de dag
- Compact maar informatief

### 📅 Echte Weekkalender Layout

De 30-dagen view is nu een **echte kalender** met:
- ✅ **7 kolommen** (Zo, Ma, Di, Wo, Do, Vr, Za)
- ✅ **Weekdag headers** met gradient styling
- ✅ **Logische week indeling** 
- ✅ **Compacte weergave** - eb en vloed tijden per dag
- ✅ **Responsive** - Past zich aan op mobiel, tablet en desktop

### 🎨 Design Verbeteringen

#### Desktop (> 1024px):
- 7 kolommen kalender
- Volledige route informatie naast kaart
- Grote, leesbare teksten

#### Tablet (768px - 1024px):
- 7 kolommen kalender (smaller)
- Gestapelde route informatie

#### Mobiel (< 768px):
- 7 kolommen kalender (compact)
- Kleine maar leesbare teksten
- Touch-friendly knoppen

### 🔗 Externe Links

1. **Komoot Route**
   - URL: https://www.komoot.com/nl-nl/tour/296286553
   - Groene gradient knop met kaart icoon
   - Opens in nieuw tabblad

2. **PWN Duinkaart**
   - URL: https://www.pwn.nl/duinkaartkopen#
   - Blauwe gradient knop met PWN logo
   - €2,00 per dag herinnering
   - Opens in nieuw tabblad

### 📸 Route Afbeelding

De app probeert eerst `nh100.png` te laden (je eigen foto/kaart).
Als die niet bestaat, wordt automatisch `nh100.svg` (placeholder) gebruikt.

**Om je eigen route afbeelding toe te voegen:**
1. Plaats je `nh100.png` in de hoofdmap
2. Aanbevolen formaat: Staand (bijv. 400x600px of hoger)
3. De afbeelding wordt automatisch geschaald

## Gebruikerservaring Verbeteringen

### Voor Gebruikers:
- ✅ Zelf eb/vloed tijden kunnen analyseren
- ✅ Direct naar Komoot voor navigatie
- ✅ Snelle duinkaart aankoop
- ✅ Overzichtelijke weekplanning

### Voor Planning:
- ✅ Zie meerdere weken in één oogopslag
- ✅ Vergelijk verschillende dagen
- ✅ Identificeer patronen in getijden
- ✅ Plan met vrienden (kalender delen via screenshot)

## Technische Details

### Getijden Berekening:
```javascript
// Eb + Vloed per dag
lowTides: [
  { time: '06:30', height: 0.35 },
  { time: '19:00', height: 0.42 }
]
highTides: [
  { time: '00:45', height: 2.15 },
  { time: '13:15', height: 1.98 }
]
```

### Kalender Layout:
- CSS Grid met 7 kolommen
- Dynamische dag generatie
- Automatische week wrapping
- Responsive gap sizing

### Browser Compatibiliteit:
- ✅ Modern CSS Grid
- ✅ Flexbox fallbacks
- ✅ SVG support
- ✅ ES6+ JavaScript

## Wat Volgt?

Mogelijke toekomstige features:
- [ ] Echte getijden API integratie (WorldTides/Rijkswaterstaat)
- [ ] Weer voorspelling integratie
- [ ] Wind data (belangrijk voor strand)
- [ ] GPS track export
- [ ] Kalender export (iCal)
- [ ] Notificaties voor goede dagen
- [ ] Delen functionaliteit
- [ ] PWA (Progressive Web App) support
