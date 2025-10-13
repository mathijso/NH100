// NH100 Route Planner Configuratie

const CONFIG = {
    // Locatie voor getijden data (Egmond aan Zee)
    location: {
        name: 'Egmond aan Zee',
        lat: 52.6167,
        lon: 4.6333
    },

    // API instellingen
    api: {
        // Zet op true om echte API data te gebruiken
        // Zet op false om gesimuleerde data te gebruiken
        useRealAPI: false,

        // WorldTides API (optioneel)
        worldTides: {
            endpoint: 'https://www.worldtides.info/api/v3',
            key: '' // Vul je API key hier in
        },

        // Rijkswaterstaat API (gratis, geen key nodig)
        rijkswaterstaat: {
            endpoint: 'https://waterinfo.rws.nl/api/v1',
            locationCode: 'EGMAZE' // Egmond aan Zee
        }
    },

    // Route restricties
    restrictions: {
        // Duinreservaat moet verlaten zijn voor deze tijd
        duneExitTime: '10:30',

        // Strandgedeelte tijdvenster
        beachStart: 10, // uur
        beachEnd: 12,    // uur

        // Eb moet binnen dit venster zijn
        tideWindowBefore: 2, // uur voor eb
        tideWindowAfter: 2,  // uur na eb

        // Zomer restricties (strand alleen voor 10:00)
        summerBeachLimit: 10 // uur
    },

    // UI instellingen
    ui: {
        calendarDays: 30,
        recommendedStartTime: '07:00',
        animationDuration: 500
    }
};

// Export voor gebruik in andere bestanden
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}

