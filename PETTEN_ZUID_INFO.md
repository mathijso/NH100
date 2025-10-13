# 📍 Station Petten Zuid - Getijden Data

## Overzicht

De NH100 Route Planner gebruikt **Meetstation Petten Zuid** voor de meest accurate getijden voorspellingen voor Egmond aan Zee.

## 📊 Station Details

**Station ID:** `GESLA3:eb1c86d10b`

**Naam:** Petten Zuid

**Land:** Netherlands (Nederland)

**Coördinaten:**
- Latitude: 52.772657° (52°46'21.6"N)
- Longitude: 4.649702° (4°38'58.9"E)

**Database:** GESLA3 (Global Extreme Sea Level Analysis)

## 🗺️ Locatie

```
          Petten Zuid ⬤ (Station)
                |
                | ~18 km
                ↓
        Egmond aan Zee ⬤ (NH100 Start)
```

**Afstand:** ~18 kilometer noordelijk van Egmond aan Zee

**Kustgebied:** Zelfde Noord-Hollandse kust, zeer relevant voor NH100 route

## ✅ Waarom Petten Zuid?

### 1. **Dichtste Meetstation**
- Slechts 18km van Egmond aan Zee
- Zelfde kustgebied en getijden-patronen
- Meest relevante meetdata voor de NH100 route

### 2. **Echte Meetdata**
- Fysiek meetstation met sensoren
- Historische data van werkelijke metingen
- Veel accurater dan globale modellen

### 3. **GESLA3 Database**
- Professionele wetenschappelijke database
- Global Extreme Sea Level Analysis
- Peer-reviewed en betrouwbaar

### 4. **Actueel**
- Continue monitoring
- Real-time voorspellingen
- Automatisch up-to-date

## 🔄 Fallback Strategie

Als Petten Zuid station niet beschikbaar is, gebruikt de app automatisch:

1. **Coördinaten-gebaseerde voorspelling**
   - Egmond aan Zee: 52.618694°N, 4.618250°E
   - FES2014 global ocean model
   - Nog steeds accuraat, maar minder specifiek

2. **Gesimuleerde Data**
   - Als laatste fallback
   - Bij API problemen of geen connectie
   - Realistische patronen voor planning

## 📈 Vergelijking Data Sources

| Bron | Accuratesse | Relevantie | Beschikbaarheid |
|------|------------|-----------|----------------|
| **Station Petten Zuid** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Via Marea API |
| FES2014 Model | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | Via Marea API |
| Gesimuleerd | ⭐⭐ | ⭐⭐⭐ | Altijd |

## 🌊 Getijden Informatie

**Data die het station levert:**
- Eb tijden (Low Tide)
- Vloed tijden (High Tide)
- Waterstanden in meters (MSL - Mean Sea Level)
- Voorspellingen tot 35 dagen vooruit

**Update frequentie:**
- API call elke 6 uur (via caching)
- Voorspellingen worden automatisch herberekend

## 🔧 Technische Details

### API Call

```bash
curl -H "x-marea-api-token: YOUR_KEY" \
  "https://api.marea.ooo/v2/tides?station_id=GESLA3:eb1c86d10b&duration=50400&interval=60"
```

### Parameters

```php
[
    'station_id' => 'GESLA3:eb1c86d10b',  // Petten Zuid
    'duration' => 50400,                   // 35 dagen (in minuten)
    'interval' => 60,                      // 1 uur tussen metingen
    'datum' => 'MSL',                     // Mean Sea Level reference
]
```

### Response Example

```json
{
  "status": 200,
  "source": "STATION",
  "origin": {
    "station": {
      "id": "GESLA3:eb1c86d10b",
      "name": "Petten Zuid",
      "provider": "GESLA3"
    },
    "latitude": 52.772657,
    "longitude": 4.649702
  },
  "extremes": [
    {
      "timestamp": 1737123600,
      "datetime": "2025-01-17T09:00:00+00:00",
      "height": 0.35,
      "state": "LOW TIDE"
    },
    {
      "timestamp": 1737145200,
      "datetime": "2025-01-17T15:00:00+00:00",
      "height": 1.85,
      "state": "HIGH TIDE"
    }
  ]
}
```

## 📍 Andere Nabijgelegen Stations

Voor referentie, andere stations in de buurt:

| Station | ID | Afstand |
|---------|-----|---------|
| **Petten Zuid** | GESLA3:eb1c86d10b | **18km (IN GEBRUIK)** |
| IJmuiden | (verschillende IDs) | ~30km |
| Den Helder | (verschillende IDs) | ~45km |

Petten Zuid is het beste compromis tussen nabijheid en datakwaliteit voor Egmond aan Zee.

## 🔍 Verificatie

### Check welke bron wordt gebruikt:

1. Start de app: `npm run dev` + `php artisan serve`
2. Open: http://localhost:8000
3. Scroll naar beneden
4. Zoek: **"✓ Marea API - Station Petten Zuid"**

### Check logs:

```bash
tail -f storage/logs/laravel.log | grep "Marea"
```

Zoek naar:
```
Marea API station call successful
station_id: GESLA3:eb1c86d10b
source: STATION
```

## 🎯 Resultaat voor NH100

Door Petten Zuid te gebruiken krijg je:

✅ **Meest accurate getijden data** voor het strandgedeelte
✅ **Lokale metingen** van hetzelfde kustgebied
✅ **Betrouwbare voorspellingen** voor 35 dagen vooruit
✅ **Automatische updates** via Marea API
✅ **Fallback opties** bij problemen

Perfect voor het plannen van je NH100 route! 🚴‍♂️🌊

## 📚 Meer Info

- **Marea API Docs:** https://api.marea.ooo/docs
- **GESLA Database:** https://gesla.org
- **Station Explorer:** https://marea.ooo/en/map

---

**Configuratie:**
```bash
# In .env
MAREA_STATION_ID=GESLA3:eb1c86d10b
```

**Locatie check:**
```bash
curl "https://api.marea.ooo/v2/stations/GESLA3:eb1c86d10b" \
  -H "x-marea-api-token: YOUR_KEY"
```

