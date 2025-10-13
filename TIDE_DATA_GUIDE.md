# Getijden Data Handleiding

## Overzicht

De NH100 Route Planner gebruikt getijdendata van Rijkswaterstaat in CSV formaat. Deze data wordt lokaal opgeslagen en automatisch ingeladen door de applicatie.

## Data Structuur

### Locatie
- **Directory**: `public/data/`
- **Bestandsnaam formaat**: `YYYY.csv` (bijv. `2025.csv`, `2026.csv`)

### CSV Formaat

De CSV bestanden gebruiken het Rijkswaterstaat formaat met semicolon als scheidingsteken:

```csv
Datum;Nederlandsetijd;Hoogwater/laagwater;Waarde
30/12/2025;19:28;LW;-67 cm
30/12/2025;11:36;HW;84 cm
29/12/2025;18:23;LW;-69 cm
29/12/2025;10:29;HW;80 cm
```

### Kolommen

1. **Datum**: DD/MM/YYYY formaat
2. **Nederlandsetijd**: HH:MM formaat
3. **Hoogwater/laagwater**: 
   - `HW` = Hoogwater (High Water)
   - `LW` = Laagwater (Low Water)
   - Andere waardes (M, K, etc.) worden genegeerd
4. **Waarde**: Hoogte in centimeters (bijv. "-67 cm", "84 cm")

## Nieuw Jaar Toevoegen

### Stappen

1. **Verkrijg de data**
   - Bezoek de Rijkswaterstaat website voor getijdendata
   - Download de data voor het gewenste jaar
   - Zorg dat het formaat overeenkomt met bovenstaande structuur

2. **Plaats het bestand**
   ```bash
   cp /pad/naar/2026.csv public/data/2026.csv
   ```

3. **Verifieer het formaat**
   - Open het bestand en controleer of het semicolon-gescheiden is
   - Controleer of de header correct is
   - Zorg dat datums in DD/MM/YYYY formaat staan

4. **Test de applicatie**
   - De applicatie detecteert automatisch nieuwe jaren
   - Geen code aanpassingen nodig
   - Herstart eventueel de server om cache te wissen

### Automatische Detectie

De applicatie scant automatisch de `public/data/` directory voor alle `.csv` bestanden met numerieke namen. Elk bestand wordt automatisch ingeladen en beschikbaar gemaakt.

## Technische Details

### TideDataService

De `App\Services\TideDataService` class verzorgt:
- Inlezen van CSV bestanden
- Parsen van Rijkswaterstaat formaat
- Conversie van centimeters naar meters
- Filteren op datumbereik
- Sorteren op tijdstip

### Data Conversie

- **Input**: Hoogte in centimeters (bijv. "-67 cm", "84 cm")
- **Output**: 
  - `height`: Meters (bijv. -0.67, 0.84)
  - `height_cm`: Originele centimeters (bijv. -67, 84)

### Data Caching

De CSV bestanden worden bij elke request opnieuw ingelezen. Voor productie gebruik kan Laravel caching worden toegevoegd aan de `TideDataService::parseCsvFile()` methode.

## Troubleshooting

### Geen data zichtbaar

1. Controleer of het CSV bestand in `public/data/` staat
2. Verifieer het bestandsformaat en encoding (UTF-8)
3. Check de Laravel logs: `storage/logs/laravel.log`
4. Clear cache: `php artisan optimize:clear`

### Foute data

1. Controleer het CSV formaat (semicolon, juiste kolommen)
2. Verifieer datum formaat (DD/MM/YYYY)
3. Check tijd formaat (HH:MM)
4. Zorg dat HW/LW correct zijn gespeld

### Performance issues

Voor grote datasets (meerdere jaren):
1. Overweeg Laravel caching toe te voegen
2. Gebruik de `getTidesForDateRange()` methode voor specifieke periodes
3. Optimaliseer CSV parsing indien nodig

## Voordelen van CSV Aanpak

✅ **Betrouwbaar**: Geen afhankelijkheid van externe API's  
✅ **Snel**: Lokale data, geen netwerkvertraging  
✅ **Voorspelbaar**: Data verandert niet, consistente resultaten  
✅ **Schaalbaar**: Eenvoudig nieuwe jaren toevoegen  
✅ **Offline**: Werkt zonder internetverbinding  

## Data Bronnen

- **Rijkswaterstaat**: Officiële getijdendata voor Nederlandse kust
- **Waterinfo.rws.nl**: Download historische en toekomstige getijden
- **Format**: Standaard Rijkswaterstaat CSV export

## Voorbeeld Workflow

```bash
# 1. Download nieuwe data
wget "https://waterinfo.rws.nl/..." -O 2026.csv

# 2. Plaats in data directory
mv 2026.csv public/data/

# 3. Verifieer formaat
head -5 public/data/2026.csv

# 4. Clear cache (optioneel)
php artisan optimize:clear

# 5. Test applicatie
php artisan serve
```

## Contact

Voor vragen over de data of het formaat, neem contact op met de ontwikkelaar.

