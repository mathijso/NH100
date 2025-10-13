# Marea API Integratie - NH100 Route Planner

## 📋 Overzicht

De NH100 Route Planner is nu geïntegreerd met de **Marea API** voor echte getijden data voor Egmond aan Zee.

**Meetstation: Petten Zuid (GESLA3:eb1c86d10b)**
- Latitude: 52.772657° (52°46'21.6"N)
- Longitude: 4.649702° (4°38'58.9"E)
- Afstand tot Egmond aan Zee: ~18km noordelijk
- Voordeel: Echte meetdata van een fysiek station (meest accuraat!)

**Fallback Coördinaten (indien station niet beschikbaar):**
- Egmond aan Zee: 52.618694°N, 4.618250°E

## 🔧 Setup

### 1. Marea API Key verkrijgen

1. Ga naar [Marea API](https://api.marea.ooo)
2. Maak een account aan
3. Je krijgt **100 gratis requests** om de API te verkennen
4. Voor meer requests, check de [pricing page](https://api.marea.ooo/pricing)

### 2. .env Configuratie

Voeg de volgende variabelen toe aan je `.env` bestand:

```bash
# Marea API Configuration
MAREA_API_ENABLED=true
MAREA_API_KEY=your-api-key-here
MAREA_STATION_ID=GESLA3:eb1c86d10b  # Petten Zuid station
```

**Belangrijk:**
- Zet `MAREA_API_ENABLED=false` om gesimuleerde data te gebruiken (geen API calls)
- Zet `MAREA_API_ENABLED=true` om echte Marea API data te gebruiken

### 3. Config Cache Clearen

Na het wijzigen van `.env`:

```bash
php artisan config:clear
php artisan cache:clear
```

## 🏗️ Architectuur

### Components

1. **MareaApiService** (`app/Services/MareaApiService.php`)
   - Handles all API communication
   - Caches responses voor 6 uur
   - Transformeert API data naar NH100 formaat
   - Bevat fallback logica

2. **TideData Livewire Component** (`app/Livewire/TideData.php`)
   - Laadt getijden data bij page load
   - Switcht tussen real/simulated data
   - Dispatcht events naar Alpine.js components

3. **Alpine.js Components** (in `welcome.blade.php`)
   - `todayStatus` - Toont vandaag status
   - `calendar` - 30-dagen kalender
   - Luistert naar Livewire events

### Data Flow

```
User opent pagina
    ↓
Livewire TideData mount()
    ↓
Check MAREA_API_ENABLED
    ↓
├─ TRUE → MareaApiService::getEgmondTides()
│           ↓
│       Try Petten Zuid Station (GESLA3:eb1c86d10b)
│           ↓
│       ├─ Success → STATION data ✓
│       └─ Fail → Fallback to coordinates
│           ↓
│       Marea API Call (met caching)
│           ↓
│       Transform naar NH100 format
│
└─ FALSE → Generate simulated tides
    ↓
Dispatch 'tides-loaded' event
    ↓
Alpine.js components ontvangen data
    ↓
UI update met getijden info
```

## 📊 API Response

### Marea API Response Format

```json
{
  "status": 200,
  "latitude": 52.618694,
  "longitude": 4.618250,
  "source": "FES2014",
  "datum": "MSL",
  "unit": "m",
  "extremes": [
    {
      "timestamp": 1633451650,
      "datetime": "2021-10-05T16:34:10+00:00",
      "height": 0.109,
      "state": "HIGH TIDE"
    },
    {
      "timestamp": 1633482505,
      "datetime": "2021-10-06T01:08:25+00:00",
      "height": -0.035,
      "state": "LOW TIDE"
    }
  ]
}
```

### NH100 Transformed Format

```json
{
  "tides": [
    {
      "time": "2021-10-05T16:34:10+00:00",
      "type": "High",
      "height": 0.109
    },
    {
      "time": "2021-10-06T01:08:25+00:00",
      "type": "Low",
      "height": -0.035
    }
  ],
  "source": "FES2014",
  "location": {
    "latitude": 52.618694,
    "longitude": 4.618250
  }
}
```

## 🔍 Data Sources

De Marea API gebruikt verschillende bronnen:

### 1. **Stations** (Meest accuraat) ✓ IN GEBRUIK
- Historische metingen van specifieke locaties
- >5,000 stations wereldwijd
- **NH100 gebruikt: Petten Zuid (GESLA3:eb1c86d10b)**
  - Afstand: ~18km van Egmond aan Zee
  - Land: Nederland
  - Bron: GESLA3 (Global Extreme Sea Level Analysis)

### 2. **FES2014 Model** (Fallback)
- Global ocean tide model
- Gegenereerd door AVISO+ Products
- Goed voor locaties zonder station
- Gebruikt als Petten Zuid station niet beschikbaar is

### 3. **EOT20 Model**
- Alternatief global model
- Beschikbaar via `model` parameter

Voor Egmond aan Zee gebruiken we **primair Station Petten Zuid** (meest accuraat!) met fallback naar coördinaten indien nodig.

## 💾 Caching

De MareaApiService cached alle API responses:

- **Cache duration:** 6 uur
- **Cache key:** `marea_tides_{lat}_{lon}_{timestamp}_{duration}_{interval}`
- **Rounded timestamp:** Per uur (voor efficiëntie)

**Cache clearen:**
```bash
php artisan cache:clear
```

## 🔄 API Parameters

### Voor Station (Primair):

```php
[
    'station_id' => 'GESLA3:eb1c86d10b',  // Petten Zuid
    'duration' => 50400,                   // 35 dagen in minuten
    'interval' => 60,                      // 1 uur tussen metingen
    'timestamp' => time(),                 // Start tijd
    'datum' => 'MSL',                     // Mean Sea Level
]
```

### Voor Coördinaten (Fallback):

```php
[
    'latitude' => 52.618694,      // Egmond aan Zee
    'longitude' => 4.618250,
    'duration' => 50400,           // 35 dagen in minuten
    'interval' => 60,              // 1 uur tussen metingen
    'timestamp' => time(),         // Start tijd
    'datum' => 'MSL',             // Mean Sea Level
    'model' => 'FES2014',         // Default model
    'station_radius' => 50,       // Prioriteer station binnen 50km
]
```

## 📈 API Usage & Limits

### Gratis Tier
- **100 requests** bij aanmelding
- Ideaal voor testen

### Request Cost
Elke request naar `/tides` kost **1 credit**.

### Headers
Response bevat usage info in headers:
- `x-marea-api-remaining-prepaid-requests`
- `x-marea-api-remaining-subscription-requests`
- `x-marea-api-request-cost`

## 🐛 Debugging

### Check of API werkt:

1. **Bekijk logs:**
```bash
tail -f storage/logs/laravel.log
```

2. **Zoek naar:**
```
Marea API call successful
Loaded real tide data
```

3. **Of error:**
```
Marea API call failed
Marea API exception
```

### Test API direct:

```bash
curl -H "x-marea-api-token: YOUR_KEY" \
  "https://api.marea.ooo/v2/tides?latitude=52.618694&longitude=4.618250&duration=1440"
```

## 🔀 Switchen tussen Real/Simulated

### Gebruik Echte API:
```bash
MAREA_API_ENABLED=true
MAREA_API_KEY=your-key
```

### Gebruik Simulatie:
```bash
MAREA_API_ENABLED=false
# Of verwijder MAREA_API_KEY
```

De app heeft automatisch fallback naar gesimuleerde data bij:
- API errors
- Geen API key
- Rate limits bereikt
- Network issues

## 🎯 Voordelen Petten Zuid Station

1. **Hoogste Accuratesse** - Echte meetdata van fysiek station
2. **Lokaal** - Slechts 18km van Egmond aan Zee
3. **Betrouwbaar** - GESLA3 database (Global Extreme Sea Level Analysis)
4. **Relevant** - Zelfde kustgebied als Egmond
5. **Details** - Precieze tijden en waterstanden
6. **Updates** - Automatisch up-to-date

## ⚠️ Disclaimer

De Marea API disclaimer:
> "NOT SUITABLE FOR NAVIGATIONAL PURPOSES. Marea API does not warrant that the provided data will be free from errors or omissions."

De NH100 app is **een hulpmiddel** - controleer altijd:
- Actuele weersomstandigheden
- Strand toegankelijkheid
- Lokale condities

## 🚀 Live Status

De app toont de data bron in de UI:

- ✓ **Marea API - Station Petten Zuid** - Beste! Echte meetdata (primair)
- ✓ **Marea API (FES2014 Model)** - Goed! Global model data (fallback)
- ✓ **Marea API (EOT20 Model)** - Goed! Alternatief model
- 🎲 **Gesimuleerd** - Fallback bij problemen

**Locatie Info:**
- Station: Petten Zuid, Nederland
- Coördinaten: 52°46'21.6"N, 4°38'58.9"E
- Afstand: ~18km noordelijk van Egmond aan Zee

## 📧 Support

Voor API vragen:
- Email: api@marea.ooo
- Website: https://api.marea.ooo
- Docs: https://api.marea.ooo/docs (OpenAPI spec)

Voor research doeleinden zijn custom plans beschikbaar.

## 🎉 Klaar!

Je NH100 Route Planner gebruikt nu echte getijden data! 🌊🚴‍♂️

