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


## Data Bronnen

De applicatie gebruikt getijdendata van Rijkswaterstaat in CSV formaat:
- Data bestanden bevinden zich in `public/data/`
- Formaat: `YYYY.csv` (bijv. `2025.csv`, `2026.csv`)
- De applicatie ondersteunt automatisch meerdere jaren

### CSV Formaat
```
Datum;Nederlandsetijd;Hoogwater/laagwater;Waarde
30/12/2025;19:28;LW;-67 cm
30/12/2025;11:36;HW;84 cm
```

### Nieuw jaar toevoegen
1. Plaats nieuw CSV bestand in `public/data/` met naam `YYYY.csv`
2. De applicatie herkent automatisch nieuwe jaren
3. Geen code aanpassingen nodig

## Todo
- [x] Gebruik Rijkswaterstaat CSV data voor betrouwbare getijden informatie
- [ ] Weer voorspelling integratie
- [ ] Wind data (belangrijk voor strand)
- [ ] GPS track export
- [ ] Kalender export (iCal) ics file
- [ ] Notificaties voor goede dagen
- [ ] Delen functionaliteit
- [ ] PWA (Progressive Web App) support
- [ ] H100 variant toevoegen
 