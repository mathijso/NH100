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

    // 2) Amber - partial overlap (~>20 and <120)
    // Example from user: 2025-10-16 tides
    // Vloed: 00:17, Eb: 08:24, Vloed: 12:50, Eb: 20:16
    // 10:00-12:00 intersects Eb window (06:24-10:24) by ~24 min => amber
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
        assert(res.status === 'amber', 'Expected amber for partial overlap on 2025-10-16');
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

    // 4) Green - eb between 09:00 and 12:00
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
        assert(res.status === 'green', 'Expected green when Eb is between 09:00–12:00');
    }

    // 5) Amber/Red near recent Vloed before 10:00
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
        assert(res.status === 'amber' || res.status === 'red', 'Expected amber/red when recent Vloed before window');
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


