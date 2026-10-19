// Simple Node test harness for nh100.js logic
import { isRouteRideable } from '../resources/js/nh100.js';

function parse(timeStr) {
    // timeStr like '2025-10-16 08:24'
    const [datePart, timePart] = timeStr.split(' ');
    const [y, m, d] = datePart.split('-').map(Number);
    const [hh, mm] = timePart.split(':').map(Number);
    const dt = new Date(y, m - 1, d, hh, mm, 0, 0);
    return dt.toISOString();
}

function tide(timeIso, type) {
    return { time: timeIso, type, height: 0 };
}

function assert(condition, message) {
    if (!condition) {
        throw new Error('Assertion failed: ' + message);
    }
}

// Build tides list for a specific day
function buildDayTides(dateStr, pairs) {
    // pairs: [ ['Eb','08:24'], ['Vloed','12:50'], ... ]
    return pairs.map(([nlType, hhmm]) => {
        const iso = parse(`${dateStr} ${hhmm}`);
        const type = nlType === 'Eb' ? 'Low' : 'High';
        return tide(iso, type);
    });
}

// Merge with neighboring days (empty here, we only test given day)
function buildTidesForTest(list) {
    return list;
}

function run() {
    // Test thresholds
    // 1) Green - full 120 min overlap
    {
        const date = new Date(2025, 9, 13); // 13 Oct 2025
        const tides = buildTidesForTest(
            buildDayTides('2025-10-13', [['Eb', '10:30']])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'green', 'Expected green for full overlap');
    }

    // 2) Groen - Eb om 08:24 valt sinds 28-02-2026 binnen 08:00–15:00
    // Voorbeeld 2025-10-16: Vloed 00:17, Eb 08:24, Vloed 12:50, Eb 20:16
    {
        const date = new Date(2025, 9, 16); // 16 Oct 2025
        const tides = buildTidesForTest(
            buildDayTides('2025-10-16', [
                ['Vloed', '00:17'],
                ['Eb', '08:24'],
                ['Vloed', '12:50'],
                ['Eb', '20:16'],
            ])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'green', 'Expected green for Eb at 08:24 on 2025-10-16');
    }

    // 2b) Amber - Eb 15:30: eb-venster (4u voor / 2u na) overlapt 10:30–12:00 maar 30 minuten
    {
        const date = new Date(2025, 10, 4);
        const tides = buildTidesForTest(
            buildDayTides('2025-11-04', [
                ['Eb', '03:00'],
                ['Vloed', '07:30'],
                ['Eb', '15:30'],
                ['Vloed', '20:00'],
            ])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'amber', 'Expected amber for 30 min overlap');
    }

    // 2c) Rood - geen getijdendata in de winter
    {
        const res = isRouteRideable(new Date(2025, 10, 5), []);
        assert(res.status === 'red' && res.reason === 'Geen data beschikbaar', 'Expected red without data');
    }

    // 2d) Rood - zomer, ongeacht getij
    {
        const date = new Date(2025, 6, 1);
        const tides = buildTidesForTest(buildDayTides('2025-07-01', [['Eb', '10:30']]));
        const res = isRouteRideable(date, tides);
        assert(res.status === 'red', 'Expected red in summer season');
    }

    // 3) Red - negligible overlap (<20 min)
    {
        const date = new Date(2025, 9, 14); // 14 Oct 2025
        const tides = buildTidesForTest(
            buildDayTides('2025-10-14', [['Eb', '07:35']]) // eb 07:35 → window 10-12: 2h window around eb is 05:35-09:35, no overlap
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'red', 'Expected red for no/low overlap');
    }

    // 4) Green - eb between 08:00 and 15:00
    {
        const date = new Date(2025, 9, 20);
        const tides = buildTidesForTest(
            buildDayTides('2025-10-20', [
                ['Vloed', '01:40'],
                ['Eb', '10:00'],
                ['Vloed', '13:58'],
                ['Eb', '21:38'],
            ])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'green', 'Expected green when Eb is between 08:00–15:00');
    }

    // 5) Groen - Eb 14:59 valt binnen 08:00–15:00 (vóór 28-02-2026 was dit amber/rood)
    {
        const date = new Date(2025, 9, 11); // example like user: Vloed 07:09
        const tides = buildTidesForTest(
            buildDayTides('2025-10-11', [
                ['Eb', '02:36'],
                ['Vloed', '07:09'],
                ['Eb', '14:59'],
                ['Vloed', '19:26']
            ])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'green', 'Expected green when Eb at 14:59');
    }

    // 6) Red when very recent Vloed (e.g., ~08:50 → <2h before 10:00)
    {
        const date = new Date(2025, 9, 13); // similar pattern user says should be not good
        const tides = buildTidesForTest(
            buildDayTides('2025-10-13', [
                ['Eb', '04:15'],
                ['Vloed', '08:51'],
                ['Eb', '16:38'],
                ['Vloed', '21:08']
            ])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'red', 'Expected red when vloed <2h before 10:00');
    }

    // 7) Likely red when pattern similar to Oct 12 (Vloed ~07:56, Eb 15:47)
    {
        const date = new Date(2025, 9, 12);
        const tides = buildTidesForTest(
            buildDayTides('2025-10-12', [
                ['Eb', '03:22'],
                ['Vloed', '07:56'],
                ['Eb', '15:47'],
                ['Vloed', '20:12']
            ])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'red' || res.status === 'amber', 'Expected not green for Oct 12 pattern');
    }

    console.log('✅ nh100 logic tests passed');
}

run();


