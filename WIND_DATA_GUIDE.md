# Wind Data Integratie - NH100 Route Planner

Deze applicatie toont real-time windgegevens voor Egmond aan Zee via een windroos visualisatie.

## Configuratie

### 1. Windy API Key verkrijgen

1. Ga naar [Windy API Keys](https://api.windy.com/keys)
2. Log in of maak een account aan
3. Genereer een nieuwe "Point Forecast API" key
4. Kopieer de API key

### 2. API Key configureren

Voeg de API key toe aan je `.env` bestand:

```bash
WINDY_API_KEY=jouw_api_key_hier
```

### 3. Cache legen (indien nodig)

Als je problemen hebt met de windgegevens, clear de cache:

```bash
php artisan cache:clear
php artisan config:clear
```

## Technische Details

### Coördinaten
- **Locatie**: Egmond aan Zee
- **Coördinaten**: 52°37'02.6"N 4°37'04.7"E
- **Decimaal**: 52.617389, 4.617972

### API Configuratie
- **Model**: GFS (Global Forecast System)
- **Parameters**: wind, windGust
- **Level**: surface
- **Cache duur**: 15 minuten

### Weergegeven gegevens
- Windrichting (graden en kompasrichting)
- Windsnelheid (km/h)
- Beaufort schaal (0-12)
- Windstoten (indien beschikbaar)

## Architectuur

### Services
- **`WindDataService`** (`app/Services/WindDataService.php`)
  - Haalt windgegevens op van Windy API
  - Berekent windrichting, snelheid en Beaufort schaal
  - Cached resultaten voor 15 minuten

### Livewire Componenten
- **`WindData`** (`app/Livewire/WindData.php`)
  - Laadt windgegevens via de service
  - Maakt data beschikbaar voor frontend

### Frontend
- **Windroos SVG**: Visuele weergave met geanimeerde windpijl
- **Alpine.js component**: Beheert state en updates
- **Responsive design**: Werkt op alle schermformaten

## API Limieten

De Windy API heeft verschillende rate limits afhankelijk van je plan:
- **Free tier**: ~5,000 calls/maand
- **Cache**: 15 minuten (max 2,880 calls/maand voor real-time updates)

## Troubleshooting

### Geen windgegevens zichtbaar

1. **Controleer API key**:
   ```bash
   php artisan tinker
   >>> config('services.windy.api_key')
   ```

2. **Controleer logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Test API direct**:
   ```bash
   php artisan tinker
   >>> app(\App\Services\WindDataService::class)->getCurrentWindData()
   ```

### API Error 400
- Controleer of de API key correct is
- Controleer of de coördinaten geldig zijn

### API Error 204
- Het GFS model heeft geen data voor de gevraagde parameters
- Probeer een ander model (iconEu voor Europa)

## Windroos Visualisatie

De windroos toont:
- **Blauwe kompasrichtingen**: N (Noord) prominent, O/Z/W subtiel
- **Rode windpijl**: Geeft de windrichting aan (waar wind VANDAAN komt)
- **Animatie**: Soepele rotatie bij windrichtingverandering
- **Centrum punt**: Referentiepunt van de windroos

## Updates

De windgegevens worden automatisch ververst:
- Bij pagina laden
- Elke 15 minuten (via cache)
- Handmatig via refresh (indien geïmplementeerd)

## API Documentatie

Volledige Windy API documentatie: https://api.windy.com/point-forecast/docs

