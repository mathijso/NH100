
// Datum helper functies
export function formatDate(date) {
    return date.toLocaleDateString('nl-NL', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

export function formatDateShort(date) {
    return date.toLocaleDateString('nl-NL', {
        day: 'numeric',
        month: 'short'
    });
}

export function formatTime(date) {
    return date.toLocaleTimeString('nl-NL', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

export function getDayName(date) {
    return date.toLocaleDateString('nl-NL', { weekday: 'short' });
}

export function getDayOfWeek(date) {
    return date.getDay();
}

// Check of datum in winter seizoen is (1 okt - 1 mei)
export function isWinterSeason(date) {
    const month = date.getMonth() + 1;
    return month >= 10 || month <= 4;
}

// Haal alle getijden voor een dag op
export function getTidesForDay(date, tides) {
    const dayTides = tides.filter(tide => {
        const tideDate = new Date(tide.time);
        return tideDate.toDateString() === date.toDateString();
    });

    const lowTides = dayTides.filter(t => t.type === 'Low')
        .map(t => ({ ...t, time: new Date(t.time) }))
        .sort((a, b) => a.time - b.time);

    const highTides = dayTides.filter(t => t.type === 'High')
        .map(t => ({ ...t, time: new Date(t.time) }))
        .sort((a, b) => a.time - b.time);

    return { lowTides, highTides };
}

// Bereken of de route berijdbaar is op een bepaalde datum
export function isRouteRideable(date, tides) {
    // Check 1: Winter seizoen check (1 okt - 1 mei is hele dag OK)
    const winterSeason = isWinterSeason(date);
    const { lowTides, highTides } = getTidesForDay(date, tides);

    // Combineer en sorteer alle getijden chronologisch
    const allTides = [
        ...lowTides.map(t => ({ ...t, tideType: 'Eb' })),
        ...highTides.map(t => ({ ...t, tideType: 'Vloed' }))
    ].sort((a, b) => a.time - b.time);

    if (!winterSeason) {
        // Zomer: alleen voor 10:00 toegankelijk, maar we rijden 10:00-12:00
        return {
            rideable: false,
            status: 'red',
            reason: 'Strand alleen voor 10:00 toegankelijk',
            lowTides: lowTides,
            highTides: highTides,
            allTides: allTides
        };
    }

    // Voorrangsregels gebruiker (aangescherpt):
    // Regel 1) Groen als Eb valt tussen 08:00 en 13:00
    const nine = new Date(date);
    nine.setHours(8, 0, 0, 0);
    const twelve = new Date(date);
    twelve.setHours(14, 0, 0, 0);

    const ebInMorningWindow = lowTides.find(t => t.time >= nine && t.time <= twelve);
    if (ebInMorningWindow) {
        return {
            rideable: true,
            status: 'green',
            reason: `Eb om ${formatTime(ebInMorningWindow.time)} (tussen 08:00–13:00)`,
            lowTides: lowTides,
            highTides: highTides,
            allTides: allTides
        };
    }

    // Regel 2) Aangescherpt: beoordeel nabijheid van laatste vloed vóór 10:30
    const windowStart = new Date(date);
    windowStart.setHours(10, 30, 0, 0);
    const highsBeforeWindow = highTides.filter(t => t.time <= windowStart).sort((a, b) => a.time - b.time);
    const lastHighBeforeWindow = highsBeforeWindow.length > 0 ? highsBeforeWindow[highsBeforeWindow.length - 1] : null;
    if (lastHighBeforeWindow) {
        const hoursSinceHigh = (windowStart - lastHighBeforeWindow.time) / (1000 * 60 * 60);
        if (hoursSinceHigh < 2) {
            return {
                rideable: false,
                status: 'red',
                reason: `Te dicht op vloed`,
                lowTides: lowTides,
                highTides: highTides,
                allTides: allTides
            };
        }

    }

    // Check 3 (fallback): Getijden-overlap met venster (10:00-12:00)
    const windowEnd = new Date(date);
    windowEnd.setHours(12, 0, 0, 0);

    // Bepaal overlap met eb-venster (2 uur voor/na eb)
    let bestOverlapMinutes = 0;
    let bestLowTide = null;

    for (let lowTide of lowTides) {
        const ebStart = new Date(lowTide.time);
        ebStart.setHours(ebStart.getHours() - 2);
        const ebEnd = new Date(lowTide.time);
        ebEnd.setHours(ebEnd.getHours() + 2);

        const overlapStart = new Date(Math.max(windowStart, ebStart));
        const overlapEnd = new Date(Math.min(windowEnd, ebEnd));
        const overlapMinutes = Math.max(0, (overlapEnd - overlapStart) / (1000 * 60));

        if (overlapMinutes > bestOverlapMinutes) {
            bestOverlapMinutes = overlapMinutes;
            bestLowTide = lowTide;
        }
    }

    // Bepaal status op basis van overlap (fallback regels)
    // - groen: volledige 120 minuten overlap
    // - amber: tenminste 20 minuten maar minder dan 120 minuten
    // - rood: minder dan 20 minuten of geen data
    if (bestOverlapMinutes >= 120) {
        return {
            rideable: true,
            status: 'green',
            reason: `Het is eb genoeg`,
            lowTides: lowTides,
            highTides: highTides,
            allTides: allTides
        };
    }

    if (bestOverlapMinutes >= 20) {
        return {
            rideable: false,
            status: 'amber',
            reason: `Het kan misschien`,
            lowTides: lowTides,
            highTides: highTides,
            allTides: allTides
        };
    }

    if (lowTides.length > 0) {
        return {
            rideable: false,
            status: 'red',
            reason: `Het is te vloed`,
            lowTides: lowTides,
            highTides: highTides,
            allTides: allTides
        };
    }

    return {
        rideable: false,
        status: 'red',
        reason: 'Geen data beschikbaar',
        lowTides: [],
        highTides: [],
        allTides: []
    };
}

// Genereer gesimuleerde getijden data
export function generateSimulatedTides() {
    const tides = [];
    const now = new Date();
    now.setHours(0, 0, 0, 0);

    for (let day = 0; day < 35; day++) {
        const currentDate = new Date(now);
        currentDate.setDate(now.getDate() + day);

        const timeShift = day * 50; // minuten shift per dag

        // Eerste eb
        const tide1 = new Date(currentDate);
        tide1.setHours(6, 0 + timeShift % 60, 0);
        tide1.setHours(tide1.getHours() + Math.floor(timeShift / 60));

        // Tweede eb (ongeveer 12.5 uur later)
        const tide2 = new Date(currentDate);
        tide2.setHours(18, 30 + timeShift % 60, 0);
        tide2.setHours(tide2.getHours() + Math.floor(timeShift / 60));

        tides.push({
            time: tide1.toISOString(),
            type: 'Low',
            height: 0.3 + Math.random() * 0.2
        });

        tides.push({
            time: tide2.toISOString(),
            type: 'Low',
            height: 0.3 + Math.random() * 0.2
        });

        // Hoog water tussen de eb tijden
        const high1 = new Date(currentDate);
        high1.setHours(0, 0 + timeShift % 60, 0);
        high1.setHours(high1.getHours() + Math.floor(timeShift / 60));

        const high2 = new Date(currentDate);
        high2.setHours(12, 30 + timeShift % 60, 0);
        high2.setHours(high2.getHours() + Math.floor(timeShift / 60));

        tides.push({
            time: high1.toISOString(),
            type: 'High',
            height: 1.8 + Math.random() * 0.4
        });

        tides.push({
            time: high2.toISOString(),
            type: 'High',
            height: 1.8 + Math.random() * 0.4
        });
    }

    return tides.sort((a, b) => new Date(a.time) - new Date(b.time));
}

// Fetch getijden data (voor nu gesimuleerd, maar kan later echte API gebruiken)
export async function fetchTideData() {
    // TODO: In de toekomst kunnen we hier een echte API aanroepen
    // via een Laravel backend endpoint die Livewire gebruikt
    return generateSimulatedTides();
}

// Export alle functies als window object voor gebruik in Alpine components
if (typeof window !== 'undefined') {
    window.NH100 = {
        formatDate,
        formatDateShort,
        formatTime,
        getDayName,
        getDayOfWeek,
        isWinterSeason,
        getTidesForDay,
        isRouteRideable,
        generateSimulatedTides,
        fetchTideData
    };
}

